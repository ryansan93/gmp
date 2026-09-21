<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intercompany Pakan - sisi PENERIMA (instance ini = pemegang fisik).
 *
 * Endpoint machine-to-machine dipanggil oleh instance GMP lain (lewat
 * IntercompanyClient::post() di sisi pengirim, application/modules/intercompany/
 * controllers/IntercompanyPakan.php::kirimKePartner()). TIDAK ada konfirmasi manual
 * staff di sisi ini (sesuai keputusan: otomatis begitu pengirim simpan) - begitu
 * signature & idempotency lolos, stok REAL langsung tercatat.
 *
 * Beda dgn controller api/ lain di app ini (Mobile.php dkk) yang TANPA autentikasi
 * sama sekali - endpoint ini WAJIB diverifikasi HMAC karena langsung menulis stok
 * fisik & baris jurnal_manajemen tanpa review manusia. Lihat plan: intercompany
 * pakan - stok rill vs jurnal manajemen lintas-GMP.
 */
class IntercompanyPakanTerima extends API_Controller {

    /**
     * TODO (perlu dikonfirmasi tim akuntansi sebelum live): kode COA yang dipakai
     * utk baris jurnal_manajemen (shadow, bukan hutang nyata - jadi COA di sini
     * hanya utk keperluan visibilitas biaya internal, TIDAK boleh diikutkan
     * perhitungan Neraca/Laba Rugi resmi karena jurnal_manajemen bukan tabel jurnal
     * yang dibaca laporan keuangan manapun).
     */
    const COA_ASAL_DEFAULT = null;
    const COA_TUJUAN_DEFAULT = null;

