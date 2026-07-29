<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class PphUnifikasi extends Public_Controller {

    private $pathView = 'report/pph_unifikasi/';
    private $url;

    const NITKU_PEMOTONG = '1000000002244403000000';

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/pph_unifikasi/js/pph-unifikasi.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/pph_unifikasi/css/pph-unifikasi.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $this->getUnit();
            $content['title_menu'] = 'PPH Unifikasi';

            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
            order by
                p1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama,
                w1.kode
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah where jenis = 'UN' group by kode) w2
                on
                    w1.id = w2.id
            order by
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * HELPER
     **************************************************************************************/
    public function buildPeriode($bulan, $tahun) {
        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        $date = $tahun.'-'.$angka_bulan.'-01';

        return array(
            'bulan' => $angka_bulan,
            'tahun' => $tahun,
            // konvensi NOMOR_DOKUMEN di file sample: YYMM, mis. "2604" utk April 2026
            'yymm' => substr($tahun, -2).$angka_bulan,
            'start_date' => date("Y-m-d", strtotime($date)),
            'end_date' => date("Y-m-t", strtotime($date)),
        );
    }

    /**
     * NPWP_PENERIMA/NITKU_PENERIMA: pakai NPWP kalau terisi (bukan placeholder nol),
     * kalau tidak fallback ke NIK -- kebijakan pemadanan NIK=NPWP, sama seperti
     * FpOrtax::getIdentitasPembeli (lihat memory fp-ortax-report).
     */
    public function resolveIdentitas($npwp, $nik) {
        $npwp = trim((string) $npwp);
        $nik = trim((string) $nik);

        $has_npwp = !empty($npwp) && !preg_match('/^0+$/', $npwp);
        $id_value = $has_npwp ? $npwp : $nik;

        return array(
            'npwp' => $id_value,
            'nitku' => !empty($id_value) ? $id_value.'000000' : '',
        );
    }

    /**
     * Susun satu baris output BPPU. Rate 0.5% (Suket PP23/PP52) selalu dialihkan ke
     * kode final 28-423-01/fasilitas 6 terlepas dari sumbernya (RHPP/OA), sesuai sheet
     * referensi "Ref Fasilitas Insentif" & "Ref Daftar Kode Bukti Potong" di template.
     */
    public function buildRow($periode, $tgl_transaksi, $npwp, $nik, $nama, $bruto, $rate, $skb, $kode_normal, $nomor_dokumen) {
        $identitas = $this->resolveIdentitas($npwp, $nik);

        $is_final = ( abs((float) $rate - 0.5) < 0.0001 );
        $kode_objek_pajak = $is_final ? '28-423-01' : $kode_normal;
        $fasilitas = $is_final ? '6' : '9';

        $no_fasilitas = '-';
        if ( $is_final ) {
            $skb = trim((string) $skb);
            $no_fasilitas = !empty($skb) ? $skb : '-';
        }

        $tgl_iso = substr($tgl_transaksi, 0, 10);

        return array(
            'masa_pajak' => $periode['bulan'],
            'tahun_pajak' => $periode['tahun'],
            'tanggal_pemotongan' => $tgl_iso,
            'nitku_pemotong' => self::NITKU_PEMOTONG,
            'npwp_penerima' => $identitas['npwp'],
            'nitku_penerima' => $identitas['nitku'],
            'nama_penerima' => strtoupper(trim($nama)),
            'kode_objek_pajak' => $kode_objek_pajak,
            'bruto' => round($bruto),
            'fasilitas' => $fasilitas,
            'no_fasilitas' => $no_fasilitas,
            'tarif_lainnya' => '',
            'jenis_dokumen' => 'Other',
            'nomor_dokumen' => $nomor_dokumen,
            'tanggal_dokumen' => $tgl_iso,
            'email' => '',
            'keterangan1' => '',
            'keterangan2' => '',
            'keterangan3' => '',
            'keterangan4' => '',
            'keterangan5' => '',
            'referensi' => $nomor_dokumen,
            'no_bukti_potong_diganti' => '',
            'pengganti_ke' => '',
        );
    }

    /**************************************************************************************
     * DATA - SEMUA SUMBER DARI det_jurnal
     **************************************************************************************/
    /**
     * Filter dasar dipakai semua kategori: coa PPh (kepala 2462, kecuali 24621 = PPh 21,
     * di luar cakupan Unifikasi Badan) di SISI KREDIT (coa_asal -- inilah sisi yg terjadi
     * saat PPh benar2 DIPOTONG dari pihak ketiga; sisi debet/coa_tujuan adalah jurnal SETOR
     * ke kas negara, sudah dikonfirmasi user bukan yg dimaksud).
     */
    public function sqlFilterDasarJurnal($periode, $params, $alias = 'dj') {
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $sql = "
            ".$alias.".tanggal between '".$periode['start_date']."' and '".$periode['end_date']."' and
            ".$alias.".coa_asal like '2462%' and
            ".$alias.".coa_asal not like '24621%' and
            ".$alias.".nominal <> 0
        ";

        if ( !in_array('all', $perusahaan) ) {
            $sql .= " and ".$alias.".perusahaan in ('".implode("', '", $perusahaan)."')";
        }
        if ( !in_array('all', $unit) ) {
            $sql .= " and ".$alias.".unit in ('".implode("', '", $unit)."')";
        }

        return $sql;
    }

    /**
     * RHPP (kemitraan plasma, "Jasa Maklon") -- driver det_jurnal (tbl_name rhpp/rhpp_group),
     * di-enrich lewat tbl_id -> rhpp/rhpp_group utk ambil rate & bruto asli, lalu -> mitra
     * (via nim+kandang / nomor) utk NPWP/NIK/Suket. det_jurnal.mitra sendiri isinya KODE
     * (mitra.nomor), BUKAN nama -- makanya nama tetap diambil dari rhpp.mitra/rhpp_group_header.mitra
     * (kolom itu di tabel SUMBER isinya nama, beda konvensi dgn kolom generik di det_jurnal).
     * Divalidasi: 350 baris, total bruto & jumlah baris final(0,5%) identik dgn hasil query
     * langsung ke tabel rhpp/rhpp_group (tanpa lewat det_jurnal).
     */
    public function getDataRhpp($params) {
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);

        $mtr_by_kandang = "
            select
                k.kandang, mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, mtr.skb
            from mitra mtr
            right join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    mtr.id = mm.mitra
            left join
                kandang k
                on
                    k.mitra_mapping = mm.id
            group by
                k.kandang, mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, mtr.skb
        ";
        $mtr_by_nomor = "
            select
                mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, mtr.skb
            from mitra mtr
            right join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    mtr.id = mm.mitra
            group by
                mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, mtr.skb
        ";

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.tanggal as tgl_rhpp,
                r.mitra as nama,
                mtr.ktp,
                REPLACE(REPLACE(mtr.npwp, '-', ''), '.', '') as npwp,
                r.prs_potongan_pajak,
                r.pdpt_peternak_belum_pajak as bruto,
                mtr.skb
            from det_jurnal dj
            left join
                rhpp r
                on
                    r.id = dj.tbl_id
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            left join
                (".$mtr_by_kandang.") mtr
                on
                    mtr.nim = SUBSTRING(ts.noreg, 0, 8) and
                    mtr.kandang = cast(SUBSTRING(ts.noreg, 10, 2) as int)
            where
                ".$this->sqlFilterDasarJurnal($periode, $params)." and
                dj.coa_asal = '24623.000' and
                dj.tbl_name = 'rhpp'

            union all

            select
                dj.tanggal as tgl_rhpp,
                rgh.mitra as nama,
                mtr.ktp,
                REPLACE(REPLACE(mtr.npwp, '-', ''), '.', '') as npwp,
                rg.prs_potongan_pajak,
                rg.pdpt_peternak_belum_pajak as bruto,
                mtr.skb
            from det_jurnal dj
            left join
                rhpp_group rg
                on
                    rg.id = dj.tbl_id
            left join
                rhpp_group_header rgh
                on
                    rg.id_header = rgh.id
            left join
                (".$mtr_by_nomor.") mtr
                on
                    mtr.nomor = rgh.nomor
            where
                ".$this->sqlFilterDasarJurnal($periode, $params)." and
                dj.coa_asal = '24623.000' and
                dj.tbl_name = 'rhpp_group'

            order by
                tgl_rhpp asc,
                nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**
     * OA Pakan (jasa angkutan/ekspedisi) -- driver det_jurnal (tbl_name realisasi_pembayaran,
     * kolom ekspedisi terisi), di-enrich lewat tbl_id -> realisasi_pembayaran_det (transaksi=
     * 'OA PAKAN') -> konfirmasi_pembayaran_oa_pakan (by nomor) -> ekspedisi (by ekspedisi_id).
     * Divalidasi: 110 baris, total bruto & jumlah baris final identik dgn query langsung ke
     * konfirmasi_pembayaran_oa_pakan.
     */
    public function getDataOa($params) {
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.tanggal as tgl_bayar,
                kpop.ekspedisi as nama,
                eks.nik,
                eks.npwp,
                case
                    when kpop.potongan_pph_23 > 0 then
                        (kpop.potongan_pph_23 / kpop.sub_total) * 100
                    else
                        0
                end as prs_potongan_pajak,
                kpop.sub_total as bruto,
                eks.skb
            from det_jurnal dj
            left join
                (select distinct id_header, no_bayar from realisasi_pembayaran_det where transaksi = 'OA PAKAN') rpd
                on
                    rpd.id_header = dj.tbl_id
            left join
                konfirmasi_pembayaran_oa_pakan kpop
                on
                    kpop.nomor = rpd.no_bayar
            left join
                (
                    select eks1.* from ekspedisi eks1
                    right join
                        (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                        on
                            eks1.id = eks2.id
                ) eks
                on
                    kpop.ekspedisi_id = eks.nomor
            where
                ".$this->sqlFilterDasarJurnal($periode, $params)." and
                dj.coa_asal = '24623.000' and
                dj.tbl_name = 'realisasi_pembayaran' and
                dj.ekspedisi is not null
            order by
                dj.tanggal asc,
                kpop.ekspedisi asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * DATA - DOC (PPh 22 pembelian DOC)
     **************************************************************************************/
    /**
     * Diagregasi per region (Jateng/Jatim, dari wilayah.induk) + supplier + bulan, sesuai
     * pola "Kw DOC <region> <YYMM>" di file sample (1 baris konsolidasi per region per
     * supplier per bulan, bukan per invoice).
     *
     * Sumber: det_jurnal (bukan konfirmasi_pembayaran_doc + netting CN/memo manual).
     * Divalidasi terhadap file sample April 2026: pendekatan netting manual meleset
     * ~1,19M di region Jatim, sedangkan angka PPh 22 yg BENAR-BENAR ke-posting ke GL
     * (coa 24622.000 "PPH Psl 22", asal tbl_name='realisasi_pembayaran') cocok PERSIS
     * (Jateng & Jatim exact match). Bruto = nominal PPh dibagi tarif 0,25% (tarif ini
     * sendiri sdh established dipakai konsisten di seluruh formula PPh22 lain di codebase,
     * lihat LaporanHutangRingkas.php:754).
     */
    public function getDataDoc($params) {
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                case
                    when wp.nama like 'Jawa Timur%' then 'Jatim'
                    when wp.nama like 'Jawa Tengah%' or wp.nama = 'YOGYAKARTA' then 'Jateng'
                    else 'Lainnya'
                end as region,
                supplier.nomor as no_supplier,
                REPLACE(REPLACE(supplier.npwp, '-', ''), '.', '') as npwp,
                supplier.nik,
                supplier.nama,
                sum(dj.nominal / 0.0025) as bruto
            from det_jurnal dj
            left join
                (
                    select w1.* from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah where jenis = 'UN' group by kode) w2
                        on
                            w1.id = w2.id
                ) w
                on
                    w.kode = dj.unit
            left join
                wilayah wp
                on
                    w.induk = wp.id
            left join
                (
                    select p1.* from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' group by nomor) p2
                        on
                            p1.id = p2.id
                ) supplier
                on
                    supplier.nomor = dj.supplier
            where
                ".$this->sqlFilterDasarJurnal($periode, $params)." and
                dj.coa_asal = '24622.000' and
                dj.tbl_name = 'realisasi_pembayaran' and
                dj.supplier is not null
            group by
                case
                    when wp.nama like 'Jawa Timur%' then 'Jatim'
                    when wp.nama like 'Jawa Tengah%' or wp.nama = 'YOGYAKARTA' then 'Jateng'
                    else 'Lainnya'
                end,
                supplier.nomor, supplier.npwp, supplier.nik, supplier.nama
            having
                sum(dj.nominal) <> 0
            order by
                region asc,
                supplier.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * DATA - LAINNYA (catch-all: coa 2462x lain / entri tanpa lawan transaksi dikenal)
     **************************************************************************************/
    /**
     * Jaring pengaman: baris di sisi kredit coa 2462x (kecuali 24621) yg TIDAK match salah
     * satu dari 3 pola di atas (rhpp/rhpp_group/realisasi_pembayaran+supplier/ekspedisi) --
     * misal koreksi manual langsung ke coa PPh tanpa lewat proses rhpp/oa/doc, atau coa baru
     * (24625/24626/24629) yg belum ada pola khususnya. NPWP dikosongkan (sesuai arahan user)
     * krn tidak ada lawan transaksi utk ditelusuri; KODE_OBJEK_PAJAK & nama diisi seadanya
     * dari keterangan jurnal, DITANDAI utk dicek manual sebelum submit.
     */
    public function getDataLainnya($params) {
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.tanggal,
                dj.coa_asal,
                cast(dj.keterangan as varchar(200)) as keterangan,
                dj.nominal as bruto
            from det_jurnal dj
            where
                ".$this->sqlFilterDasarJurnal($periode, $params)." and
                not (
                    (dj.coa_asal = '24622.000' and dj.tbl_name = 'realisasi_pembayaran' and dj.supplier is not null) or
                    (dj.coa_asal = '24623.000' and dj.tbl_name = 'realisasi_pembayaran' and dj.ekspedisi is not null) or
                    (dj.coa_asal = '24623.000' and dj.tbl_name = 'rhpp') or
                    (dj.coa_asal = '24623.000' and dj.tbl_name = 'rhpp_group')
                )
            order by
                dj.tanggal asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * GABUNGAN
     **************************************************************************************/
    /**
     * Urutan blok (DOC lalu OA lalu RHPP) & penomoran NO sekuensial lintas blok mengikuti
     * pola persis di file sample "template-import-Ortax BPPU.xlsx".
     */
    public function getRows($params) {
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);
        $rows = array();

        $doc = $this->getDataDoc($params);
        if ( !empty($doc) ) {
            foreach ($doc as $d) {
                $nomor_dokumen = 'Kw DOC '.$d['region'].' '.$periode['yymm'];
                // Bukti potong konsolidasi bulanan: tgl pemotongan/dokumen = akhir bulan, sesuai sample.
                $rows[] = $this->buildRow($periode, $periode['end_date'], $d['npwp'], $d['nik'], $d['nama'], $d['bruto'], 0, null, '22-100-18', $nomor_dokumen);
            }
        }

        $oa = $this->getDataOa($params);
        if ( !empty($oa) ) {
            $nomor_dokumen = 'Tag OA '.$periode['yymm'];
            foreach ($oa as $o) {
                $rows[] = $this->buildRow($periode, $o['tgl_bayar'], $o['npwp'], $o['nik'], $o['nama'], $o['bruto'], $o['prs_potongan_pajak'], $o['skb'], '24-104-56', $nomor_dokumen);
            }
        }

        $rhpp = $this->getDataRhpp($params);
        if ( !empty($rhpp) ) {
            $nomor_dokumen = 'RHPP '.$periode['yymm'];
            foreach ($rhpp as $r) {
                $rows[] = $this->buildRow($periode, $r['tgl_rhpp'], $r['npwp'], $r['ktp'], $r['nama'], $r['bruto'], $r['prs_potongan_pajak'], $r['skb'], '24-104-31', $nomor_dokumen);
            }
        }

        $lainnya = $this->getDataLainnya($params);
        if ( !empty($lainnya) ) {
            foreach ($lainnya as $l) {
                $row = $this->buildRow($periode, $l['tanggal'], '', '', $l['keterangan'], $l['bruto'], 0, null, $this->kodeObjekPajakDariCoa($l['coa_asal']), $l['keterangan']);
                $row['keterangan1'] = 'PERLU CEK MANUAL - tidak ada lawan transaksi (mitra/ekspedisi/supplier) di jurnal';
                $rows[] = $row;
            }
        }

        foreach ($rows as $i => $row) {
            $rows[$i]['no'] = $i + 1;
        }

        return $rows;
    }

    /**
     * Tebakan terbaik KODE_OBJEK_PAJAK utk baris "Lainnya" (tanpa lawan transaksi dikenal),
     * murni dari kepala coa -- tidak bisa membedakan Jasa Maklon vs Ekspedisi utk 24623
     * (butuh rate/lawan transaksi yg justru tidak ada di baris ini), makanya dikosongkan.
     */
    public function kodeObjekPajakDariCoa($coa) {
        if ( $coa == '24622.000' ) {
            return '22-100-18';
        }
        return '';
    }

    /**************************************************************************************
     * AJAX
     **************************************************************************************/
    public function getLists() {
        $params = $this->input->get('params');

        $rows = $this->getRows($params);

        $list = array();
        $total_bruto = 0;
        foreach ($rows as $row) {
            $total_bruto += $row['bruto'];
            $list[] = array(
                $row['no'],
                $row['masa_pajak'],
                $row['tahun_pajak'],
                tglIndonesia($row['tanggal_pemotongan'], '-', ' '),
                $row['nitku_pemotong'],
                $row['npwp_penerima'],
                $row['nitku_penerima'],
                $row['nama_penerima'],
                $row['kode_objek_pajak'],
                $row['bruto'],
                $row['fasilitas'],
                $row['no_fasilitas'],
                $row['jenis_dokumen'],
                $row['nomor_dokumen'],
                tglIndonesia($row['tanggal_dokumen'], '-', ' '),
            );
        }

        $this->result['data'] = $list;
        $this->result['total'] = array(
            'bruto' => $total_bruto,
            'jumlah' => count($rows),
        );

        display_json( $this->result );
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**************************************************************************************
     * EXPORT
     **************************************************************************************/
    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );
        $periode = $this->buildPeriode($params['bulan'], $params['tahun']);

        $rows = $this->getRows($params);

        $filename = "PPH_UNIFIKASI_".$periode['tahun'].$periode['bulan'].".xlsx";

        $this->exportExcelPphUnifikasi( $filename, $rows );
    }

    public function exportExcelPphUnifikasi( $file_name, $rows ) {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DATA');

        $header = array('NO', 'MASA_PAJAK', 'TAHUN_PAJAK', 'TANGGAL_PEMOTONGAN', 'NITKU_PEMOTONG', 'NPWP_PENERIMA', 'NITKU_PENERIMA', 'NAMA_PENERIMA', 'KODE_OBJEK_PAJAK', 'BRUTO', 'FASILITAS', 'NO_FASILITAS', 'TARIF_LAINNYA', 'JENIS_DOKUMEN', 'NOMOR_DOKUMEN', 'TANGGAL_DOKUMEN', 'EMAIL', 'KETERANGAN1', 'KETERANGAN2', 'KETERANGAN3', 'KETERANGAN4', 'KETERANGAN5', 'Referensi', 'Nomor Bukti Potong Diganti', 'Pengganti Ke-');
        // Kolom kode/leading-zero diformat teks ('@') spy tidak hilang saat dibuka Excel (pola sama spt FpOrtax).
        $textFormatCols = array('B', 'E', 'F', 'G', 'I', 'K', 'L');

        for ($i=0; $i < count($header); $i++) {
            $huruf = toAlpha($i+1);
            $sheet->setCellValue($huruf.'1', $header[$i]);
            $sheet->getStyle($huruf.'1')->applyFromArray(array('font' => array('bold' => true)));
        }

        $baris = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$baris, $row['no']);
            $sheet->setCellValueExplicit('B'.$baris, $row['masa_pajak'], DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$baris, $row['tahun_pajak']);

            $dtPemotongan = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', $row['tanggal_pemotongan']));
            $sheet->setCellValue('D'.$baris, $dtPemotongan);

            $sheet->setCellValueExplicit('E'.$baris, $row['nitku_pemotong'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F'.$baris, $row['npwp_penerima'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G'.$baris, $row['nitku_penerima'], DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$baris, $row['nama_penerima']);
            $sheet->setCellValueExplicit('I'.$baris, $row['kode_objek_pajak'], DataType::TYPE_STRING);
            $sheet->setCellValue('J'.$baris, $row['bruto']);
            $sheet->setCellValueExplicit('K'.$baris, $row['fasilitas'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L'.$baris, $row['no_fasilitas'], DataType::TYPE_STRING);
            $sheet->setCellValue('M'.$baris, $row['tarif_lainnya']);
            $sheet->setCellValue('N'.$baris, $row['jenis_dokumen']);
            $sheet->setCellValue('O'.$baris, $row['nomor_dokumen']);

            $dtDokumen = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', $row['tanggal_dokumen']));
            $sheet->setCellValue('P'.$baris, $dtDokumen);

            $sheet->setCellValue('Q'.$baris, $row['email']);
            $sheet->setCellValue('R'.$baris, $row['keterangan1']);
            $sheet->setCellValue('S'.$baris, $row['keterangan2']);
            $sheet->setCellValue('T'.$baris, $row['keterangan3']);
            $sheet->setCellValue('U'.$baris, $row['keterangan4']);
            $sheet->setCellValue('V'.$baris, $row['keterangan5']);
            $sheet->setCellValue('W'.$baris, $row['referensi']);
            $sheet->setCellValue('X'.$baris, $row['no_bukti_potong_diganti']);
            $sheet->setCellValue('Y'.$baris, $row['pengganti_ke']);

            $baris++;
        }

        $lastRow = $baris - 1;
        if ( $lastRow >= 2 ) {
            $sheet->setAutoFilter('A1:Y'.$lastRow);

            foreach (array('D', 'P') as $col) {
                $sheet->getStyle($col.'2:'.$col.$lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            }
            foreach ($textFormatCols as $col) {
                $sheet->getStyle($col.'2:'.$col.$lastRow)->getNumberFormat()->setFormatCode('@');
            }
        }

        $colWidths = array('D'=>16,'F'=>18,'G'=>22,'H'=>27,'I'=>13,'J'=>16,'O'=>20,'P'=>16,'W'=>20);
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('export_excel/'.$file_name);

        $this->load->helper('download');
        force_download('export_excel/'.$file_name, NULL);
    }
}
