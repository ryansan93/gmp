<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

/*
 * Port dari project Bagia (bagia_sinar_jaya_corp/application/modules/report/controllers/GeneralLedger.php).
 * Beda utama dari GeneralLedger.php (V1) gmperp:
 * 1. Filter Start Date / End Date bebas (bukan cuma Bulan+Tahun terkunci ke 1 bulan kalender).
 * 2. Perusahaan multi-select (bisa pilih lebih dari 1 sekaligus).
 * 3. Fallback saldo awal saat snapshot saldo_bulanan periode ini belum ada (Opsi B) di-ANCHOR ke
 *    snapshot saldo_bulanan TERAKHIR yg tersedia (tanggal <= start_date), lalu roll-forward mutasi
 *    det_jurnal dari anchor s/d akhir bulan sebelumnya, pakai rentang sacoa dari anchor_month s/d
 *    report_month (bukan cuma lompat mundur PERSIS 1 bulan seperti versi lama). Kalau bulan tutup
 *    buku sempat terlewat berturut-turut, versi lama diam-diam pakai baseline yg salah/basi --
 *    versi ini yg jadi alasan utama laporan ini dibuat, supaya residual kecil (mis. sisa DOC
 *    21180.200 per unit) bisa ketahuan sumbernya dgn baseline yg benar.
 */
class GeneralLedgerV2 extends Public_Controller {

    private $pathView = 'report/general_ledger_v2/';
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
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/general_ledger_v2/js/general-ledger-v2.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/general_ledger_v2/css/general-ledger-v2.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $m_wilayah->getDataUnit();
            $content['title_menu'] = 'Laporan GL V2 (Buku Besar)';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    /*
     * gmperp.wilayah TIDAK punya kolom perusahaan (beda dari Bagia) -- unit dan perusahaan
     * independen, tidak berelasi lewat wilayah di sini. Jadi bagian resolve-unit-dari-perusahaan
     * milik Bagia diganti pola asli GeneralLedger.php (V1) gmperp: perusahaan dikelompokkan via
     * kode_gabung_perusahaan, unit difilter langsung di select terluar (data.unit = ...).
     */
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

