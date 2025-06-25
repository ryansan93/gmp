<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterKBD extends Public_Controller
{
    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/parameter/master_kbd/js/master_kbd.js'
            ));
            $this->add_external_css(array(
                'assets/parameter/master_kbd/css/master_kbd.css'
            ));

            $data = $this->includes;

            $m_pk = new \Model\Storage\PolaKerjasama_model();
            $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
            $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

            $m_pw = new \Model\Storage\Wilayah_model();
            $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();

            $content['pola_budidaya'] = $d_pk_bdy;
            $content['pola_kemitraan'] = $d_pk_km;
            $content['perwakilan'] = $d_pw;
            $data['title_menu'] = 'Master Kontrak, Bonus Dan Denda';

            // $content['nama_lampiran'] = $this->get_nama_lampiran();
            // $content['perusahaan'] = $this->get_data_perusahaan();

            $content['akses'] = $akses;
            $content['list'] = $this->list_sapronak_kesepakatan();
            // $content['list'] = array();
            // $content['jenis_pakan'] = $this->get_jenis_pakan();
            // $content['supplier'] = $this->get_data_supplier();
            // $content['jenis_doc'] = $this->get_jenis_doc();
            $content['add_form'] = $this->add_form();
            $data['view'] = $this->load->view('parameter/master_kbd/index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function get_jenis_doc()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                brg.*
            from barang brg
            right join
                (select max(id) as id, kode from barang where tipe = 'doc' group by kode) brg2
                on
                    brg.id = brg2.id
            order by
                brg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $datas = array();
        if ( $d_conf->count() > 0 ) {
            $datas = $d_conf->toArray();
        }

        // $m_brg = new \Model\Storage\Barang_model();
        // $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'doc')->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $pelanggan = $m_brg->where('tipe', 'doc')
        //                                   ->where('kode', $nomor['kode'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->with(['logs'])
        //                                   ->first()->toArray();

        //         array_push($datas, $pelanggan);
        //     }
        // }

        return $datas;
    }

    public function get_jenis_pakan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                brg.*
            from barang brg
            right join
                (select max(id) as id, kode from barang where tipe = 'pakan' group by kode) brg2
                on
                    brg.id = brg2.id
            order by
                brg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $datas = array();
        if ( $d_conf->count() > 0 ) {
            $datas = $d_conf->toArray();
        }

        // $m_brg = new \Model\Storage\Barang_model();
        // $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'pakan')->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $pelanggan = $m_brg->where('tipe', 'pakan')
        //                                   ->where('kode', $nomor['kode'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->with(['logs'])
        //                                   ->first()->toArray();

        //         array_push($datas, $pelanggan);
        //     }
        // }

        return $datas;
    }

    public function get_nama_lampiran()
    {
        $m_nama_lampiran = new \Model\Storage\NamaLampiran_model();
        $d_nama_lampiran = $m_nama_lampiran->where('jenis', 'SAPRONAK_KESEPAKATAN')->where('sequence', '>', 5)->get()->toArray();

        return $d_nama_lampiran;
    }

    public function get_data_supplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                plg.*
            from pelanggan plg
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg.id = plg2.id
            where
                plg.mstatus = 1
            order by
                plg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $datas = array();
        if ( $d_conf->count() > 0 ) {
            $datas = $d_conf->toArray();
        }

        // $m_pelanggan = new \Model\Storage\Pelanggan_model();
        // $d_nomor = $m_pelanggan->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $pelanggan = $m_pelanggan->where('nomor', $nomor['nomor'])
        //                                  ->where('tipe', 'supplier') 
        //                                  ->orderBy('version', 'desc')
        //                                  ->first();

        //         if ( $pelanggan ) {
        //             $pelanggan = $pelanggan->toArray();
        //             $key = $pelanggan['nama'].' | '.$pelanggan['nomor'];
        //             $datas[$key] = $pelanggan;
                    
        //             ksort( $datas );                    
        //         }

        //         // array_push($datas, $pelanggan);
        //     }
        // }

        return $datas;
    }

    public function get_data_perusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                prs.*
            from perusahaan prs
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs.id = prs2.id
            order by
                prs.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $datas = array();
        if ( $d_conf->count() > 0 ) {
            $datas = $d_conf->toArray();
        }

        // $m_perusahaan = new \Model\Storage\Perusahaan_model();
        // $d_nomor = $m_perusahaan->select('kode')->distinct('kode')->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $perusahaan = $m_perusahaan->where('kode', $nomor['kode'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->first()->toArray();

        //         array_push($datas, $perusahaan);
        //     }
        // }

        return $datas;
    }

    public function list_sapronak_kesepakatan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                logs.deskripsi,
                logs.waktu
            from
            (
                select 
                    max(sk.id) as id,
                    sk.mulai,
                    sk.nomor,
                    sk.item_pola,
                    sk.g_status,
                    pk.item as nama_pola
                from sapronak_kesepakatan sk
                right join
                    (select max(id) as id, nomor from sapronak_kesepakatan group by nomor) sk2
                    on
                        sk.id = sk2.id
                left join
                    pola_kerjasama pk
                    on
                        pk.id = sk.pola
                where
                    sk.g_status <> ".getStatus('delete')."
                group by
                    sk.mulai,
                    sk.nomor,
                    sk.item_pola,
                    sk.g_status,
                    pk.item
            ) data
            left join
                (
                    select lg1.* from log_tables lg1
                    right join
                        (select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'sapronak_kesepakatan' group by tbl_name, tbl_id) lg2
                        on
                            lg1.id = lg2.id
                ) logs
                on
                    logs.tbl_id = data.id
            order by
                data.mulai desc,
                data.nomor desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        // $d_nomor = $m_sk->select('nomor')->distinct('nomor')->get()->toArray();

        // $data = null;
        // foreach ($d_nomor as $nomor) {
        //     $d_sk = $m_sk->where('nomor', $nomor['nomor'])->where('g_status','<>',getStatus('delete'))->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'lampiran', 'logs', 'pola_kerjasama'])->orderBy('version', 'DESC')->first();

        //     if ( !empty($d_sk) ) {
        //         $data[ $d_sk['id'] ] = $d_sk->toArray();
        //     }
        // }

        // if ( !empty($data) ) {
        //     krsort($data);
        // }

        return $data;
    }

    public function list_sk()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list'] = $this->list_sapronak_kesepakatan();
        $html = $this->load->view('parameter/master_kbd/list', $content);
        
        echo $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk = $m_sk->where('g_status','<>',getStatus('delete'))
                     ->where('id', $id)
                     ->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'bonus_insentif_listrik', 'logs', 'pola_kerjasama', 'data_perusahaan'])
                     ->orderBy('id', 'DESC')->first();

        $show_header = null;
        $show_detail = null;
        if ( $akses['a_approve'] == 1 ) {
            $show_header = 'show';
            if ( $d_sk['g_status'] != getStatus('submit') ) {
                $show_detail = 'show2';
            } else {
                $show_detail = 'show';
            }
        } else if ( $akses['a_submit'] == 1 ) {
            if ( $d_sk['g_status'] != getStatus('submit') ) {
                $show_header = 'show';
                $show_detail = 'show2';
            } 
        }

        $content['show_header'] = $show_header;
        $content['show_detail'] = $show_detail;
        
        $content['tbl_logs'] = $this->getLogs( formatURL($d_sk->nomor) );

        $content['akses'] = $akses;
        $content['data'] = $d_sk;
        $html = $this->load->view('parameter/master_kbd/view_form', $content, true);
        
        return $html;
    }

    public function getLogs($nomor = null) {
        $m_sk = new \Model\Storage\SapronakKesepakatan_model;
        $d_sk = $m_sk->where('nomor', unformatURL($nomor))->orderBy('id', 'desc')->get()->toArray();

        $logs = array();
        foreach ($d_sk as $key => $v_sk) {
            $m_log = new \Model\Storage\LogTables_model;
            $d_log = $m_log->where('tbl_name', 'sapronak_kesepakatan')->where('tbl_id', $v_sk['id'])->get()->toArray();

            if ( !empty($d_log) ) {
                foreach ($d_log as $key => $v_log) {
                    $logs[] = $v_log;
                }
            }
        }

        return $logs;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $m_pk = new \Model\Storage\PolaKerjasama_model();
        $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
        $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

        $m_pw = new \Model\Storage\Wilayah_model();
        $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();

        $content['pola_budidaya'] = $d_pk_bdy;
        $content['pola_kemitraan'] = $d_pk_km;
        $content['perwakilan'] = $d_pw;
        $content['nama_lampiran'] = $this->get_nama_lampiran();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['jenis_pakan'] = $this->get_jenis_pakan();
        $content['supplier'] = $this->get_data_supplier();
        $content['jenis_doc'] = $this->get_jenis_doc();

        $content['akses'] = $akses;
        $content['data'] = null;
        $html = $this->load->view('parameter/master_kbd/add_form', $content, true);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk = $m_sk->where('g_status','<>',getStatus('delete'))
                     ->where('id', $id)
                     ->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'bonus_insentif_listrik', 'logs', 'pola_kerjasama'])
                     ->orderBy('id', 'DESC')->first();

        $m_pk = new \Model\Storage\PolaKerjasama_model();
        $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
        $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

        $m_pw = new \Model\Storage\Wilayah_model();
        $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();

        $content['pola_budidaya'] = $d_pk_bdy;
        $content['pola_kemitraan'] = $d_pk_km;
        $content['perwakilan'] = $d_pw;
        $content['nama_lampiran'] = $this->get_nama_lampiran();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['jenis_pakan'] = $this->get_jenis_pakan();
        $content['supplier'] = $this->get_data_supplier();
        $content['jenis_doc'] = $this->get_jenis_doc();

        $content['akses'] = $akses;
        $content['data'] = $d_sk;
        $html = $this->load->view('parameter/master_kbd/edit_form', $content, true);
        
        return $html;
    }

    public function list_sb()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list'] = $this->list_standar_budidaya();
        $html = $this->load->view('parameter/standar_budidaya/list', $content);
        
        echo $html;
    }

    public function mappingFiles($files)
    {
        $mappingFiles = [];
        foreach ($files['tmp_name'] as $key => $file) {
            $sha1 = sha1_file($file);
            $index = $key;
            $mappingFiles[$index] = [
                'name' => $files['name'][$key],
                'tmp_name' => $file,
                'type' => $files['type'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key]
            ];
        }
        
        return $mappingFiles;
    }

    public function save_data()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
            // cetak_r( $params, 1 );

            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $m_sk->nomor = $m_sk->getNextDocNum('ADM/HSK');
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $g_status;
            $m_sk->note = $params['note'];
            $m_sk->version = 1;
            $m_sk->perusahaan = $params['perusahaan'];
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            foreach ($params['harga_sapronak'] as $k_hs => $v_hs) {
                // NOTE: simpan lampiran sapronak
                $file_doc = $mappingFiles[ $v_hs['supplier'].'_DOC' ] ?: '';
                $file_pakan = $mappingFiles[ $v_hs['supplier'].'_PAKAN' ] ?: '';

                $file_name_doc = $path_name_doc = null;
                $file_name_pakan = $path_name_pakan = null;
                $isMoved_doc = 0;
                $moved_doc = 0;
                if (!empty($file_doc)) {
                    $moved_doc = uploadFile($file_doc);
                    $isMoved_doc = $moved_doc['status'];
                    if ($isMoved_doc) {
                        $file_name_doc = $moved_doc['name'];
                        $path_name_doc = $moved_doc['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_doc ;
                        $m_lampiran->path = $path_name_doc;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                }

                if (!empty($file_pakan)) {
                    $moved_pakan = uploadFile($file_pakan);
                    $isMoved_pakan = $moved_pakan['status'];
                    if ($isMoved_pakan) {
                        $file_name_pakan = $moved_pakan['name'];
                        $path_name_pakan = $moved_pakan['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_pakan ;
                        $m_lampiran->path = $path_name_pakan;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                }

                $m_hs = new \Model\Storage\HargaSapronak_model();
                $m_hs->id_sk = $m_sk->id;
                $m_hs->kode_supl = $v_hs['supplier'];
                $m_hs->doc_dok_cp = $path_name_doc;
                $m_hs->pakan_dok_cp = $path_name_pakan;
                $m_hs->save();

                foreach ($v_hs['doc'] as $k_hsd => $v_hsd) {
                    $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                    $m_dhs->id_header = $m_hs->id;
                    $m_dhs->kode_brg = $v_hsd['doc'];
                    $m_dhs->hrg_supplier = $v_hsd['hrg_doc_supplier'];
                    $m_dhs->hrg_peternak = $v_hsd['hrg_doc_peternak'];
                    $m_dhs->jenis = 'doc';
                    $m_dhs->save();
                }

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan1'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan1_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan1_peternak'];
                $m_dhs->jenis = 'pakan1';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan2'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan2_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan2_peternak'];
                $m_dhs->jenis = 'pakan2';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan3'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan3_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan3_peternak'];
                $m_dhs->jenis = 'pakan3';
                $m_dhs->save();
            }

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->kode_pakan1 = $params['performa']['kode_pakan1'];
            $m_hp->kode_pakan2 = $params['performa']['kode_pakan2'];
            $m_hp->kode_pakan3 = $params['performa']['kode_pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = (empty($v_sp['range_max'])) ? 0 : $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['bonus_fcr'] as $k_bonus_fcr => $v_bonus_fcr) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = !empty($v_bonus_fcr['range_awal']) ? $v_bonus_fcr['range_awal'] : 0;
                $m_ssp->range_akhir = !empty($v_bonus_fcr['range_akhir']) ? $v_bonus_fcr['range_akhir'] : 0;
                $m_ssp->tarif = $v_bonus_fcr['tarif'];
                $m_ssp->save();
            }

            foreach ($params['bonus'] as $k_bonus => $v_bonus) {
                $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                $m_hbi->id_sk =  $m_sk->id;
                $m_hbi->pola_kemitraan = $v_bonus['pola_kemitraan'];
                $m_hbi->ip_awal = $v_bonus['ip_awal'];
                $m_hbi->ip_akhir = $v_bonus['ip_akhir'];
                $m_hbi->bonus_ip = $v_bonus['bonus_harga'];
                $m_hbi->bonus_dh = $v_bonus['bonus_kematian'];
                $m_hbi->save();
            }

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            foreach ($params['bonus_insentif_listrik'] as $key => $v_bil) {
                $m_bil = new \Model\Storage\BonusInsentifListrik_model();
                $m_bil->id_sk = $m_sk->id;
                $m_bil->ip_awal = !empty($v_bil['range_awal']) ? $v_bil['range_awal'] : 0;
                $m_bil->ip_akhir = !empty($v_bil['range_akhir']) ? $v_bil['range_akhir'] : 0;
                $m_bil->bonus = $v_bil['tarif'];
                $m_bil->save();
            }

            $deskripsi_log_sk = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sk, $deskripsi_log_sk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_data()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = null;
        if ( !empty($files) ) {
            $mappingFiles = $this->mappingFiles($files);
        }

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
            $m_sk = new \Model\Storage\SapronakKesepakatan_model();

            $m_sk->where('id', $params['id'])->update(
                array(
                    'berakhir' => $now['waktu']
                    )
                );

            $d_sk = $m_sk->where('id', $params['id'])->first();

            $m_sk->nomor = $d_sk['nomor'];
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $g_status;
            $m_sk->note = $params['note'];
            $m_sk->version = $d_sk['version'] + 1;
            $m_sk->perusahaan = $params['perusahaan'];
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            foreach ($params['harga_sapronak'] as $k_hs => $v_hs) {
                // NOTE: simpan lampiran sapronak
                $file_doc = isset($mappingFiles[ $v_hs['supplier'].'_DOC' ]) ? $mappingFiles[ $v_hs['supplier'].'_DOC' ] : '';
                $file_pakan = isset($mappingFiles[ $v_hs['supplier'].'_PAKAN' ]) ? $mappingFiles[ $v_hs['supplier'].'_PAKAN' ] : '';

                $file_name_doc = $path_name_doc = null;
                $file_name_pakan = $path_name_pakan = null;
                $isMoved_doc = 0;
                $moved_doc = 0;
                if (!empty($file_doc)) {
                    $moved_doc = uploadFile($file_doc);
                    $isMoved_doc = $moved_doc['status'];
                    if ($isMoved_doc) {
                        $file_name_doc = $moved_doc['name'];
                        $path_name_doc = $moved_doc['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_doc ;
                        $m_lampiran->path = $path_name_doc;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                } else {
                    if ( !empty($v_hs['doc_dok_old']) ) {
                        $file_name_doc = $v_hs['doc_dok_old'];
                        $path_name_doc = $v_hs['doc_dok_old'];
                    }
                }

                if (!empty($file_pakan)) {
                    $moved_pakan = uploadFile($file_pakan);
                    $isMoved_pakan = $moved_pakan['status'];
                    if ($isMoved_pakan) {
                        $file_name_pakan = $moved_pakan['name'];
                        $path_name_pakan = $moved_pakan['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_pakan ;
                        $m_lampiran->path = $path_name_pakan;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                } else {
                    if ( !empty($v_hs['pakan_dok_old']) ) {
                        $file_name_pakan = $v_hs['pakan_dok_old'];
                        $path_name_pakan = $v_hs['pakan_dok_old'];
                    }
                }

                $m_hs = new \Model\Storage\HargaSapronak_model();
                $m_hs->id_sk = $m_sk->id;
                $m_hs->kode_supl = $v_hs['supplier'];
                $m_hs->doc_dok_cp = $path_name_doc;
                $m_hs->pakan_dok_cp = $path_name_pakan;
                $m_hs->save();

                foreach ($v_hs['doc'] as $k_hsd => $v_hsd) {
                    $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                    $m_dhs->id_header = $m_hs->id;
                    $m_dhs->kode_brg = $v_hsd['doc'];
                    $m_dhs->hrg_supplier = $v_hsd['hrg_doc_supplier'];
                    $m_dhs->hrg_peternak = $v_hsd['hrg_doc_peternak'];
                    $m_dhs->jenis = 'doc';
                    $m_dhs->save();
                }

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan1'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan1_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan1_peternak'];
                $m_dhs->jenis = 'pakan1';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan2'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan2_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan2_peternak'];
                $m_dhs->jenis = 'pakan2';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan3'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan3_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan3_peternak'];
                $m_dhs->jenis = 'pakan3';
                $m_dhs->save();
            }

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->kode_pakan1 = $params['performa']['kode_pakan1'];
            $m_hp->kode_pakan2 = $params['performa']['kode_pakan2'];
            $m_hp->kode_pakan3 = $params['performa']['kode_pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = (empty($v_sp['range_max'])) ? 0 : $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['bonus_fcr'] as $k_bonus_fcr => $v_bonus_fcr) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = !empty($v_bonus_fcr['range_awal']) ? $v_bonus_fcr['range_awal'] : 0;
                $m_ssp->range_akhir = !empty($v_bonus_fcr['range_akhir']) ? $v_bonus_fcr['range_akhir'] : 0;
                $m_ssp->tarif = $v_bonus_fcr['tarif'];
                $m_ssp->save();
            }

            foreach ($params['bonus'] as $k_bonus => $v_bonus) {
                $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                $m_hbi->id_sk =  $m_sk->id;
                $m_hbi->pola_kemitraan = $v_bonus['pola_kemitraan'];
                $m_hbi->ip_awal = $v_bonus['ip_awal'];
                $m_hbi->ip_akhir = $v_bonus['ip_akhir'];
                $m_hbi->bonus_ip = $v_bonus['bonus_harga'];
                $m_hbi->bonus_dh = $v_bonus['bonus_kematian'];
                $m_hbi->save();
            }

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            foreach ($params['bonus_insentif_listrik'] as $key => $v_bil) {
                $m_bil = new \Model\Storage\BonusInsentifListrik_model();
                $m_bil->id_sk = $m_sk->id;
                $m_bil->ip_awal = !empty($v_bil['range_awal']) ? $v_bil['range_awal'] : 0;
                $m_bil->ip_akhir = !empty($v_bil['range_akhir']) ? $v_bil['range_akhir'] : 0;
                $m_bil->bonus = $v_bil['tarif'];
                $m_bil->save();
            }

            $deskripsi_log_sk = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_sk, $deskripsi_log_sk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil diubah';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_copy()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        if ( !empty($files) ) {
            $mappingFiles = mappingFiles($files);
        }

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $m_sk->nomor = $m_sk->getNextDocNum('ADM/HSK');
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $g_status;
            $m_sk->note = $params['note'];
            $m_sk->version = 1;
            $m_sk->perusahaan = $params['perusahaan'];
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            foreach ($params['harga_sapronak'] as $k_hs => $v_hs) {
                // NOTE: simpan lampiran sapronak
                $file_doc = isset($mappingFiles[ $v_hs['supplier'].'_DOC' ]) ? $mappingFiles[ $v_hs['supplier'].'_DOC' ] : '';
                $file_pakan = isset($mappingFiles[ $v_hs['supplier'].'_PAKAN' ]) ? $mappingFiles[ $v_hs['supplier'].'_PAKAN' ] : '';

                $file_name_doc = $path_name_doc = null;
                $file_name_pakan = $path_name_pakan = null;
                $isMoved_doc = 0;
                $moved_doc = 0;
                if (!empty($file_doc)) {
                    $moved_doc = uploadFile($file_doc);
                    $isMoved_doc = $moved_doc['status'];
                    if ($isMoved_doc) {
                        $file_name_doc = $moved_doc['name'];
                        $path_name_doc = $moved_doc['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_doc ;
                        $m_lampiran->path = $path_name_doc;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                } else {
                    if ( !empty($v_hs['doc_dok_old']) ) {
                        $file_name_doc = $v_hs['doc_dok_old'];
                        $path_name_doc = $v_hs['doc_dok_old'];
                    }
                }

                if (!empty($file_pakan)) {
                    $moved_pakan = uploadFile($file_pakan);
                    $isMoved_pakan = $moved_pakan['status'];
                    if ($isMoved_pakan) {
                        $file_name_pakan = $moved_pakan['name'];
                        $path_name_pakan = $moved_pakan['path'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = null;
                        $m_lampiran->filename = $file_name_pakan ;
                        $m_lampiran->path = $path_name_pakan;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                } else {
                    if ( !empty($v_hs['pakan_dok_old']) ) {
                        $file_name_pakan = $v_hs['pakan_dok_old'];
                        $path_name_pakan = $v_hs['pakan_dok_old'];
                    }
                }

                $m_hs = new \Model\Storage\HargaSapronak_model();
                $m_hs->id_sk = $m_sk->id;
                $m_hs->kode_supl = $v_hs['supplier'];
                $m_hs->doc_dok_cp = $path_name_doc;
                $m_hs->pakan_dok_cp = $path_name_pakan;
                $m_hs->save();

                foreach ($v_hs['doc'] as $k_hsd => $v_hsd) {
                    $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                    $m_dhs->id_header = $m_hs->id;
                    $m_dhs->kode_brg = $v_hsd['doc'];
                    $m_dhs->hrg_supplier = $v_hsd['hrg_doc_supplier'];
                    $m_dhs->hrg_peternak = $v_hsd['hrg_doc_peternak'];
                    $m_dhs->jenis = 'doc';
                    $m_dhs->save();
                }

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan1'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan1_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan1_peternak'];
                $m_dhs->jenis = 'pakan1';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan2'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan2_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan2_peternak'];
                $m_dhs->jenis = 'pakan2';
                $m_dhs->save();

                $m_dhs = new \Model\Storage\DetHargaSapronak_model();
                $m_dhs->id_header = $m_hs->id;
                $m_dhs->kode_brg = $v_hs['pakan3'];
                $m_dhs->hrg_supplier = $v_hs['hrg_pakan3_supplier'];
                $m_dhs->hrg_peternak = $v_hs['hrg_pakan3_peternak'];
                $m_dhs->jenis = 'pakan3';
                $m_dhs->save();
            }

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->kode_pakan1 = $params['performa']['kode_pakan1'];
            $m_hp->kode_pakan2 = $params['performa']['kode_pakan2'];
            $m_hp->kode_pakan3 = $params['performa']['kode_pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = (empty($v_sp['range_max'])) ? 0 : $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['bonus_fcr'] as $k_bonus_fcr => $v_bonus_fcr) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = !empty($v_bonus_fcr['range_awal']) ? $v_bonus_fcr['range_awal'] : 0;
                $m_ssp->range_akhir = !empty($v_bonus_fcr['range_akhir']) ? $v_bonus_fcr['range_akhir'] : 0;
                $m_ssp->tarif = $v_bonus_fcr['tarif'];
                $m_ssp->save();
            }

            foreach ($params['bonus'] as $k_bonus => $v_bonus) {
                $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                $m_hbi->id_sk =  $m_sk->id;
                $m_hbi->pola_kemitraan = $v_bonus['pola_kemitraan'];
                $m_hbi->ip_awal = $v_bonus['ip_awal'];
                $m_hbi->ip_akhir = $v_bonus['ip_akhir'];
                $m_hbi->bonus_ip = $v_bonus['bonus_harga'];
                $m_hbi->bonus_dh = $v_bonus['bonus_kematian'];
                $m_hbi->save();
            }

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            foreach ($params['bonus_insentif_listrik'] as $key => $v_bil) {
                $m_bil = new \Model\Storage\BonusInsentifListrik_model();
                $m_bil->id_sk = $m_sk->id;
                $m_bil->ip_awal = !empty($v_bil['range_awal']) ? $v_bil['range_awal'] : 0;
                $m_bil->ip_akhir = !empty($v_bil['range_akhir']) ? $v_bil['range_akhir'] : 0;
                $m_bil->bonus = $v_bil['tarif'];
                $m_bil->save();
            }

            $deskripsi_log_sk = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sk, $deskripsi_log_sk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function ack_data()
    {
        $id_sk = $this->input->post('params');

        try {
            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $status_doc = getStatus('ack');

            // NOTE: ack header sapronak_kesepakatan
            $m_sk->where('id', $id_sk)->update(
                array(
                    'g_status' => $status_doc
                )
            );

            $d_sk = $m_sk->where('id', $id_sk)->first();

            $deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ACK';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$d_sk['mulai']);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function approve_data()
    {
        $id_sk = $this->input->post('params');

        try {
            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $status_doc = getStatus('approve');

            // NOTE: ack header sapronak_kesepakatan
            $m_sk->where('id', $id_sk)->update(
                array(
                    'g_status' => $status_doc
                )
            );

            $d_sk = $m_sk->where('id', $id_sk)->first();

            $deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di APPROVE';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$d_sk['mulai']);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id_group = $this->input->post('params');

        try {
            $m_dgrp = new \Model\Storage\DetGroup_model();          
            $m_dgrp->where('id_group', $id_group)->delete();

            $m_grp = new \Model\Storage\Group_model();
            $m_grp->where('id_group', $id_group)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function model($status)
    {
        $m_sp = new \Model\Storage\SapronakKesepakatan_model();
        $dashboard = $m_sp->getDashboard($status);

        return $dashboard;
    }
}