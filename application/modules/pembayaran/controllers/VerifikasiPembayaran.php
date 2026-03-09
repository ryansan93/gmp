<?php defined('BASEPATH') OR exit('No direct script access allowed');

class VerifikasiPembayaran extends Public_Controller
{
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
                'assets/pembayaran/verifikasi_pembayaran/js/verifikasi-pembayaran.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/verifikasi_pembayaran/css/verifikasi-pembayaran.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Verifikasi Pembayaran';

            $content['akses'] = $this->hakAkses;
            $content['outstanding'] = $this->outstanding();
            $content['history'] = $this->history();
            $data['view'] = $this->load->view('pembayaran/verifikasi_pembayaran/index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function outstanding() {
        $m_coa = new \Model\Storage\Coa_model();

        $content['bank'] = $m_coa->getDataBank();
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/outstanding', $content, true);

        return $html;
    }

    public function history() {
        $m_coa = new \Model\Storage\Coa_model();

        $content['bank'] = $m_coa->getDataBank();
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/history', $content, true);

        return $html;
    }

    public function getData($id = null, $status = 1, $start_date = null, $end_date = null, $jenis = null, $bank = null, $tbl_name = null) {
        $sql_condition = null;

        $sql_id = null;
        if ( !empty($id) ) {
            $sql_id = "data.id = ".$id;
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ".$sql_id;
            } else {
                $sql_condition .= "where ".$sql_id;
            }
        }