    public function getData($start_date, $end_date, $kode_gabung_perusahaan, $unit) {
        // Mode MANAJEMEN (lihat application/config/app_mode.php) baca dari
        // shadow det_jurnal_manajemen, BUKAN det_jurnal riil - laporan ini
        // pakai SQL mentah dgn nama tabel literal, jadi tidak ke-cover
        // otomatis oleh redirect di Model\Storage\Conf (itu cuma jalan utk
        // model Eloquent, bukan string SQL yg di-hydrateRaw() di sini).
        $tbl_det_jurnal = (defined('APP_MODE') && APP_MODE === 'manajemen') ? 'det_jurnal_manajemen' : 'det_jurnal';

        $sql_kode_gabung_perusahaan = "and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '".$kode_gabung_perusahaan."')";
        if ( $kode_gabung_perusahaan == 'all' ) {
            $sql_kode_gabung_perusahaan = null;
        }

        $sql_unit = null;
        if ( $unit != 'all' ) {
            $sql_unit = "where data.unit = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql_sa = "
            /* SALDO AWAL */
            select
                sb.no_coa as no_coa,
                sb.unit,
                c.nama_coa,
                case
                    when sb.debet2 <> 0 then
                        0
                    else
                        sb.debet1
                end as saldo_awal,
                0 as kredit,
                0 as debet
            from (
                select
                    sa.no_coa,
                    sa.unit,
                    sum(sa.debet1) as debet1,
                    sum(sa.kredit1) as kredit1,
                    sum(sa.debet2) as debet2,
                    sum(sa.kredit2) as kredit2
                from
                (
                    select
                        sb.coa as no_coa,
                        sb.unit,
                        isnull(sb.saldo_awal, 0) as debet1,
                        0 as kredit1,
                        0 as debet2,
                        0 as kredit2
                    from saldo_bulanan sb
                    where
                        sb.tanggal between '".$start_date."' and '".$end_date."' and
                        isnull(sb.saldo_awal, 0) <> 0

                    union all

                    select
                        sc.no_coa,
                        sc.unit,
                        0 as debet1,
                        0 as kredit1,
                        isnull(sc.debet, 0) as debet2,
                        0 as kredit2
                    from sacoa sc
                    where
                        sc.periode = '".substr($start_date, 0, 7)."' and
                        sc.debet <> 0
                ) sa
                group by
                    sa.no_coa,
                    sa.unit
            ) sb
            left join
                coa c
                on
                    sb.no_coa = c.coa
            /* END - SALDO AWAL */
        ";
        $d_conf = $m_conf->hydrateRaw( $sql_sa );
        if ( $d_conf->count() <= 0 ) {
            /*
             * Opsi B: saldo_bulanan periode ini belum ada (mis. bulan sebelumnya belum ditutup).
             * Anchor ke snapshot saldo_bulanan TERAKHIR yg tersedia (tanggal <= start_date), lalu
             * roll-forward mutasi det_jurnal dari anchor s/d end_date_new. Kalau tidak ada snapshot
             * sama sekali, pakai perilaku lama (mundur ke awal histori).
             */
            $end_date_new = prev_date($start_date);

            $d_anchor = $m_conf->hydrateRaw("
                select max(tanggal) as anchor from saldo_bulanan where tanggal <= '".$start_date."'
            ");
            $start_date_new = null;
            if ( $d_anchor->count() > 0 ) {
                $row_anchor = $d_anchor->toArray()[0];
                if ( !empty($row_anchor['anchor']) ) {
                    $start_date_new = substr($row_anchor['anchor'], 0, 10);
                }
            }
            if ( empty($start_date_new) ) {
                $start_date_new = '1900-01-01';
            }
            $anchor_month = substr($start_date_new, 0, 7);
            $report_month = substr($start_date, 0, 7);

            $sql_sa = "
                select
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_awal,
                    0 as kredit,
                    0 as debet
                from
                (
                    select
                        sb.no_coa as no_coa,
                        sb.unit,
                        c.nama_coa,
                        case
                            when sb.debet2 <> 0 then
                                0
                            else
                                sb.debet1
                        end as saldo_awal,
                        0 as kredit,
                        0 as debet
                    from (
                        select
                            sa.no_coa,
                            sa.unit,
                            sum(sa.debet1) as debet1,
                            sum(sa.kredit1) as kredit1,
                            sum(sa.debet2) as debet2,
                            sum(sa.kredit2) as kredit2
                        from
                        (
                            select
                                sb.coa as no_coa,
                                sb.unit,
                                isnull(sb.saldo_awal, 0) as debet1,
                                0 as kredit1,
                                0 as debet2,
                                0 as kredit2
                            from saldo_bulanan sb
                            where
                                sb.tanggal between '".$start_date_new."' and '".$end_date_new."' and
                                isnull(sb.saldo_awal, 0) <> 0

                            union all

                            select
                                sc.no_coa,
                                sc.unit,
                                0 as debet1,
                                0 as kredit1,
                                isnull(sc.debet, 0) as debet2,
                                0 as kredit2
                            from sacoa sc
                            where
                                sc.periode >= '".$anchor_month."' and
                                sc.periode < '".$report_month."' and
                                sc.debet <> 0
                        ) sa
                        group by
                            sa.no_coa,
                            sa.unit
                    ) sb
                    left join
                        coa c
                        on
                            sb.no_coa = c.coa

                    union all

                    select
                        sc.no_coa,
                        sc.unit,
                        c.nama_coa,
                        0 as saldo_awal,
                        case
                            when isnull(sc.debet, 0) < 0 then
                                isnull(sc.debet, 0)
                            else
                                0
                        end as kredit,
                        case
                            when isnull(sc.debet, 0) >= 0 then
                                isnull(sc.debet, 0)
                            else
                                0
                        end as debet
                    from sacoa sc
                    left join
                        coa c
                        on
                            sc.no_coa = c.coa
                    where
                        sc.periode >= '".$anchor_month."' and
                        sc.periode < '".$report_month."' and
                        sc.debet <> 0

                    union all

                    select
                        c.coa as no_coa,
                        case
                            when c.unit is not null and c.unit <> '' then
                                c.unit
                            else
                                dj.unit
                        end as unit,
                        c.nama_coa,
                        0 as saldo_awal,
                        (0-isnull(dj.kredit, 0)) as kredit,
                        isnull(dj.debet, 0) as debet
                    from coa c
                    left join
                        (
                            select no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                                select
                                    dj.coa_asal as no_coa,
                                    sum(dj.nominal) as kredit,
                                    0 as debet,
                                    dj.unit
                                from ".$tbl_det_jurnal." dj
                                where
                                    dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                group by dj.coa_asal, dj.unit

                                union all

                                select
                                    dj.coa_tujuan as no_coa,
                                    0 as kredit,
                                    sum(dj.nominal) as debet,
                                    case
                                        when dj.unit_tujuan is not null then
                                            dj.unit_tujuan
                                        else
                                            dj.unit
                                    end as unit
                                from ".$tbl_det_jurnal." dj
                                where
                                    dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                group by dj.coa_tujuan, dj.unit, dj.unit_tujuan
                            ) data
                            group by
                                no_coa, unit
                        ) dj
                        on
                            dj.no_coa = c.coa
                    where
                        (0-isnull(dj.kredit, 0)) <> 0 or
                        isnull(dj.debet, 0) <> 0
                ) data
                left join
                    wilayah w
                    on
                        w.kode = data.unit
                ".$sql_unit."
                group by
                    data.no_coa,
                    data.unit,
                    data.nama_coa
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_coa,
                data.unit,
                '' as kode_perusahaan,
                data.nama_coa,
                sum(isnull(data.saldo_awal, 0)) as saldo_awal,
                sum(isnull(data.kredit, 0)) as kredit,
                sum(isnull(data.debet, 0)) as debet,
                sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_akhir
            from
            (
                ".$sql_sa."

                union all

                select
                    sc.no_coa,
                    sc.unit,
                    c.nama_coa,
                    0 as saldo_awal,
                    case
                        when isnull(sc.debet, 0) < 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as kredit,
                    case
                        when isnull(sc.debet, 0) >= 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as debet
                from sacoa sc
                left join
                    coa c
                    on
                        sc.no_coa = c.coa
                where
                    sc.periode = '".substr($start_date, 0, 7)."' and
                    sc.debet <> 0

                union all

                select
                    c.coa as no_coa,
                    case
                        when c.unit is not null and c.unit <> '' then
                            c.unit
                        else
                            dj.unit
                    end as unit,
                    c.nama_coa,
                    0 as saldo_awal,
                    (0-isnull(dj.kredit, 0)) as kredit,
                    isnull(dj.debet, 0) as debet
                from coa c
                left join
                    (
                        select no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                            select
                                dj.coa_asal as no_coa,
                                sum(dj.nominal) as kredit,
                                0 as debet,
                                dj.unit
                            from ".$tbl_det_jurnal." dj
                            where
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by dj.coa_asal, dj.unit

                            union all

                            select
                                dj.coa_tujuan as no_coa,
                                0 as kredit,
                                sum(dj.nominal) as debet,
                                case
                                    when dj.unit_tujuan is not null then
                                        dj.unit_tujuan
                                    else
                                        dj.unit
                                end as unit
                            from ".$tbl_det_jurnal." dj
                            where
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by dj.coa_tujuan, dj.unit, dj.unit_tujuan
                        ) data
                        group by
                            no_coa, unit
                    ) dj
                    on
                        dj.no_coa = c.coa
                where
                    (0-isnull(dj.kredit, 0)) <> 0 or
                    isnull(dj.debet, 0) <> 0
            ) data
            left join
                wilayah w
                on
                    w.kode = data.unit
            ".$sql_unit."
            group by
                data.no_coa,
                data.unit,
                data.nama_coa
            order by
                data.no_coa asc,
                data.unit asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDetail($start_date, $end_date, $unit, $no_coa) {

        // Sama seperti getData() - baca shadow det_jurnal_manajemen di mode
        // MANAJEMEN (lihat NB di getData()).
        $tbl_det_jurnal = (defined('APP_MODE') && APP_MODE === 'manajemen') ? 'det_jurnal_manajemen' : 'det_jurnal';

        $m_conf = new \Model\Storage\Conf();

        $d_sb_check = $m_conf->hydrateRaw("
            select top 1 1 as ada from saldo_bulanan where tanggal between '".$start_date."' and '".$end_date."'
        ");

        if ( $d_sb_check->count() > 0 ) {
            $sql_saldo_awal = "
                select
                    '' as tanggal,
                    'Saldo Awal' as keterangan,
                    '' as kode_trans,
                    sb.no_coa as no_coa,
                    sb.unit,
                    c.nama_coa,
                    case
                        when sb.kredit2 <> 0 then
                            0
                        else
                            case
                                when sb.debet1 + sb.kredit1 < 0 then
                                    sb.debet1 + sb.kredit1
                                else
                                    0
                            end
                    end as kredit,
                    case
                        when sb.debet2 <> 0 then
                            0
                        else
                            case
                                when sb.debet1 + sb.kredit1 >= 0 then
                                    sb.debet1 + sb.kredit1
                                else
                                    0
                            end
                    end as debet,
                    0 as urut,
                    '' as noreg
                from (
                    select
                        sa.no_coa,
                        sa.unit,
                        sum(sa.debet1) as debet1,
                        sum(sa.kredit1) as kredit1,
                        sum(sa.debet2) as debet2,
                        sum(sa.kredit2) as kredit2
                    from
                    (
                        select
                            sb.coa as no_coa,
                            sb.unit,
                            case
                                when isnull(sb.saldo_awal, 0) >= 0 then
                                    isnull(sb.saldo_awal, 0)
                                else
                                    0
                            end as debet1,
                            case
                                when isnull(sb.saldo_awal, 0) < 0 then
                                    isnull(sb.saldo_awal, 0)
                                else
                                    0
                            end as kredit1,
                            0 as debet2,
                            0 as kredit2
                        from saldo_bulanan sb
                        where
                            sb.tanggal between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            sc.no_coa,
                            sc.unit,
                            0 as debet1,
                            0 as kredit1,
                            case
                                when isnull(sc.debet, 0) >= 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as debet2,
                            case
                                when isnull(sc.debet, 0) < 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as kredit2
                        from sacoa sc
                        where
                            sc.periode = '".substr($start_date, 0, 7)."' and
                            sc.debet <> 0
                    ) sa
                    group by
                        sa.no_coa,
                        sa.unit
                ) sb
                left join
                    coa c
                    on
                        sb.no_coa = c.coa
            ";
        } else {
            /* Opsi B, sama persis dgn di getData() -- lihat komentar di atas kelas. */
            $end_date_new = prev_date($start_date);

            $d_anchor = $m_conf->hydrateRaw("
                select max(tanggal) as anchor from saldo_bulanan where tanggal <= '".$start_date."'
            ");
            $start_date_new = null;
            if ( $d_anchor->count() > 0 ) {
                $row_anchor = $d_anchor->toArray()[0];
                if ( !empty($row_anchor['anchor']) ) {
                    $start_date_new = substr($row_anchor['anchor'], 0, 10);
                }
            }
            if ( empty($start_date_new) ) {
                $start_date_new = '1900-01-01';
            }
            $anchor_month = substr($start_date_new, 0, 7);
            $report_month = substr($start_date, 0, 7);

            $sql_saldo_awal = "
                select
                    '' as tanggal,
                    'Saldo Awal' as keterangan,
                    '' as kode_trans,
                    sa.no_coa,
                    sa.unit,
                    sa.nama_coa,
                    case when sa.saldo_awal < 0 then sa.saldo_awal else 0 end as kredit,
                    case when sa.saldo_awal >= 0 then sa.saldo_awal else 0 end as debet,
                    0 as urut,
                    '' as noreg
                from
                (
                    select
                        data.no_coa,
                        data.unit,
                        data.nama_coa,
                        sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_awal
                    from
                    (
                        select
                            sb.no_coa as no_coa,
                            sb.unit,
                            c.nama_coa,
                            case
                                when sb.debet2 <> 0 then
                                    0
                                else
                                    sb.debet1
                            end as saldo_awal,
                            0 as kredit,
                            0 as debet
                        from (
                            select
                                sa.no_coa,
                                sa.unit,
                                sum(sa.debet1) as debet1,
                                sum(sa.kredit1) as kredit1,
                                sum(sa.debet2) as debet2,
                                sum(sa.kredit2) as kredit2
                            from
                            (
                                select
                                    sb.coa as no_coa,
                                    sb.unit,
                                    isnull(sb.saldo_awal, 0) as debet1,
                                    0 as kredit1,
                                    0 as debet2,
                                    0 as kredit2
                                from saldo_bulanan sb
                                where
                                    sb.tanggal between '".$start_date_new."' and '".$end_date_new."' and
                                    isnull(sb.saldo_awal, 0) <> 0

                                union all

                                select
                                    sc.no_coa,
                                    sc.unit,
                                    0 as debet1,
                                    0 as kredit1,
                                    isnull(sc.debet, 0) as debet2,
                                    0 as kredit2
                                from sacoa sc
                                where
                                    sc.periode >= '".$anchor_month."' and
                                    sc.periode < '".$report_month."' and
                                    sc.debet <> 0
                            ) sa
                            group by
                                sa.no_coa,
                                sa.unit
                        ) sb
                        left join
                            coa c
                            on
                                sb.no_coa = c.coa

                        union all

                        select
                            sc.no_coa,
                            sc.unit,
                            c.nama_coa,
                            0 as saldo_awal,
                            case
                                when isnull(sc.debet, 0) < 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as kredit,
                            case
                                when isnull(sc.debet, 0) >= 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as debet
                        from sacoa sc
                        left join
                            coa c
                            on
                                sc.no_coa = c.coa
                        where
                            sc.periode >= '".$anchor_month."' and
                            sc.periode < '".$report_month."' and
                            sc.debet <> 0

                        union all

                        select
                            c.coa as no_coa,
                            case
                                when c.unit is not null and c.unit <> '' then
                                    c.unit
                                else
                                    dj.unit
                            end as unit,
                            c.nama_coa,
                            0 as saldo_awal,
                            (0-isnull(dj.kredit, 0)) as kredit,
                            isnull(dj.debet, 0) as debet
                        from coa c
                        left join
                            (
                                select no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                                    select
                                        dj.coa_asal as no_coa,
                                        sum(dj.nominal) as kredit,
                                        0 as debet,
                                        dj.unit
                                    from ".$tbl_det_jurnal." dj
                                    where
                                        dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                    group by dj.coa_asal, dj.unit

                                    union all

                                    select
                                        dj.coa_tujuan as no_coa,
                                        0 as kredit,
                                        sum(dj.nominal) as debet,
                                        case
                                            when dj.unit_tujuan is not null then
                                                dj.unit_tujuan
                                            else
                                                dj.unit
                                        end as unit
                                    from ".$tbl_det_jurnal." dj
                                    where
                                        dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                    group by dj.coa_tujuan, dj.unit, dj.unit_tujuan
                                ) data
                                group by
                                    no_coa, unit
                            ) dj
                            on
                                dj.no_coa = c.coa
                        where
                            (0-isnull(dj.kredit, 0)) <> 0 or
                            isnull(dj.debet, 0) <> 0
                    ) data
                    group by
                        data.no_coa,
                        data.unit,
                        data.nama_coa
                ) sa
            ";
        }

        $sql = "
            select
                data.tanggal,
                data.keterangan,
                data.kode_trans,
                data.no_coa,
                data.unit,
                '' as nama_perusahaan,
                data.nama_coa,
                isnull(data.kredit, 0) as kredit,
                isnull(data.debet, 0) as debet,
                data.noreg
            from
            (
                ".$sql_saldo_awal."

                union all

                select
                    sc.periode+'-01' as tanggal,
                    'Initial Balance' as keterangan,
                    'INIT'+REPLACE(sc.periode, '-', '') as kode_trans,
                    sc.no_coa,
                    sc.unit,
                    c.nama_coa,
                    case
                        when isnull(sc.debet, 0) < 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as kredit,
                    case
                        when isnull(sc.debet, 0) >= 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as debet,
                    1 as urut,
                    '' as noreg
                from sacoa sc
                left join
                    coa c
                    on
                        sc.no_coa = c.coa
                where
                    sc.periode = '".substr($start_date, 0, 7)."' and
                    sc.debet <> 0

                union all

                select
                    dj.tanggal,
                    dj.keterangan,
                    dj.kode_trans,
                    c.coa as no_coa,
                    case
                        when c.unit is not null and c.unit <> '' then
                            c.unit
                        else
                            dj.unit
                    end as unit,
                    c.nama_coa,
                    (0-isnull(dj.kredit, 0)) as kredit,
                    isnull(dj.debet, 0) as debet,
                    2 as urut,
                    dj.noreg
                from coa c
                left join
                    (
                        select
                            tanggal,
                            cast(keterangan as varchar(max)) as keterangan,
                            kode_trans,
                            no_coa,
                            kredit as kredit,
                            debet as debet,
                            unit
                            ".(($no_coa == '12020.000') ? ', noreg' : ", '' as noreg")."
                        from (
                            select
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)) as keterangan,
                                dj.kode_trans,
                                dj.coa_asal as no_coa,
                                sum(dj.nominal) as kredit,
                                0 as debet,
                                dj.unit,
                                dj.noreg
                            from ".$tbl_det_jurnal." dj
                            where
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)),
                                dj.kode_trans,
                                dj.coa_asal,
                                dj.unit,
                                dj.noreg

                            union all

                            select
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)) as keterangan,
                                dj.kode_trans,
                                dj.coa_tujuan as no_coa,
                                0 as kredit,
                                sum(dj.nominal) as debet,
                                case
                                    when dj.unit_tujuan is not null then
                                        dj.unit_tujuan
                                    else
                                        dj.unit
                                end as unit,
                                dj.noreg
                            from ".$tbl_det_jurnal." dj
                            where
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)),
                                dj.kode_trans,
                                dj.coa_tujuan,
                                dj.unit,
                                dj.unit_tujuan,
                                dj.noreg
                        ) data
                        group by
                            tanggal, cast(keterangan as varchar(max)), kode_trans, no_coa, unit, kredit, debet
                            ".(($no_coa == '12020.000') ? ', noreg' : '')."
                    ) dj
                    on
                        dj.no_coa = c.coa
                where
                    (0-isnull(dj.kredit, 0)) <> 0 or
                    isnull(dj.debet, 0) <> 0
            ) data
            left join
                wilayah w
                on
                    data.unit = w.kode
            where
                data.no_coa = '".$no_coa."' and
                data.unit = '".$unit."'
            order by
                data.tanggal asc,
                data.urut asc,
                data.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $kode_perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $data = $this->getData( $start_date, $end_date, $kode_perusahaan, $unit );

        $content['data'] = $data;
        $content['periode'] = $start_date;
        $content['end_date'] = $end_date;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function formDetail()
    {
        $params = $this->input->get('params');

        $detail = $this->getDetail( $params['periode'], $params['end_date'], $params['unit'], $params['no_coa'] );

        $content['data'] = $params;
        $content['detail'] = $detail;
        $html = $this->load->view($this->pathView.'detail', $content, TRUE);

        echo $html;
    }

    public function encryptParams()
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

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $kode_perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $data = $this->getData( $start_date, $end_date, $kode_perusahaan, $unit );

        $filename = 'GL_V2_PERIODE_'.str_replace('-', '', $start_date).'-'.str_replace('-', '', $end_date).'_'.strtoupper($unit);

        $arr_header = array('No. COA', 'Perusahaan', 'Unit', 'Nama COA', 'Saldo Awal', 'Debet', 'Kredit', 'Saldo Akhir');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $tot_saldo_awal = 0;
            $tot_debet = 0;
            $tot_kredit = 0;
            $tot_saldo_akhir = 0;

            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'No. COA' => array('value' => strtoupper($value['no_coa']), 'data_type' => 'nik'),
                    'Perusahaan' => array('value' => strtoupper($value['kode_perusahaan']), 'data_type' => 'string'),
                    'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                    'Nama COA' => array('value' => strtoupper($value['nama_coa']), 'data_type' => 'string'),
                    'Saldo Awal' => array('value' => $value['saldo_awal'], 'data_type' => 'decimal2'),
                    'Debet' => array('value' => $value['debet'], 'data_type' => 'decimal2'),
                    'Kredit' => array('value' => $value['kredit'], 'data_type' => 'decimal2'),
                    'Saldo Akhir' => array('value' => $value['saldo_akhir'], 'data_type' => 'decimal2'),
                );

                $tot_saldo_awal += $value['saldo_awal'];
                $tot_debet += $value['debet'];
                $tot_kredit += $value['kredit'];
                $tot_saldo_akhir += $value['saldo_akhir'];

                $idx++;
            }

            $arr_column[] = array(
                'Nama COA' => array('value' => 'Total', 'data_type' => 'string', 'colspan' => array('A','D'), 'align' => 'right', 'text_style' => 'bold'),
                'Saldo Awal' => array('value' => $tot_saldo_awal, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Debet' => array('value' => $tot_debet, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Kredit' => array('value' => $tot_kredit, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Saldo Akhir' => array('value' => $tot_saldo_akhir, 'data_type' => 'decimal2', 'text_style' => 'bold'),
            );
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}
