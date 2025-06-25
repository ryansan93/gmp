<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AdjustmentOutVoadip extends Public_Controller {

    private $path = 'transaksi/adjustment_out_voadip/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/adjustment_out_voadip/js/adjustment-out-voadip.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/adjustment_out_voadip/css/adjustment-out-voadip.css",
            ));

            $data = $this->includes;

            // $mitra = $this->getMitra();
            // $peralatan = $this->get_peralatan();

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Adjustment Out OVK';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_query_gudang = null;
        if (  stristr($params['gudang'], 'all') === FALSE  ) {
            $sql_query_gudang = "and adjout.kode_gudang = '".$params['gudang']."'";
        }
        $sql_query_barang = null;
        if (  stristr($params['barang'], 'all') === FALSE  ) {
            $sql_query_barang = "and adjout.kode_barang = '".$params['barang']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                adjout.*, gdg.nama as nama_gudang, brg.nama as nama_barang 
            from adjout_voadip adjout
            right join
                (
                    select g.* from gudang g
                ) gdg
                on
                    gdg.id = adjout.kode_gudang
            right join
                (
                    select b.* from barang b 
                    right join
                        (select max(id) as id, kode from barang group by kode) brg
                        on
                            b.id = brg.id
                ) brg
                on
                    brg.kode = adjout.kode_barang
            where
                adjout.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_query_gudang."
                ".$sql_query_barang."
        ";
        $d_adjout = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_adjout->count() > 0 ) {
            $data = $d_adjout->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $params = $this->input->get('params');

        if ( isset($params['id']) && !empty($params['id']) ) {
            if ( isset($params['edit']) && !empty($params['edit']) ) {
                $html = $this->editForm( $params['id'] );
            } else {
                $html = $this->viewForm( $params['id'] );
            }
        } else {
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getGudang()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select g.* from gudang g where g.jenis = 'OBAT' order by g.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_gdg->count() > 0 ) {
            $data = $d_gdg->toArray();
        }

        return $data;
    }

    public function getBarang()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select b.* from barang b 
            right join
                (select max(id) as id, kode from barang group by kode) brg
                on
                    b.id = brg.id
            where b.tipe = 'obat'
            order by b.nama asc

        ";
        $d_brg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }

        return $data;
    }

    public function getSj()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1 s.* from stok s order by s.periode desc
            ";
            $d_stok = $m_conf->hydrateRaw( $sql );

            $id_header = null;
            if ( $d_stok->count() > 0 ) {
                $d_stok = $d_stok->toArray();

                $id_header = $d_stok[0]['id'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    ds.*,
                    brg.nama,
                    kirim.no_sj
                from det_stok ds 
                right join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        ds.kode_barang = brg.kode
                right join
                    kirim_voadip kirim
                    on
                        ds.kode_trans = kirim.no_order
                where 
                    ds.id_header = '".$id_header."' and 
                    ds.kode_gudang = '".$params['gudang']."' and
                    ds.kode_barang = '".$params['barang']."' and
                    ds.tgl_trans = '".$params['tgl_sj']."'
                order by
                    ds.tgl_trans asc
            ";
            $d_ds = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_ds->count() > 0 ) {
                $d_ds = $d_ds->toArray();

                foreach ($d_ds as $key => $value) {
                    $data[ $key ] = array(
                        'tanggal' => strtoupper(tglIndonesia( $value['tgl_trans'], '-', ' ' )),
                        'no_sj' => $value['no_sj'],
                        'sisa_stok' => $value['jml_stok'],
                        'harga' => $value['hrg_beli'],
                    );
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function riwayat()
    {
        $html = null;

        $content['gudang'] = $this->getGudang();
        $content['barang'] = $this->getBarang();

        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $html = null;

        $content['gudang'] = $this->getGudang();
        $content['barang'] = $this->getBarang();

        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $html = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                adjout.*, gdg.nama as nama_gudang, brg.nama as nama_barang 
            from adjout_voadip adjout
            right join
                (
                    select g.* from gudang g
                ) gdg
                on
                    gdg.id = adjout.kode_gudang
            right join
                (
                    select b.* from barang b 
                    right join
                        (select max(id) as id, kode from barang group by kode) brg
                        on
                            b.id = brg.id
                ) brg
                on
                    brg.kode = adjout.kode_barang
            where
                adjout.id = ".$id."
        ";
        $d_adjout = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_adjout->count() > 0 ) {
            $data = $d_adjout->toArray()[0];
        }

        $content['akses'] = $this->akses;
        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_adjout = new \Model\Storage\AdjoutVoadip_model();
            $now = $m_adjout->getDate();

            $tanggal = $now['tanggal'];

            $kode = $m_adjout->getNextId();

            $m_adjout->kode = $kode;
            $m_adjout->tanggal = $tanggal;
            $m_adjout->tgl_trans = $params['tgl_sj'];
            $m_adjout->kode_trans = $params['no_sj'];
            $m_adjout->kode_gudang = $params['kode_gudang'];
            $m_adjout->kode_barang = $params['kode_barang'];
            $m_adjout->sisa_stok = $params['sisa_stok'];
            $m_adjout->harga = $params['harga'];
            $m_adjout->jumlah = $params['jumlah'];
            $m_adjout->keterangan = $params['keterangan'];
            $m_adjout->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_adjout, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id' => $m_adjout->id);
            $this->result['content'] = array(
                'id' => $m_adjout->id,
                'tanggal' => $tanggal,
                'delete' => 0,
                'message' => 'Data berhasil di simpan.',
                'status_jurnal' => 0
            );
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_adjout = new \Model\Storage\AdjoutVoadip_model();
            $d_adjout = $m_adjout->where('id', $params['id'])->first();

            $tanggal = $d_adjout->tanggal;

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_adjout, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id' => $m_adjout->id);
            $this->result['content'] = array(
                'id' => $params['id'],
                'tanggal' => $tanggal,
                'delete' => 1,
                'message' => 'Data berhasil di hapus.',
                'status_jurnal' => 0
            );
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hitungStokByTransaksi()
    {
        $params = $this->input->post('params');

        $id = $params['id'];
        $tanggal = $params['tanggal'];
        $delete = $params['delete'];
        $message = $params['message'];
        $status_jurnal = $params['status_jurnal'];

        try {
            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_voadip_by_transaksi 'adjout_voadip', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";

            $d_conf = $conf->hydrateRaw($sql);

            $id = ($delete == 0) ? $id : 0;

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hitungStokAwal()
    {
        $params = $this->input->post('params');

        try {
            $id_adjout = $params['id_adjout'];

            $date = date('Y-m-d');
            
            $m_stok = new \Model\Storage\Stok_model();
            $now = $m_stok->getDate();
            $d_stok = $m_stok->where('periode', $date)->first();

            $stok_id = null;
            if ( $d_stok ) {
                $stok_id = $d_stok->id;

                $this->hitungStok($id_adjout, $stok_id);
            } else {
                $m_stok->periode = $date;
                $m_stok->user_proses = $this->userid;
                $m_stok->tgl_proses = $now['waktu'];
                $m_stok->save();

                $stok_id = $m_stok->id;

                $conf = new \Model\Storage\Conf();
                $sql = "EXEC get_data_stok_voadip_by_tanggal @date = '$date'";

                $d_conf = $conf->hydrateRaw($sql);

                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray();
                    $jml_data = count($d_conf);
                    $idx = 0;
                    foreach ($d_conf as $k_conf => $v_conf) {
                        $m_ds = new \Model\Storage\DetStok_model();
                        $m_ds->id_header = $stok_id;
                        $m_ds->tgl_trans = $v_conf['tgl_trans'];
                        $m_ds->kode_gudang = $v_conf['kode_gudang'];
                        $m_ds->kode_barang = $v_conf['kode_barang'];
                        $m_ds->jumlah = $v_conf['jumlah'];
                        $m_ds->hrg_jual = $v_conf['hrg_jual'];
                        $m_ds->hrg_beli = $v_conf['hrg_beli'];
                        $m_ds->kode_trans = $v_conf['kode_trans'];
                        $m_ds->jenis_barang = $v_conf['jenis_barang'];
                        $m_ds->jenis_trans = $v_conf['jenis_trans'];
                        $m_ds->jml_stok = $v_conf['jml_stok'];
                        $m_ds->save();

                        $idx++;

                        if ( $jml_data == $idx ) {
                            $this->hitungStok($id_adjout, $stok_id);
                        }
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStok($id_adjout, $stok_id)
    {
        // $m_adjout = new \Model\Storage\AdjoutVoadip_model();
        // $d_adjout = $m_adjout->where('id', $id_adjout)->first()->toArray();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                adjout.kode,
                adjout.tanggal,
                kirim.no_order as kode_trans,
                adjout.kode_trans as no_sj,
                adjout.tgl_trans as tgl_sj,
                adjout.kode_gudang,
                adjout.kode_barang,
                adjout.jumlah
            from adjout_voadip adjout
            right join
                kirim_voadip kirim
                on
                    adjout.kode_trans = kirim.no_sj
            where
                adjout.id = ".$id_adjout."
        ";
        $d_adjout = $m_conf->hydrateRaw( $sql );

        if ( $d_adjout->count() > 0 ) {
            $d_adjout = $d_adjout->toArray()[0];

            // KELUAR STOK GUDANG
            $nilai_beli = 0;
            $nilai_jual = 0;
            $jml_keluar = $d_adjout['jumlah'];
            while ($jml_keluar > 0) {
                $m_dstok = new \Model\Storage\DetStok_model();
                $sql = "
                    select top 1 * from det_stok ds 
                    where
                        ds.id_header = ".$stok_id." and 
                        ds.kode_gudang = ".$d_adjout['kode_gudang']." and 
                        ds.kode_barang = '".$d_adjout['kode_barang']."' and 
                        ds.kode_trans = '".$d_adjout['kode_trans']."' and 
                        ds.tgl_trans = '".$d_adjout['tgl_sj']."' and 
                        ds.jml_stok > 0
                    order by
                        ds.jenis_trans desc,
                        ds.tgl_trans asc,
                        ds.kode_trans asc,
                        ds.id asc
                ";

                $d_dstok = $m_dstok->hydrateRaw($sql);

                if ( $d_dstok->count() > 0 ) {
                    $d_dstok = $d_dstok->toArray()[0];

                    $harga_jual = $d_dstok['hrg_jual'];
                    $harga_beli = $d_dstok['hrg_beli'];

                    $jml_stok = $d_dstok['jml_stok'];
                    if ( $jml_stok > $jml_keluar ) {
                        $jml_stok = $jml_stok - $jml_keluar;
                        $nilai_beli += $jml_keluar*$harga_beli;
                        $nilai_jual += $jml_keluar*$harga_jual;

                        $m_dstokt = new \Model\Storage\DetStokTrans_model();
                        $m_dstokt->id_header = $d_dstok['id'];
                        $m_dstokt->kode_trans = $d_adjout['kode'];
                        $m_dstokt->jumlah = $jml_keluar;
                        $m_dstokt->kode_barang = $d_adjout['kode_barang'];
                        $m_dstokt->save();

                        $jml_keluar = 0;
                    } else {
                        $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
                        $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
                        $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

                        $m_dstokt = new \Model\Storage\DetStokTrans_model();
                        $m_dstokt->id_header = $d_dstok['id'];
                        $m_dstokt->kode_trans = $d_adjout['kode'];
                        $m_dstokt->jumlah = $d_dstok['jml_stok'];
                        $m_dstokt->kode_barang = $d_adjout['kode_barang'];
                        $m_dstokt->save();

                        $jml_stok = 0;
                    }
                    $m_dstok->where('id', $d_dstok['id'])->update(
                        array(
                            'jml_stok' => $jml_stok
                        )
                    );
                } else {
                    $jml_keluar = 0;
                }
            }
        }
    }
}