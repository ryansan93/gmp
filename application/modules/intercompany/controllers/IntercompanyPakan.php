<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intercompany Pakan - sisi PENGIRIM.
 *
 * Dipanggil sbg hook TAMBAHAN dari PengirimanPenerimaanPakan::save() ATAU dari
 * TransferTransaksi::transfer() saat staff menandai tujuan sebagai lintas-GMP.
 *
 * KEPUTUSAN (beda dgn dokumentasi lama "pemilik finansial vs pemegang fisik" yg
 * SEBALIKNYA - lihat riwayat diskusi "intercompany pakan - stok rill vs jurnal
 * manajemen lintas-GMP"): utk transaksi lewat TransferTransaksi (order_pakan
 * pengirim SUDAH menunjuk gudang lokal pengirim sendiri di order_pakan_detail.
 * id_tujuan_kirim), instance PENGIRIM (di sini) yg dianggap PEMILIK STOK RIIL
 * (barang dicatat masuk ke stok/det_stok asli-nya SENDIRI, bukan shadow), sedangkan
 * JURNAL (hutang ke supplier) di sisi pengirim cuma SHADOW (jurnal_manajemen) -
 * yg posting jurnal RIIL (via InsertJurnal::exec(), sama dgn Terima Pakan normal)
 * justru instance PARTNER (lihat IntercompanyPakanTerima::terima()).
 *
 * Juga menyediakan layar rekonsiliasi (intercompany_pakan_log) + retry manual utk
 * baris yang gagal terkirim.
 */
class IntercompanyPakan extends Public_Controller {

