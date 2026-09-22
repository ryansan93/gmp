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
            $partner_cocok = $this->verifikasiPartner($raw_body, $timestamp, $signature);

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
     * Cari peternak AKTIF (rdim_submit.status=1) & belum tutup siklus (tidak ada baris
     * tutup_siklus utk noreg itu) di unit tertentu - dipanggil live oleh partner via HTTP
     * (BUKAN nulis apapun, murni lookup) saat staff di sisi pengirim mencari noreg tujuan
     * utk transfer OPKG (gudang->peternak). Query & kondisi persis meniru inti logic
     * PengirimanPenerimaanPakan::get_peternak() (rs.status=1, LEFT JOIN tutup_siklus ts
     * ... ts.id is null), tanpa filter tanggal docin bulan berjalan punya fitur ITU sendiri
     * (tidak relevan di sini - kita cuma perlu "peternak mana yg masih aktif di unit ini").
     * Riwayat pakan yg sudah diterima PECAH PER JENIS (bukan 1 angka total gabungan) - lewat
     * OUTER APPLY: 1 baris peternak x 1 baris per jenis pakan APAPUN yg pernah dia terima
     * (SEMUA jenis, bukan cuma yg sesuai barang di shipment yg mau ditransfer - staff perlu
     * lihat riwayat lengkap utk milih peternak yg paling tepat). Kalau belum pernah terima
     * sama sekali, tetap 1 baris muncul dgn kode_barang/jumlah NULL, spy peternaknya tidak
     * hilang dari list.
     *
     * NB tgl_docin/umur: rdim_submit.tgl_docin cuma tanggal RENCANA (bisa di masa depan,
     * blm tentu DOC-nya benar2 sudah datang) - dipakai INNER JOIN ke order_doc+terima_doc
     * (dedup versi terbaru via MAX(id), pola sama spt tabel master lain di app ini) supaya
     * HANYA peternak yg DOC-nya SUDAH dikonfirmasi diterima (terima_doc.datang not null) yg
     * muncul, dan tgl_docin/umur dihitung dari tanggal RIIL (td.datang), bukan rs.tgl_docin.
     */
    public function cariPeternakAktif()
    {
        $raw_body = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : null;
        $timestamp = isset($_SERVER['HTTP_X_TIMESTAMP']) ? $_SERVER['HTTP_X_TIMESTAMP'] : null;

        $result = array('status' => 0, 'message' => '');

        try {
            $this->verifikasiPartner($raw_body, $timestamp, $signature);

            $payload = json_decode($raw_body, true);
            $kode_unit = isset($payload['kode_unit']) ? $payload['kode_unit'] : null;

            if (empty($kode_unit) || !preg_match('/^[A-Za-z0-9]+$/', $kode_unit)) {
                throw new Exception('kode_unit tidak valid.');
            }

            $sql_riwayat = "
                select
                    dtp.item as kode_barang,
                    b.nama as nama_barang,
                    sum(dtp.jumlah) as jumlah
                from det_terima_pakan dtp
                left join
                    terima_pakan tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang group by kode) b2
                            on
                                b1.id = b2.id
                    ) b
                    on
                        b.kode = dtp.item
                where
                    kp.jenis_kirim = 'opkg' and
                    kp.jenis_tujuan = 'peternak' and
                    kp.tujuan = rs.noreg
                group by
                    dtp.item,
                    b.nama
            ";

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    rs.noreg,
                    m.nama,
                    cast(td.datang as date) as tgl_docin,
                    DATEDIFF(day, td.datang, GETDATE()) as umur,
                    riwayat.kode_barang,
                    riwayat.nama_barang,
                    riwayat.jumlah
                from rdim_submit rs
                inner join
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(id) as id, noreg from order_doc group by noreg) od2
                            on
                                od1.id = od2.id
                    ) od
                    on
                        rs.noreg = od.noreg
                inner join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        od.no_order = td.no_order
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        w.id = k.unit
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        rs.nim = mm.nim
                left join
                    mitra m
                    on
                        m.id = mm.mitra
                left join
                    tutup_siklus ts
                    on
                        rs.noreg = ts.noreg
                outer apply
                    (".$sql_riwayat.") riwayat
                where
                    rs.status = 1 and
                    ts.id is null and
                    td.datang is not null and
                    w.kode = '".$kode_unit."'
                order by
                    td.datang asc
            ";
            $d_conf = $m_conf->hydrateRaw($sql);

            $data = array();
            if ($d_conf->count() > 0) {
                $data = $d_conf->toArray();
            }

            $result['status'] = 1;
            $result['content'] = $data;
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $this->balas($result);
    }

    /**
     * Terima transfer OPKG (gudang->peternak) dari partner - lihat NB di
     * TransferTransaksi::transferOpkg(). BEDA dgn terima() (order pakan/supplier): TIDAK
     * ada stok yg ditulis sama sekali di sini (barangnya sudah fisik di peternak, dicatat
     * lewat proses normal di instance PENGIRIM) - endpoint ini CUMA bikin dokumen
     * kirim_pakan+terima_pakan (anchor referensi) + jurnal RIIL, dgn isi jurnal (nominal/
     * coa/asal/tujuan) disalin APA ADANYA dari payload (yg dikirim dari jurnal riil yg
     * SUDAH ADA di sisi pengirim) - HANYA kode_trans/tbl_id/unit/perusahaan yg disesuaikan
     * ke dokumen milik instance ini sendiri.
     */
    public function terimaOpkg()
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

            $partner_cocok = $this->verifikasiPartner($raw_body, $timestamp, $signature);

            $m_log = new \Model\Storage\IntercompanyPakanLog_model();
            $sudah_ada = $m_log->where('referensi_idempotency', $payload['referensi_idempotency'])->first();

            if (!empty($sudah_ada)) {
                $result['status'] = 1;
                $result['message'] = 'Sudah pernah diproses sebelumnya (idempotent).';
                return $this->balas($result);
            }

            $tbl_name_asal = isset($payload['tbl_name_asal']) ? $payload['tbl_name_asal'] : null;
            $tbl_id_asal = isset($payload['tbl_id_asal']) ? $payload['tbl_id_asal'] : null;
            $tanggal = isset($payload['tanggal']) ? $payload['tanggal'] : null;
            $noreg_tujuan = isset($payload['noreg_tujuan']) ? trim((string) $payload['noreg_tujuan']) : null;
            $detail = isset($payload['detail']) && is_array($payload['detail']) ? $payload['detail'] : array();
            $jurnal_items = isset($payload['jurnal']) && is_array($payload['jurnal']) ? $payload['jurnal'] : array();
            $info_kirim = isset($payload['info_kirim']) && is_array($payload['info_kirim']) ? $payload['info_kirim'] : array();
            $asal = isset($payload['asal']) ? $payload['asal'] : null;

            if (empty($noreg_tujuan) || empty($tanggal) || empty($detail) || empty($jurnal_items)) {
                throw new Exception('Payload OPKG tidak lengkap (noreg_tujuan/tanggal/detail/jurnal).');
            }

            // Validasi ketat SEBELUM dipakai di raw SQL (kodeUnitPeternak()) - noreg cuma
            // alfanumerik, sama pola dgn validasi kode_unit/kode_barang di cariPeternakAktif().
            if (!preg_match('/^[A-Za-z0-9]+$/', $noreg_tujuan)) {
                throw new Exception('noreg_tujuan tidak valid.');
            }

            $result['content'] = $m_log->getConnection()->transaction(function () use (
                $partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $tanggal, $noreg_tujuan, $detail, $jurnal_items, $info_kirim, $asal, $m_log
            ) {
                return $this->prosesTerimaOpkg($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $tanggal, $noreg_tujuan, $detail, $jurnal_items, $info_kirim, $asal, $m_log);
            });

            $result['status'] = 1;
            $result['message'] = 'Jurnal diterima & tercatat.';
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $this->balas($result);
    }

    /**
     * Isi asli terimaOpkg() - dipisah jadi method sendiri spy bisa dibungkus
     * $connection->transaction(), sama pola dgn prosesTerima()/terima().
     */
    private function prosesTerimaOpkg($partner_cocok, $payload, $tbl_name_asal, $tbl_id_asal, $tanggal, $noreg_tujuan, $detail, $jurnal_items, $info_kirim, $asal, $m_log)
    {
        $kode_unit = $this->kodeUnitPeternak($noreg_tujuan);
        $perusahaan = $this->defaultKodePerusahaan();

        // 1) Dokumen kirim_pakan+terima_pakan - CUMA anchor referensi (tbl_id/kode_trans)
        //    utk jurnal di bawah, TIDAK ada stok yg ditulis sama sekali (lihat NB kelas ini).
        //    'asal' diisi APA ADANYA sama persis dgn kirim_pakan.asal milik GML (id gudang
        //    mentah dr instance pengirim, BUKAN di-resolve jadi nama/teks apapun) - kolomnya
        //    VARCHAR(50) jadi tidak masalah walau nilainya "milik" instance lain. Layar
        //    Pengiriman/Penerimaan Pakan (PengirimanPenerimaanPakan::load_form()) sudah
        //    di-null-safe-kan+is_numeric-guard utk kasus id ini tidak match gudang manapun
        //    di instance ini.
        $m_kp = new \Model\Storage\KirimPakan_model();
        $no_order = $m_kp->getNextIdOrder('OP/' . $kode_unit);

        $m_kp->no_order = $no_order;
        $m_kp->tgl_trans = $tanggal;
        $m_kp->tgl_kirim = $tanggal;
        $m_kp->jenis_kirim = 'opkg';
        $m_kp->jenis_tujuan = 'peternak';
        $m_kp->asal = $asal;
        $m_kp->tujuan = $noreg_tujuan;
        // Info kirim ASLI dari instance pengirim (No SJ, ongkos angkut, ekspedisi, no polisi,
        // sopir) - diteruskan apa adanya, sama persis dgn yg tercatat di sana.
        $m_kp->no_sj = isset($info_kirim['no_sj']) ? $info_kirim['no_sj'] : null;
        $m_kp->ongkos_angkut = isset($info_kirim['ongkos_angkut']) ? $info_kirim['ongkos_angkut'] : null;
        $m_kp->ekspedisi = isset($info_kirim['ekspedisi']) ? $info_kirim['ekspedisi'] : null;
        $m_kp->ekspedisi_id = isset($info_kirim['ekspedisi_id']) ? $info_kirim['ekspedisi_id'] : null;
        $m_kp->no_polisi = isset($info_kirim['no_polisi']) ? $info_kirim['no_polisi'] : null;
        $m_kp->sopir = isset($info_kirim['sopir']) ? $info_kirim['sopir'] : null;
        $m_kp->save();

        foreach ($detail as $item) {
            $m_dkp = new \Model\Storage\KirimPakanDetail_model();
            $m_dkp->id_header = $m_kp->id;
            $m_dkp->item = $item['kode_barang'];
            $m_dkp->jumlah = (int) $item['jumlah'];
            $m_dkp->kondisi = isset($item['kondisi']) ? $item['kondisi'] : null;
            $m_dkp->save();
        }

        $m_tp = new \Model\Storage\TerimaPakan_model();
        $m_tp->id_kirim_pakan = $m_kp->id;
        $m_tp->tgl_trans = $tanggal;
        $m_tp->tgl_terima = $tanggal;
        $m_tp->no_bbm = 'BBM/PKN/G' . str_replace('OP', '', $no_order);
        $m_tp->save();

        foreach ($detail as $item) {
            $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
            $m_dtp->id_header = $m_tp->id;
            $m_dtp->item = $item['kode_barang'];
            $m_dtp->jumlah = (int) $item['jumlah'];
            $m_dtp->kondisi = isset($item['kondisi']) ? $item['kondisi'] : null;
            $m_dtp->save();
        }

        // 2) Log (arah TERIMA)
        $m_log->arah = 'TERIMA';
        $m_log->kode_partner = $partner_cocok->kode_partner;
        $m_log->referensi_idempotency = $payload['referensi_idempotency'];
        $m_log->kode_barang = $detail[0]['kode_barang'];
        $m_log->jml_qty = array_sum(array_column($detail, 'jumlah'));
        $m_log->tbl_name_asal = $tbl_name_asal;
        $m_log->tbl_id_asal = $tbl_id_asal;
        $m_log->tbl_name_tujuan = 'kirim_pakan';
        $m_log->tbl_id_tujuan = $m_kp->id;
        $m_log->status = 'DITERIMA';
        $m_log->waktu_terima = date('Y-m-d H:i:s');
        $m_log->save();

        // 3) Jurnal RIIL - isi (nominal/coa/asal/tujuan) disalin APA ADANYA dari jurnal riil
        //    yg SUDAH ADA di sisi pengirim (lihat payload['jurnal'], dibangun di
        //    TransferTransaksi::transferOpkg()) - cuma kode_trans/tbl_id/unit/perusahaan yg
        //    disesuaikan ke dokumen instance ini sendiri.
        $m_jurnal = new \Model\Storage\Jurnal_model();
        $m_jurnal->tanggal = $tanggal;
        $m_jurnal->unit = $kode_unit;
        $m_jurnal->save();

        foreach ($jurnal_items as $ji) {
            $m_det_jurnal = new \Model\Storage\DetJurnal_model();
            $m_det_jurnal->id_header = $m_jurnal->id;
            $m_det_jurnal->tanggal = $tanggal;
            $m_det_jurnal->perusahaan = $perusahaan;
            $m_det_jurnal->keterangan = $ji['keterangan'];
            $m_det_jurnal->nominal = $ji['nominal'];
            $m_det_jurnal->asal = $ji['asal'];
            $m_det_jurnal->coa_asal = $ji['coa_asal'];
            $m_det_jurnal->tujuan = $ji['tujuan'];
            $m_det_jurnal->coa_tujuan = $ji['coa_tujuan'];
            $m_det_jurnal->unit = $kode_unit;
            $m_det_jurnal->tbl_name = 'terima_pakan';
            $m_det_jurnal->tbl_id = $m_tp->id;
            $m_det_jurnal->kode_trans = $m_tp->no_bbm;
            $m_det_jurnal->save();
        }

        return array('id_log' => $m_log->id, 'id_kirim_pakan' => $m_kp->id, 'id_terima_pakan' => $m_tp->id, 'no_transaksi' => $no_order);
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
     * Verifikasi signature terhadap SEMUA partner aktif (dipakai bersama oleh terima() dan
     * cariPeternakAktif() - endpoint generic, tidak tahu di muka siapa pengirimnya, lihat NB
     * di terima()). Melempar Exception kalau tidak ada yg cocok, return \Model\Storage\
     * IntercompanyPartner_model yg cocok kalau valid.
     */
    private function verifikasiPartner($raw_body, $timestamp, $signature)
    {
        $this->load->library('IntercompanyClient');

        $m_partner = new \Model\Storage\IntercompanyPartner_model();
        $partners = $m_partner->where('status', 1)->get();

        foreach ($partners as $p) {
            $cek = IntercompanyClient::verifikasi($raw_body, $timestamp, $signature, $p->shared_secret);
            if ($cek['valid']) { return $p; }
        }

        throw new Exception('Signature tidak valid utk partner manapun yang terdaftar/aktif.');
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
     * Kode unit (wilayah) pemilik kandang peternak $noreg (di instance INI) - dipakai utk
     * nomor kirim_pakan OPKG ('OP/<kode_unit>/...') & kolom 'unit' jurnal riil di
     * prosesTerimaOpkg(). Fallback 'ICP' kalau peternak/kandang/unit-nya entah kenapa tidak
     * ketemu (mis. noreg yg dikirim staff salah ketik) - tetap dapat nilai valid drpd error.
     */
    private function kodeUnitPeternak($noreg)
    {
        if (empty($noreg)) { return 'ICP'; }

        // NB: $noreg WAJIB sudah divalidasi alfanumerik oleh caller (terimaOpkg()) SEBELUM
        // sampai sini - dipakai langsung di raw SQL di bawah, tidak di-escape lagi di sini.
        // Raw SQL (BUKAN relasi Eloquent bersarang kandang->d_unit) - relasi itu sempat
        // dicoba (termasuk dot-notation 'kandang.d_unit') tapi TERBUKTI tidak pernah
        // ter-resolve dgn benar (selalu jatuh ke fallback ICP walau datanya ada & query SQL
        // manual terbukti benar) - entah kenapa, tidak digali lebih jauh, raw SQL lebih
        // predictable & konsisten dgn pola query lain di app ini (banyak join kompleks di
        // codebase ini sengaja pakai hydrateRaw(), bukan relasi Eloquent).
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select top 1 w.kode
            from rdim_submit rs
            left join
                kandang k
                on
                    rs.kandang = k.id
            left join
                wilayah w
                on
                    w.id = k.unit
            where
                rs.noreg = '".$noreg."'
            order by
                rs.id desc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        if ($d_conf->count() > 0) {
            $kode = $d_conf->toArray()[0]['kode'];
            if (!empty($kode)) { return $kode; }
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
