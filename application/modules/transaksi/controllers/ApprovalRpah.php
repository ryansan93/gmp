<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ApprovalRpah extends Public_Controller {

    private $pathView = 'transaksi/approval_rpah/';
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
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/approval_rpah/js/approval-rpah.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/approval_rpah/css/approval-rpah.css",
            ));

            $data = $this->includes;

            $isMobile = false;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;

            // $mitra = $this->get_mitra();
            // $pelanggan = $this->get_pelanggan();
            // $unit = $this->get_unit();
            // $content['add_form'] = $this->add_form($mitra, $pelanggan);
            // $content['riwayat'] = $this->riwayat($unit);

            $content = null;

            // Load Indexx
            $data['title_menu'] = 'Approval RPAH';
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getListRpahNotApprove() {
        $data = $this->getDataRpahNotApprove();

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getDataRpahNotApprove() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                r.id,
                w.kode as kode_unit,
                REPLACE(REPLACE(r.unit, 'KAB ', ''), 'KOTA ', '') as nama_unit,
                r.tgl_panen,
                r.bottom_price as harga,
                REPLACE(REPLACE(lt.deskripsi, 'di-submit oleh ', ''), 'di-update oleh ', '') as deskripsi,
                lt.waktu
            from rpah r
            right join
                wilayah w
                on
                    w.id = r.id_unit
            left join
                (
                    select lt1.* from log_tables lt1
                    right join
                        (select max(id) as id, tbl_id, tbl_name from log_tables where tbl_name = 'rpah' group by tbl_id, tbl_name) lt2
                        on
                            lt1.id = lt2.id
                ) lt
                on
                    r.id = lt.tbl_id
            where
                r.g_status = 1 and
                w.kode is not null
            order by
                REPLACE(REPLACE(r.unit, 'KAB ', ''), 'KOTA ', '') asc
        ";
        $d_rpah = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_rpah->count() > 0 ) {
            $d_rpah = $d_rpah->toArray();

            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $key = $v_rpah['kode_unit'].'_'.str_replace('-', '', $v_rpah['tgl_panen']);

                $data[ $key ] = array(
                    'id' => $v_rpah['id'],
                    'tgl_panen' => $v_rpah['tgl_panen'],
                    'nama_unit' => $v_rpah['nama_unit'],
                    'harga' => $v_rpah['harga'],
                    'deskripsi' => $v_rpah['deskripsi'],
                    'waktu' => $v_rpah['waktu']
                );

                $m_conf = new \Model\Storage\Conf();
                $now = $m_conf->getDate();

                $sql = "
                    select 
                        rdim.nama as nama_mitra,
                        kp.populasi as ekor_konfir,
                        kp.total as kg_konfir,
                        dr.id,
                        dr.id_rpah,
                        dr.id_konfir,
                        dr.noreg,
                        dr.no_pelanggan,
                        dr.pelanggan,
                        dr.outstanding,
                        dr.tonase,
                        dr.ekor,
                        dr.bb,
                        dr.harga,
                        dr.no_do,
                        dr.no_sj,
                        hutang.jumlah_do,
                        hutang.tgl_terkecil,
                        hutang.total_do,
                        hutang.total_bayar
                    from det_rpah dr                    
                    right join
                        konfir kp
                        on
                            kp.id = dr.id_konfir
                    right join
                        (
                            select
                                rs.noreg,
                                m.nama
                            from rdim_submit rs
                            right join
                                (
                                    select mm1.* from mitra_mapping mm1
                                    right join
                                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                        on
                                            mm1.id = mm2.id
                                ) mm
                                on
                                    rs.nim = mm.nim
                            right join
                                mitra m
                                on
                                    mm.mitra = m.id
                        ) rdim
                        on
                            rdim.noreg = dr.noreg
                    left join
                        (
                            select
                                d_rs.no_pelanggan,
                                min(d_rs.tgl_panen) as tgl_terkecil,
                                count(d_rs.tgl_panen) as jumlah_do,
                                sum(d_rs.total_do) as total_do,
                                sum(d_rs.total_bayar) as total_bayar
                            from
                            (
                                select 
                                    drs.no_pelanggan, 
                                    rs.tgl_panen,
                                    sum(drs.tonase * drs.harga) as total_do,
                                    sum(dpp.jumlah_bayar) as total_bayar
                                from det_real_sj drs
                                right join	
                                    (
                                        select rs1.* from real_sj rs1
                                        right join
                                            (select max(id) as id, noreg, tgl_panen from real_sj group by noreg, tgl_panen) rs2
                                            on
                                                rs1.id = rs2.id
                                    ) rs
                                    on
                                        drs.id_header = rs.id
                                left join
                                    (
                                        select dpp1.* from det_pembayaran_pelanggan dpp1
                                        right join
                                            (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                                            on
                                                dpp1.id = dpp2.id
                                    ) dpp
                                    on
                                        dpp.id_do =  drs.id
                                left join
                                    (
                                        select no_pelanggan, max(tgl_min) as tgl_min from (
                                            select no_pelanggan, max(tgl_mulai_bayar) as tgl_min from saldo_pelanggan sp group by no_pelanggan
                                            
                                            union all
                                            
                                            select drs.no_pelanggan, max(rs.tgl_panen) as tgl_min from det_real_sj drs 
                                            right join
                                                (
                                                    select dpp1.* from det_pembayaran_pelanggan dpp1
                                                    right join
                                                        (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                                                        on
                                                            dpp1.id = dpp2.id
                                                    where
                                                        dpp1.status = 'LUNAS'
                                                ) dpp
                                                on
                                                    drs.id = dpp.id_do
                                            right join
                                                real_sj rs 
                                                on
                                                    drs.id_header = rs.id
                                            group by
                                                drs.no_pelanggan
                                        ) data
                                        where
                                            no_pelanggan is not null
                                        group by
                                            no_pelanggan
                                    ) tgl_min_bayar
                                    on
                                        drs.no_pelanggan = tgl_min_bayar.no_pelanggan
                                where
                                    drs.no_pelanggan is not null and
                                    drs.harga > 0 and
                                    (dpp.status = 'BELUM' or dpp.id is null) and
                                    rs.tgl_panen >= tgl_min_bayar.tgl_min
                                group by
                                    drs.no_pelanggan, 
                                    rs.tgl_panen
                            ) d_rs
                            group by
                                d_rs.no_pelanggan
                        ) hutang
                        on
                            dr.no_pelanggan = hutang.no_pelanggan
                    where
                        dr.id_rpah = ".$v_rpah['id']."
                    group by
                        rdim.nama,
                        kp.populasi,
                        kp.total,
                        dr.id,
                        dr.id_rpah,
                        dr.id_konfir,
                        dr.noreg,
                        dr.no_pelanggan,
                        dr.pelanggan,
                        dr.outstanding,
                        dr.tonase,
                        dr.ekor,
                        dr.bb,
                        dr.harga,
                        dr.no_do,
                        dr.no_sj,
                        hutang.jumlah_do,
                        hutang.tgl_terkecil,
                        hutang.total_do,
                        hutang.total_bayar
                    order by
                        rdim.nama asc,
                        dr.pelanggan asc
                ";
                $d_drpah = $m_conf->hydrateRaw( $sql );

                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $key_mitra = $v_drpah['noreg'].'_'.$v_drpah['ekor_konfir'].'_'.$v_drpah['kg_konfir'];
                        $key_detail = $v_drpah['id'];

                        if ( !isset($data[ $key ]['detail'][ $key_mitra ]) ) {
                            $data[ $key ]['detail'][ $key_mitra ] = array(
                                'nama_mitra' => $v_drpah['nama_mitra'],
                                'ekor_konfir' => $v_drpah['ekor_konfir'],
                                'kg_konfir' => $v_drpah['kg_konfir']
                            );
                        }

                        $jml_do_hutang = ($v_drpah['jumlah_do'] > 0) ? $v_drpah['jumlah_do'] : 0;
                        $tgl_terkecil_hutang = !empty($v_drpah['tgl_terkecil']) ? $v_drpah['tgl_terkecil'] : $v_rpah['tgl_panen'];

                        $bg_color = null;
                        $selisih_hari = (selisihTanggal($tgl_terkecil_hutang, $now['tanggal'])) + 1;
                        $jumlah_do = $jml_do_hutang;

                        if ( $selisih_hari > 3 || $jumlah_do > 3 ) {
                            $bg_color = 'red';
                        }

                        $data[ $key ]['detail'][ $key_mitra ]['detail'][ $key_detail ] = array(
                            'id' => $v_drpah['id'],
                            'id_rpah' => $v_drpah['id_rpah'],
                            'id_konfir' => $v_drpah['id_konfir'],
                            'no_pelanggan' => $v_drpah['no_pelanggan'],
                            'pelanggan' => $v_drpah['pelanggan'],
                            'outstanding' => $v_drpah['outstanding'],
                            'tonase' => $v_drpah['tonase'],
                            'ekor' => $v_drpah['ekor'],
                            'bb' => $v_drpah['bb'],
                            'harga' => $v_drpah['harga'],
                            'no_do' => $v_drpah['no_do'],
                            'no_sj' => $v_drpah['no_sj'],
                            'jml_do_hutang' => $jml_do_hutang,
                            'tgl_terkecil_hutang' => $tgl_terkecil_hutang,
                            'selisih_hari' => ($jml_do_hutang > 0) ? $selisih_hari : 0,
                            'bg_color' => $bg_color,
                            'total_do' => $v_drpah['total_do'],
                            'total_bayar' => $v_drpah['total_bayar']
                        );
                    }
                }
            }
        }

        return $data;
    }

    public function approve() {
        $params = $this->input->post('params');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $m_rpah->where('id', $params['id'])->update(
                array(
                    'g_status' => getStatus('approve')
                )
            );

            $d_rpah = $m_rpah->where('id', $params['id'])->first();

            $deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di approve.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function reject() {
        $params = $this->input->post('params');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $m_rpah->where('id', $params['id'])->update(
                array(
                    'g_status' => getStatus('reject')
                )
            );

            $d_rpah = $m_rpah->where('id', $params['id'])->first();

            $deskripsi_log = 'di-reject oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di reject.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}