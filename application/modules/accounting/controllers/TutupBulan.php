<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TutupBulan extends Public_Controller
{
    private $pathView = 'accounting/tutup_bulan/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/accounting/tutup_bulan/js/tutup-bulan.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/tutup_bulan/css/tutup-bulan.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Tutup Bulan';

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function dataLhkAkhirBulan($end_date) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.* from
            (
                select
                    rs.noreg,
                    m.nama as nama_mitra,
                    k.kandang,
                    ppl.nik as nik_ppl,
                    ppl.nama as nama_ppl
                    , w.kode as unit
                from
                (
                	select rs.* from rdim_submit rs
	                left join
	                    tutup_siklus ts
	                    on
	                        ts.noreg = rs.noreg
                    left join
                        (
                            select l.* from lhk l
                            right join
                                (
                                    select 
                                        rs.noreg, 
                                        max(rs.tgl_panen) as tgl_panen 
                                    from real_sj rs
                                    where
                                        rs.tgl_panen <= '".$end_date."'
                                    group by 
                                        rs.noreg
                                ) rs
                                on
                                    l.noreg = rs.noreg and
                                    l.tanggal >= rs.tgl_panen
                            where
                                l.id is not null
                        ) l_tutup_siklus
                        on
                            l_tutup_siklus.noreg = rs.noreg
	                where
	                    ts.id is null and
                        l_tutup_siklus.id is null
                ) rs
                left join
                    kandang k
                    on
                        k.id = rs.kandang
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
                        mm.mitra = m.id
                left join
                    (
                        select k1.* from karyawan k1
                        right join
                            (select max(id) as id, nik from karyawan where jabatan = 'ppl' and status = 1 group by nik) k2
                            on
                                k1.id = k2.id
                    ) ppl
                    on
                        ppl.nik = rs.sampling
            ) data
            left join
                (
                    select od1.* from order_doc od1
                    right join
                        (select max(id) as id, no_order from order_doc group by no_order) od2
                        on
                            od1.id = od2.id
                ) od
                on
                    od.noreg = data.noreg
            left join
                (
                    select td1.* from terima_doc td1
                    right join
                        (select max(id) as id, no_order from terima_doc group by no_order) td2
                        on
                            td1.id = td2.id
                ) td
                on
                    td.no_order = od.no_order
            left join
                (
                    select * from lhk 
                    where 
                        tanggal = '".$end_date."'
                ) l
                on
                    l.noreg = data.noreg
            where
                l.id is null and
                td.id is not null and
                td.datang < '".next_date($end_date)."' 
                and
                (
                    '".$end_date."' < '2025-11-01' and 
                    data.noreg not in (
                        '25091460102',
                        '25091460103',
                        '25101600101',
                        '25101350101',
                        '25101590101',
                        '25101330101'
                    )
                )
            order by
                data.unit asc,
                data.nama_ppl asc,
                data.nama_mitra asc,
                data.kandang asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function cekDataLhkAkhirBulan() {
        $params = $this->input->post('params');

        try {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);
            
            $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
            
            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));

            $data = $this->dataLhkAkhirBulan($end_date);

            if ( !empty($data) ) {
                $this->result['status'] = 2;
            } else {
                $this->result['status'] = 1;
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function formDataLhkAkhirBulan() {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        
        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        
        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->dataLhkAkhirBulan($end_date);

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'listCekLhk', $content, true);

        echo $html;
    }

    public function tutupBulan() {
        $params = $this->input->post('params');

        try {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);
            
            $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
            
            $tgl_awal_tahun = $tahun.'-01-01';
            $tgl_next_awal_tahun = ($tahun+1).'-01-01';
            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
            
            $tgl_next_saldo = next_date( $end_date );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                /*
                select
                    d_jurnal.coa,
                    -- d_jurnal.kode_trans,
                    -- d_jurnal.kode_jurnal,
                    sum(d_jurnal.debet) as debet,
                    sum(d_jurnal.kredit) as kredit,
                    d_jurnal.unit
                from
                (
                    /*
                    select
                        sb.coa,
                        sb.kode_trans,
                        sb.kode_jurnal,
                        sb.saldo_awal as debet,
                        0 as kredit,
                        sb.unit
                    from saldo_bulanan sb
                    where
                        sb.tanggal between '".$start_date."' and '".$end_date."'
                    */

                    select
                        sb.no_coa as coa,
                        sb.kode_trans,
                        sb.kode_jurnal,
                        case
                            when sb.debet2 > 0 then
                                sb.debet2
                            else
                                sb.debet1
                        end as debet,
                        -- sb.saldo_awal,
                        0 as kredit,
                        sb.unit
                    from (
                        select
                            sa.coa as no_coa,
                            sa.kode_trans,
                            sa.kode_jurnal,
                            sa.unit,
                            sum(sa.debet1) as debet1,
                            sum(sa.kredit1) as kredit1,
                            sum(sa.debet2) as debet2,
                            sum(sa.kredit2) as kredit2
                        from
                        (
                            select
                                sb.coa,
                                sb.kode_trans,
                                sb.kode_jurnal,
                                sb.saldo_awal as debet1,
                                0 as kredit1,
                                0 as debet2,
                                0 as kredit2,
                                sb.unit
                            from saldo_bulanan sb
                            where
                                sb.tanggal between '".$start_date."' and '".$end_date."'

                            union all

                            select
                                sc.no_coa as coa,
                                'INIT'+REPLACE(sc.periode, '-', '') as kode_trans,
                                null as kode_jurnal,
                                0 as debet1,
                                0 as kredit1,
                                sc.debet as debet2,
                                0 as kredit2,
                                sc.unit
                            from sacoa sc
                            where
                                sc.periode = '".substr($start_date, 0, 7)."'
                        ) sa
                        group by
                            sa.coa,
                            sa.kode_trans,
                            sa.kode_jurnal,
                            sa.unit
                    ) sb 

                    union all

                    select
                        dj.coa_asal as coa,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        0 as debet,
                        sum(dj.nominal) as kredit,
                        dj.unit
                    from det_jurnal dj
                    where
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        dj.coa_asal,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        dj.unit

                    union all

                    select
                        dj.coa_tujuan as coa,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        sum(dj.nominal) as debet,
                        0 as kredit,
                        case
                            when dj.unit_tujuan is not null then
                                dj.unit_tujuan
                            else
                                dj.unit
                        end as unit
                    from det_jurnal dj
                    where
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        dj.coa_tujuan,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        dj.unit_tujuan,
                        dj.unit
                ) d_jurnal
                group by
                    d_jurnal.coa,
                    d_jurnal.unit
                    -- ,
                    -- d_jurnal.kode_trans,
                    -- d_jurnal.kode_jurnal
                */

                select
                    data.no_coa as coa,
                    data.unit,
                    data.nama_coa,
                    data.noreg,
                    -- sum(isnull(data.saldo_awal, 0)) as saldo_awal,
                    -- sum(isnull(data.kredit, 0)) as kredit,
                    -- sum(isnull(data.debet, 0)) as debet,
                    sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_akhir
                from
                (
                    /* SALDO AWAL */
                    select
                        sb.no_coa as no_coa,
                        sb.unit,
                        sb.noreg,
                        c.nama_coa,
                        case
                            when sb.debet2 <> 0 then
                                -- sb.debet2
                                0
                            else
                                sb.debet1
                        end as saldo_awal,
                        -- sb.saldo_awal,
                        0 as kredit,
                        0 as debet
                    from (
                        select
                            sa.no_coa,
                            sa.unit,
                            sa.noreg,
                            sum(sa.debet1) as debet1,
                            sum(sa.kredit1) as kredit1,
                            sum(sa.debet2) as debet2,
                            sum(sa.kredit2) as kredit2
                        from
                        (
                            select
                                sb.coa as no_coa,
                                sb.unit,
                                sb.noreg,
                                isnull(sb.saldo_awal, 0) as debet1,
                                0 as kredit1,
                                0 as debet2,
                                0 as kredit2
                            from saldo_bulanan sb 
                            where 
                                sb.tanggal between '".$start_date."' and '".$end_date."'

                            union all

                            select
                                sc.no_coa,
                                sc.unit,
                                null as noreg,
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
                            sa.unit,
                            sa.noreg
                    ) sb 
                    left join
                        coa c
                        on
                            sb.no_coa = c.coa
                    /* END - SALDO AWAL */

                    union all

                    select
                        sc.no_coa,
                        sc.unit,
                        null as noreg,
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
                        dj.noreg,
                        c.nama_coa,
                        0 as saldo_awal,
                        (0-isnull(dj.kredit, 0)) as kredit,
                        isnull(dj.debet, 0) as debet
                    from coa c
                    left join
                        (
                            select noreg, no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                                select
                                    dj.noreg,
                                    dj.coa_asal as no_coa, 
                                    sum(dj.nominal) as kredit, 
                                    0 as debet, 
                                    dj.unit
                                from det_jurnal dj 
                                where 
                                    dj.tanggal between '".$start_date."' and '".$end_date."'
                                    -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                                group by dj.noreg, dj.coa_asal, dj.unit
                                
                                union all
                                
                                select 
                                    dj.noreg,
                                    dj.coa_tujuan as no_coa, 
                                    0 as kredit, 
                                    sum(dj.nominal) as debet, 
                                    case
                                        when dj.unit_tujuan is not null then
                                            dj.unit_tujuan
                                        else
                                            dj.unit
                                    end as unit
                                from det_jurnal dj 
                                where 
                                    dj.tanggal between '".$start_date."' and '".$end_date."'
                                    -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                                group by dj.noreg, dj.coa_tujuan, dj.unit, dj.unit_tujuan
                            ) data
                            group by
                                noreg, no_coa, unit
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
                    data.nama_coa,
                    data.noreg
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                $m_sb = new \Model\Storage\SaldoBulanan_model();
                $m_sb->where('periode_fiskal', $start_date)->delete();

                foreach ($d_conf as $k_conf => $v_conf) {
                    $m_sb = new \Model\Storage\SaldoBulanan_model();
                    $d_sb = $m_sb->where('tanggal', $start_date)
                                ->where('coa', $v_conf['coa'])
                                ->where('unit', $v_conf['unit'])
                                ->where('noreg', $v_conf['noreg'])
                                ->first();

                    if ( $d_sb ) {
                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $m_sb->where('tanggal', $start_date)
                            ->where('coa', $v_conf['coa'])
                            ->where('unit', $v_conf['unit'])
                            ->where('noreg', $v_conf['noreg'])
                            ->update(
                                array(
                                    'saldo_akhir' => $v_conf['saldo_akhir']
                                )
                            );
                    } else {
                        $firstDayOfMonth = date('Y-m-01', strtotime(prev_date( $start_date )));

                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $now = $m_conf->getDate();

                        $m_sb->tgl_trans = $now['waktu'];
                        $m_sb->coa = $v_conf['coa'];
                        $m_sb->tanggal = $start_date;
                        // $m_sb->saldo_awal = $v_conf['debet']-$v_conf['kredit'];
                        $m_sb->saldo_awal = 0;
                        $m_sb->saldo_akhir = $v_conf['saldo_akhir'];
                        $m_sb->periode_fiskal = $firstDayOfMonth;
                        $m_sb->unit = $v_conf['unit'];
                        $m_sb->noreg = $v_conf['noreg'];
                        $m_sb->save();
                    }

                    $save = 1;
                    if ( substr($v_conf['coa'], 0, 1) >= 5 && $tgl_next_awal_tahun == $tgl_next_saldo) {
                        $save = 0;
                    }

                    if ( $save == 1 ) {
                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $now = $m_conf->getDate();
    
                        $m_sb->tgl_trans = $now['waktu'];
                        $m_sb->coa = $v_conf['coa'];
                        $m_sb->tanggal = $tgl_next_saldo;
                        // $m_sb->saldo_awal = $v_conf['debet']-$v_conf['kredit'];
                        $m_sb->saldo_awal = $v_conf['saldo_akhir'];
                        $m_sb->saldo_akhir = 0;
                        $m_sb->periode_fiskal = $start_date;
                        $m_sb->unit = $v_conf['unit'];
                        $m_sb->noreg = $v_conf['noreg'];
                        $m_sb->save();
                    }
                }
            }

            if ( $tgl_next_awal_tahun == $tgl_next_saldo ) {
                /* UPDATE COA LABA RUGI TAHUN LALU */
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select noreg, '29200.000' as no_coa, isnull(sum(debet), 0) - isnull(sum(kredit), 0) as saldo_akhir, unit from (
                        select 
                            null as noreg,
                            no_coa,
                            sum(kredit) as kredit,
                            sum(debet) as debet,
                            unit 
                        from sacoa 
                        where
                            SUBSTRING(periode, 0, 5) = SUBSTRING('".$tgl_awal_tahun."', 0, 5)
                        group by 
                            no_coa, unit

                        union all

                        select
                            dj.noreg,
                            dj.coa_asal as no_coa, 
                            sum(dj.nominal) as kredit, 
                            0 as debet, 
                            dj.unit
                        from det_jurnal dj 
                        where 
                            dj.tanggal between '".$tgl_awal_tahun."' and '".$end_date."'
                            -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                        group by dj.noreg, dj.coa_asal, dj.unit
                        
                        union all
                        
                        select 
                            dj.noreg,
                            dj.coa_tujuan as no_coa, 
                            0 as kredit, 
                            sum(dj.nominal) as debet, 
                            case
                                when dj.unit_tujuan is not null then
                                    dj.unit_tujuan
                                else
                                    dj.unit
                            end as unit
                        from det_jurnal dj 
                        where 
                            dj.tanggal between '".$tgl_awal_tahun."' and '".$end_date."'
                            -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                        group by dj.noreg, dj.coa_tujuan, dj.unit, dj.unit_tujuan
                    ) data
                    where
                        SUBSTRING(data.no_coa, 1, 1) >= 5
                    group by
                        noreg, unit
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray();

                    foreach ($d_conf as $k_conf => $v_conf) {
                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $d_sb = $m_sb->where('tanggal', $tgl_next_saldo)
                                    ->where('coa', $v_conf['no_coa'])
                                    ->where('unit', $v_conf['unit'])
                                    ->where('noreg', $v_conf['noreg'])
                                    ->first();

                        if ( $d_sb ) {
                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $m_sb->where('tanggal', $tgl_next_saldo)
                                ->where('coa', $v_conf['no_coa'])
                                ->where('unit', $v_conf['unit'])
                                ->where('noreg', $v_conf['noreg'])
                                ->update(
                                    array(
                                        'saldo_akhir' => $v_conf['saldo_akhir']
                                    )
                                );
                        } else {
                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $now = $m_conf->getDate();

                            $m_sb->tgl_trans = $now['waktu'];
                            $m_sb->coa = $v_conf['no_coa'];
                            $m_sb->tanggal = $tgl_next_saldo;
                            // $m_sb->saldo_awal = $v_conf['debet']-$v_conf['kredit'];
                            $m_sb->saldo_awal = $v_conf['saldo_akhir'];
                            $m_sb->saldo_akhir = 0;
                            $m_sb->periode_fiskal = $start_date;
                            $m_sb->unit = $v_conf['unit'];
                            $m_sb->noreg = $v_conf['noreg'];
                            $m_sb->save();
                        }
                    }
                }
                /* END - UPDATE COA LABA RUGI TAHUN LALU */
            }

            /* PERIODE FISKAL */
            $m_bo = new \Model\Storage\PeriodeFiskal_model();
            $m_bo->where('start_date', $start_date)->update(
                array(
                    'status' => 0,
                    'opr' => 0,
                    'kas_bank' => 0
                )
            );

            $d_bo = $m_bo->where('start_date', $start_date)->first();

            $deskripsi_log = 'di-tutup oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/edit', $d_bo, $deskripsi_log );

            $m_conf = new \Model\Storage\Conf();
            $sql = "select * from periode_fiskal where start_date = '".$tgl_next_saldo."'";
            $d_pf_next = $m_conf->hydrateRaw( $sql );
            if ( $d_pf_next->count() > 0 ) {

                $d_pf_next = $d_pf_next->toArray()[0];

                $m_bo = new \Model\Storage\PeriodeFiskal_model();
                $m_bo->where('id', $d_pf_next['id'])->update(
                    array(
                        'status' => 1,
                        'opr' => 1,
                        'kas_bank' => 1
                    )
                );
                $d_bo = $m_bo->where('id', $d_pf_next['id'])->first();

                $deskripsi_log = 'di-aktifkan kembali oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_bo, $deskripsi_log );
            } else {
                $m_bo = new \Model\Storage\PeriodeFiskal_model();
                $m_bo->periode = substr($tgl_next_saldo, 0, 7);
                $m_bo->start_date = $tgl_next_saldo;
                $m_bo->end_date = date("Y-m-t", strtotime($tgl_next_saldo));
                $m_bo->status = 1;
                $m_bo->opr = 1;
                $m_bo->kas_bank = 1;
                $m_bo->save();

                $deskripsi_log = 'di-aktifkan oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_bo, $deskripsi_log );
            }
            /* END - PERIODE FISKAL */

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hapusTutupBulan() {
        $params = $this->input->post('params');

        try {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);

            $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));

            $tgl_next_saldo = next_date( $end_date );

            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();
            $sql = "select * from saldo_bulanan where tanggal = '".$tgl_next_saldo."'";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $m_sb = new \Model\Storage\SaldoBulanan_model();
                $m_sb->where('tanggal', $tgl_next_saldo)->delete();
            }

            $m_sb = new \Model\Storage\SaldoBulanan_model();
            $m_sb->where('tanggal', $start_date)->update(
                array('saldo_akhir' => null)
            );

            $m_bo = new \Model\Storage\PeriodeFiskal_model();
            $m_bo->where('start_date', $tgl_next_saldo)->update(
                array(
                    'status' => 0
                )
            );

            $m_bo->where('start_date', $start_date)->update(
                array(
                    'status' => 1
                )
            );
            $d_bo = $m_bo->where('start_date', $start_date)->first();

            $deskripsi_log = 'di-buka oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/edit', $d_bo, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        cetak_r( substr('21180.100', 0, 1) );
    }
}