        $sql_date = null;
        if ( !empty($start_date) && !empty($end_date) ) {
            $sql_date = "data.tgl_bayar between '".$start_date."' and '".$end_date."'";
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ".$sql_date;
            } else {
                $sql_condition .= "where ".$sql_date;
            }
        }

        $sql_jenis = null;
        if ( !empty($jenis) && !in_array('all', $jenis) ) {
            $sql_jenis = "data.jenis_transaksi in ('".implode("', '", $jenis)."')";
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ".$sql_jenis;
            } else {
                $sql_condition .= "where ".$sql_jenis;
            }
        }

        $sql_bank = null;
        if ( !empty($bank) && $bank != 'all' ) {
            $sql_bank = "cast(data.coa_bank as varchar(15)) = '".$bank."'";
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ".$sql_bank;
            } else {
                $sql_condition .= "where ".$sql_bank;
            }
        }

        $sql_tbl_name = null;
        if ( !empty($tbl_name) && $tbl_name != 'all' ) {
            $sql_tbl_name = "data.tbl_name = '".$tbl_name."'";
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ".$sql_tbl_name;
            } else {
                $sql_condition .= "where ".$sql_tbl_name;
            }
        }

        $m_wil = new \Model\Storage\Wilayah_model();
        $d_wil = $m_wil->getDataUnit(1, $this->userid);

        $unit = null;
        foreach ($d_wil as $k_wil => $v_wil) {
            $unit[] = $v_wil['kode'];
        }

        // $sql_unit = null;
        // if ( !empty($unit) ) {
        //     $sql_unit = "c.unit in ('".implode("', '", $unit)."')";
        //     if ( !empty($sql_condition) ) {
        //         $sql_condition .= " and ".$sql_unit;
        //     } else {
        //         $sql_condition .= "where ".$sql_unit;
        //     }
        // }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                data.*,
                supl.nama_supl,
                lt.deskripsi,
                lt.waktu,
                case
                    when supl.no_rek is not null then
                        supl.no_rek
                    else
                        isnull(rek.no_rek, '')
                end as no_rek,
                case
                    when supl.atas_nama is not null then
                        supl.atas_nama
                    else
                        isnull(rek.atas_nama, '')
                end as atas_nama, 
                case
                    when supl.bank is not null then
                        supl.bank
                    else
                        isnull(rek.bank, '')
                end as bank,
                case
                    when c.unit in ('".implode("', '", $unit)."') then
                        1
                    else
                        0
                end as verifikasi,
                c.unit
            from
            (
                select
                    rpd.transaksi as jenis_transaksi,
                    case
                        when rpd.transaksi like 'OA PAKAN' then
                            'ekspedisi'
                        when rpd.transaksi like 'PLASMA' then
                            'mitra'
                        else
                            'supplier'
                    end as jenis_supl,
                    case
                        when rpd.transaksi like 'OA PAKAN' then
                            rp.ekspedisi
                        when rpd.transaksi like 'PLASMA' then
                            rp.peternak
                        else
                            rp.supplier
                    end as kode_supl,
                    rp.tgl_bayar as tgl_pengajuan,
                    rp.jml_transfer as jml_transfer,
                    rp.jml_bayar as jml_bayar,
                    cast(rp.lampiran as varchar(max)) as lampiran,
                    rp.coa_bank,
                    rp.nama_bank,
                    rp.id,
                    rp.tgl_realisasi as tgl_bayar,
                    cast(rp.lampiran_realisasi as varchar(max)) as lampiran_realisasi,
                    cast(rp.ket_realisasi as varchar(max)) as ket_realisasi,
                    rp.no_bukti,
                    nb.kode as kode_trans,
                    rp.no_rek,
                    'realisasi_pembayaran' as tbl_name
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                left join
                    no_bbk nb
                    on
                        nb.tbl_id = rp.nomor
                where
                    rp.status = ".$status."
                group by
                    rpd.transaksi,
                    rp.ekspedisi,
                    rp.peternak,
                    rp.supplier,
                    rp.tgl_bayar,
                    rp.jml_transfer,
                    rp.jml_bayar,
                    cast(rp.lampiran as varchar(max)),
                    rp.coa_bank,
                    rp.nama_bank,
                    rp.id,
                    rp.tgl_realisasi,
                    cast(rp.lampiran_realisasi as varchar(max)),
                    cast(rp.ket_realisasi as varchar(max)),
                    rp.no_bukti,
                    nb.kode,
                    rp.no_rek

                union all

                select
                    'PIUTANG PLASMA' as jenis_transaksi,
                    'mitra' as jenis_supl,
                    case
                        when p.jenis like 'mitra' then
                            p.mitra
                        else
                            p.karyawan
                    end as kode_supl,
                    p.tanggal as tgl_pengajuan,
                    p.nominal as jml_transfer,
                    p.nominal as jml_bayar,
                    cast(p.path as varchar(max)) as lampiran,
                    p.tf_bank as coa_bank,
                    c.nama_coa as nama_bank,
                    p.id,
                    p.tgl_realisasi as tgl_bayar,
                    cast(p.lampiran_realisasi as varchar(max)) as lampiran_realisasi,
                    cast(p.ket_realisasi as varchar(max)) as ket_realisasi,
                    p.no_bukti,
                    nb.kode as kode_trans,
                    '' as no_rek,
                    'piutang' as tbl_name
                from piutang p
                left join
                    coa c
                    on
                        p.tf_bank = c.coa
                left join
                    no_bbk nb
                    on
                        nb.tbl_id = p.kode
                where
                    p.status = ".$status." and
                    p.tf_bank <> '0'

                union all

                select
                    'PERALATAN' as jenis_transaksi,
                    'supplier' as jenis_supl,
                    op.supplier as kode_supl,
                    bp.tgl_bayar as tgl_pengajuan,
                    bp.jml_bayar as jml_transfer,
                    bp.jml_bayar as jml_bayar,
                    cast(bp.lampiran as varchar(max)) as lampiran,
                    bp.coa_bank as coa_bank,
                    bp.nama_bank as nama_bank,
                    bp.id,
                    bp.tgl_realisasi as tgl_bayar,
                    cast(bp.lampiran_realisasi as varchar(max)) as lampiran_realisasi,
                    cast(bp.ket_realisasi as varchar(max)) as ket_realisasi,
                    bp.no_bukti,
                    nb.kode as kode_trans,
                    cast(bp.no_rek as varchar(50)) as no_rek,
                    'bayar_peralatan' as tbl_name
                from bayar_peralatan bp
                left join
                    order_peralatan op
                    on
                        bp.no_order = op.no_order
                left join
                    no_bbk nb
                    on
                        nb.tbl_id = cast(bp.id as varchar(50))
                where
                    bp.mstatus = ".$status."
            ) data
            left join
                (
                    select plg1.nomor as kode_supl, plg1.nama as nama_supl, 'supplier' as jenis, null as no_rek, null as atas_nama, null as bank from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.mstatus = 1

                    union all

                    select eks1.nomor as kode_supl, eks1.nama as nama_supl, 'ekspedisi' as jenis, null as no_rek, null as atas_nama, null as bank from ekspedisi eks1
                    right join
                        (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                        on
                            eks1.id = eks2.id

                    union all
                    
                    select mtr1.nomor as kode_supl, mtr1.nama as nama_supl, 'mitra' as jenis, mtr1.rekening_nomor as no_rek, mtr1.rekening_pemilik as atas_nama, mtr1.bank from mitra mtr1
                    right join 
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) supl
                on
                    data.jenis_supl = supl.jenis and
                    data.kode_supl = supl.kode_supl
            left join
                (
                    select lt1.* from log_tables lt1
                    right join
                        (select min(id) as id, tbl_name, tbl_id from log_tables where tbl_name in ('realisasi_pembayaran', 'piutang', 'bayar_peralatan') group by tbl_name, tbl_id) lt2
                        on
                            lt1.id = lt2.id
                ) lt
                on
                    cast(data.id as varchar(20)) = lt.tbl_id and
                    data.tbl_name = lt.tbl_name
            left join
                coa c
                on
                    c.coa = data.coa_bank
            left join
                (
                    select cast(id as varchar(10)) as id, rekening_nomor as no_rek, rekening_pemilik as atas_nama, bank, 'ekspedisi' as jenis from bank_ekspedisi be

                    union all

                    select cast(id as varchar(10)) as id, rekening_nomor as no_rek, rekening_pemilik as atas_nama, bank, 'supplier' as jenis from bank_pelanggan bp
                ) rek
                on
                    data.no_rek = rek.id and
                    data.jenis_supl = rek.jenis
            ".$sql_condition."
            order by
                lt.waktu asc
        ";
        
        // cetak_r( $sql, 1 );

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataOutstanding() {
        $data = $this->getData();

        $content['data'] = $data;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/list_outstanding', $content, true);

        echo $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis_transaksi = $params['jenis'];
        $bank = $params['bank'];
        $tbl_name = $params['tbl_name'];

        $data = $this->getData(null, 2, $start_date, $end_date, $jenis_transaksi, $bank);

        $files = \Model\Storage\AttachmentRealisasiPembayaran_model::showLastData($data['id'], $tbl_name);

        $temp_file =[];
        foreach($files as $f){
            if($f['tbl_name']){
                $temp_file[$f['realisasi_id']][] = $f;
            }
        }
      
        $content['attachment'] =  $temp_file;
        // echo "<pre>";
        // print_r($temp_file);
        // die;


        $content['data'] = $data;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/list_history', $content, true);

        echo $html;
    }

    public function getDataDetail($params) {
        $id = $params['id'];
        $tbl_name = $params['tbl_name'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                data.*,
                supl.nama_supl
            from
            (
                select * from
                (
                    select
                        case
                            when rpd.transaksi like 'OA PAKAN' then
                                'ekspedisi'
                            when rpd.transaksi like 'PLASMA' then
                                'mitra'
                            else
                                'supplier'
                        end as jenis_supl,
                        case
                            when rpd.transaksi like 'OA PAKAN' then
                                rp.ekspedisi
                            when rpd.transaksi like 'PLASMA' then
                                rp.peternak
                            else
                                rp.supplier
                        end as kode_supl,
                        rp.tgl_bayar as tgl_pengajuan,
                        rpd.id_header,
                        rpd.transaksi,
                        rpd.no_bayar,
                        rpd.tagihan,
                        rpd.bayar,
                        rpd.cn,
                        rpd.potongan,
                        rpd.transfer,
                        rpd.uang_muka,
                        rpd.dn,
                        rpd.id,
                        case
                            when konfir_pembayaran.no_inv is not null then
                                konfir_pembayaran.no_inv
                            else
                                rp.no_invoice
                        end as no_inv,
                        konfir_pembayaran.no_sj,
                        konfir_pembayaran.bruto,
                        konfir_pembayaran.pph,
                        konfir_pembayaran.bruto - konfir_pembayaran.pph as netto,
                        case
                            when rp.lampiran is not null then
                                rp.lampiran
                            else
                                konfir_pembayaran.lampiran
                        end as lampiran,
                        konfir_pembayaran.tanggal,
                        konfir_pembayaran.jenis,
                        'realisasi_pembayaran' as tbl_name
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    left join
                        (
                            select
                                kpd.tgl_bayar as tanggal,
                                kpd.nomor as kode_trans,
                                td.no_sj as no_inv,
                                td.no_sj as no_sj,
                                case
                                    when kpd.tgl_bayar >= '2026-01-01' then
                                        ((kpdd.total + isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0))
                                    else
                                        kpdd.total
                                end as bruto,
                                case
                                    when kpd.tgl_bayar >= '2026-01-01' then
                                        ((kpdd.total + isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0)) * (0.25/100)
                                    else
                                        kpdd.total * (0.25/100)
                                end as pph,
                                '' as lampiran,
                                'DOC' as jenis
                            from konfirmasi_pembayaran_doc_det kpdd
                            left join
                                konfirmasi_pembayaran_doc kpd
                                on
                                    kpdd.id_header = kpd.id
                            left join
                                (
                                    select td1.* from terima_doc td1
                                    right join
                                        (select max(id) as id, no_order from terima_doc group by no_order) td2
                                        on
                                            td1.id = td2.id
                                ) td
                                on
                                    td.no_order = kpdd.no_order
                            left join
                                (select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
                                on
                                    _cn.nomor = kpd.nomor
                            left join
                                (select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
                                on
                                    _dn.nomor = kpd.nomor
        
                            union all
        
                            select
                                kpp.tgl_bayar as tanggal,
                                kpp.nomor as kode_trans,
                                kpp.invoice as no_inv,
                                kpp.invoice as no_sj,
                                case
                                    when kpp.tgl_bayar >= '2026-01-01' then
                                        ((kpp.total + isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0))
                                    else
                                        kpp.total
                                end as bruto,
                                0 as pph,
                                '' as lampiran,
                                'PAKAN' as jenis
                            from konfirmasi_pembayaran_pakan kpp
                            left join
                                (select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
                                on
                                    _cn.nomor = kpp.nomor
                            left join
                                (select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
                                on
                                    _dn.nomor = kpp.nomor
        
                            union all
        
                            select
                                kpv.tgl_bayar as tanggal,
                                kpv.nomor as kode_trans,
                                null as no_inv,
                                kpvd.no_sj as no_sj,
                                case
                                    when kpv.tgl_bayar >= '2026-01-01' then
                                        ((kpv.total + isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0))
                                    else
                                        kpv.total
                                end as bruto,
                                0 as pph,
                                '' as lampiran,
                                'OVK' as jenis
                            from konfirmasi_pembayaran_voadip_det kpvd
                            left join
                                konfirmasi_pembayaran_voadip kpv
                                on
                                    kpvd.id_header = kpv.id
                            left join
                                (select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
                                on
                                    _cn.nomor = kpv.nomor
                            left join
                                (select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
                                on
                                    _dn.nomor = kpv.nomor
        
                            union all
        
                            select
                                kpop.tgl_bayar as tanggal,
                                kpop.nomor as kode_trans,
                                kpop.invoice as no_inv,
                                null as no_sj,
                                case
                                    when kpop.tgl_bayar >= '2026-01-01' then
                                        ((kpop.total+kpop.potongan_pph_23+isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0))
                                    else
                                        (kpop.total+kpop.potongan_pph_23)
                                end as bruto,
                                kpop.potongan_pph_23 as pph,
                                kpop.lampiran,
                                'OA PAKAN' as jenis
                            from konfirmasi_pembayaran_oa_pakan kpop
                            left join
                                (select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
                                on
                                    _cn.nomor = kpop.nomor
                            left join
                                (select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
                                on
                                    _dn.nomor = kpop.nomor
        
                            union all
        
                            select
                                kpp.tgl_bayar as tanggal,
                                kpp.nomor as kode_trans,
                                kpp.invoice as no_inv,
                                null as no_sj,
                                case
                                    when kpp.tgl_bayar >= '2026-01-01' then
                                        ((kpp.total+isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0))
                                    else
                                        kpp.total
                                end as bruto,
                                0 as pph,
                                kpp.lampiran,
                                'RHPP' as jenis
                            from konfirmasi_pembayaran_peternak kpp
                            left join
                                (select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
                                on
                                    _cn.nomor = kpp.nomor
                            left join
                                (select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
                                on
                                    _dn.nomor = kpp.nomor
                        ) konfir_pembayaran
                        on
                            rpd.no_bayar = konfir_pembayaran.kode_trans
                    where
                        rp.id = ".$id."

                    union all

                    select
                        'mitra' as jenis_supl,
                        case
                            when p.jenis like 'mitra' then
                                p.mitra
                            else
                                p.karyawan
                        end as kode_supl,
                        p.tanggal as tgl_pengajuan,
                        p.id as id_header,
                        'PIUTANG PLASMA' as transaksi,
                        p.kode as no_bayar,
                        p.nominal as tagihan,
                        p.nominal as bayar,
                        0 as cn,
                        0 as potongan,
                        p.nominal as transfer,
                        0 as uang_muka,
                        0 as dn,
                        p.id,
                        p.kode as no_inv,
                        p.kode as no_sj,
                        p.nominal as bruto,
                        0 as pph,
                        p.nominal as netto,
                        p.path as lampiran,
                        p.tanggal as tanggal,
                        'PIUTANG PLASMA' as jenis,
                        'piutang' as tbl_name
                    from piutang p
                    left join
                        coa c
                        on
                            p.tf_bank = c.coa
                    left join
                        no_bbk nb
                        on
                            nb.tbl_id = p.no_bukti
                    where
                        p.id = ".$id."

                    union all

                    select
                        'supplier' as jenis_supl,
                        op.supplier as kode_supl,
                        bp.tgl_bayar as tgl_pengajuan,
                        bp.id as id_header,
                        'PERALATAN' as transaksi,
                        bp.no_faktur as no_bayar,
                        bp.jml_bayar as tagihan,
                        bp.jml_bayar as bayar,
                        0 as cn,
                        0 as potongan,
                        bp.jml_bayar as transfer,
                        0 as uang_muka,
                        0 as dn,
                        bp.id,
                        bp.no_faktur as no_inv,
                        bp.no_faktur as no_sj,
                        bp.jml_tagihan as bruto,
                        0 as pph,
                        bp.jml_tagihan as netto,
                        bp.lampiran as lampiran,
                        bp.tgl_bayar as tanggal,
                        'PERALATAN' as jenis,
                        'bayar_peralatan' as tbl_name
                    from bayar_peralatan bp
                    left join
                        order_peralatan op
                        on
                            bp.no_order = op.no_order
                    left join
                        no_bbk nb
                        on
                            nb.tbl_id = cast(bp.id as varchar(50))
                    where
                        bp.id = ".$id."
                ) data
                where
                    data.tbl_name = '".$tbl_name."'
            ) data
            left join
                (
                    select plg1.nomor as kode_supl, plg1.nama as nama_supl, 'supplier' as jenis, null as no_rek, null as atas_nama, null as bank from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.mstatus = 1

                    union all

                    select eks1.nomor as kode_supl, eks1.nama as nama_supl, 'ekspedisi' as jenis, null as no_rek, null as atas_nama, null as bank from ekspedisi eks1
                    right join
                        (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                        on
                            eks1.id = eks2.id

                    union all
                    
                    select mtr1.nomor as kode_supl, mtr1.nama as nama_supl, 'mitra' as jenis, mtr1.rekening_nomor as no_rek, mtr1.rekening_pemilik as atas_nama, mtr1.bank from mitra mtr1
                    right join 
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) supl
                on
                    data.jenis_supl = supl.jenis and
                    data.kode_supl = supl.kode_supl
            order by
                data.tanggal asc,
                data.no_inv asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function formDetail() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $no_rek = $params['no_rek'];
        $atas_nama = $params['atas_nama'];
        $bank = $params['bank'];

        $data = $this->getDataDetail( $params );

        $content['id'] = $id;
        $content['data'] = $data;
        $content['no_rek'] = $no_rek;
        $content['atas_nama'] = $atas_nama;
        $content['bank'] = $bank;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_detail', $content, true);

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

        $id = $params['id'];
        $no_rek = $params['no_rek'];
        $atas_nama = $params['atas_nama'];
        $bank = $params['bank'];

        $data = $this->getDataDetail( $params );
            
        $filename = 'DETAIL_PENGAJUAN';

        $tot_bruto = 0;
        $tot_pph = 0;
        $tot_netto = 0;
        $tot_transfer = 0;

        $arr_header = array('A', 'B', 'C', 'D', 'E', 'F');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $arr_column[ $idx ] = array(
                'A' => array('value' => 'SUPPLIER', 'data_type' => 'string', 'border' => 'none'),
                'B' => array('value' => strtoupper($data[0]['nama_supl']), 'data_type' => 'string', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => 'TGL PENGAJUAN', 'data_type' => 'string', 'border' => 'none'),
                'B' => array('value' => $data[0]['tgl_pengajuan'], 'data_type' => 'date', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => 'ATAS NAMA', 'data_type' => 'string', 'border' => 'none'),
                'B' => array('value' => strtoupper($atas_nama), 'data_type' => 'string', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => 'BANK', 'data_type' => 'string', 'border' => 'none'),
                'B' => array('value' => strtoupper($bank), 'data_type' => 'string', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => 'NO. REKENING', 'data_type' => 'string', 'border' => 'none'),
                'B' => array('value' => strtoupper($no_rek), 'data_type' => 'string', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => '', 'data_type' => 'string', 'border' => 'none')
            );
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => 'TANGGAL', 'data_type' => 'string'),
                'B' => array('value' => 'NO. BAYAR / NO. INVOICE', 'data_type' => 'string'),
                'C' => array('value' => 'BRUTO', 'data_type' => 'string'),
                'D' => array('value' => 'POTONGAN PPH', 'data_type' => 'string'),
                'E' => array('value' => 'NETTO', 'data_type' => 'string'),
                'F' => array('value' => 'PENGAJUAN TRANSFER', 'data_type' => 'string'),
            );
            $idx++;

            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'A' => array('value' => $value['tanggal'], 'data_type' => 'date'),
                    'B' => array('value' => $value['no_inv'], 'data_type' => 'string'),
                    'C' => array('value' => $value['bruto'], 'data_type' => 'decimal2'),
                    'D' => array('value' => $value['pph'], 'data_type' => 'decimal2'),
                    'E' => array('value' => $value['netto'], 'data_type' => 'decimal2'),
                    'F' => array('value' => $value['transfer'], 'data_type' => 'decimal2'),
                );

                $tot_bruto =+ $value['bruto'];
                $tot_pph =+ $value['pph'];
                $tot_netto =+ $value['netto'];
                $tot_transfer =+ $value['transfer'];

                $idx++;
            }

            $arr_column[] = array(
                'B' => array('value' => 'TOTAL', 'data_type' => 'string', 'colspan' => array('A','B'), 'align' => 'right', 'text_style' => 'bold'),
                'C' => array('value' => $tot_bruto, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'D' => array('value' => $tot_pph, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'E' => array('value' => $tot_netto, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'F' => array('value' => $tot_transfer, 'data_type' => 'decimal2', 'text_style' => 'bold'),
            );
        }

        // $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, 1, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }

    public function formRealisasiBayar() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $tbl_name = $params['tbl_name'];

        $data = $this->getData($id, 1, null, null, null, null, $tbl_name)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar', $content, true);

        echo $html;
    }

    public function formRealisasiBayarDetail() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $tbl_name = $params['tbl_name'];

        $data = $this->getData($id, 2, null, null, null, null, $tbl_name)[0];

        $content['attachment']  = \Model\Storage\AttachmentRealisasiPembayaran_model::showLastData($data['id'], $tbl_name);        
        $content['data']        = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar_detail', $content, true);

        echo $html;
    }

    public function formRealisasiBayarEdit() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $tbl_name = $params['tbl_name'];

        $data = $this->getData($id, 2, null, null, null, null, $tbl_name)[0];
        $content['attachment'] = \Model\Storage\AttachmentRealisasiPembayaran_model::showLastData($data['id'], $tbl_name);   
        $content['data'] = $data;
       
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar_edit', $content, true);

        echo $html;
    }

    public function save() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

       // tambahan hafidz

        if (isset($_FILES['files']) && !empty($_FILES['files']['name'])) {
            $groupedFiles = [];

            foreach ($_FILES['files']['name'] as $index => $filename) {
                $groupedFiles[$index] = [
                    'name'      => $_FILES['files']['name'][$index],
                    'type'      => $_FILES['files']['type'][$index],
                    'tmp_name'  => $_FILES['files']['tmp_name'][$index],
                    'size'      => $_FILES['files']['size'][$index],
                    'error'     => $_FILES['files']['error'][$index],
                ];
            }

            // check nama file
            $existing_files = \Model\Storage\AttachmentRealisasiPembayaran_model::showAll();

            $existing_names = [];
            foreach ($existing_files as $file) {
                $existing_names[] = strtolower($file['name_file_old']);
            }

            $name_exits = false;

            // end check nama file

            $uploadDir = FCPATH . "uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                echo "Folder dibuat: $uploadDir<br>";
            }

            foreach ($groupedFiles as $file) {
                if ($file['error'] === 0) {
                    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $targetFile = $uploadDir . ubahNama($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {

                        $m_attach = new \Model\Storage\AttachmentRealisasiPembayaran_model();
                        $m_attach->insert([
                            'realisasi_id' => $data['id'],
                            'file_name'    => $file['name'],
                            'path'         => $targetFile,
                            'created_at'   => date("Y-m-d H:i:s"),
                            'name_file_old'=> $file['name'],
                            'tbl_name'     => $data['tbl_name']
                        ]);

                    } else {
                        echo "Gagal upload file '{$file['name']}'<br>";
                    }
                } else {
                    echo "File '{$file['name']}' memiliki error saat upload<br>";
                }
            }
        }

        // end tambahan hafidz
      

        try {
            // $file_name = $path_name = null;
            // $isMoved = 0;
            // if (!empty($files)) {
            //     $moved = uploadFile($files);
            //     $isMoved = $moved['status'];

            //     $file_name = $moved['name'];
            //     $path_name = $moved['path'];
            // }

            if ( $data['tbl_name'] == 'realisasi_pembayaran' ) {
                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $d_rp = $m_rp->where('id', $data['id'])->first();

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_rp->coa_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode, $data['tgl_bayar']);

                $m_nbbk->tbl_name = $m_rp->getTable();
                $m_nbbk->tbl_id = $d_rp->nomor;
                $m_nbbk->kode = $no_kk;
                $m_nbbk->save();

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $m_rp->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $no_kk,
                        'tgl_realisasi' => $data['tgl_bayar'],
                        // 'lampiran_realisasi' => $data['id'],
                        'ket_realisasi' => $data['ket_bayar'],
                        'status' => 2
                    )
                );

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, $data['tbl_name'], $data['tgl_bayar']);

                $_d_rp = $m_rp->where('id', $data['id'])->first();
                $deskripsi_log = 'di-bayar oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_rp, $deskripsi_log);
            }

            if ( $data['tbl_name'] == 'piutang' ) {
                $m_piutang = new \Model\Storage\Piutang_model();
                $d_piutang = $m_piutang->where('id', $data['id'])->first();

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_piutang->tf_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode, $data['tgl_bayar']);

                $m_nbbk->tbl_name = $m_piutang->getTable();
                $m_nbbk->tbl_id = $d_piutang->kode;
                $m_nbbk->kode = $no_kk;
                $m_nbbk->save();

                $m_piutang = new \Model\Storage\Piutang_model();
                $m_piutang->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $no_kk,
                        'tgl_realisasi' => $data['tgl_bayar'],
                        // 'lampiran_realisasi' => $data['id'],
                        'ket_realisasi' => $data['ket_bayar'],
                        'status' => 2
                    )
                );

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, $data['tbl_name'], $data['tgl_bayar']);

                $_d_piutang = $m_piutang->where('id', $data['id'])->first();
                $deskripsi_log = 'di-bayar oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_piutang, $deskripsi_log);
            }

            if ( $data['tbl_name'] == 'bayar_peralatan' ) {
                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $d_bp = $m_bp->where('id', $data['id'])->first();

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_bp->coa_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode, $data['tgl_bayar']);

                $m_nbbk->tbl_name = $m_bp->getTable();
                $m_nbbk->tbl_id = $d_bp->id;
                $m_nbbk->kode = $no_kk;
                $m_nbbk->save();

                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $m_bp->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $no_kk,
                        'tgl_realisasi' => $data['tgl_bayar'],
                        'lampiran_realisasi' => $data['id'],
                        'ket_realisasi' => $data['ket_bayar'],
                        'mstatus' => 2
                    )
                );

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, $data['tbl_name'], $data['tgl_bayar']);

                $_d_bp = $m_bp->where('id', $data['id'])->first();
                $deskripsi_log = 'di-bayar oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_bp, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pembayaran berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            // cetak_r( $data, 1 );

            // $isMoved = 0;
            // if (!empty($files)) {
            //     $moved = uploadFile($files);
            //     $isMoved = $moved['status'];
            // }

            $this->exec_editAttachment($data, $files);
            
            if ( $data['tbl_name'] == 'realisasi_pembayaran' ) {
                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $d_rp = $m_rp->where('id', $data['id'])->first();
                
                // $file_name = $path_name = $d_rp->lampiran_realisasi;
                // if ($isMoved) {
                //     $file_name = $moved['name'];
                //     $path_name = $moved['path'];
                // }

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_rp->coa_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $d_nbbk = $m_nbbk->where('tbl_name', $m_rp->getTable())->where('tbl_id', $d_rp->nomor)->where('kode', 'like', $d_coa->kode.'%')->first();

                // if ( !$d_nbbk ) {
                //     $m_nbbk = new \Model\Storage\NoBbk_model();
                //     $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode, $data['tgl_bayar']);
                //     $m_nbbk->where('tbl_name', $m_rp->getTable())->where('tbl_id', $d_rp->nomor)->update(
                //         array('kode' => $no_kk)
                //     );
                // }

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $m_rp->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $data['no_bukti'],
                        'tgl_realisasi' => $data['tgl_bayar'],
                        // 'lampiran_realisasi' => $path_name,
                        'ket_realisasi' => $data['ket_bayar'],
                        'status' => 2
                    )
                );

                $tgl_bayar = $d_rp->tgl_realisasi;
                if ( $data['tgl_bayar'] < $tgl_bayar ) {
                    $tgl_bayar = $data['tgl_bayar'];
                }

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $tgl_bayar);

                $_d_rp = $m_rp->where('id', $data['id'])->first();
                $deskripsi_log = 'update pembayaran oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_rp, $deskripsi_log);
            }

            if ( $data['tbl_name'] == 'bayar_peralatan' ) {
                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $d_bp = $m_bp->where('id', $data['id'])->first();
                
                // $file_name = $path_name = $d_bp->lampiran_realisasi;
                // if ($isMoved) {
                //     $file_name = $moved['name'];
                //     $path_name = $moved['path'];
                // }

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_bp->coa_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $d_nbbk = $m_nbbk->where('tbl_name', $m_bp->getTable())->where('tbl_id', $d_bp->id)->where('kode', 'like', $d_coa->kode.'%')->first();

                if ( !$d_nbbk ) {
                    $m_nbbk = new \Model\Storage\NoBbk_model();
                    $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode, $data['tgl_bayar']);
                    $m_nbbk->where('tbl_name', $m_bp->getTable())->where('tbl_id', $d_bp->id)->update(
                        array('kode' => $no_kk)
                    );
                }

                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $m_bp->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $data['no_bukti'],
                        'tgl_realisasi' => $data['tgl_bayar'],
                        // 'lampiran_realisasi' => $path_name,
                        'ket_realisasi' => $data['ket_bayar'],
                        'mstatus' => 2

                    )
                );

                $tgl_bayar = $d_bp->tgl_realisasi;
                if ( $data['tgl_bayar'] < $tgl_bayar ) {
                    $tgl_bayar = $data['tgl_bayar'];
                }

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $tgl_bayar);

                $_d_bp = $m_bp->where('id', $data['id'])->first();
                $deskripsi_log = 'update pembayaran oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_bp, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pembayaran berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        \Model\Storage\AttachmentRealisasiPembayaran_model::deleteByRealisasiId($params['id'] ?? [], $params['tbl_name']);

        try {
            if ( $params['tbl_name'] == 'realisasi_pembayaran' ) {
                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $d_rp = $m_rp->where('id', $params['id'])->first();
    
                Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3, $params['tbl_name'], $d_rp->tgl_realisasi);
    
                $m_nbbk = new \Model\Storage\NoBbk_model();
                $m_nbbk->where('tbl_name', $m_rp->getTable())->where('tbl_id', $d_rp->nomor)->delete();
    
                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $m_rp->where('id', $params['id'])->update(
                    array(
                        'no_bukti' => null,
                        'tgl_realisasi' => null,
                        'lampiran_realisasi' => null,
                        'ket_realisasi' => null,
                        'status' => 1
                    )
                );
    
                $_d_rp = $m_rp->where('id', $params['id'])->first();
                $deskripsi_log = 'hapus pembayaran oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_rp, $deskripsi_log);
            }

            if ( $params['tbl_name'] == 'piutang' ) {
                $m_piutang = new \Model\Storage\Piutang_model();
                $d_piutang = $m_piutang->where('id', $params['id'])->first();
    
                Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3, $params['tbl_name'], $d_piutang->tgl_realisasi);
    
                $m_nbbk = new \Model\Storage\NoBbk_model();
                $m_nbbk->where('tbl_name', $m_piutang->getTable())->where('tbl_id', $d_piutang->kode)->delete();
    
                $m_piutang = new \Model\Storage\Piutang_model();
                $m_piutang->where('id', $params['id'])->update(
                    array(
                        'no_bukti' => null,
                        'tgl_realisasi' => null,
                        'lampiran_realisasi' => null,
                        'ket_realisasi' => null,
                        'status' => 1
                    )
                );
    
                $_d_piutang = $m_piutang->where('id', $params['id'])->first();
                $deskripsi_log = 'hapus pembayaran oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_piutang, $deskripsi_log);
            }

            if ( $params['tbl_name'] == 'bayar_peralatan' ) {
                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $d_bp = $m_bp->where('id', $params['id'])->first();
    
                Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3, $params['tbl_name'], $d_bp->tgl_realisasi);
    
                $m_nbbk = new \Model\Storage\NoBbk_model();
                $m_nbbk->where('tbl_name', $m_bp->getTable())->where('tbl_id', $d_bp->id)->delete();
    
                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $m_bp->where('id', $params['id'])->update(
                    array(
                        'no_bukti' => null,
                        'tgl_realisasi' => null,
                        'lampiran_realisasi' => null,
                        'ket_realisasi' => null,
                        'mstatus' => 1
                    )
                );
    
                $_d_bp = $m_bp->where('id', $params['id'])->first();
                $deskripsi_log = 'hapus pembayaran oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_bp, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pembayaran berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function printPreview($id) {        
        $id = exDecrypt( $id );
        
        $data = $this->getData( $id, 2 )[0];

        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('id', 'desc')->with(['d_kota'])->first();

        $content['perusahaan'] = $d_prs->toArray();
        $content['data'] = $data;

        $res_view_html = $this->load->view('pembayaran/verifikasi_pembayaran/exportPdf', $content, true);

        echo $res_view_html;
    }

    public function exec_editAttachment($data, $files)
    {   
        \Model\Storage\AttachmentRealisasiPembayaran_model::deleteNotInOldFile($data['id'], $data['old_file'] ?? [], $data['tbl_name']);

        if (isset($_FILES['files']) && !empty($_FILES['files']['name'])) {
            $groupedFiles = [];

            foreach ($_FILES['files']['name'] as $index => $filename) {
                $groupedFiles[$index] = [
                    'name'      => $_FILES['files']['name'][$index],
                    'type'      => $_FILES['files']['type'][$index],
                    'tmp_name'  => $_FILES['files']['tmp_name'][$index],
                    'size'      => $_FILES['files']['size'][$index],
                    'error'     => $_FILES['files']['error'][$index],
                ];
            }

            $uploadDir = FCPATH . "uploads/"; 

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                echo "Folder dibuat: $uploadDir<br>";
            } 

            // check nama file
                $existing_files = \Model\Storage\AttachmentRealisasiPembayaran_model::showAll();
                $existing_names = [];
                foreach ($existing_files as $file) {
                    $existing_names[] = strtolower($file['name_file_old']);
                }
                $name_exits = false;
            // end check nama file
    
            foreach ($groupedFiles as $file) {
                if ($file['error'] === 0) {
                    $ext            = pathinfo($file['name'], PATHINFO_EXTENSION);
                    // $encryptedName  = md5(uniqid() . $file['name'] . time()) . '.' . $ext;
                    // $targetFile     = $uploadDir . $encryptedName;
                    $targetFile     = $uploadDir . ubahNama($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        if (in_array(strtolower($file['name']), $existing_names)) {
                            $name_exits = true;
                        }
                        $m_attach       = new \Model\Storage\AttachmentRealisasiPembayaran_model();
                        $m_attach->insert([
                            'realisasi_id' => $data['id'],
                            'file_name'    => ubahNama($file['name']),
                            'path'         => $targetFile,
                            'created_at'   => date("Y-m-d H:i:s"),
                            'name_file_old'=> $name_exits ? ubahNama($file['name']) : $file['name'],
                            'tbl_name' => $data['tbl_name'],
                        ]);
                    
                    } else {
                        echo "Gagal upload file '{$file['name']}'<br>";
                    }
                } else {
                    echo "File '{$file['name']}' memiliki error saat upload<br>";
                }
            }
        }
    }

    public function tes() {
        Modules::run( 'base/InsertJurnal/exec', $this->url, 1142, 1142, 2, 'realisasi_pembayaran', '2026-01-05');
    }
}