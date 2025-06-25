<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RekapLebihBayar extends Public_Controller {

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
                "assets/select2/js/select2.min.js",
                "assets/report/rekap_lebih_bayar/js/rekap-lebih-bayar.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/rekap_lebih_bayar/css/rekap-lebih-bayar.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['pelanggan'] = $this->get_pelanggan();
            $content['title_menu'] = 'Laporan Lebih Bayar Per Pelanggan';

            // Load Indexx
            $data['view'] = $this->load->view('report/rekap_lebih_bayar/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_pelanggan()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                p.*,
                REPLACE(REPLACE(l_kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota
            from pelanggan p
            right join
                (
                    select max(id) as id, nomor from pelanggan group by nomor
                ) p1
                on
                    p.id = p1.id
            right join
                lokasi l_kec
                on
                    p.alamat_kecamatan = l_kec.id
            right join
                lokasi l_kab_kota
                on
                    l_kec.induk = l_kab_kota.id
            where
                p.tipe = 'pelanggan'
        ";
        $d_plg = $m_conf->hydrateRaw( $sql );

        if ( $d_plg->count() > 0 ) {
            $d_plg = $d_plg->toArray();

            foreach ($d_plg as $key => $value) {
                $key = $value['kab_kota'].'|'.$value['nama'].'|'.$value['nomor'];
                $data[$key] = $value;
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        return $data;
    }

    public function get_lists()
    {
        $params = $this->input->post('params');
        try {
            $data = null;

            $sql_pelanggan = null;
            if ( $params['no_pelanggan'] != 'all' ) {
                $sql_pelanggan = "and pp.no_pelanggan = '".$params['no_pelanggan']."'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    pp.*,
                    p.nama as nama_pelanggan,
                    p.kab_kota,
                    drs.no_do,
                    drs.no_sj,
                    dpp.total_bayar as tagihan,
                    rs.tgl_panen,
                    DATEDIFF(day, rs.tgl_panen, pp.tgl_bayar) as lama_bayar,
                    prs.perusahaan as nama_perusahaan
                from pembayaran_pelanggan pp
                right join
                    det_pembayaran_pelanggan dpp
                    on
                        pp.id = dpp.id_header
                right join
                    det_real_sj drs
                    on
                        dpp.id_do = drs.id
                right join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                right join
                    (
                        select
                            p1.*,
                            REPLACE(REPLACE(l_kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota
                        from pelanggan p1
                        right join
                            (
                                select max(id) as id, nomor from pelanggan group by nomor
                            ) p2
                            on
                                p1.id = p2.id
                        right join
                            lokasi l_kec
                            on
                                p1.alamat_kecamatan = l_kec.id
                        right join
                            lokasi l_kab_kota
                            on
                                l_kec.induk = l_kab_kota.id
                        where
                            p1.tipe = 'pelanggan'
                    ) p
                    on
                        p.nomor = pp.no_pelanggan
                right join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        pp.perusahaan = prs.kode
                where
                    pp.tgl_bayar between '".$params['start_date']."' and '".$params['end_date']."' and
                    pp.total_uang > pp.total_bayar
                    ".$sql_pelanggan."
            ";
            $d_lb = $m_conf->hydrateRaw( $sql );

            if ( $d_lb->count() > 0 ) {
                $d_lb = $d_lb->toArray();

                foreach ($d_lb as $k_lb => $v_lb) {
                    $key = strtoupper($v_lb['nama_pelanggan']).'|'.$v_lb['no_pelanggan'].'|'.$v_lb['tgl_bayar'].'|'.$v_lb['nama_perusahaan'];

                    if ( !isset($data[ $key ]) ) {
                        $data[ $key ] = array(
                            'nama' => strtoupper($v_lb['nama_pelanggan'].' ('.$v_lb['kab_kota'].')'),
                            'nama_perusahaan' => strtoupper($v_lb['nama_perusahaan']),
                            'tgl_bayar' => $v_lb['tgl_bayar'],
                            'total_tagihan' => $v_lb['total_bayar'],
                            'total_bayar' => $v_lb['total_uang'],
                            'lebih_bayar' => $v_lb['total_uang'] - $v_lb['total_bayar']
                        );

                        $data[ $key ]['detail'][ $v_lb['no_do'] ] = array(
                            'tgl_do' => $v_lb['tgl_panen'],
                            'tgl_bayar' => $v_lb['tgl_bayar'],
                            'no_do' => $v_lb['no_do'],
                            'no_sj' => $v_lb['no_sj'],
                            'tagihan' => $v_lb['tagihan'],
                            'lama_bayar' => $v_lb['lama_bayar']
                        );
                    } else {
                        if ( !isset($data[ $key ]['detail'][ $v_lb['no_do'] ]) ) {
                            $data[ $key ]['detail'][ $v_lb['no_do'] ] = array(
                                'tgl_do' => $v_lb['tgl_panen'],
                                'tgl_bayar' => $v_lb['tgl_bayar'],
                                'no_do' => $v_lb['no_do'],
                                'no_sj' => $v_lb['no_sj'],
                                'tagihan' => $v_lb['tagihan'],
                                'lama_bayar' => $v_lb['lama_bayar']
                            );
                        } else {
                            $data[ $key ]['detail'][ $v_lb['no_do'] ]['tagihan'] += $v_lb['tagihan'];
                        }
                    }

                    ksort($data[ $key ]['detail']);
                }

                ksort($data);
            }

            // if ( $params['no_pelanggan'] != 'all' ) {
            //     $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            //     $d_pp = $m_pp->where('no_pelanggan', $params['no_pelanggan'])->where('lebih_kurang', '>', 0)->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])->get();

            //     if ( $d_pp->count() > 0 ) {
            //         $d_pp = $d_pp->toArray();
            //         foreach ($d_pp as $k_pp => $v_pp) {
            //             $m_plg = new \Model\Storage\Pelanggan_model();
            //             $d_plg = $m_plg->where('nomor', $v_pp['no_pelanggan'])->with(['kecamatan'])->orderBy('version', 'desc')->first();

            //             $nama_plg = $d_plg->nama.' ('.strtoupper(str_replace('Kab ', '', str_replace('Kota ', '', $d_plg->kecamatan->dKota->nama))).')';

            //             $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
            //             $d_dpp = $m_dpp->select('id_do')->where('id_header', $v_pp['id'])->where('status', 'LUNAS')->get();

            //             $d_dpp_lebih_bayar = null;
            //             if ( $d_dpp->count() > 0 ) {
            //                 $d_dpp = $d_dpp->toArray();
            //                 $d_dpp_lebih_bayar = $m_dpp->whereIn('id_do', $d_dpp)->get();
            //                 if ( $d_dpp_lebih_bayar->count() > 0 ) {
            //                     $d_dpp_lebih_bayar = $d_dpp_lebih_bayar->toArray();
            //                 }
            //             }

            //             if ( !empty($d_dpp_lebih_bayar) ) {
            //                 foreach ($d_dpp_lebih_bayar as $k_ddbl => $v_ddbl) {
            //                     $m_drs = new \Model\Storage\DetRealSJ_model();
            //                     $d_drs_do = $m_drs->where('id', $v_ddbl['id_do'])->first();

            //                     $m_rs = new \Model\Storage\RealSJ_model();
            //                     $d_rs = $m_rs->where('id', $d_drs_do->id_header)->first();

            //                     $no_do = $d_drs_do->no_do;

            //                     $total_bayar = ($v_ddbl['status'] == 'BELUM') ? $v_ddbl['jumlah_bayar'] : $v_pp['jml_transfer'];
            //                     if ( !isset($data[ $params['no_pelanggan'] ]['detail'][ $no_do ]) ) {
            //                         $detail[ $no_do ] = array(
            //                             'tgl_do' => $d_rs->tgl_panen,
            //                             'tgl_bayar' => $v_pp['tgl_bayar'],
            //                             'no_do' => $no_do,
            //                             'no_sj' => $d_drs_do->no_sj,
            //                             'tagihan' => $v_ddbl['total_bayar'],
            //                             'lama_bayar' => selisihTanggal($d_rs->tgl_panen, $v_pp['tgl_bayar']),
            //                             'lebih_bayar' => $total_bayar - $v_ddbl['total_bayar']
            //                         );
            //                         $data[ $params['no_pelanggan'] ] = array(
            //                             'nama' => $nama_plg,
            //                             'detail' => $detail,
            //                             'total_tagihan' => $v_ddbl['total_bayar'],
            //                             'total_bayar' => 0,
            //                             'lebih_bayar' => 0
            //                         );
            //                     } else {
            //                         $detail[ $no_do ]['tagihan'] += $v_ddbl['total_bayar'];

            //                         $data[ $params['no_pelanggan'] ]['total_tagihan'] += $v_ddbl['total_bayar'];
            //                     }

            //                     $data[ $params['no_pelanggan'] ]['total_bayar'] = $total_bayar;
            //                     $data[ $params['no_pelanggan'] ]['lebih_bayar'] = $data[ $params['no_pelanggan'] ]['total_bayar'] - $data[ $params['no_pelanggan'] ]['total_tagihan'];
            //                 }
            //             }
            //         }
            //     }
            // } else {
            //     $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            //     $d_pp = $m_pp->where('lebih_kurang', '>', 0)->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])->get();

            //     if ( $d_pp->count() > 0 ) {
            //         $d_pp = $d_pp->toArray();
            //         foreach ($d_pp as $k_pp => $v_pp) {
            //             $no_pelanggan = $v_pp['no_pelanggan'];

            //             $detail = null;

            //             $m_plg = new \Model\Storage\Pelanggan_model();
            //             $d_plg = $m_plg->where('nomor', $no_pelanggan)->with(['kecamatan'])->orderBy('version', 'desc')->first();

            //             $nama_plg = $d_plg->nama.' ('.strtoupper(str_replace('Kab ', '', str_replace('Kota ', '', $d_plg->kecamatan->dKota->nama))).')';

            //             $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
            //             $d_dpp = $m_dpp->select('id_do')->where('id_header', $v_pp['id'])->where('status', 'LUNAS')->get();

            //             $d_dpp_lebih_bayar = null;
            //             if ( $d_dpp->count() > 0 ) {
            //                 $d_dpp = $d_dpp->toArray();
            //                 $d_dpp_lebih_bayar = $m_dpp->whereIn('id_do', $d_dpp)->get();
            //                 if ( $d_dpp_lebih_bayar->count() > 0 ) {
            //                     $d_dpp_lebih_bayar = $d_dpp_lebih_bayar->toArray();
            //                 }
            //             }

            //             if ( !empty($d_dpp_lebih_bayar) ) {
            //                 foreach ($d_dpp_lebih_bayar as $k_ddbl => $v_ddbl) {
            //                     $m_drs = new \Model\Storage\DetRealSJ_model();
            //                     $d_drs_do = $m_drs->where('id', $v_ddbl['id_do'])->first();

            //                     $m_rs = new \Model\Storage\RealSJ_model();
            //                     $d_rs = $m_rs->where('id', $d_drs_do->id_header)->first();

            //                     $no_do = $d_drs_do->no_do;

            //                     $total_bayar = ($v_ddbl['status'] == 'BELUM') ? $v_ddbl['jumlah_bayar'] : $v_pp['jml_transfer'];
            //                     if ( !isset($data[ $no_pelanggan ]['detail'][ $no_do ]) ) {
            //                         $detail[ $no_do ] = array(
            //                             'tgl_do' => $d_rs->tgl_panen,
            //                             'tgl_bayar' => $v_pp['tgl_bayar'],
            //                             'no_do' => $no_do,
            //                             'no_sj' => $d_drs_do->no_sj,
            //                             'tagihan' => $v_ddbl['total_bayar'],
            //                             'lama_bayar' => selisihTanggal($d_rs->tgl_panen, $v_pp['tgl_bayar']),
            //                             'lebih_bayar' => $total_bayar - $v_ddbl['total_bayar']
            //                         );
            //                         $data[ $no_pelanggan ] = array(
            //                             'nama' => $nama_plg,
            //                             'detail' => $detail,
            //                             'total_tagihan' => $v_ddbl['total_bayar'],
            //                             'total_bayar' => 0,
            //                             'lebih_bayar' => 0
            //                         );
            //                     } else {
            //                         $detail[ $no_do ]['tagihan'] += $v_ddbl['total_bayar'];

            //                         $data[ $no_pelanggan ]['total_tagihan'] += $v_ddbl['total_bayar'];
            //                     }
                                
            //                     $data[ $no_pelanggan ]['total_bayar'] = $total_bayar;
            //                     $data[ $no_pelanggan ]['lebih_bayar'] = $data[ $no_pelanggan ]['total_bayar'] - $data[ $no_pelanggan ]['total_tagihan'];
            //                 }
            //             }
            //         }
            //     }
            // }

            $content['data'] = $data;
            $list = $this->load->view('report/rekap_lebih_bayar/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['list'] = $list;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}