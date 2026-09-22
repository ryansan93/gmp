<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intercompany Pakan - sisi PENERIMA.
 *
 * Endpoint machine-to-machine dipanggil oleh instance GML/GMP lain (lewat
 * IntercompanyClient::post() di sisi pengirim, application/modules/intercompany/
 * controllers/IntercompanyPakan.php::kirimKePartner()). TIDAK ada konfirmasi manual
 * staff di sisi ini (sesuai keputusan: otomatis begitu pengirim simpan).
 *
 * KEPUTUSAN: stok RIIL sudah dicatat di instance PENGIRIM (order_pakan pengirim sejak
 * awal menunjuk gudang lokal miliknya sendiri) - jadi stok di sisi PENERIMA ini cuma
 * SHADOW (stok_manajemen), murni visibilitas manajemen. Jurnal (hutang ke supplier)
 * justru RIIL di sisi PENERIMA (insert LANGSUNG ke jurnal/det_jurnal, BUKAN lewat
 * engine InsertJurnal::exec() - dicoba tapi dibatalkan krn nominalnya bergantung ke
 * det_stok riil yg tidak ada di sini, dan ada risiko self-deadlock; lihat NB di
 * prosesTerima() poin 3). Nominal & COA dihitung/di-hardcode sendiri di sini, tidak
 * bergantung ke det_stok sama sekali. Konsekuensinya, tagihan ini WAJIB muncul di
 * layar Pengajuan Pembayaran (pembayaran/RealisasiPembayaran) sisi PENERIMA - baris
 * konfirmasi_pembayaran_pakan/_det direplikasi manual di prosesTerima() poin 4, krn
 * layar itu baca tabel itu, BUKAN jurnal/det_jurnal langsung. Sebaliknya di sisi
 * PENGIRIM, baris konfirmasi_pembayaran_pakan yg mungkin sudah kebentuk dari Terima
 * Pakan normal (SEBELUM staff transfer order ini) dihapus oleh
 * TransferTransaksi::hapusKonfirmasiPembayaranPakan() - supaya tagihan tidak nyangkut
 * dobel (nempel di GML DAN di sini).
 *
 * Beda dgn controller api/ lain di app ini (Mobile.php dkk) yang TANPA autentikasi
 * sama sekali - endpoint ini WAJIB diverifikasi HMAC karena langsung menulis dokumen
 * order_pakan/kirim_pakan/terima_pakan & posting jurnal riil tanpa review manusia.
 */
class IntercompanyPakanTerima extends API_Controller {

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
            $kondisi = isset($payload['kondisi']) ? $payload['kondisi'] : null;

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

            // Info kirim_pakan ASLI dari instance pengirim (kalau ada) - diteruskan apa adanya,
            // BUKAN digenerate ulang di sisi partner.
            $info_kirim = array(
                'no_sj' => isset($payload['no_sj']) ? $payload['no_sj'] : null,
                'ongkos_angkut' => isset($payload['ongkos_angkut']) ? $payload['ongkos_angkut'] : null,
                'ekspedisi' => isset($payload['ekspedisi']) ? $payload['ekspedisi'] : null,
                'ekspedisi_id' => isset($payload['ekspedisi_id']) ? $payload['ekspedisi_id'] : null,
                'no_polisi' => isset($payload['no_polisi']) ? $payload['no_polisi'] : null,
                'sopir' => isset($payload['sopir']) ? $payload['sopir'] : null,
            );