    private $pathView = 'intercompany_pakan/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    public function index()
    {
        if ($this->hakAkses['a_view'] == 1) {
            $this->add_external_js(array(
                "assets/intercompany/intercompany_pakan/js/intercompany-pakan.js",
            ));

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Rekonsiliasi Intercompany Pakan';

            $data = $this->includes;
            $data['title_menu'] = 'Intercompany Pakan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function list_log()
    {
        $params = $this->input->post('params');
        $status = !empty($params['status']) ? $params['status'] : null;

        $m_log = new \Model\Storage\IntercompanyPakanLog_model();
        $q = $m_log->with(['d_partner', 'd_barang'])->orderBy('id', 'desc');
        if (!empty($status)) {
            $q = $q->where('status', $status);
        }
        $d_log = $q->get()->toArray();

        $content['list'] = $d_log;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);
        echo $html;
    }

    /**
     * Kirim ulang baris GAGAL secara manual (tidak ada job queue otomatis di versi awal ini).
     */
    public function kirimUlang()
    {
        if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $id_log = $this->input->post('id_log');

        try {
            $m_log = new \Model\Storage\IntercompanyPakanLog_model();
            $d_log = $m_log->where('id', $id_log)->where('status', 'GAGAL')->first();

            if (empty($d_log)) {
                throw new Exception('Baris log tidak ditemukan atau bukan berstatus GAGAL.');
            }

            $m_partner = new \Model\Storage\IntercompanyPartner_model();
            $d_partner = $m_partner->where('kode_partner', $d_log->kode_partner)->first();

            if (empty($d_partner)) {
                throw new Exception('Partner ' . $d_log->kode_partner . ' tidak ditemukan/nonaktif.');
            }

            $hasil = $this->kirimHttp($d_partner, $d_log);

            if ($hasil['success']) {
                $m_log->where('id', $id_log)->update(array(
                    'status' => 'DITERIMA',
                    'waktu_terima' => date('Y-m-d H:i:s'),
                    'pesan_error' => null,
                ));
                $this->catatNomorPartner($d_log->kode_partner, $d_log->tbl_name_asal, $d_log->tbl_id_asal, $hasil['body']);
                $this->result['status'] = 1;
                $this->result['message'] = 'Berhasil dikirim ulang ke ' . $d_partner->nama_partner . '.';
            } else {
                // pesan_error VARCHAR(500) - potong dulu, lihat NB sama di TransferTransaksi::transferOpkg().
                $m_log->where('id', $id_log)->update(array('pesan_error' => substr($hasil['error'], 0, 500)));
                $this->result['message'] = 'Masih gagal: ' . $hasil['error'];
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * Dipanggil dari PengirimanPenerimaanPakan::save() (via Modules::run) saat tujuan
     * kirim pakan adalah lintas-GMP, BUKAN gudang/kandang lokal.
     *
     * @param string $tbl_name_asal   nama tabel sumber di pengirim, mis. 'terima_pakan'
     * @param string $tbl_id_asal     id baris sumber di pengirim, mis. id_terima
     * @param string $tanggal         tanggal transaksi (Y-m-d)
     * @param array  $detail          array of ['kode_barang' => .., 'jumlah' => .., 'harga' => .., 'kondisi' => ..]
     *                                 'harga' WAJIB diisi oleh caller (harga beli dari order_pakan_detail
     *                                 milik instance pengirim) - controller ini tidak menebak harga sendiri.
     *                                 'kondisi' OPSIONAL (mis. 'BAIK') - diteruskan apa adanya ke
     *                                 det_kirim_pakan/det_terima_pakan partner, sesuai yg dicatat di
     *                                 instance pengirim (bukan digenerate ulang di sisi partner).
     * @param string $kode_partner
     * @param string $kode_gudang_partner  kode gudang tujuan di sisi partner (bukan id lokal)
     * @param string $kode_supplier  nomor supplier ASLI (sama dgn punya instance ini) - diteruskan
     *                                apa adanya ke partner supaya order_pakan yg dibuat di sana pakai
     *                                supplier yg SAMA (bukan mendaftarkan instance ini sbg supplier baru).
     * @param array  $info_kirim  OPSIONAL, info kirim_pakan ASLI dari instance ini (kalau ada) -
     *                             ['no_sj'=>.., 'ongkos_angkut'=>.., 'ekspedisi'=>.., 'ekspedisi_id'=>..,
     *                             'no_polisi'=>.., 'sopir'=>..] - diteruskan apa adanya ke kirim_pakan
     *                             partner, sesuai yg dicatat di instance pengirim.
     * @return array ['status' => 0|1, 'message' => string]
     */
    public function kirimKePartner($tbl_name_asal, $tbl_id_asal, $tanggal, array $detail, $kode_partner, $kode_gudang_partner, $kode_supplier = null, array $info_kirim = array())
    {
        $hasil = array('status' => 0, 'message' => '');

        try {
            $m_partner = new \Model\Storage\IntercompanyPartner_model();
            $d_partner = $m_partner->where('kode_partner', $kode_partner)->where('status', 1)->first();

            if (empty($d_partner)) {
                throw new Exception("Partner '{$kode_partner}' tidak ditemukan/nonaktif.");
            }

            // 1) Tulis stok RIIL (stok/det_stok asli, BUKAN shadow) - sesuai keputusan,
            //    instance pengirim adalah pemilik stok riil krn order_pakan_detail.
            //    id_tujuan_kirim sejak awal menunjuk gudang LOKAL milik instance ini sendiri.
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

            // Header jurnal SHADOW (jurnal_manajemen) - visibilitas biaya di sisi pengirim,
            // BUKAN hutang riil (hutang riil justru diposting otomatis di sisi PARTNER).
            $m_jurnal_mnj = new \Model\Storage\JurnalManajemen_model();
            $m_jurnal_mnj->tanggal = $tanggal;
            $m_jurnal_mnj->save();

            // no_order asal (dipakai APA ADANYA sbg det_stok/det_stok_trans.kode_trans - BUKAN
            // konstanta custom spt 'ic_pakan_log' sebelumnya - supaya baris ini otomatis masuk
            // kategori 'ORDER' yg SUDAH ADA di Laporan Kartu Stok (KartuStok::mappingDataReport()
            // match via kp.no_order = det_stok.kode_trans), tanpa perlu ubah logic laporan sama
            // sekali) + kode_trans versi no_bbm (dipakai KHUSUS di det_jurnal_manajemen.kode_trans,
            // mengikuti pola det_jurnal.kode_trans = $m_tp->no_bbm di sisi partner/
            // IntercompanyPakanTerima - beda konvensi krn kolom itu meniru dokumen BBM, bukan no_order).
            $no_order_asal = null;
            $kode_trans_jurnal = null;
            if ($tbl_name_asal == 'order_pakan') {
                $m_op_asal = new \Model\Storage\OrderPakan_model();
                $d_op_asal = $m_op_asal->where('id', $tbl_id_asal)->first();
                if (!empty($d_op_asal)) {
                    $no_order_asal = $d_op_asal->no_order;
                    $kode_trans_jurnal = 'BBM/PKN/S' . str_replace('OPK', '', $d_op_asal->no_order);
                }
            }

            $log_ids = array();

            foreach ($detail as $item) {
                $referensi = bin2hex(random_bytes(16));

                // 2) Log intercompany dulu (status TERKIRIM sementara) supaya idempotency key ada
                //    sebelum request keluar - kalau proses ini gagal di tengah, retry manual masih
                //    bisa pakai baris log yang sama.
                $m_log = new \Model\Storage\IntercompanyPakanLog_model();
                $m_log->arah = 'KIRIM';
                $m_log->kode_partner = $kode_partner;
                $m_log->referensi_idempotency = $referensi;
                $m_log->kode_barang = $item['kode_barang'];
                $m_log->jml_qty = $item['jumlah'];
                $m_log->harga = isset($item['harga']) ? $item['harga'] : null;
                $m_log->kode_gudang_tujuan = $kode_gudang_partner;
                $m_log->tbl_name_asal = $tbl_name_asal;
                $m_log->tbl_id_asal = $tbl_id_asal;
                $m_log->status = 'TERKIRIM';
                $m_log->waktu_kirim = date('Y-m-d H:i:s');
                $m_log->save();

                $log_ids[] = $m_log->id;

                // 3) Stok RIIL (det_stok/det_stok_trans asli) - kode_gudang = gudang LOKAL
                //    milik instance pengirim SENDIRI (dari order_pakan_detail.id_tujuan_kirim,
                //    diteruskan lewat $item['id_tujuan_kirim_lokal']), BUKAN gudang partner.
                $kode_gudang_lokal = isset($item['id_tujuan_kirim_lokal']) ? $item['id_tujuan_kirim_lokal'] : null;

                // no_order asli (fallback 'ic_pakan_log' kalau entah kenapa tidak ketemu, mis.
                // dipanggil dari hook lain dgn tbl_name_asal != 'order_pakan') - lihat NB di atas.
                $kode_trans_item = !empty($no_order_asal) ? $no_order_asal : 'ic_pakan_log';

                // NB: kalau dokumen kirim_pakan/terima_pakan utk order ini SUDAH ADA SEBELUM
                // transfer (staff sempat simpan manual lewat layar Pengiriman & Penerimaan
                // Pakan biasa - lihat TransferTransaksi::transfer()), stoknya SUDAH otomatis
                // dihitung SP hitung_stok_pakan_by_transaksi SAAT itu (kode_trans = no_order,
                // sama persis konvensi di sini). Insert manual di bawah HARUS di-skip utk kasus
                // itu, kalau tidak stoknya kehitung DOBEL (sekali dari SP, sekali dari sini) -
                // makanya dicek dulu apa sudah ada baris det_stok utk kode_trans+barang ini.
                $m_det_stok_cek = new \Model\Storage\DetStok_model();
                $stok_sudah_ada = $m_det_stok_cek->where('kode_trans', $kode_trans_item)
                                                  ->where('kode_barang', $item['kode_barang'])
                                                  ->exists();

                if (!$stok_sudah_ada) {
                    $m_det_stok = new \Model\Storage\DetStok_model();
                    $m_det_stok->id_header = $id_header_stok;
                    $m_det_stok->kode_gudang = is_numeric($kode_gudang_lokal) ? (int) $kode_gudang_lokal : null;
                    $m_det_stok->kode_barang = $item['kode_barang'];
                    $m_det_stok->jumlah = $item['jumlah'];
                    $m_det_stok->jml_stok = $item['jumlah'];
                    $m_det_stok->hrg_beli = isset($item['harga']) ? $item['harga'] : null;
                    $m_det_stok->tgl_trans = $tanggal;
                    $m_det_stok->jenis_trans = 'masuk';
                    $m_det_stok->jenis_barang = 'pakan';
                    $m_det_stok->kode_trans = $kode_trans_item;
                    $m_det_stok->save();

                    $m_det_stok_trans = new \Model\Storage\DetStokTrans_model();
                    $m_det_stok_trans->id_header = $m_det_stok->id;
                    $m_det_stok_trans->kode_barang = $item['kode_barang'];
                    $m_det_stok_trans->kode_trans = $kode_trans_item;
                    $m_det_stok_trans->jumlah = $item['jumlah'];
                    $m_det_stok_trans->save();
                }

                // Jurnal SHADOW (biaya, BUKAN hutang riil - hutang riil diposting otomatis
                // di sisi partner lewat InsertJurnal::exec(), lihat IntercompanyPakanTerima).
                // Semua kolom yg diisi di det_jurnal RIIL sisi partner (coa_asal/coa_tujuan,
                // asal/tujuan, supplier, perusahaan, unit, gudang, kode_trans - lihat
                // IntercompanyPakanTerima::prosesTerima() poin 3) disamakan persis di sini,
                // supaya baris shadow ini setara isinya, cuma beda tabel (shadow vs riil).
                $harga_item = isset($item['harga']) ? $item['harga'] : 0;
                $m_det_jurnal_mnj = new \Model\Storage\DetJurnalManajemen_model();
                $m_det_jurnal_mnj->id_header = $m_jurnal_mnj->id;
                $m_det_jurnal_mnj->tanggal = $tanggal;
                $m_det_jurnal_mnj->supplier = $kode_supplier;
                $m_det_jurnal_mnj->perusahaan = $this->defaultKodePerusahaan();
                $m_det_jurnal_mnj->nominal = $harga_item * $item['jumlah'];
                $m_det_jurnal_mnj->keterangan = 'Transfer pakan intercompany ke ' . $kode_partner;
                $m_det_jurnal_mnj->asal = 'Hutang Niaga ORP (Pakan)';
                $m_det_jurnal_mnj->coa_asal = '21180.100';
                $m_det_jurnal_mnj->tujuan = 'Persediaan Pakan';
                $m_det_jurnal_mnj->coa_tujuan = '12030.000';
                $m_det_jurnal_mnj->unit = $this->kodeUnitGudang($kode_gudang_lokal);
                $m_det_jurnal_mnj->tbl_name = 'intercompany_pakan_log';
                $m_det_jurnal_mnj->tbl_id = $m_log->id;
                $m_det_jurnal_mnj->kode_trans = $kode_trans_jurnal;
                $m_det_jurnal_mnj->gudang = is_numeric($kode_gudang_lokal) ? (int) $kode_gudang_lokal : null;
                $m_det_jurnal_mnj->id_intercompany_log = $m_log->id;
                $m_det_jurnal_mnj->save();

                // 4) Kirim ke partner
                $payload = array(
                    'referensi_idempotency' => $referensi,
                    'kode_barang' => $item['kode_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga' => isset($item['harga']) ? $item['harga'] : null,
                    'kode_gudang_tujuan' => $kode_gudang_partner,
                    'tanggal' => $tanggal,
                    'tbl_name_asal' => $tbl_name_asal,
                    'tbl_id_asal' => $tbl_id_asal,
                    'kode_supplier' => $kode_supplier,
                    'kondisi' => isset($item['kondisi']) ? $item['kondisi'] : null,
                    'no_sj' => isset($info_kirim['no_sj']) ? $info_kirim['no_sj'] : null,
                    'ongkos_angkut' => isset($info_kirim['ongkos_angkut']) ? $info_kirim['ongkos_angkut'] : null,
                    'ekspedisi' => isset($info_kirim['ekspedisi']) ? $info_kirim['ekspedisi'] : null,
                    'ekspedisi_id' => isset($info_kirim['ekspedisi_id']) ? $info_kirim['ekspedisi_id'] : null,
                    'no_polisi' => isset($info_kirim['no_polisi']) ? $info_kirim['no_polisi'] : null,
                    'sopir' => isset($info_kirim['sopir']) ? $info_kirim['sopir'] : null,
                );

                $kirim = $this->kirimHttp($d_partner, (object) $payload);

                if ($kirim['success']) {
                    $m_log->where('id', $m_log->id)->update(array('status' => 'DITERIMA', 'waktu_terima' => date('Y-m-d H:i:s')));
                    $this->catatNomorPartner($kode_partner, $tbl_name_asal, $tbl_id_asal, $kirim['body']);
                } else {
                    // GAGAL kirim TIDAK membatalkan pembukuan lokal (stok riil & shadow jurnal
                    // sudah tersimpan sah) - tinggal retry manual lewat layar rekonsiliasi.
                    // pesan_error VARCHAR(500) - potong dulu, bisa jauh lebih panjang dari itu.
                    $m_log->where('id', $m_log->id)->update(array('status' => 'GAGAL', 'pesan_error' => substr($kirim['error'], 0, 500)));
                }
            }

            $hasil['status'] = 1;
            $hasil['message'] = 'Stok tercatat & ' . count($log_ids) . ' item diproses ke partner ' . $kode_partner . '.';
        } catch (Exception $e) {
            $hasil['message'] = $e->getMessage();
        }

        return $hasil;
    }

    /**************************************************************************************
     * PRIVATE HELPERS
     **************************************************************************************/

    private function kirimHttp($d_partner, $payloadOrLog)
    {
        $this->load->library('IntercompanyClient');

        if (is_object($payloadOrLog) && !($payloadOrLog instanceof \stdClass)) {
            // baris \Model\Storage\IntercompanyPakanLog_model (dipakai saat retry)
            $payload = array(
                'referensi_idempotency' => $payloadOrLog->referensi_idempotency,
                'kode_barang' => $payloadOrLog->kode_barang,
                'jumlah' => $payloadOrLog->jml_qty,
                'harga' => $payloadOrLog->harga,
                'kode_gudang_tujuan' => $payloadOrLog->kode_gudang_tujuan,
                'tanggal' => date('Y-m-d'),
                'tbl_name_asal' => $payloadOrLog->tbl_name_asal,
                'tbl_id_asal' => $payloadOrLog->tbl_id_asal,
            );
        } else {
            $payload = (array) $payloadOrLog;
        }

        return $this->intercompanyclient->post(
            $d_partner->base_url,
            $d_partner->shared_secret,
            'api/IntercompanyPakanTerima/terima',
            $payload
        );
    }

    /**
     * Simpan nomor transaksi yg di-generate PARTNER (sisi penerima, dari
     * intercompany_pakan_log miliknya sendiri - lihat IntercompanyPakanTerima::terima(),
     * TIDAK butuh tabel baru di database partner) ke salinan lokal tabel
     * intercompany_pakan_nomor DI SINI SAJA (arah='KIRIM') - tabel ini cuma perlu ada
     * di GML_ERP, supaya layar Transfer Transaksi bisa menampilkan nomor sisi partner
     * tanpa GML_ERP perlu tahu skema/isi database partner. 1 nomor per
     * (partner, tbl_name_asal, tbl_id_asal) - dicek dulu sebelum insert spy tidak
     * dobel kalau dipanggil berkali2 (banyak baris barang dari transaksi asal yg sama).
     */
    private function catatNomorPartner($kode_partner, $tbl_name_asal, $tbl_id_asal, $body)
    {
        if (empty($tbl_name_asal) || empty($tbl_id_asal)) { return; }

        $no_transaksi = null;
        if (is_array($body) && isset($body['content']['no_transaksi'])) {
            $no_transaksi = $body['content']['no_transaksi'];
        }

        if (empty($no_transaksi)) { return; }

        $m_nomor = new \Model\Storage\IntercompanyPakanNomor_model();
        $d_nomor = $m_nomor->where('arah', 'KIRIM')
                           ->where('kode_partner', $kode_partner)
                           ->where('tbl_name_asal', $tbl_name_asal)
                           ->where('tbl_id_asal', $tbl_id_asal)
                           ->first();

        if (empty($d_nomor)) {
            $m_nomor = new \Model\Storage\IntercompanyPakanNomor_model();
            $m_nomor->arah = 'KIRIM';
            $m_nomor->kode_partner = $kode_partner;
            $m_nomor->tbl_name_asal = $tbl_name_asal;
            $m_nomor->tbl_id_asal = $tbl_id_asal;
            $m_nomor->no_transaksi = $no_transaksi;
            $m_nomor->waktu = date('Y-m-d H:i:s');
            $m_nomor->save();
        }
    }

    /**
     * Kode perusahaan default milik instance PENGIRIM ini - dipakai utk
     * det_jurnal_manajemen.perusahaan, sama polanya dgn
     * IntercompanyPakanTerima::defaultKodePerusahaan() di sisi partner.
     */
    private function defaultKodePerusahaan()
    {
        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('kode', 'asc')->orderBy('id', 'desc')->first();

        return !empty($d_prs) ? $d_prs->kode : null;
    }

    /**
     * Kode unit (wilayah) pemilik gudang LOKAL pengirim - dipakai utk
     * det_jurnal_manajemen.unit, sama polanya dgn
     * IntercompanyPakanTerima::kodeUnitGudang() di sisi partner.
     */
    private function kodeUnitGudang($kode_gudang)
    {
        if (empty($kode_gudang)) { return null; }

        $m_gdg = new \Model\Storage\Gudang_model();
        $d_gdg = $m_gdg->where('id', $kode_gudang)->with(['dUnit'])->first();

        if (!empty($d_gdg) && !empty($d_gdg->dUnit) && !empty($d_gdg->dUnit->kode)) {
            return $d_gdg->dUnit->kode;
        }

        return null;
    }
}
