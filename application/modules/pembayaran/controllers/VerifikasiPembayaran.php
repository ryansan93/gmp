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

    public function getData($id = null, $status = 1, $start_date = null, $end_date = null, $jenis = null, $bank = null) {
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
                    rp.no_rek
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
                        (select min(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'realisasi_pembayaran' group by tbl_name, tbl_id) lt2
                        on
                            lt1.id = lt2.id
                ) lt
                on
                    data.id = lt.tbl_id
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

        $data = $this->getData(null, 2, $start_date, $end_date, $jenis_transaksi, $bank);

        $content['data'] = $data;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/list_history', $content, true);

        echo $html;
    }

    public function formDetail() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $no_rek = $params['no_rek'];
        $atas_nama = $params['atas_nama'];
        $bank = $params['bank'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rpd.*,
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
                end as lampiran
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            left join
                (
                    select
                        kpd.nomor as kode_trans,
                        td.no_sj as no_inv,
                        td.no_sj as no_sj,
                        kpdd.total as bruto,
                        kpdd.total * (0.25/100) as pph,
                        '' as lampiran
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

                    union all

                    select
                        kpp.nomor as kode_trans,
                        kpp.invoice as no_inv,
                        kpp.invoice as no_sj,
                        kpp.total as bruto,
                        0 as pph,
                        '' as lampiran
                    from konfirmasi_pembayaran_pakan kpp

                    union all

                    select
                        kpv.nomor as kode_trans,
                        null as no_inv,
                        kpvd.no_sj as no_sj,
                        kpvd.total as bruto,
                        0 as pph,
                        '' as lampiran
                    from konfirmasi_pembayaran_voadip_det kpvd
                    left join
                        konfirmasi_pembayaran_voadip kpv
                        on
                            kpvd.id_header = kpv.id

                    union all

                    select
                        kpop.nomor as kode_trans,
                        kpop.invoice as no_inv,
                        null as no_sj,
                        (kpop.total+kpop.potongan_pph_23) as bruto,
                        kpop.potongan_pph_23 as pph,
                        kpop.lampiran
                    from konfirmasi_pembayaran_oa_pakan kpop

                    union all

                    select
                        kpp.nomor as kode_trans,
                        kpp.invoice as no_inv,
                        null as no_sj,
                        kpp.total as bruto,
                        0 as pph,
                        kpp.lampiran
                    from konfirmasi_pembayaran_peternak kpp
                ) konfir_pembayaran
                on
                    rpd.no_bayar = konfir_pembayaran.kode_trans
            where
                rp.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $content['no_rek'] = $no_rek;
        $content['atas_nama'] = $atas_nama;
        $content['bank'] = $bank;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_detail', $content, true);

        echo $html;
    }

    public function formRealisasiBayar() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $this->getData($id)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar', $content, true);

        echo $html;
    }

    public function formRealisasiBayarDetail() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $this->getData($id, 2)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar_detail', $content, true);

        echo $html;
    }

    public function formRealisasiBayarEdit() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $this->getData($id, 2)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar_edit', $content, true);

        echo $html;
    }

    public function save() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

            }
            
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $d_rp = $m_rp->where('id', $data['id'])->first();

                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $d_rp->coa_bank)->orderBy('id', 'desc')->first();

                $m_nbbk = new \Model\Storage\NoBbk_model();
                $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode);

                $m_nbbk->tbl_name = $m_rp->getTable();
                $m_nbbk->tbl_id = $d_rp->nomor;
                $m_nbbk->kode = $no_kk;
                $m_nbbk->save();

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $m_rp->where('id', $data['id'])->update(
                    array(
                        'no_bukti' => $no_kk,
                        'tgl_realisasi' => $data['tgl_bayar'],
                        'lampiran_realisasi' => $path_name,
                        'ket_realisasi' => $data['ket_bayar'],
                        'status' => 2
                    )
                );

                Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $data['tgl_bayar']);

                $_d_rp = $m_rp->where('id', $data['id'])->first();
                $deskripsi_log = 'di-bayar oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $_d_rp, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data pembayaran berhasil di simpan.';
            } else {
                $this->result['message'] = 'Error, segera hubungi tim IT.';
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                
            }
            
            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $data['id'])->first();
            
            $file_name = $path_name = $d_rp->lampiran_realisasi;
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];
            }

            $m_coa = new \Model\Storage\Coa_model();
            $d_coa = $m_coa->where('coa', $d_rp->coa_bank)->orderBy('id', 'desc')->first();

            $m_nbbk = new \Model\Storage\NoBbk_model();
            $d_nbbk = $m_nbbk->where('tbl_name', $m_rp->getTable())->where('tbl_id', $d_rp->nomor)->where('kode', 'like', $d_coa->kode.'%')->first();

            if ( !$d_nbbk ) {
                $m_nbbk = new \Model\Storage\NoBbk_model();
                $no_kk = $m_nbbk->getKodeKeluar($d_coa->kode);
                $m_nbbk->where('tbl_name', $m_rp->getTable())->where('tbl_id', $d_rp->nomor)->update(
                    array('kode' => $no_kk)
                );
            }

            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $m_rp->where('id', $data['id'])->update(
                array(
                    'no_bukti' => $data['no_bukti'],
                    'tgl_realisasi' => $data['tgl_bayar'],
                    'lampiran_realisasi' => $path_name,
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

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pembayaran berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $params['id'])->first();

            Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3, null, $d_rp->tgl_realisasi);

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
}