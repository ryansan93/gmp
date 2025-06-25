<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class LabaRugi extends Public_Controller {

    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/laba_rugi/js/laba-rugi.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laba_rugi/css/laba-rugi.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laba Rugi';

            // Load Indexx
            $data['view'] = $this->load->view('report/laba_rugi/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = $d_perusahaan['kode_gabung_perusahaan'];
                $key_detail = strtoupper($d_perusahaan->perusahaan).' | '.$d_perusahaan->kode;

                $data[ $key ]['kode_gabung_perusahaan'] = $d_perusahaan['kode_gabung_perusahaan'];
                $data[ $key ]['detail'][ $key_detail ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->get('params');

        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);

        $sql_unit = "and rdim_submit.kode_unit = '".$unit."'";
        if ( $unit == 'all' ) {
            $sql_unit = null;
        }

        $sql_perusahaan = "and prs.kode_gabung_perusahaan = '".$perusahaan."'";
        if ( $perusahaan == 'all' ) {
            $sql_perusahaan = null;
        }

        $i = 0;
        $_bulan = 12;
        if ( $bulan != 'all' ) {
            $i = $bulan-1;
            $_bulan = $bulan;
        }

        $data = null;
        for (; $i < $_bulan; $i++) { 
            $angka_bulan = (strlen($i+1) == 1) ? '0'.$i+1 : $i+1;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date)).' 00:00:00';
            $end_date = date("Y-m-t", strtotime($date)).' 23:59:59';

            $sql = "
                select
                    count(*) as jumlah_rhpp,
                    sum(ekor_panen) as ekor_panen,
                    sum(total_pakai_pakan) as total_pakai_pakan,
                    sum(lama_panen) / count(*) as lama_panen,
                    sum(ekor_panen) as ekor_panen,
                    sum(kg_panen) as kg_panen,
                    sum(total) as total,
                    sum(rata_harga_panen) / count(*) as rata_harga_panen,
                    sum(umur) / count(*) as umur,
                    ABS(((sum(populasi_panen) - sum(ekor_panen)) / sum(populasi_panen)) * 100) as deplesi,
                    sum(fcr) / count(*) as fcr,
                    sum(bb) / count(*) as bb,
                    sum(ip) / count(*) as ip,
                    sum(rhpp_ke_pusat) / count(*) as rata_rhpp_ke_pusat,
                    sum(transfer) / count(*) as rata_transfer,
                    sum(lr_inti) as lr_inti,
                    ABS(sum(lr_inti)) - sum(biaya_operasional) as lr_inti_tanpa_ops_300,
                    sum(bonus_pasar) as bonus_pasar,
                    sum(tot_pembelian_sapronak) as total_sapronak,
                    sum(pdpt_peternak_belum_pajak) as total_pendapatan_peternak,
                    (sum(pdpt_peternak_belum_pajak) / sum(populasi_panen)) as rata_total_pendapatan_peternak,
                    sum(biaya_materai) as total_biaya_materai,
                    sum(biaya_operasional) as total_biaya_ops_300,
                    sum(modal_inti) / count(*) as modal_inti,
                    sum(modal_inti_sebenarnya) / count(*) as modal_inti_sebenarnya
                from (
                    select 
                        ts.noreg,
                        ts.tgl_tutup,
                        CAST(drs.ekor_panen AS FLOAT) as ekor_panen,
                        sum(drs.kg_panen) as kg_panen,
                        drs.total as total,
                        drs.rata_harga_panen as rata_harga_panen,
                        kp_tujuan.total_pakan as total_pakan_terima,
                        CASE
                            WHEN kp_asal.total_pakan IS NOT NULL THEN kp_asal.total_pakan
                            ELSE 0
                        END AS total_pakan_pindah,
                        CASE
                            WHEN rp.total_retur IS NOT NULL THEN rp.total_retur
                            ELSE 0
                        END AS total_retur,
                        CASE
                            WHEN kp_asal.total_pakan IS NOT NULL AND rp.total_retur IS NOT NULL THEN (kp_tujuan.total_pakan - kp_asal.total_pakan - rp.total_retur)
                            WHEN kp_asal.total_pakan IS NOT NULL AND rp.total_retur IS NULL THEN (kp_tujuan.total_pakan - kp_asal.total_pakan)
                            WHEN kp_asal.total_pakan IS NULL AND rp.total_retur IS NOT NULL THEN (kp_tujuan.total_pakan - rp.total_retur)
                            ELSE kp_tujuan.total_pakan
                        END AS total_pakai_pakan,
                        drs.tgl_panen_awal,
                        drs.tgl_panen_akhir,
                        drs.lama_panen,
                        (DateDiff (Day,rhpp.tgl_docin,max(drs.tgl_panen_akhir)) + 1) as umur,
                        CAST(rhpp.populasi AS FLOAT) as populasi_panen,
                        CAST(rhpp.fcr AS FLOAT) as fcr,
                        CAST(rhpp.bb AS FLOAT) as bb,
                        CAST(rhpp.ip AS FLOAT) as ip,
                        (sum((DateDiff (Day,drs.tgl_panen_akhir,ts.tgl_tutup) + 1)) / count(*)) as rhpp_ke_pusat,
                        (sum((DateDiff (Day,drs.tgl_panen_akhir,kp.tgl_bayar) + 1)) / count(*)) as transfer,
                        rhpp.lr_inti,
                        rhpp.bonus_pasar,
                        rhpp.tot_pembelian_sapronak,
                        rhpp.pdpt_peternak_belum_pajak,
                        rhpp.biaya_materai,
                        rhpp.biaya_operasional,
                        (rhpp.tot_pembelian_sapronak+rhpp.pdpt_peternak_belum_pajak+rhpp.biaya_materai+rhpp.biaya_operasional) / sum(drs.kg_panen) as modal_inti,
                        ((rhpp.tot_pembelian_sapronak+rhpp.pdpt_peternak_belum_pajak+rhpp.biaya_materai+rhpp.biaya_operasional)-rhpp.bonus_pasar) / sum(drs.kg_panen) as modal_inti_sebenarnya
                    from
                        tutup_siklus ts 
                    right join
                        (
                            select max(id) as id, noreg from tutup_siklus group by noreg
                        ) _ts
                        on
                            ts.id = _ts.id
                    right join
                        (
                            select r.noreg, r.populasi, r.tgl_docin, r.jml_panen_ekor, r.jml_panen_kg, r.bb, r.fcr, r.deplesi, r.rata_umur, r.ip, rhpp_inti.lr_inti, rhpp_plasma.bonus_pasar, rhpp_inti.tot_pembelian_sapronak, rhpp_plasma.pdpt_peternak_belum_pajak, rhpp_inti.biaya_materai, rhpp_inti.biaya_operasional
                            from rhpp r
                            right join
                                (
                                    select noreg, bonus_pasar, pdpt_peternak_belum_pajak from rhpp r where jenis = 'rhpp_plasma'
                                ) rhpp_plasma
                                on
                                    rhpp_plasma.noreg = r.noreg 
                            right join
                                (
                                    select noreg, lr_inti, biaya_materai, biaya_operasional, tot_pembelian_sapronak from rhpp r where jenis = 'rhpp_inti'
                                ) rhpp_inti
                                on
                                    rhpp_inti.noreg = r.noreg
                            right join
                                tutup_siklus ts 
                                on
                                    r.id_ts = ts.id
                            right join
                                (
                                    select rs.noreg, k.id as id_kandang, k.kandang as no_kandang, w.kode as kode_unit from rdim_submit rs
                                    right join
                                        kandang k 
                                        on
                                            rs.kandang = k.id
                                    right join
                                        wilayah w 
                                        on
                                            k.unit = w.id
                                )  rdim_submit
                                on
                                    rdim_submit.noreg = r.noreg 
                            where
                                r.lr_inti is not null and
                                ts.tgl_tutup between '".$start_date."' and '".$end_date."'
                                ".$sql_unit."
                            group by r.noreg, r.populasi, r.tgl_docin, r.jml_panen_ekor, r.jml_panen_kg, r.bb, r.fcr, r.deplesi, r.rata_umur, r.ip, rhpp_inti.lr_inti, rhpp_plasma.bonus_pasar, rhpp_inti.tot_pembelian_sapronak, rhpp_plasma.pdpt_peternak_belum_pajak, rhpp_inti.biaya_materai, rhpp_inti.biaya_operasional
                        ) rhpp
                        on
                            rhpp.noreg = ts.noreg
                    right join
                        (
                            select rs.noreg, k.id as id_kandang, k.kandang as no_kandang, w.kode as kode_unit from rdim_submit rs
                            right join
                                kandang k 
                                on
                                    rs.kandang = k.id
                            right join
                                wilayah w 
                                on
                                    k.unit = w.id
                        )  rdim_submit
                        on
                            rdim_submit.noreg = rhpp.noreg 
                    left join
                        (
                            select 
                                rs.noreg as noreg, 
                                min(rs.tgl_panen) as tgl_panen_awal,
                                max(rs.tgl_panen) as tgl_panen_akhir,
                                (DateDiff (Day,min(rs.tgl_panen),max(rs.tgl_panen)) + 1) as lama_panen,
                                (DateDiff (Day,min(rs.tgl_panen),max(rs.tgl_panen)) + 1) as umur_panen,
                                sum(drs.ekor) as ekor_panen,
                                sum(drs.tonase) as kg_panen,
                                sum(drs.tonase * drs.harga) as total, 
                                (sum(drs.tonase * drs.harga) / sum(drs.tonase)) as rata_harga_panen
                            from det_real_sj drs 
                            right join
                                (select max(id) as id, tgl_panen, noreg from real_sj group by tgl_panen, noreg) rs 
                                on
                                    drs.id_header = rs.id
                            group by 
                                rs.noreg  
                        ) drs 
                        on
                            drs.noreg = rhpp.noreg
                    left join
                        (
                            select kp.tujuan as tujuan, sum(dtp.jumlah) as total_pakan from kirim_pakan kp 
                            left join
                                terima_pakan tp 
                                on
                                    kp.id = tp.id_kirim_pakan 
                            left join
                                det_terima_pakan dtp  
                                on
                                    tp.id = dtp.id_header 
                            group by
                                kp.tujuan
                        ) kp_tujuan
                        on
                            drs.noreg = kp_tujuan.tujuan 
                    left join
                        (
                            select 
                                kp.asal as asal, 
                                sum(dtp.jumlah) AS total_pakan 
                            from kirim_pakan kp 
                            left join
                                terima_pakan tp 
                                on
                                    kp.id = tp.id_kirim_pakan 
                            left join
                                det_terima_pakan dtp  
                                on
                                    tp.id = dtp.id_header 
                            group by
                                kp.asal
                        ) kp_asal
                        on
                            drs.noreg = kp_asal.asal 
                    left join
                        (
                            select rp.id_asal, sum(drp.jumlah) as total_retur from retur_pakan rp 
                            left join
                                det_retur_pakan drp 
                                on
                                    rp.id = drp.id_header 
                            group by
                                rp.id_asal
                        ) as rp
                        on
                            drs.noreg = rp.id_asal
                    left join
                        (
                            select
                                kppd2.noreg,
                                kpp.tgl_bayar
                            from 
                                konfirmasi_pembayaran_peternak_det2 kppd2
                            right join
                                konfirmasi_pembayaran_peternak_det kppd
                                on
                                    kppd2.id_header = kppd.id 
                            right join
                                konfirmasi_pembayaran_peternak kpp 
                                on
                                    kpp.id = kppd.id_header 
                            group by
                                kppd2.noreg,
                                kpp.tgl_bayar
                        ) kp
                        on
                            kp.noreg = ts.noreg 
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            left join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mm.nim = substring(ts.noreg, 0, 8)
                    left join
                        mitra m
                        on
                            m.id = mm.mitra
                    left join
                        perusahaan prs
                        on
                            prs.kode = m.perusahaan
                    where
                        ts.tgl_tutup between '".$start_date."' and '".$end_date."'
                        ".$sql_unit."
                        ".$sql_perusahaan."
                    group by
                        ts.noreg,
                        ts.tgl_tutup,
                        kp_tujuan.total_pakan,
                        kp_asal.total_pakan,
                        rp.total_retur,
                        drs.ekor_panen,
                        drs.total,
                        drs.rata_harga_panen,
                        drs.tgl_panen_awal,
                        drs.tgl_panen_akhir,
                        drs.lama_panen,
                        drs.umur_panen,
                        rhpp.tgl_docin,
                        rhpp.populasi,
                        rhpp.fcr,
                        rhpp.bb,
                        rhpp.ip,
                        rhpp.lr_inti,
                        rhpp.bonus_pasar,
                        rhpp.tot_pembelian_sapronak,
                        rhpp.pdpt_peternak_belum_pajak,
                        rhpp.biaya_materai,
                        rhpp.biaya_operasional
                ) as data
            ";

            $m_conf = new \Model\Storage\Conf();
            $d_conf = $m_conf->hydrateRaw($sql);

            $_data = null;
            if ( $d_conf->count() > 0 ) {
                $_data = $d_conf->toArray();
            }

            $nama_bulan = explode(' ', tglIndonesia($date, '-', ' ', true))[1];

            $kolom_performa = array('ekor_panen', 'total_pakai_pakan', 'lama_panen', 'umur', 'deplesi', 'fcr', 'bb', 'ip');
            $kolom_panen_dan_rhpp_plasma = array('ekor_panen', 'kg_panen', 'total', 'rata_harga_panen', 'total_pendapatan_peternak', 'rata_total_pendapatan_peternak', 'rata_rhpp_ke_pusat', 'rata_transfer');
            $kolom_laporan_inti = array('modal_inti', 'modal_inti_sebenarnya', 'total_biaya_ops_300', 'lr_inti_tanpa_ops_300', 'lr_inti', 'total_biaya_ops_300');
            $performa = null;
            $panen_dan_rhpp_plasma = null;
            $laporan_inti = null;
            if ( !empty($_data) ) {
                if ( !empty($kolom_performa) ) {
                    foreach ($kolom_performa as $key => $value) {
                        if ( !empty($_data[0][ $value ]) ) {
                            $performa[ $value ] = $_data[0][ $value ];
                        }
                    }
                }

                if ( !empty($kolom_panen_dan_rhpp_plasma) ) {
                    foreach ($kolom_panen_dan_rhpp_plasma as $key => $value) {
                        if ( !empty($_data[0][ $value ]) ) {
                            $panen_dan_rhpp_plasma[ $value ] = $_data[0][ $value ];
                        }
                    }
                }

                if ( !empty($kolom_laporan_inti) ) {
                    foreach ($kolom_laporan_inti as $key => $value) {
                        if ( !empty($_data[0][ $value ]) ) {
                            $laporan_inti[ $value ] = $_data[0][ $value ];
                        }
                    }
                }
            }

            $data[ $i ]['bulan'] = $nama_bulan;
            $data[ $i ]['jumlah_rhpp'] = $_data[0]['jumlah_rhpp'];
            $data[ $i ]['data'] = array(
                'performa' => $performa, 
                'panen_dan_rhpp_plasma' => $panen_dan_rhpp_plasma, 
                'laporan_inti' => $laporan_inti
            );
        }

        $content['data'] = $data;
        $content['tahun'] = $tahun;
        $content['unit'] = $unit;
        $content['perusahaan'] = $perusahaan;

        $html = $this->load->view('report/laba_rugi/list', $content, TRUE);

        echo $html;
    }

    public function getDataViewForm($unit, $bulan, $tahun, $perusahaan)
    {
        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;

        $sql_unit = null;
        if ( $unit != 'all' ) {
            $sql_unit = "and rdim_submit.kode_unit = '".$unit."'";
        }

        $sql_perusahaan = null;
        if ( $perusahaan != 'all' ) {
            $sql_perusahaan = "and prs.kode_gabung_perusahaan = '".$perusahaan."'";
        }

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date)).' 00:00:00';
        $end_date = date("Y-m-t", strtotime($date)).' 23:59:59';

        $sql = "
            select 
                rdim_submit.nama_mitra,
                rdim_submit.no_kandang,
                CAST(SUBSTRING(ts.noreg, 8, 2) as int) as no_siklus,
                ts.noreg,
                ts.tgl_docin,
                CAST(rhpp.populasi AS FLOAT) as populasi_panen,
                rhpp.nama_doc,
                ts.tgl_tutup,
                CAST(drs.ekor_panen AS FLOAT) as ekor_panen,
                drs.kg_panen as kg_panen,
                drs.total as total,
                drs.rata_harga_panen as rata_harga_panen,
                kp_tujuan.kode_barang as kode_barang,
                kp_tujuan.nama_barang as nama_barang,
                sum(kp_tujuan.total_pakan) as total_pakan,
                drs.tgl_panen_awal,
                drs.tgl_panen_akhir,
                drs.lama_panen,
                (DateDiff (Day,rhpp.tgl_docin,max(drs.tgl_panen_akhir)) + 1) as umur,
                CAST(rhpp.fcr AS FLOAT) as fcr,
                CAST(rhpp.bb AS FLOAT) as bb,
                CAST(rhpp.ip AS FLOAT) as ip,
                CAST(rhpp.deplesi AS FLOAT) as deplesi,
                kp.tgl_bayar,
                (DateDiff (Day,drs.tgl_panen_akhir,ts.tgl_tutup) + 1) as rhpp_ke_pusat,
                (DateDiff (Day,ts.tgl_tutup,kp.tgl_bayar)) as transfer,
                rhpp.lr_inti,
                rhpp.bonus_pasar,
                rhpp.tot_pembelian_sapronak,
                rhpp.pdpt_peternak_belum_pajak,
                rhpp.biaya_materai,
                rhpp.biaya_operasional,
                (rhpp.tot_pembelian_sapronak+rhpp.pdpt_peternak_belum_pajak+rhpp.biaya_materai+rhpp.biaya_operasional) / drs.kg_panen as modal_inti,
                ((rhpp.tot_pembelian_sapronak+rhpp.pdpt_peternak_belum_pajak+rhpp.biaya_materai+rhpp.biaya_operasional)-rhpp.bonus_pasar) / drs.kg_panen as modal_inti_sebenarnya
            from
                tutup_siklus ts 
            right join
                (
                    select r.noreg, r.populasi, r.tgl_docin, r.jml_panen_ekor, r.jml_panen_kg, r.bb, r.fcr, r.deplesi, r.rata_umur, r.ip, rhpp_inti.lr_inti, rhpp_plasma.bonus_pasar, rhpp_inti.tot_pembelian_sapronak, rhpp_plasma.pdpt_peternak_belum_pajak, rhpp_inti.biaya_materai, rhpp_inti.biaya_operasional, rhpp_inti.barang as nama_doc
                    from rhpp r
                    right join
                        (
                            select noreg, bonus_pasar, pdpt_peternak_belum_pajak from rhpp r where jenis = 'rhpp_plasma'
                        ) rhpp_plasma
                        on
                            rhpp_plasma.noreg = r.noreg 
                    right join
                        (
                            select 
                                r.noreg, 
                                r.lr_inti, 
                                r.biaya_materai, 
                                r.biaya_operasional, 
                                r.tot_pembelian_sapronak,
                                rd.barang
                            from rhpp r 
                            right join
                                rhpp_doc rd
                                on
                                    r.id = rd.id_header
                            where 
                                r.jenis = 'rhpp_inti'
                        ) rhpp_inti
                        on
                            rhpp_inti.noreg = r.noreg
                    right join
                        tutup_siklus ts 
                        on
                            r.id_ts = ts.id
                    right join
                        (
                            select 
                                rs.noreg, 
                                k.id as id_kandang, 
                                k.kandang as no_kandang, 
                                w.kode as kode_unit 
                            from 
                                rdim_submit rs
                            right join
                                kandang k 
                                on
                                    rs.kandang = k.id
                            right join
                                wilayah w 
                                on
                                    k.unit = w.id
                        )  rdim_submit
                        on
                            rdim_submit.noreg = r.noreg 
                    where
                        r.lr_inti is not null and
                        ts.tgl_tutup between '".$start_date."' and '".$end_date."'
                        ".$sql_unit."
                    group by r.noreg, r.populasi, r.tgl_docin, r.jml_panen_ekor, r.jml_panen_kg, r.bb, r.fcr, r.deplesi, r.rata_umur, r.ip, rhpp_inti.lr_inti, rhpp_plasma.bonus_pasar, rhpp_inti.tot_pembelian_sapronak, rhpp_plasma.pdpt_peternak_belum_pajak, rhpp_inti.biaya_materai, rhpp_inti.biaya_operasional, rhpp_inti.barang
                ) rhpp
                on
                    rhpp.noreg = ts.noreg
            right join
                (
                    select 
                        rs.noreg, 
                        m.nama as nama_mitra,
                        k.id as id_kandang, 
                        k.kandang as no_kandang, 
                        w.kode as kode_unit,
                        m.perusahaan as kode_prs
                    from rdim_submit rs
                    right join
                        kandang k 
                        on
                            rs.kandang = k.id
                    right join
                        wilayah w 
                        on
                            k.unit = w.id
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim ) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.mitra = m.id
                )  rdim_submit
                on
                    rdim_submit.noreg = rhpp.noreg 
            left join
                (
                    select 
                        rs.noreg as noreg, 
                        min(rs.tgl_panen) as tgl_panen_awal,
                        max(rs.tgl_panen) as tgl_panen_akhir,
                        (DateDiff (Day,min(rs.tgl_panen),max(rs.tgl_panen)) + 1) as lama_panen,
                        (DateDiff (Day,min(rs.tgl_panen),max(rs.tgl_panen)) + 1) as umur_panen,
                        sum(drs.ekor) as ekor_panen,
                        sum(drs.tonase) as kg_panen,
                        sum(drs.tonase * drs.harga) as total, 
                        (sum(drs.tonase * drs.harga) / sum(drs.tonase)) as rata_harga_panen
                    from 
                        det_real_sj drs 
                    right join
                        (select max(id) as id, tgl_panen, noreg from real_sj group by tgl_panen, noreg) rs 
                        on
                            drs.id_header = rs.id
                    group by 
                        rs.noreg  
                ) drs 
                on
                    drs.noreg = rhpp.noreg
            right join
                (
                    select 
                        kp.tujuan,
                        kp.kode_barang,
                        brg.nama as nama_barang,
                        kp.total_pakan_terima,
                        pindah_pakan.total_pindah_pakan,
                        retur_pakan.total_retur_pakan,
                        kp.total_pakan_terima - (ISNULL(pindah_pakan.total_pindah_pakan, 0) + ISNULL(retur_pakan.total_retur_pakan, 0)) as total_pakan
                    from 
                        (
                            select 
                                kp.tujuan as tujuan, 
                                dtp.item as kode_barang, 
                                sum(dtp.jumlah) as total_pakan_terima 
                            from kirim_pakan kp 
                            right join
                                terima_pakan tp 
                                on
                                    kp.id = tp.id_kirim_pakan 
                            right join
                                det_terima_pakan dtp  
                                on
                                    tp.id = dtp.id_header 
                            group by
                                kp.tujuan,
                                dtp.item
                        ) kp
                    left join
                        (
                            select 
                                kp.asal as asal, 
                                dtp.item as kode_barang, 
                                sum(dtp.jumlah) AS total_pindah_pakan 
                            from kirim_pakan kp 
                            left join
                                terima_pakan tp 
                                on
                                    kp.id = tp.id_kirim_pakan 
                            left join
                                det_terima_pakan dtp  
                                on
                                    tp.id = dtp.id_header 
                            group by
                                kp.asal,
                                dtp.item
                        ) pindah_pakan
                        on
                            pindah_pakan.asal = kp.tujuan and
                            pindah_pakan.kode_barang = kp.kode_barang
                    left join
                        (
                            select 
                                rp.id_asal, 
                                drp.item as kode_barang,
                                sum(drp.jumlah) as total_retur_pakan 
                            from retur_pakan rp 
                            left join
                                det_retur_pakan drp 
                                on
                                    rp.id = drp.id_header 
                            group by
                                rp.id_asal,
                                drp.item
                        ) retur_pakan
                        on
                            retur_pakan.id_asal = kp.tujuan and
                            retur_pakan.kode_barang = kp.kode_barang
                    right join
                        (
                            select brg1.* from barang brg1
                            right join
                                (select max(id) as id, kode from barang group by kode) brg2
                                on
                                    brg1.id = brg2.id
                        ) brg
                        on
                            brg.kode = kp.kode_barang
                ) kp_tujuan
                on
                    drs.noreg = kp_tujuan.tujuan 
            left join
                (
                    select
                        kppd2.noreg,
                        kpp.tgl_bayar
                    from 
                        konfirmasi_pembayaran_peternak_det2 kppd2
                    right join
                        konfirmasi_pembayaran_peternak_det kppd
                        on
                            kppd2.id_header = kppd.id 
                    right join
                        konfirmasi_pembayaran_peternak kpp 
                        on
                            kpp.id = kppd.id_header 
                    group by
                        kppd2.noreg,
                        kpp.tgl_bayar
                ) kp
                on
                    kp.noreg = ts.noreg 
            left join
                perusahaan prs
                on
                    prs.kode = rdim_submit.kode_prs
            where
                ts.tgl_tutup between '".$start_date."' and '".$end_date."'
                ".$sql_unit."
                ".$sql_perusahaan."
            group by
                rdim_submit.nama_mitra,
                rdim_submit.no_kandang,
                rdim_submit.kode_prs,
                ts.noreg,
                ts.tgl_docin,
                rhpp.populasi,
                rhpp.nama_doc,
                ts.tgl_tutup,
                kp_tujuan.kode_barang,
                kp_tujuan.nama_barang,
                kp_tujuan.total_pakan,
                drs.ekor_panen,
                drs.kg_panen,
                drs.total,
                drs.rata_harga_panen,
                drs.tgl_panen_awal,
                drs.tgl_panen_akhir,
                drs.lama_panen,
                drs.umur_panen,
                kp.tgl_bayar,
                rhpp.tgl_docin,
                rhpp.fcr,
                rhpp.bb,
                rhpp.ip,
                rhpp.deplesi,
                rhpp.lr_inti,
                rhpp.bonus_pasar,
                rhpp.tot_pembelian_sapronak,
                rhpp.pdpt_peternak_belum_pajak,
                rhpp.biaya_materai,
                rhpp.biaya_operasional
        ";

        $m_conf = new \Model\Storage\Conf();
        $d_rhpp = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_rhpp->count() > 0 ) {
            $d_rhpp = $d_rhpp->toArray();

            foreach ($d_rhpp as $key => $value) {
                if ( !isset($data[ $value['noreg'] ]) ) {
                    $data[ $value['noreg'] ] = array(
                        'nama_mitra' => $value['nama_mitra'],
                        'no_kandang' => $value['no_kandang'],
                        'no_siklus' => $value['no_siklus'],
                        'noreg' => $value['noreg'],
                        'tgl_docin' => $value['tgl_docin'],
                        'nama_doc' => $value['nama_doc'],
                        'tgl_tutup' => $value['tgl_tutup'],
                        'ekor_panen' => $value['ekor_panen'],
                        'kg_panen' => $value['kg_panen'],
                        'total' => $value['total'],
                        'rata_harga_panen' => $value['rata_harga_panen'],
                        'detail_pakan' => null,
                        'tgl_panen_awal' => $value['tgl_panen_awal'],
                        'tgl_panen_akhir' => $value['tgl_panen_akhir'],
                        'lama_panen' => $value['lama_panen'],
                        'umur' => $value['umur'],
                        'populasi_panen' => $value['populasi_panen'],
                        'fcr' => $value['fcr'],
                        'bb' => $value['bb'],
                        'ip' => $value['ip'],
                        'deplesi' => $value['deplesi'],
                        'tgl_bayar' => $value['tgl_bayar'],
                        'rhpp_ke_pusat' => $value['rhpp_ke_pusat'],
                        'transfer' => $value['transfer'],
                        'lr_inti' => $value['lr_inti'],
                        'bonus_pasar' => $value['bonus_pasar'],
                        'tot_pembelian_sapronak' => $value['tot_pembelian_sapronak'],
                        'pdpt_peternak_belum_pajak' => $value['pdpt_peternak_belum_pajak'],
                        'biaya_materai' => $value['biaya_materai'],
                        'biaya_operasional' => $value['biaya_operasional'],
                        'modal_inti' => $value['modal_inti'],
                        'modal_inti_sebenarnya' => $value['modal_inti_sebenarnya']
                    );

                    $key = $value['nama_barang'].' | '.$value['kode_barang'];

                    if ( !isset($data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]) ) {
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['kode_barang'] = isset($value['kode_barang']) ? $value['kode_barang'] : null;
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['nama_barang'] = isset($value['nama_barang']) ? $value['nama_barang'] : null;
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['total_pakan'] = isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                        $data[ $value['noreg'] ]['detail_pakan']['total'] = isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                    } else {
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['total_pakan'] += isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                        $data[ $value['noreg'] ]['detail_pakan']['total'] += isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                    }
                } else {
                    $key = $value['nama_barang'].' | '.$value['kode_barang'];

                    if ( !isset($data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]) ) {
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['kode_barang'] = isset($value['kode_barang']) ? $value['kode_barang'] : null;
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['nama_barang'] = isset($value['nama_barang']) ? $value['nama_barang'] : null;
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['total_pakan'] = isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                        $data[ $value['noreg'] ]['detail_pakan']['total'] = isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                    } else {
                        $data[ $value['noreg'] ]['detail_pakan']['detail'][ $key ]['total_pakan'] += isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                        $data[ $value['noreg'] ]['detail_pakan']['total'] += isset($value['total_pakan']) ? $value['total_pakan'] : 0;
                    }
                }

                ksort($data[ $value['noreg'] ]['detail_pakan']['detail']);
                ksort($data);
            }
        }

        return $data;
    }

    public function viewForm()
    {
        $params = $this->input->get('params');

        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $bulan = $params['bulan'];
        $tahun = $params['tahun'];

        $data = $this->getDataViewForm($unit, $bulan, $tahun, $perusahaan);

        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        $date = $tahun.'-'.$angka_bulan.'-01';
        $nama_bulan = explode(' ', tglIndonesia($date, '-', ' ', true))[1];

        $content['nama_bulan'] = $nama_bulan;
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $content['bulan'] = $bulan;
        $content['tahun'] = $tahun;
        $content['data'] = $data;
        $html = $this->load->view('report/laba_rugi/viewForm', $content, TRUE);

        echo $html;
    }

    public function getDataDetail($unit, $bulan, $tahun, $perusahaan) {
        $list_nama_bulan = array(
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER'
        );

        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;

        $sql_unit = null;
        if ( $unit != 'all' ) {
            $sql_unit = "and w.kode = '".$unit."'";
        }

        $sql_perusahaan = null;
        if ( $perusahaan != 'all' ) {
            $sql_perusahaan = "and prs.kode_gabung_perusahaan = '".$perusahaan."'";
        }

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date)).' 00:00:00';
        $end_date = date("Y-m-t", strtotime($date)).' 23:59:59';
        $nama_bulan = $list_nama_bulan[ $bulan ];

        $sql = "
            select
                '".$nama_bulan."' as nama_bulan,
                w.kode,
                prs.perusahaan as nama_perusahaan,
                mtr.nama as nama_mitra,
                mtr.tipe_kdg,
                mtr.nik,
                mtr.npwp,
                rhpp.*
            from
            (
                select 
                    ts.id,
                    mm.nomor as no_mitra, 
                    ts.tgl_tutup, 
                    ts.noreg,
                    cast(SUBSTRING(ts.noreg, 10, 2) as int) as kandang, 
                    cast(SUBSTRING(ts.noreg, 8, 2) as int) as periode, 
                    kry.nama as ppl, 
                    ts.tgl_docin, 
                    rd.barang as jenis_doc, 
                    rd.jumlah as populasi, 
                    rd.total as tot_doc,
                    rp_supl.nama as jenis_pakan,
                    (rp.jumlah - (isnull(rpp.jumlah, 0) + isnull(rrp.jumlah, 0))) as jml_pakan, 
                    isnull(rp.total, 0) as _tot_pakan,
                    isnull(rop.total, 0) as _tot_oa_pakan,
                    isnull(rpp.total, 0) as tot_pindah_pakan,
                    isnull(ropp.total, 0) as tot_oa_pindah_pakan,
                    isnull(rrp.total, 0) as tot_retur_pakan,
                    isnull(rorp.total, 0) as tot_oa_retur_pakan,
                    ((rp.total + rop.total) - ((isnull(rpp.total, 0) + isnull(ropp.total, 0)) + (isnull(rrp.total, 0) + isnull(rorp.total, 0)))) as tot_pakan,
                    rv_inti.total as tot_obat_inti,
                    rv_plasma.total as tot_obat_plasma,
                    r_penjualan.tgl_panen_awal,
                    r_penjualan.tgl_panen_akhir,
                    (DateDiff (Day, r_penjualan.tgl_panen_awal, r_penjualan.tgl_panen_akhir) + 1) as durasi_panen,
                    r_plasma.rata_umur as umur,
                    r_plasma.deplesi,
                    r_plasma.fcr,
                    r_plasma.bb,
                    r_plasma.ip,
                    case
                        when r_penjualan.tgl_panen_akhir <= rpp.tanggal then
                            (DateDiff (Day, r_penjualan.tgl_panen_akhir, rpp.tanggal) + 1)
                        else
                            '-'
                    end as mutasi,
                    r_penjualan.ekor as jml_ekor_terpanen,
                    r_penjualan.tonase as tonase,
                    r_penjualan.total as hasil_penjualan_ayam,
                    (r_penjualan.total / r_penjualan.tonase) as rata_harga,
                    r_plasma.pdpt_peternak_belum_pajak as pdpt_plasma,
                    r_plasma.potongan_pajak,
                    r_piutang.nominal as potongan,
                    trf.bayar as transfer,
                    ts.tgl_tutup as tgl_rhpp_ke_pusat, 
                    trf.tgl_real_bayar as tgl_transfer,
                    (DateDiff (Day, r_penjualan.tgl_panen_akhir, ts.tgl_tutup) + 1) as durasi_rhpp_ke_pusat,
                    (DateDiff (Day, ts.tgl_tutup, trf.tgl_real_bayar) + 1) as durasi_transfer,
                    (r_plasma.pdpt_peternak_belum_pajak / rd.jumlah) as rata_pdpt_plasma_per_populasi,
                    ((r_inti.tot_pembelian_sapronak + r_plasma.pdpt_peternak_belum_pajak + r_inti.biaya_materai + r_inti.biaya_operasional) / r_penjualan.tonase) as modal_inti,
                    ((r_inti.tot_pembelian_sapronak + (r_plasma.pdpt_peternak_belum_pajak - r_plasma.bonus_pasar) + r_inti.biaya_materai + r_inti.biaya_operasional) / r_penjualan.tonase) as modal_inti_tanpa_bonus_pasar,
                    r_inti.lr_inti,
                    (r_inti.lr_inti + r_inti.biaya_operasional) as lr_inti_tanpa_opr,
                    r_inti.biaya_operasional,
                    r_inti.biaya_materai
                from (
                    select ts1.* from tutup_siklus ts1
                    right join
                        (select max(id) as id, noreg from tutup_siklus group by noreg) ts2
                        on
                            ts1.id = ts2.id
                ) ts
                left join
                    (select * from rhpp where jenis = 'rhpp_inti') r_inti
                    on
                        r_inti.id_ts = ts.id
                left join
                    (select * from rhpp where jenis = 'rhpp_plasma') r_plasma
                    on
                        r_plasma.id_ts = ts.id
                left join
                    rhpp_doc rd
                    on
                        rd.id_header = r_inti.id
                left join
                    (
                        select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_pakan group by id_header
                    ) rp
                    on
                        rp.id_header = r_inti.id
                left join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_oa_pakan group by id_header) rop
                    on
                        rop.id_header = r_inti.id
                left join
                    (
                        select data.id_header, data.nama from
                        (
                            select 
                                dt.*,
                                supl.nama
                            from 
                            (
                                select
                                    rp.id_header,
                                    max(kp.tgl_kirim) as tgl_kirim,
                                    kp.no_order,
                                    kp.jenis_kirim 
                                from rhpp_pakan rp
                                left join
                                    kirim_pakan kp 
                                    on
                                        kp.no_sj = rp.nota
                                where
                                    kp.jenis_kirim <> 'opkp'
                                group by
                                    rp.id_header,
                                    kp.no_order,
                                    kp.jenis_kirim
                            ) dt
                            left join
                                (
                                    select dst.*, ds.kode_trans as kode_trans_stok from det_stok_trans dst
                                    right join
                                        det_stok ds
                                        on
                                            dst.id_header = ds.id
                                    left join
                                        kirim_pakan kp
                                        on
                                            ds.kode_trans = kp.no_order 
                                    where
                                        ds.jenis_barang = 'pakan' and
                                        kp.jenis_kirim = 'opks'
                                ) dst 
                                on
                                    dst.kode_trans = dt.no_order
                            left join
                                kirim_pakan kp_stok
                                on
                                    kp_stok.no_order = dst.kode_trans_stok
                            left join
                                (
                                    select plg1.* from pelanggan plg1
                                    right join
                                        (select max(id) as id, nomor from pelanggan group by nomor) plg2
                                        on
                                            plg1.id = plg2.id
                                    where
                                        plg1.tipe = 'supplier' and plg1.jenis <> 'ekspedisi'
                                ) supl
                                on
                                    supl.nomor = kp_stok.asal
                            group by
                                dt.id_header,
                                dt.tgl_kirim,
                                dt.no_order,
                                dt.jenis_kirim,
                                supl.nama
                        ) data
                        where
                            data.nama is not null
                        group by
                            data.id_header, data.nama
                    ) rp_supl
                    on
                        rp_supl.id_header = r_inti.id
                left join
                    (
                        select
                            rv.id_header,
                            rv.total - isnull(rrv.total, 0) as total
                        from (
                            select rv.id_header, sum(rv.total) as total 
                            from rhpp_voadip rv 
                            right join
                                rhpp r
                                on
                                    r.id = rv.id_header
                            where
                                r.jenis like '%inti%'
                            group by rv.id_header
                        ) rv
                        left join
                            (select id_header, sum(total) as total from rhpp_retur_voadip group by id_header) rrv
                            on
                                rrv.id_header = rv.id_header
                    ) rv_inti
                    on
                        rv_inti.id_header = r_inti.id
                left join
                    (
                        select
                            rv.id_header,
                            rv.total - isnull(rrv.total, 0) as total
                        from (
                            select rv.id_header, sum(rv.total) as total 
                            from rhpp_voadip rv 
                            right join
                                rhpp r
                                on
                                    r.id = rv.id_header
                            where
                                r.jenis like '%plasma%'
                            group by rv.id_header
                        ) rv
                        left join
                            (select id_header, sum(total) as total from rhpp_retur_voadip group by id_header) rrv
                            on
                                rrv.id_header = rv.id_header
                    ) rv_plasma
                    on
                        rv_plasma.id_header = r_plasma.id
                left join
                    (select id_header, min(tanggal) as tgl_panen_awal, max(tanggal) as tgl_panen_akhir, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_penjualan group by id_header) r_penjualan
                    on
                        r_penjualan.id_header = r_plasma.id
                left join
                    (select id_header, max(tanggal) as tanggal, sum(jumlah) as jumlah, sum(total) as total from rhpp_pindah_pakan group by id_header) rpp
                    on
                        rpp.id_header = r_inti.id
                left join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_oa_pindah_pakan group by id_header) ropp
                    on
                        ropp.id_header = r_inti.id
                left join
                    (select id_header, max(tanggal) as tanggal, sum(jumlah) as jumlah, sum(total) as total from rhpp_retur_pakan group by id_header) rrp
                    on
                        rrp.id_header = r_inti.id
                left join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_oa_retur_pakan group by id_header) rorp
                    on
                        rorp.id_header = r_inti.id
                left join
                    (select id_header, sum(nominal) as nominal from rhpp_piutang group by id_header) r_piutang
                    on
                        r_piutang.id_header = r_plasma.id
                left join
                    (
                        select kpp.tgl_bayar, kpp.nomor, kppd.*, min(rp.tgl_bayar) as tgl_real_bayar, sum(rpd.bayar) as bayar from konfirmasi_pembayaran_peternak_det kppd 
                        left join
                            konfirmasi_pembayaran_peternak kpp 
                            on
                                kppd.id_header = kpp.id
                        left join
                            realisasi_pembayaran_det rpd
                            on
                                rpd.no_bayar = kpp.nomor
                        left join
                            realisasi_pembayaran rp
                            on
                                rp.id = rpd.id_header
                        where 
                            kppd.jenis = 'RHPP' 
                        group by
                            kpp.tgl_bayar,
                            kpp.nomor,
                            kppd.id,
                            kppd.id_header,
                            kppd.id_trans,
                            kppd.jenis,
                            kppd.sub_total
                    ) trf
                    on
                        trf.id_trans = r_plasma.id
                left join
                    rdim_submit rs
                    on
                        rs.noreg = ts.noreg
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        mm.nim = rs.nim
                left join
                    (
                        select kry1.* from karyawan kry1
                        right join
                            (select max(id) as id, nik from karyawan group by nik) kry2
                            on
                                kry1.id = kry2.id
                    ) kry
                    on
                        kry.nik = rs.sampling
                where
                    not exists (select * from rhpp_group_noreg where noreg = ts.noreg)
                
                union all

                select 
                    rgh.id,
                    rgh.nomor as no_mitra, 
                    rgh.tgl_submit as tgl_tutup, 
                    rgn.noreg,
                    cast(SUBSTRING(rgn.noreg, 10, 2) as int) as kandang, 
                    cast(SUBSTRING(rgn.noreg, 8, 2) as int) as periode, 
                    kry.nama as ppl, 
                    rgn.tgl_docin, 
                    rgd.barang as jenis_doc, 
                    rgd.jumlah as populasi, 
                    rgd.total as tot_doc,
                    rgp_supl.nama as jenis_pakan, 
                    (rgp.jumlah - (isnull(rgpp.jumlah, 0) + isnull(rgrp.jumlah, 0))) as jml_pakan, 
                    isnull(rgp.total, 0) as _tot_pakan,
                    isnull(rgop.total, 0) as _tot_oa_pakan,
                    isnull(rgpp.total, 0) as tot_pindah_pakan,
                    isnull(rgopp.total, 0) as tot_oa_pindah_pakan,
                    isnull(rgrp.total, 0) as tot_retur_pakan,
                    isnull(rgorp.total, 0) as tot_oa_retur_pakan,
                    ((rgp.total + rgop.total) - ((isnull(rgpp.total, 0) + isnull(rgopp.total, 0)) + (isnull(rgrp.total, 0) + isnull(rgorp.total, 0)))) as tot_pakan,
                    rgv_inti.total as tot_obat_inti,
                    rgv_plasma.total as tot_obat_plasma,
                    rg_penjualan.tgl_panen_awal,
                    rg_penjualan.tgl_panen_akhir,
                    (DateDiff (Day, rg_penjualan.tgl_panen_awal, rg_penjualan.tgl_panen_akhir) + 1) as durasi_panen,
                    rg_plasma.rata_umur as umur,
                    rg_plasma.deplesi,
                    rg_plasma.fcr,
                    rg_plasma.bb,
                    rg_plasma.ip,
                    case
                        when rg_penjualan.tgl_panen_akhir <= rgpp.tanggal then
                            (DateDiff (Day, rg_penjualan.tgl_panen_akhir, rgpp.tanggal) + 1)
                        else
                            '-'
                    end as mutasi,
                    rg_penjualan.ekor as jml_ekor_terpanen,
                    rg_penjualan.tonase as tonase,
                    rg_penjualan.total as hasil_penjualan_ayam,
                    (rg_penjualan.total / rg_penjualan.tonase) as rata_harga,
                    rg_plasma.pdpt_peternak_belum_pajak as pdpt_plasma,
                    rg_plasma.potongan_pajak,
                    rg_piutang.nominal as potongan,
                    trf.bayar as transfer,
                    rgh.tgl_submit as tgl_rhpp_ke_pusat,
                    trf.tgl_real_bayar as tgl_transfer, 
                    (DateDiff (Day, rg_penjualan.tgl_panen_akhir, rgh.tgl_submit) + 1) as durasi_rhpp_ke_pusat,
                    (DateDiff (Day, rgh.tgl_submit, trf.tgl_real_bayar) + 1) as durasi_transfer,
                    (rg_plasma.pdpt_peternak_belum_pajak / rgd.jumlah) as rata_pdpt_plasma_per_populasi,
                    ((rg_inti.tot_pembelian_sapronak + rg_plasma.pdpt_peternak_belum_pajak + rg_inti.biaya_materai + rg_inti.biaya_operasional) / rg_penjualan.tonase) as modal_inti,
                    ((rg_inti.tot_pembelian_sapronak + (rg_plasma.pdpt_peternak_belum_pajak - rg_plasma.bonus_pasar) + rg_inti.biaya_materai + rg_inti.biaya_operasional) / rg_penjualan.tonase) as modal_inti_tanpa_bonus_pasar,
                    rg_inti.lr_inti,
                    (rg_inti.lr_inti + rg_inti.biaya_operasional) as lr_inti_tanpa_opr,
                    rg_inti.biaya_operasional,
                    rg_inti.biaya_materai
                from (
                    select rgh1.* from rhpp_group_header rgh1
                    right join
                        (select max(id) as id, nomor, tgl_submit from rhpp_group_header group by nomor, tgl_submit) rgh2
                        on
                            rgh1.id = rgh2.id
                ) rgh
                left join
                    (select * from rhpp_group where jenis = 'rhpp_inti') rg_inti
                    on
                        rg_inti.id_header = rgh.id
                left join
                    (select * from rhpp_group where jenis = 'rhpp_plasma') rg_plasma
                    on
                        rg_plasma.id_header = rgh.id
                left join
                    (
                        select id_header, barang, sum(box) as box, sum(jumlah) as jumlah, sum(total) as total 
                        from rhpp_group_doc 
                        group by 
                            id_header, 
                            barang
                    ) rgd
                    on
                        rgd.id_header= rg_inti.id
                left join
                    (
                        select id_header, sum(jumlah) as jumlah, sum(total) as total 
                        from rhpp_group_pakan
                        group by 
                            id_header
                    ) rgp
                    on
                        rgp.id_header = rg_inti.id
                left join
                    (
                        select id_header, sum(jumlah) as jumlah, sum(total) as total 
                        from rhpp_group_oa_pakan
                        group by 
                            id_header
                    ) rgop
                    on
                        rgop.id_header = rg_inti.id
                left join
                    (
                        select data.id_header, data.nama from
                        (
                            select 
                                dt.*,
                                supl.nama
                            from 
                            (
                                select
                                    rp.id_header,
                                    max(kp.tgl_kirim) as tgl_kirim,
                                    kp.no_order,
                                    kp.jenis_kirim 
                                from rhpp_group_pakan rp
                                left join
                                    kirim_pakan kp 
                                    on
                                        kp.no_sj = rp.nota
                                where
                                    kp.jenis_kirim <> 'opkp'
                                group by
                                    rp.id_header,
                                    kp.no_order,
                                    kp.jenis_kirim
                            ) dt
                            left join
                                (
                                    select dst.*, ds.kode_trans as kode_trans_stok from det_stok_trans dst
                                    right join
                                        det_stok ds
                                        on
                                            dst.id_header = ds.id
                                    left join
                                        kirim_pakan kp
                                        on
                                            ds.kode_trans = kp.no_order 
                                    where
                                        ds.jenis_barang = 'pakan' and
                                        kp.jenis_kirim = 'opks'
                                ) dst 
                                on
                                    dst.kode_trans = dt.no_order
                            left join
                                kirim_pakan kp_stok
                                on
                                    kp_stok.no_order = dst.kode_trans_stok
                            left join
                                (
                                    select plg1.* from pelanggan plg1
                                    right join
                                        (select max(id) as id, nomor from pelanggan group by nomor) plg2
                                        on
                                            plg1.id = plg2.id
                                    where
                                        plg1.tipe = 'supplier' and plg1.jenis <> 'ekspedisi'
                                ) supl
                                on
                                    supl.nomor = kp_stok.asal
                            group by
                                dt.id_header,
                                dt.tgl_kirim,
                                dt.no_order,
                                dt.jenis_kirim,
                                supl.nama
                        ) data
                        where
                            data.nama is not null
                        group by
                            data.id_header, data.nama
                    ) rgp_supl
                    on
                        rgp_supl.id_header = rg_inti.id
                left join
                    (
                        select
                            rgv.id_header,
                            rgv.total - isnull(rgrv.total, 0) as total
                        from (
                            select rgv.id_header, sum(rgv.total) as total 
                            from rhpp_group_voadip rgv 
                            right join
                                rhpp_group rg
                                on
                                    rg.id = rgv.id_header
                            where
                                rg.jenis like '%inti%'
                            group by rgv.id_header
                        ) rgv
                        left join
                            (select id_header, sum(total) as total from rhpp_group_retur_voadip group by id_header) rgrv
                            on
                                rgrv.id_header = rgv.id_header
                    ) rgv_inti
                    on
                        rgv_inti.id_header = rg_inti.id
                left join
                    (
                        select
                            rgv.id_header,
                            rgv.total - isnull(rgrv.total, 0) as total
                        from (
                            select rgv.id_header, sum(rgv.total) as total 
                            from rhpp_group_voadip rgv 
                            right join
                                rhpp_group rg
                                on
                                    rg.id = rgv.id_header
                            where
                                rg.jenis like '%plasma%'
                            group by rgv.id_header
                        ) rgv
                        left join
                            (select id_header, sum(total) as total from rhpp_group_retur_voadip group by id_header) rgrv
                            on
                                rgrv.id_header = rgv.id_header
                    ) rgv_plasma
                    on
                        rgv_plasma.id_header = rg_plasma.id
                left join
                    (select id_header, min(tanggal) as tgl_panen_awal, max(tanggal) as tgl_panen_akhir, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_group_penjualan group by id_header) rg_penjualan
                    on
                        rg_penjualan.id_header = rg_plasma.id
                left join
                    (select id_header, max(tanggal) as tanggal, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_pindah_pakan group by id_header) rgpp
                    on
                        rgpp.id_header = rg_inti.id
                left join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_oa_pindah_pakan group by id_header) rgopp
                    on
                        rgopp.id_header = rg_inti.id
                left join
                    (select id_header, max(tanggal) as tanggal, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_retur_pakan group by id_header) rgrp
                    on
                        rgrp.id_header = rg_inti.id
                left join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_oa_retur_pakan group by id_header) rgorp
                    on
                        rgorp.id_header = rg_inti.id
                left join
                    (select id_header, sum(nominal) as nominal from rhpp_group_piutang group by id_header) rg_piutang
                    on
                        rg_piutang.id_header = rg_plasma.id
                left join
                    (
                        select kpp.tgl_bayar, kpp.nomor, kppd.*, min(rp.tgl_bayar) as tgl_real_bayar, sum(rpd.bayar) as bayar from konfirmasi_pembayaran_peternak_det kppd 
                        left join
                            konfirmasi_pembayaran_peternak kpp 
                            on
                                kppd.id_header = kpp.id
                        left join
                            realisasi_pembayaran_det rpd
                            on
                                rpd.no_bayar = kpp.nomor 
                        left join
                            realisasi_pembayaran rp
                            on
                                rp.id = rpd.id_header
                        where 
                            kppd.jenis = 'RHPP GROUP' 
                        group by
                            kpp.tgl_bayar,
                            kpp.nomor,
                            kppd.id,
                            kppd.id_header,
                            kppd.id_trans,
                            kppd.jenis,
                            kppd.sub_total
                    ) trf
                    on
                        trf.id_trans = rg_plasma.id
                left join
                    rhpp_group_noreg rgn
                    on
                        rgn.id_header = rg_inti.id
                left join
                    rdim_submit rs
                    on
                        rs.noreg = rgn.noreg
                left join
                    (
                        select kry1.* from karyawan kry1
                        right join
                            (select max(id) as id, nik from karyawan group by nik) kry2
                            on
                                kry1.id = kry2.id
                    ) kry
                    on
                        kry.nik = rs.sampling
            ) rhpp
            left join
                (
                    select mtr1.nomor, mtr1.nama, mtr1.ktp as nik, mtr1.npwp, mtr1.perusahaan, mm.nim, k.unit as id_unit, k.tipe as tipe_kdg, k.kandang from mitra mtr1
                    right join
                        ( select max(id) as id, nomor from mitra group by nomor ) mtr2
                        on
                            mtr1.id = mtr2.id
                    left join
                        mitra_mapping mm
                        on
                            mtr1.id = mm.mitra
                    left join
                        kandang k
                        on
                            mm.id = k.mitra_mapping
                    group by
                        mtr1.nomor, mtr1.nama, mtr1.ktp, mtr1.npwp, mtr1.perusahaan, mm.nim, k.unit, k.tipe, k.kandang
                ) mtr
                on
                    rhpp.no_mitra = mtr.nomor and
                    cast(SUBSTRING(rhpp.noreg, 10, 2) as int) = mtr.kandang 
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) as prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = mtr.perusahaan
            left join
                wilayah w
                on
                    w.id = mtr.id_unit
            where
                rhpp.tgl_tutup between '".$start_date."' and '".$end_date."'
                ".$sql_unit."
                ".$sql_perusahaan."
            order by
                rhpp.tgl_tutup asc,
                w.kode asc,
                mtr.nama asc,
                rhpp.kandang asc
        ";

        $m_conf = new \Model\Storage\Conf();
        $d_rhpp = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_rhpp->count() > 0 ) {
            $d_rhpp = $d_rhpp->toArray();

            // cetak_r( $d_rhpp );

            foreach ($d_rhpp as $key => $value) {
                if ( !isset( $data[ $value['id'] ] ) ) {
                    $data[ $value['id'] ] = $value;
                    $data[ $value['id'] ]['kandang'] = $value['kandang']; // (int) substr($value['noreg'], 9, 2);
                    $data[ $value['id'] ]['periode'] = $value['periode'];
                    $data[ $value['id'] ]['tgl_docin'] = tglIndonesia($value['tgl_docin']);
                } else {
                    $data[ $value['id'] ]['kandang'] .= ', '.$value['kandang']; // (int) substr($value['noreg'], 9, 2);
                    $data[ $value['id'] ]['periode'] .= ', '.$value['periode'];
                    $data[ $value['id'] ]['tgl_docin'] .= ', '.tglIndonesia($value['tgl_docin']);
                    if ( $data[ $value['id'] ]['jenis_pakan'] != $value['jenis_pakan'] ) {
                        $data[ $value['id'] ]['jenis_pakan'] .= ', '.$value['jenis_pakan'];
                    }
                }
            }
        }

        return $data;
    }

    public function encryptParams() {
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

    public function exportExcel($params)
    {
        $_params = json_decode(exDecrypt($params), true);

        $perusahaan = $_params['perusahaan'];
        $unit = $_params['unit'];
        $bulan = $_params['bulan'];
        $tahun = $_params['tahun'];

        $data = $this->getDataDetail($unit, $bulan, $tahun, $perusahaan);

        $nama_bulan = null;

        $arr_header = array('Bulan', 'Unit', 'DO', 'Jenis Kandang', 'NIK', 'NPWP', 'Plasma', 'Kandang', 'Periode', 'PPL', 'Tgl Chick In', 'Jenis DOC', 'Populasi', 'Total', 'Jenis Pakan', 'Pakan (Kg)', 'Total Pemakaian Pakan Inti', 'Total Obat Inti', 'Total Obat Plasma', 'Tgl Awal Panen', 'Tgl Akhir Panen', 'Durasi Panen (Hari)', 'Umur', 'Deplesi (%)', 'FCR', 'BB (Kg)', 'IP', 'Mutasi (Hari)', 'Jumlah Ekor Terpanen', 'Tonase (Kg)', 'Hasil Penjualan Ayam', 'Rata2 Harga', 'Pendapatan Plasma', 'Potongan Pajak', 'Potongan / Tambahan', 'Transfer', 'Catatan', 'Tgl RHPP Ke Pusat', 'Tgl Transfer RHPP', 'Durasi RHPP Ke Pusat (Hari)', 'Durasi Transf (Hari)', 'Rata2 Pendapatan Plasma/Populasi', 'Modal Inti', 'Modal Inti Sebenarnya (Tanpa Bonus Pasar)', 'Laba Rugi Inti Dengan Estimasi Operasional (Rp. 300)', 'Laba Rugi Inti Tanpa Operasional (Rp. 300)', 'Biaya Operasional 300 / Kg', 'Materai');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                if ( $idx == 0 ) {
                    $nama_bulan = strtoupper($value['nama_bulan']);
                }

                $arr_column[ $idx ] = array(
                    'Bulan' => array('value' => strtoupper($value['nama_bulan']), 'data_type' => 'string'),
                    'Unit' => array('value' => strtoupper($value['kode']), 'data_type' => 'string'),
                    'DO' => array('value' => strtoupper($value['nama_perusahaan']), 'data_type' => 'string'),
                    'Jenis Kandang' => array('value' => strtoupper($value['tipe_kdg']), 'data_type' => 'string'),
                    'NIK' => array('value' => $value['nik'], 'data_type' => 'nik'),
                    'NPWP' => array('value' => $value['npwp'], 'data_type' => 'nik'),
                    'Plasma' => array('value' => strtoupper($value['nama_mitra']), 'data_type' => 'string'),
                    'Kandang' => array('value' => strtoupper($value['kandang']), 'data_type' => 'nik'),
                    'Periode' => array('value' => strtoupper($value['periode']), 'data_type' => 'nik'),
                    'PPL' => array('value' => strtoupper($value['ppl']), 'data_type' => 'string'),
                    'Tgl Chick In' => array('value' => $value['tgl_docin'], 'data_type' => 'string'),
                    'Jenis DOC' => array('value' => strtoupper($value['jenis_doc']), 'data_type' => 'string'),
                    'Populasi' => array('value' => $value['populasi'], 'data_type' => 'integer'),
                    'Total' => array('value' => $value['tot_doc'], 'data_type' => 'decimal2'),
                    'Jenis Pakan' => array('value' => $value['jenis_pakan'], 'data_type' => 'string'),
                    'Pakan (Kg)' => array('value' => $value['jml_pakan'], 'data_type' => 'decimal2'),
                    'Total Pemakaian Pakan Inti' => array('value' => $value['tot_pakan'], 'data_type' => 'decimal2'),
                    'Total Obat Inti' => array('value' => $value['tot_obat_inti'], 'data_type' => 'decimal2'),
                    'Total Obat Plasma' => array('value' => $value['tot_obat_plasma'], 'data_type' => 'decimal2'),
                    'Tgl Awal Panen' => array('value' => $value['tgl_panen_awal'], 'data_type' => 'date'),
                    'Tgl Akhir Panen' => array('value' => $value['tgl_panen_akhir'], 'data_type' => 'date'),
                    'Durasi Panen (Hari)' => array('value' => $value['durasi_panen'], 'data_type' => 'integer'),
                    'Umur' => array('value' => $value['umur'], 'data_type' => 'integer'),
                    'Deplesi (%)' => array('value' => $value['deplesi'], 'data_type' => 'decimal2'),
                    'FCR' => array('value' => $value['fcr'], 'data_type' => 'decimal2'),
                    'BB (Kg)' => array('value' => $value['bb'], 'data_type' => 'decimal2'),
                    'IP' => array('value' => $value['ip'], 'data_type' => 'decimal2'),
                    'Mutasi (Hari)' => array('value' => $value['mutasi'], 'data_type' => 'integer'),
                    'Jumlah Ekor Terpanen' => array('value' => $value['jml_ekor_terpanen'], 'data_type' => 'integer'),
                    'Tonase (Kg)' => array('value' => $value['tonase'], 'data_type' => 'decimal2'),
                    'Hasil Penjualan Ayam' => array('value' => $value['hasil_penjualan_ayam'], 'data_type' => 'decimal2'),
                    'Rata2 Harga' => array('value' => $value['rata_harga'], 'data_type' => 'decimal2'),
                    'Pendapatan Plasma' => array('value' => $value['pdpt_plasma'], 'data_type' => 'decimal2'),
                    'Potongan Pajak' => array('value' => $value['potongan_pajak'], 'data_type' => 'decimal2'),
                    'Potongan / Tambahan' => array('value' => $value['potongan'], 'data_type' => 'decimal2'),
                    'Transfer' => array('value' => $value['transfer'], 'data_type' => 'decimal2'),
                    'Catatan' => array('value' => '', 'data_type' => 'string'),
                    'Tgl RHPP Ke Pusat' => array('value' => $value['tgl_rhpp_ke_pusat'], 'data_type' => 'date'),
                    'Tgl Transfer RHPP' => array('value' => $value['tgl_transfer'], 'data_type' => 'date'),
                    'Durasi RHPP Ke Pusat (Hari)' => array('value' => $value['durasi_rhpp_ke_pusat'], 'data_type' => 'integer'),
                    'Durasi Transf (Hari)' => array('value' => $value['durasi_transfer'], 'data_type' => 'integer'),
                    'Rata2 Pendapatan Plasma/Populasi' => array('value' => $value['rata_pdpt_plasma_per_populasi'], 'data_type' => 'decimal2'),
                    'Modal Inti' => array('value' => $value['modal_inti'], 'data_type' => 'decimal2'),
                    'Modal Inti Sebenarnya (Tanpa Bonus Pasar)' => array('value' => $value['modal_inti_tanpa_bonus_pasar'], 'data_type' => 'decimal2'),
                    'Laba Rugi Inti Dengan Estimasi Operasional (Rp. 300)' => array('value' => $value['lr_inti'], 'data_type' => 'decimal2'),
                    'Laba Rugi Inti Tanpa Operasional (Rp. 300)' => array('value' => $value['lr_inti_tanpa_opr'], 'data_type' => 'decimal2'),
                    'Biaya Operasional 300 / Kg' => array('value' => $value['biaya_operasional'], 'data_type' => 'decimal2'),
                    'Materai' => array('value' => $value['biaya_materai'], 'data_type' => 'decimal2'),
                );

                $idx++;
            }
        }

        $filename = "LABA_RUGI_".$nama_bulan.'_'.$tahun.'.xls';

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );

        // $content['data'] = $data;
        // $res_view_html = $this->load->view('report/laba_rugi/exportExcel', $content, true);
        // $filename = "LABA_RUGI_".$data[0]['nama_bulan'].'_'.$tahun;

        // header("Content-type:   application/ms-excel; charset=utf-8");
        // $filename = $filename.'.xls';
        // header("Content-Disposition: attachment; filename=".$filename."");
        // echo $res_view_html;
    }

    public function exportExcelUsingSpreadSheet( $file_name, $arr_header, $arr_column ) {
        /* Spreadsheet Init */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* Excel Header */
        for ($i=0; $i < count($arr_header); $i++) { 
            $huruf = toAlpha($i+1);

            $posisi = $huruf.'1';
            $sheet->setCellValue($posisi, $arr_header[$i]);

            $styleBold = [
                'font' => [
                    'bold' => true,
                ]
            ];
            $spreadsheet->getActiveSheet()->getStyle($posisi)->applyFromArray($styleBold);
        }

        $baris = 2;
        if ( !empty($arr_column) && count($arr_column) ) {
            for ($i=0; $i < count($arr_column); $i++) {
                for ($j=0; $j < count($arr_header); $j++) {
                    $huruf = toAlpha($j+1);

                    $data = $arr_column[ $i ][ $arr_header[ $j ] ];

                    if ( $data['data_type'] == 'string' ) {
                        $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                    }

                    if ( $data['data_type'] == 'nik' ) {
                        $sheet->getCell($huruf.$baris)->setValueExplicit($data['value'], DataType::TYPE_STRING);
                    }

                    if ( $data['data_type'] == 'text' ) {
                        $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_GENERAL);
                    }

                    if ( $data['data_type'] == 'date' ) {
                        $dt = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', substr($data['value'], 0, 10)));
                        $sheet->setCellValue($huruf.$baris, $dt);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    }

                    if ( $data['data_type'] == 'integer' ) {
                        $sheet->setCellValue($huruf.$baris, $data['value']);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                    }

                    if ( $data['data_type'] == 'decimal2' ) {
                        $sheet->setCellValue($huruf.$baris, $data['value']);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                    }
                }

                $baris++;
            }

            /* Excel Total */
            // $sheet->setCellValue('A1', "TOTAL");
            // $sheet->setCellValue('J1', "TOTAL");
            // $sheet->setCellValue('K1', "=SUM(J3:J".$baris.")");
            // $sheet->setCellValue('L1', "=SUM(K3:K".$baris.")");
            // $sheet->setCellValue('N1', "=SUM(M3:M".$baris.")");
            // $sheet->setCellValue('O1', "=SUM(N3:N".$baris.")");
            // $sheet->setCellValue('P1', "=SUM(O3:O".$baris.")");
            // $sheet->setCellValue('Q1', "=SUM(P3:P".$baris.")");
            // $styleBold = [
            //     'font' => [
            //         'bold' => true,
            //     ]
            // ];
            // $spreadsheet->getActiveSheet()->getStyle('A1:U1')->applyFromArray($styleBold);
            // $spreadsheet->getActiveSheet()->mergeCells('A1:J1');
            // $spreadsheet->getActiveSheet()->getStyle('K1:P1')
            //             ->getNumberFormat()
            //             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        } else {
            $range1 = 'A'.$baris;
            $range2 = toAlpha(count($arr_header)).$baris;

            $spreadsheet->getActiveSheet()->mergeCells("$range1:$range2");
            $sheet->setCellValue($range1, 'Data tidak ditemukan.');
        }

        $styleArray = [
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A1:'.toAlpha(count($arr_header)).$baris)->applyFromArray($styleArray, false);

        /* Excel File Format */
        $writer = new Xlsx($spreadsheet);
        $filename = $file_name;
        $writer->save('export_excel/'.$filename);

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }
}