            // Semua insert di bawah (order_pakan s.d. det_jurnal) dibungkus 1 transaksi -
            // supaya kalau ada 1 langkah gagal di tengah (mis. tipe kolom tidak cocok), SEMUA
            // di-rollback, tidak menyisakan order_pakan/kirim_pakan "yatim" tanpa detail seperti
            // yg sempat kejadian. Pakai getConnection()->transaction() (bukan Facade DB::), krn
            // project ini cuma pakai Capsule Manager langsung - pola sama spt LembarKerjaHpp.php.
            // NB: sempat dicoba posting jurnal RIIL via engine InsertJurnal::exec() (sama dgn
            // Terima Pakan normal) - DIBATALKAN krn 2 masalah: (1) nominalnya dihitung dari JOIN
            // ke det_stok RIIL (kode_trans=no_order), yg TIDAK ADA di sini krn stok di sisi
            // penerima sengaja shadow (lihat NB "2) Stok SHADOW" di prosesTerima()) - hasilnya
            // nominal jurnal selalu NULL; (2) InsertJurnal query terima_pakan/kirim_pakan lewat
            // koneksi \Model\Storage\Conf() SENDIRI (beda dari connection transaction() di
            // bawah) - kalau dipanggil sebelum commit, self-deadlock (baris yg br saja di-insert
            // masih terkunci transaksi sendiri, teruji nyata: 3 menit+ tanpa respons). Jurnal
            // riil sekarang di-insert LANGSUNG (bukan lewat engine itu) di prosesTerima() poin 3
            // - nominal & COA dihitung/di-hardcode sendiri, tidak bergantung ke det_stok.
            $result['content'] = $m_log->getConnection()->transaction(function () use (
                $partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier,
                $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $kondisi, $info_kirim, $m_log
            ) {
                return $this->prosesTerima($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier, $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $kondisi, $info_kirim, $m_log);
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
    private function prosesTerima($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $kode_supplier, $tanggal, $kode_barang, $jumlah, $harga, $kode_gudang_tujuan, $kondisi, $info_kirim, $m_log)
    {
            // Sesuai keputusan: transaksi intercompany dicatat sbg dokumen NYATA di sini
            // (order_pakan/kirim_pakan/terima_pakan asli, BUKAN cuma shadow stok/jurnal) -
            // nomornya pakai konvensi/fungsi getNextNomor() yg SAMA dgn Order Pakan asli DI
            // INSTANCE INI (format 'OPK/<kode_unit>/...', kode_unit diambil dari unit pemilik
            // gudang tujuan - persis pola yg dipakai ODVP::save_order_pakan()), supaya
            // transaksi ini muncul wajar di layar Order/Kirim/Terima Pakan sendiri. 1
            // order_pakan dipakai bareng utk semua baris barang dari transaksi asal yg sama
            // (kode_partner+tbl_name_asal+tbl_id_asal) - dicari dulu via intercompany_pakan_log
            // (tbl_name_tujuan='order_pakan') sebelum bikin baru, supaya tidak nge-split 1
            // transaksi jadi banyak order_pakan.
            $d_order_pakan = $this->cariOrBuatOrderPakan($partner_cocok->kode_partner, $tbl_name_asal, $tbl_id_asal, $tanggal, $kode_supplier, $kode_gudang_tujuan);

            // NB: layar view Order Pakan (order_pakan_view_form) di-JOIN pakai RIGHT JOIN
            // perusahaan ON opd.perusahaan = prs.kode - kalau 'perusahaan' NULL, baris detail
            // ini TIDAK PERNAH match & hilang total dari tampilan (RIGHT JOIN membalik arah
            // filter-nya). Wajib diisi kode perusahaan default milik instance PENERIMA ini.
            $m_opd = new \Model\Storage\OrderPakanDetail_model();
            $m_opd->id_header = $d_order_pakan->id;
            $m_opd->barang = $kode_barang;
            $m_opd->harga = $harga;
            $m_opd->harga_jual = $harga;
            $m_opd->jumlah = $jumlah;
            $m_opd->total = !empty($harga) ? ($harga * $jumlah) : null;
            $m_opd->tujuan_kirim = 'gudang';
            $m_opd->id_tujuan_kirim = $kode_gudang_tujuan;
            $m_opd->perusahaan = $this->defaultKodePerusahaan();
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
            // NB: utk jenis_kirim='opks', layar detail (PengirimanPenerimaanPakan::loadForm())
            // cari Supplier_model WHERE nomor = kirim_pakan.asal - HARUS nomor supplier asli,
            // bukan teks bebas, kalau tidak jadi non-object & "Asal" tampil kosong.
            $m_kp->asal = $kode_supplier;
            // Info kirim ASLI dari instance pengirim (No SJ, ongkos angkut, ekspedisi, no
            // polisi, sopir) - diteruskan apa adanya, sama persis dgn yg tercatat di sana.
            $m_kp->no_sj = $info_kirim['no_sj'];
            $m_kp->ongkos_angkut = $info_kirim['ongkos_angkut'];
            $m_kp->ekspedisi = $info_kirim['ekspedisi'];
            $m_kp->ekspedisi_id = $info_kirim['ekspedisi_id'];
            $m_kp->no_polisi = $info_kirim['no_polisi'];
            $m_kp->sopir = $info_kirim['sopir'];
            $m_kp->save();

            // NB: det_kirim_pakan.jumlah & det_terima_pakan.jumlah bertipe INT di database
            // (beda dgn order_pakan_detail.jumlah yg DECIMAL) - wajib di-cast, kalau tidak
            // driver sqlsrv menolak nilai desimal spt '2500.00' ("Conversion failed...").
            $m_dkp = new \Model\Storage\KirimPakanDetail_model();
            $m_dkp->id_header = $m_kp->id;
            $m_dkp->item = $kode_barang;
            $m_dkp->jumlah = (int) $jumlah;
            $m_dkp->kondisi = $kondisi;
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
            // no_bbm - konvensi sama dgn PengirimanPenerimaanPakan.php utk jenis_kirim='opks',
            // dipakai lagi sbg kode_trans di baris jurnal riil di bawah.
            $m_tp->no_bbm = 'BBM/PKN/S' . str_replace('OPK', '', $d_order_pakan->no_order);
            $m_tp->save();

            $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
            $m_dtp->id_header = $m_tp->id;
            $m_dtp->item = $kode_barang;
            $m_dtp->jumlah = (int) $jumlah;
            $m_dtp->kondisi = $kondisi;
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

            // 2) Stok SHADOW (stok_manajemen) - sesuai keputusan, instance PENERIMA di alur
            //    intercompany ini BUKAN pemilik stok riil (barangnya sudah dicatat sbg stok
            //    riil di instance PENGIRIM, lihat IntercompanyPakan::kirimKePartner()) - di
            //    sini cuma visibilitas manajemen.
            $m_stok_mnj = new \Model\Storage\StokManajemen_model();
            $d_stok_mnj = $m_stok_mnj->where('periode', $tanggal)->first();
            if (empty($d_stok_mnj)) {
                $m_stok_mnj->periode = $tanggal;
                $m_stok_mnj->tgl_proses = date('Y-m-d H:i:s');
                $m_stok_mnj->save();
                $id_header_stok_mnj = $m_stok_mnj->id;
            } else {
                $id_header_stok_mnj = $d_stok_mnj->id;
            }

            $m_det_stok_mnj = new \Model\Storage\DetStokManajemen_model();
            $m_det_stok_mnj->id_header = $id_header_stok_mnj;
            $m_det_stok_mnj->kode_gudang = is_numeric($kode_gudang_tujuan) ? (int) $kode_gudang_tujuan : null;
            $m_det_stok_mnj->kode_barang = $kode_barang;
            $m_det_stok_mnj->jumlah = $jumlah;
            $m_det_stok_mnj->jml_stok = $jumlah;
            $m_det_stok_mnj->hrg_beli = $harga;
            $m_det_stok_mnj->tgl_trans = $tanggal;
            $m_det_stok_mnj->jenis_trans = 'masuk';
            $m_det_stok_mnj->jenis_barang = 'pakan';
            // no_order asli (BUKAN konstanta 'ic_pakan_log') - konsisten dgn sisi pengirim
            // (IntercompanyPakan::kirimKePartner()), supaya laporan stok manajemen (kalau ada)
            // otomatis mengenali baris ini lewat konvensi kode_trans = no_order yg sama.
            $m_det_stok_mnj->kode_trans = $d_order_pakan->no_order;
            $m_det_stok_mnj->id_intercompany_log = $id_log;
            $m_det_stok_mnj->save();

            $m_det_stok_trans_mnj = new \Model\Storage\DetStokTransManajemen_model();
            $m_det_stok_trans_mnj->id_header = $m_det_stok_mnj->id;
            $m_det_stok_trans_mnj->kode_barang = $kode_barang;
            $m_det_stok_trans_mnj->kode_trans = $d_order_pakan->no_order;
            $m_det_stok_trans_mnj->jumlah = $jumlah;
            $m_det_stok_trans_mnj->id_intercompany_log = $id_log;
            $m_det_stok_trans_mnj->save();

            // NB: tbl_name_tujuan/tbl_id_tujuan baris log ini SENGAJA dibiarkan menunjuk ke
            // order_pakan (diisi di atas) - dipakai lagi utk grouping barang berikutnya dari
            // transaksi asal yg sama (lihat cariOrBuatOrderPakan()).

            // 3) Jurnal RIIL (hutang ke supplier) - insert LANGSUNG ke jurnal/det_jurnal, BUKAN
            //    lewat InsertJurnal::exec() (dibatalkan - lihat NB di terima(): nominalnya
            //    bergantung ke det_stok riil yg tidak ada di sini krn stok penerima sengaja
            //    shadow, dan ada risiko self-deadlock). Nominal dihitung SENDIRI dari payload
            //    (harga*jumlah) - tidak bergantung ke det_stok sama sekali.
            //    COA & keterangan 'HUTANG PAKAN' pakai konfigurasi yg SAMA dgn rule otomatis
            //    utk jenis_kirim='opks' urut=1 (setting_automatic_jurnal_det id_header=22,
            //    dikonfirmasi via INFORMATION_SCHEMA 2026-09-22: coa_asal=21180.100 'Hutang
            //    Niaga ORP (Pakan)', coa_tujuan=12030.000 'Persediaan Pakan') - di-hardcode di
            //    sini krn cuma dipakai utk skenario intercompany ini, bukan generic spt aslinya.
            $kode_unit = $this->kodeUnitGudang($kode_gudang_tujuan);
            $nama_supplier = $this->namaSupplier($kode_supplier);

            $m_jurnal = new \Model\Storage\Jurnal_model();
            $m_jurnal->tanggal = $tanggal;
            $m_jurnal->unit = $kode_unit;
            $m_jurnal->save();

            $m_det_jurnal = new \Model\Storage\DetJurnal_model();
            $m_det_jurnal->id_header = $m_jurnal->id;
            $m_det_jurnal->tanggal = $tanggal;
            $m_det_jurnal->supplier = $kode_supplier;
            $m_det_jurnal->perusahaan = $this->defaultKodePerusahaan();
            $m_det_jurnal->keterangan = 'HUTANG PAKAN ' . strtoupper($nama_supplier);
            $m_det_jurnal->nominal = !empty($harga) ? ($harga * $jumlah) : 0;
            $m_det_jurnal->asal = 'Hutang Niaga ORP (Pakan)';
            $m_det_jurnal->coa_asal = '21180.100';
            $m_det_jurnal->tujuan = 'Persediaan Pakan';
            $m_det_jurnal->coa_tujuan = '12030.000';
            $m_det_jurnal->unit = $kode_unit;
            $m_det_jurnal->tbl_name = 'terima_pakan';
            $m_det_jurnal->tbl_id = $m_tp->id;
            $m_det_jurnal->kode_trans = $m_tp->no_bbm;
            $m_det_jurnal->gudang = !empty($kode_gudang_tujuan) ? (int) $kode_gudang_tujuan : null;
            $m_det_jurnal->save();

            // 4) Konfirmasi Pembayaran Pakan - supaya tagihan ini MUNCUL di layar Pengajuan
            //    Pembayaran (pembayaran/RealisasiPembayaran) di sisi PENERIMA ini. Layar itu
            //    TIDAK baca jurnal/det_jurnal langsung - dia baca konfirmasi_pembayaran_pakan/
            //    _det, yg NORMALNYA di-generate oleh PengirimanPenerimaanPakan::insertKonfirmasi()
            //    saat staff simpan Terima Pakan manual. Krn endpoint ini bikin terima_pakan
            //    tanpa lewat layar itu, insert-nya direplikasi manual di sini (pakai data yg
            //    sudah ada di memori, BUKAN query ulang - hindari lagi risiko yg sama dgn NB
            //    InsertJurnal::exec() di atas). 1 baris konfirmasi per prosesTerima() (per
            //    barang), sama granularitasnya dgn jurnal riil di atas - beda dgn
            //    insertKonfirmasi() asli yg agregat semua barang 1 no_order jadi 1 baris.
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
            $nomor_bayar = $m_kpp->getNextNomor();

            $m_kpp->nomor = $nomor_bayar;
            $m_kpp->tgl_bayar = $tanggal;
            $m_kpp->periode = $tanggal;
            $m_kpp->perusahaan = $this->defaultKodePerusahaan();
            $m_kpp->supplier = $kode_supplier;
            $m_kpp->total = !empty($harga) ? ($harga * $jumlah) : 0;
            $m_kpp->invoice = $info_kirim['no_sj'];
            $m_kpp->save();

            $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
            $m_kppd->id_header = $m_kpp->id;
            $m_kppd->tgl_sj = $tanggal;
            $m_kppd->kode_unit = $kode_unit;
            $m_kppd->no_order = $d_order_pakan->no_order;
            $m_kppd->no_sj = $info_kirim['no_sj'];
            // NB: kolom jumlah bertipe INT (sama spt det_kirim_pakan/det_terima_pakan) -
            // wajib di-cast, kalau tidak driver sqlsrv menolak nilai desimal spt '2500.00'.
            $m_kppd->jumlah = (int) $jumlah;
            $m_kppd->total = !empty($harga) ? ($harga * $jumlah) : 0;
            $m_kppd->save();

            return array('id_log' => $id_log, 'id_det_stok_manajemen' => $m_det_stok_mnj->id, 'id_terima_pakan' => $m_tp->id, 'no_transaksi' => $no_transaksi);
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
     * konvensi SAMA PERSIS dgn Order Pakan asli di instance ini: 'OPK/<kode_unit>/...' via
     * getNextNomor(), dgn kode_unit diambil dari unit pemilik gudang tujuan (persis pola
     * ODVP::save_order_pakan()) - BUKAN prefix 'OPK/ICP' buatan sendiri. supplier diisi APA
     * ADANYA dari $kode_supplier (nomor supplier ASLI, sama dgn di sisi pengirim) - TIDAK
     * mendaftarkan instance pengirim sbg supplier baru.
     */
    private function cariOrBuatOrderPakan($kode_partner, $tbl_name_asal, $tbl_id_asal, $tanggal, $kode_supplier, $kode_gudang_tujuan)
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

        $kode_unit = $this->kodeUnitGudang($kode_gudang_tujuan);

        $m_op = new \Model\Storage\OrderPakan_model();
        $nomor = $m_op->getNextNomor('OPK/' . $kode_unit);
        $no_po = 'PO/PKN' . str_replace('OPK', '', $nomor);

        $m_op->no_order = $nomor;
        $m_op->no_po = $no_po;
        $m_op->tgl_trans = $tanggal;
        $m_op->rcn_kirim = $tanggal;
        $m_op->supplier = $kode_supplier;
        $m_op->save();

        return $m_op;
    }

    /**
     * Kode perusahaan default milik instance PENERIMA ini (dipakai utk order_pakan_detail.
     * perusahaan - lihat NB di prosesTerima()). Kebanyakan instance cuma punya 1 perusahaan
     * terdaftar (satu kode, versi terbaru diambil via MAX(id) per kode - pola dedup yg sama
     * spt ODVP::get_data_perusahaan()); kalau ada lebih dari 1, ambil yg pertama - tidak ada
     * info dari pengirim utk milih yg mana.
     */
    private function defaultKodePerusahaan()
    {
        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('kode', 'asc')->orderBy('id', 'desc')->first();

        return !empty($d_prs) ? $d_prs->kode : null;
    }

    /**
     * Kode unit (wilayah) pemilik gudang TUJUAN (gudang.id milik instance INI) - dipakai utk
     * nomor Order Pakan ('OPK/<kode_unit>/...') maupun kolom 'unit' di jurnal riil. Fallback
     * 'ICP' kalau gudang/unit-nya entah kenapa tidak ketemu, supaya tetap dapat nilai valid.
     */
    private function kodeUnitGudang($kode_gudang_tujuan)
    {
        if (empty($kode_gudang_tujuan)) { return 'ICP'; }

        $m_gdg = new \Model\Storage\Gudang_model();
        $d_gdg = $m_gdg->where('id', $kode_gudang_tujuan)->with(['dUnit'])->first();

        if (!empty($d_gdg) && !empty($d_gdg->dUnit) && !empty($d_gdg->dUnit->kode)) {
            return $d_gdg->dUnit->kode;
        }

        return 'ICP';
    }

    /**
     * Nama supplier ASLI (dari $kode_supplier yg diteruskan apa adanya dari pengirim) - dipakai
     * utk keterangan jurnal riil ('HUTANG PAKAN <NAMA SUPPLIER>'). Null-safe kalau supplier-nya
     * belum terdaftar dgn nomor yg sama di instance ini.
     */
    private function namaSupplier($kode_supplier)
    {
        if (empty($kode_supplier)) { return '-'; }

        $m_supplier = new \Model\Storage\Supplier_model();
        $d_supplier = $m_supplier->where('nomor', $kode_supplier)->where('tipe', 'supplier')->orderBy('id', 'desc')->first();

        return !empty($d_supplier) ? $d_supplier->nama : $kode_supplier;
    }
}