    public function terima()
    {
        $raw_body = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : null;
        $timestamp = isset($_SERVER['HTTP_X_TIMESTAMP']) ? $_SERVER['HTTP_X_TIMESTAMP'] : null;

        $result = array('status' => 0, 'message' => '');

        try {
            $payload = json_decode($raw_body, true);
            if (empty($payload) || empty($payload['referensi_idempotency'])) {
                throw new Exception('Payload tidak valid / referensi_idempotency kosong.');
            }

            // Signature diverifikasi terhadap kredensial partner mana pun yang aktif -
            // endpoint ini generic (dipanggil partner manapun), jadi tidak tahu di muka
            // kode_partner pengirim; payload TIDAK membawa kode_partner sendiri (sengaja,
            // supaya pengirim tidak bisa mengaku2 jadi partner lain) - signature dicocokkan
            // ke SETIAP shared_secret partner aktif sampai salah satu cocok.
            $this->load->library('IntercompanyClient');

            $m_partner = new \Model\Storage\IntercompanyPartner_model();
            $partners = $m_partner->where('status', 1)->get();

            $partner_cocok = null;
            foreach ($partners as $p) {
                $cek = IntercompanyClient::verifikasi($raw_body, $timestamp, $signature, $p->shared_secret);
                if ($cek['valid']) {
                    $partner_cocok = $p;
                    break;
                }
            }

            if (empty($partner_cocok)) {
                throw new Exception('Signature tidak valid utk partner manapun yang terdaftar/aktif.');
            }

            // Idempotency: kalau referensi ini sudah pernah diproses, balas sukses tanpa
            // memproses ulang (mencegah dobel-catat akibat retry pengirim).
            $m_log = new \Model\Storage\IntercompanyPakanLog_model();
            $sudah_ada = $m_log->where('referensi_idempotency', $payload['referensi_idempotency'])->first();

            $tbl_name_asal = isset($payload['tbl_name_asal']) ? $payload['tbl_name_asal'] : null;
            $tbl_id_asal = isset($payload['tbl_id_asal']) ? $payload['tbl_id_asal'] : null;
            $kode_supplier = isset($payload['kode_supplier']) ? $payload['kode_supplier'] : null;

            if (!empty($sudah_ada)) {
                $no_transaksi_lama = null;
                if ($sudah_ada->tbl_name_tujuan == 'order_pakan' && !empty($sudah_ada->tbl_id_tujuan)) {
                    $m_op_lama = new \Model\Storage\OrderPakan_model();
                    $d_op_lama = $m_op_lama->where('id', $sudah_ada->tbl_id_tujuan)->first();
                    $no_transaksi_lama = !empty($d_op_lama) ? $d_op_lama->no_order : null;
                }

                $result['status'] = 1;
                $result['message'] = 'Sudah pernah diproses sebelumnya (idempotent).';
                $result['content'] = array('duplikat' => true, 'no_transaksi' => $no_transaksi_lama);
                return $this->balas($result);
            }

            $tanggal = $payload['tanggal'];
            $kode_barang = $payload['kode_barang'];
            $jumlah = $payload['jumlah'];
            $harga = isset($payload['harga']) ? $payload['harga'] : null;
            $kode_gudang_tujuan = isset($payload['kode_gudang_tujuan']) ? $payload['kode_gudang_tujuan'] : null;

            // Semua insert di bawah (order_pakan s.d. jurnal_manajemen) dibungkus 1 transaksi -
            // supaya kalau ada 1 langkah gagal di tengah (mis. tipe kolom tidak cocok), SEMUA
            // di-rollback, tidak menyisakan order_pakan/kirim_pakan "yatim" tanpa detail seperti
            // yg sempat kejadian. Pakai getConnection()->transaction() (bukan Facade DB::), krn
            // project ini cuma pakai Capsule Manager langsung - pola sama spt LembarKerjaHpp.php.
            $result['content'] = $m_log->getConnection()->transaction(function () use (
                $partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier,
                $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $m_log
            ) {
                return $this->prosesTerima($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier, $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $m_log);
            });

            $result['status'] = 1;
            $result['message'] = 'Diterima & tercatat.';
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $this->balas($result);
    }

    /**
     * Isi asli terima() (semua insert) - dipisah jadi method sendiri supaya bisa dibungkus
     * $connection->transaction(function() { return $this->prosesTerima(...); }) di caller;
     * closure yg langsung berisi banyak statement jadi sulit dibaca kalau ditaruh inline.
     */
    private function prosesTerima($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier, $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $m_log)
    {
            // Sesuai keputusan: transaksi intercompany dicatat sbg dokumen NYATA di sini
            // (order_pakan/kirim_pakan/terima_pakan asli, BUKAN cuma shadow stok/jurnal) -
            // nomornya pakai konvensi/fungsi getNextNomor() yg SAMA persis dgn Order Pakan
            // asli di instance ini, supaya transaksi ini muncul wajar di layar Order/Kirim/
            // Terima Pakan sendiri. 1 order_pakan dipakai bareng utk semua baris barang dari
            // transaksi asal yg sama (kode_partner+tbl_name_asal+tbl_id_asal) - dicari dulu
            // via intercompany_pakan_log (tbl_name_tujuan='order_pakan') sebelum bikin baru,
            // supaya tidak nge-split 1 transaksi jadi banyak order_pakan.
            $d_order_pakan = $this->cariOrBuatOrderPakan($partner_cocok->kode_partner, $tbl_name_asal, $tbl_id_asal, $tanggal, $kode_supplier);

            $m_opd = new \Model\Storage\OrderPakanDetail_model();
            $m_opd->id_header = $d_order_pakan->id;
            $m_opd->barang = $kode_barang;
            $m_opd->harga = $harga;
            $m_opd->harga_jual = $harga;
            $m_opd->jumlah = $jumlah;
            $m_opd->total = !empty($harga) ? ($harga * $jumlah) : null;
            $m_opd->tujuan_kirim = 'gudang';
            $m_opd->id_tujuan_kirim = $kode_gudang_tujuan;
            $m_opd->save();

            // kirim_pakan + det_kirim_pakan - 1 pasang per barang (mengikuti 1 HTTP call = 1
            // barang dari sisi pengirim), langsung ditandai jenis_kirim 'opks' spt Order Pakan biasa.
            $m_kp = new \Model\Storage\KirimPakan_model();
            $m_kp->no_order = $d_order_pakan->no_order;
            $m_kp->tgl_trans = $tanggal;
            $m_kp->tgl_kirim = $tanggal;
            $m_kp->jenis_kirim = 'opks';
            $m_kp->jenis_tujuan = 'gudang';
            $m_kp->tujuan = $kode_gudang_tujuan;
            $m_kp->asal = 'Intercompany - ' . $partner_cocok->kode_partner;
            $m_kp->save();

            // NB: det_kirim_pakan.jumlah & det_terima_pakan.jumlah bertipe INT di database
            // (beda dgn order_pakan_detail.jumlah yg DECIMAL) - wajib di-cast, kalau tidak
            // driver sqlsrv menolak nilai desimal spt '2500.00' ("Conversion failed...").
            $m_dkp = new \Model\Storage\KirimPakanDetail_model();
            $m_dkp->id_header = $m_kp->id;
            $m_dkp->item = $kode_barang;
            $m_dkp->jumlah = (int) $jumlah;
            $m_dkp->nilai_beli = $harga;
            $m_dkp->nilai_jual = $harga;
            $m_dkp->save();

            // terima_pakan + det_terima_pakan - langsung dianggap sudah diterima (auto, tanpa
            // konfirmasi manual staff - sesuai keputusan yg sudah berlaku utk endpoint ini).
            // TIDAK memanggil SP hitung_stok_pakan_by_transaksi (sesuai keputusan) - baris ini
            // murni dokumen, efek stoknya ditulis manual di langkah "2) Stok REAL" di bawah.
            $m_tp = new \Model\Storage\TerimaPakan_model();
            $m_tp->id_kirim_pakan = $m_kp->id;
            $m_tp->tgl_trans = $tanggal;
            $m_tp->tgl_terima = $tanggal;
            $m_tp->save();

            $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
            $m_dtp->id_header = $m_tp->id;
            $m_dtp->item = $kode_barang;
            $m_dtp->jumlah = (int) $jumlah;
            $m_dtp->save();

            // 1) Log (arah TERIMA) - tbl_name/tbl_id TUJUAN nunjuk ke order_pakan di atas,
            //    dipakai lagi utk grouping barang berikutnya dari transaksi asal yg sama.
            $m_log->arah = 'TERIMA';
            $m_log->kode_partner = $partner_cocok->kode_partner;
            $m_log->referensi_idempotency = $payload['referensi_idempotency'];
            $m_log->kode_barang = $kode_barang;
            $m_log->jml_qty = $jumlah;
            $m_log->harga = $harga;
            $m_log->kode_gudang_tujuan = $kode_gudang_tujuan;
            $m_log->tbl_name_asal = $tbl_name_asal;
            $m_log->tbl_id_asal = $tbl_id_asal;
            $m_log->tbl_name_tujuan = 'order_pakan';
            $m_log->tbl_id_tujuan = $d_order_pakan->id;
            $m_log->status = 'DITERIMA';
            $m_log->waktu_terima = date('Y-m-d H:i:s');
            $m_log->save();

            $id_log = $m_log->id;
            $no_transaksi = $d_order_pakan->no_order;

            // 2) Stok REAL (barang benar-benar ada di gudang instance ini) - tulis langsung
            //    ke det_stok/det_stok_trans, TIDAK lewat SP hitung_stok_pakan_by_transaksi
            //    (SP itu kompleks & sudah ada temuan technical-debt/duplikasi badan di
            //    docs/optimasi_sp_stok.md - risiko terlalu tinggi utk diperluas dgn sumber
            //    baru tanpa pengujian mendalam; di sini cukup "tambah stok masuk" sederhana).
            $m_stok = new \Model\Storage\Stok_model();
            $d_stok = $m_stok->where('periode', $tanggal)->first();
            if (empty($d_stok)) {
                $m_stok->periode = $tanggal;
                $m_stok->tgl_proses = date('Y-m-d H:i:s');
                $m_stok->save();
                $id_header_stok = $m_stok->id;
            } else {
                $id_header_stok = $d_stok->id;
            }

            // NB: kode_gudang di det_stok bertipe INT (FK ke gudang.id), BUKAN string kode -
            // 'kode_gudang_tujuan' yg dikirim pengirim harus berisi gudang.id milik instance
            // INI (penerima), sesuai referensi intercompany_partner_gudang di sisi pengirim.
            // 'jumlah' & 'jml_stok' dua-duanya ada di skema det_stok asli (dikonfirmasi via
            // INFORMATION_SCHEMA 2026-09-17) - 'jumlah' = qty transaksi ini, 'jml_stok' =
            // saldo berjalan. Insert ini TIDAK menghitung saldo berjalan (bukan FIFO spt SP
            // hitung_stok_pakan_by_transaksi) - jml_stok diisi sama dgn jumlah sbg
            // penyederhanaan; perlu direview kalau laporan stok butuh saldo berjalan akurat.
            $m_det_stok = new \Model\Storage\DetStok_model();
            $m_det_stok->id_header = $id_header_stok;
            $m_det_stok->kode_gudang = (int) $kode_gudang_tujuan;
            $m_det_stok->kode_barang = $kode_barang;
            $m_det_stok->jumlah = $jumlah;
            $m_det_stok->jml_stok = $jumlah;
            $m_det_stok->hrg_beli = $harga;
            $m_det_stok->tgl_trans = $tanggal;
            $m_det_stok->jenis_trans = 'masuk';
            $m_det_stok->jenis_barang = 'pakan';
            // NB: 'intercompany_pakan_log' (22 char) muat di det_stok.kode_trans (varchar 25)
            // tapi KEPANJANGAN utk det_stok_trans.kode_trans (varchar 20, cuma muat 20 char) -
            // pakai kode singkat 'ic_pakan_log' yg konsisten & muat di keduanya.
            $m_det_stok->kode_trans = 'ic_pakan_log';
            $m_det_stok->save();

            // det_stok_trans ASLI cuma: id, id_header, kode_trans, jumlah, kode_barang -
            // TIDAK ADA kolom tbl_name di tabel ini (beda dgn det_stok_trans_siklus yg punya).
            // Jangan tambahkan tbl_name di sini, akan error "invalid column name".
            $m_det_stok_trans = new \Model\Storage\DetStokTrans_model();
            $m_det_stok_trans->id_header = $m_det_stok->id;
            $m_det_stok_trans->kode_barang = $kode_barang;
            $m_det_stok_trans->kode_trans = 'ic_pakan_log';
            $m_det_stok_trans->jumlah = $jumlah;
            $m_det_stok_trans->save();

            // NB: tbl_name_tujuan/tbl_id_tujuan baris log ini SENGAJA dibiarkan menunjuk ke
            // order_pakan (diisi di atas) - dipakai lagi utk grouping barang berikutnya dari
            // transaksi asal yg sama (lihat cariOrBuatOrderPakan()). Referensi det_stok cukup
            // dikembalikan lewat $result['content']['id_det_stok'] di bawah, tidak perlu
            // menimpa kolom ini.

            // 3) Jurnal SHADOW (biaya pakan yg dipakai, TANPA hutang nyata - hutang sungguhan
            //    tetap di instance pengirim). Nominal dari payload (instance ini tidak tahu
            //    harga supplier sendiri).
            $m_jurnal_mnj = new \Model\Storage\JurnalManajemen_model();
            $m_jurnal_mnj->tanggal = $tanggal;
            $m_jurnal_mnj->save();

            $m_det_jurnal_mnj = new \Model\Storage\DetJurnalManajemen_model();
            $m_det_jurnal_mnj->id_header = $m_jurnal_mnj->id;
            $m_det_jurnal_mnj->tanggal = $tanggal;
            $m_det_jurnal_mnj->nominal = !empty($harga) ? ($harga * $jumlah) : 0;
            $m_det_jurnal_mnj->coa_asal = self::COA_ASAL_DEFAULT;
            $m_det_jurnal_mnj->coa_tujuan = self::COA_TUJUAN_DEFAULT;
            $m_det_jurnal_mnj->keterangan = 'Pemakaian pakan intercompany dari ' . $partner_cocok->kode_partner;
            $m_det_jurnal_mnj->tbl_name = 'intercompany_pakan_log';
            $m_det_jurnal_mnj->tbl_id = $id_log;
            $m_det_jurnal_mnj->gudang = !empty($kode_gudang_tujuan) ? (int) $kode_gudang_tujuan : null; // kolom asli INT (FK gudang.id)
            $m_det_jurnal_mnj->id_intercompany_log = $id_log;
            $m_det_jurnal_mnj->save();

            return array('id_log' => $id_log, 'id_det_stok' => $m_det_stok->id, 'no_transaksi' => $no_transaksi);
    }

    private function balas($result)
    {
        $http_status = ($result['status'] == 1) ? 200 : 400;
        $this->output->set_status_header($http_status);
        display_json($result);
    }

    /**
     * Cari order_pakan yg sudah dibuat utk transaksi asal (kode_partner + tbl_name_asal +
     * tbl_id_asal) ini - via intercompany_pakan_log yg tbl_name_tujuan-nya 'order_pakan'
     * (baris pertama dari grup ini). Kalau belum ada, buat order_pakan baru dgn nomor
     * konvensi SENDIRI (getNextNomor 'OPK/ICP') - PERSIS fungsi yg sama dgn Order Pakan asli,
     * jadi nomornya konsisten dgn format Order Pakan di instance ini. supplier diisi APA
     * ADANYA dari $kode_supplier (nomor supplier ASLI, sama dgn di sisi pengirim) - TIDAK
     * mendaftarkan instance pengirim sbg supplier baru.
     */
    private function cariOrBuatOrderPakan($kode_partner, $tbl_name_asal, $tbl_id_asal, $tanggal, $kode_supplier)
    {
        if (!empty($tbl_name_asal) && !empty($tbl_id_asal)) {
            $m_log_pertama = new \Model\Storage\IntercompanyPakanLog_model();
            $d_log_pertama = $m_log_pertama->where('arah', 'TERIMA')
                                            ->where('kode_partner', $kode_partner)
                                            ->where('tbl_name_asal', $tbl_name_asal)
                                            ->where('tbl_id_asal', $tbl_id_asal)
                                            ->where('tbl_name_tujuan', 'order_pakan')
                                            ->whereNotNull('tbl_id_tujuan')
                                            ->orderBy('id', 'asc')
                                            ->first();

            if (!empty($d_log_pertama)) {
                $m_op = new \Model\Storage\OrderPakan_model();
                $d_op = $m_op->where('id', $d_log_pertama->tbl_id_tujuan)->first();
                if (!empty($d_op)) { return $d_op; }
            }
        }

        $m_op = new \Model\Storage\OrderPakan_model();
        $nomor = $m_op->getNextNomor('OPK/ICP');
        $no_po = 'PO/PKN' . str_replace('OPK', '', $nomor);

        $m_op->no_order = $nomor;
        $m_op->no_po = $no_po;
        $m_op->tgl_trans = $tanggal;
        $m_op->rcn_kirim = $tanggal;
        $m_op->supplier = $kode_supplier;
        $m_op->save();

        return $m_op;
    }
}
