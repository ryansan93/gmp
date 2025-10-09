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
        $content = null;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/outstanding', $content, true);

        return $html;
    }

    public function history() {
        $content = null;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/history', $content, true);

        return $html;
    }

    public function getData($id = null, $status = 1, $start_date = null, $end_date = null, $jenis = null) {
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

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                data.*,
                supl.nama_supl,
                lt.deskripsi,
                lt.waktu
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
                    rp.no_bukti
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
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
                    rp.no_bukti
            ) data
            left join
                (
                    select plg1.nomor as kode_supl, plg1.nama as nama_supl, 'supplier' as jenis from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.mstatus = 1

                    union all

                    select eks1.nomor as kode_supl, eks1.nama as nama_supl, 'ekspedisi' as jenis from ekspedisi eks1
                    right join
                        (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                        on
                            eks1.id = eks2.id

                    union all
                    
                    select mtr1.nomor as kode_supl, mtr1.nama as nama_supl, 'mitra' as jenis from mitra mtr1
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
            ".$sql_condition."
        ";
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
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/list_outstanding', $content, true);

        echo $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis_transaksi = $params['jenis'];

        $data = $this->getData(null, 2, $start_date, $end_date, $jenis_transaksi);

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/list_history', $content, true);

        echo $html;
    }

    public function formRealisasiBayar() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $data = $this->getData($id)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar', $content, true);

        echo $html;
    }

    public function formRealisasiBayarDetail() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $data = $this->getData($id, 2)[0];

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/verifikasi_pembayaran/form_realisasi_bayar_detail', $content, true);

        echo $html;
    }

    public function formRealisasiBayarEdit() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $data = $this->getData($id, 2)[0];

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
                        'no_bukti' => $data['no_bukti'],
                        'tgl_realisasi' => $data['tgl_bayar'],
                        'lampiran_realisasi' => $path_name,
                        'ket_realisasi' => $data['ket_bayar'],
                        'status' => 2
                    )
                );

                // Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $data['tgl_bayar']);

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
}