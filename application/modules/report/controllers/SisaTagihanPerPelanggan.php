<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SisaTagihanPerPelanggan extends Public_Controller {

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
                "assets/report/sisa_tagihan_per_pelanggan/js/sisa-tagihan-per-pelanggan.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/sisa_tagihan_per_pelanggan/css/sisa-tagihan-per-pelanggan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['pelanggan'] = $this->get_pelanggan();
            $content['unit'] = $this->get_unit();
            $content['perusahaan'] = $this->get_perusahaan();
            $content['title_menu'] = 'Laporan Sisa Tagihan Per Pelanggan';

            // Load Indexx
            $data['view'] = $this->load->view('report/sisa_tagihan_per_pelanggan/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_pelanggan()
    {
        $m_plg = new \Model\Storage\Pelanggan_model();

        $sql = "
            select 
                p.*,
                REPLACE(REPLACE(l_kab.nama, 'Kota ', ''), 'Kab ', '') as nama_unit
            from pelanggan p
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p1
                on
                    p1.id = p.id
            right join
                (select * from lokasi l where jenis = 'KC') l_kec
                on
                    l_kec.id = p.alamat_kecamatan
            right join
                (select * from lokasi l where (jenis = 'KB' or jenis = 'KT')) l_kab
                on
                    l_kab.id = l_kec.induk
            where
                p.mstatus = 1
            order by
                p.nama asc
        ";

        $d_plg = $m_plg->hydrateRaw( $sql );

        $data = null;
        if ( $d_plg->count() > 0 ) {
            $data = $d_plg->toArray();
        }

        return $data;
    }

    public function get_unit()
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

    public function get_perusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function get_lists_old()
    {
        $params = $this->input->post('params');

        try {
            $_pelanggan = $params['pelanggan'];

            $kode_plg_all = false;
            foreach ($_pelanggan as $k_plg => $v_plg) {
                if ( $v_plg == 'all' ) {
                    $kode_plg_all = true;
                }
            }

            $pelanggan = null;
            if ( $kode_plg_all ) {
                $m_pelanggan = new \Model\Storage\Pelanggan_model();
                $d_pelanggan = $m_pelanggan->select('nomor')->where('tipe', 'pelanggan')->where('mstatus', 1)->groupBy('nomor')->get()->toArray();

                foreach ($d_pelanggan as $k_plg => $v_plg) {
                    $pelanggan[] = trim($v_plg['nomor']);
                }
            } else {
                $pelanggan = $_pelanggan;
            }

            $id_unit = array();
            if ( !empty( $params['kode_unit'] ) ) {
                foreach ($params['kode_unit'] as $k_ku => $v_ku) {
                    if ( stristr($v_ku, 'all') !== FALSE ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

                        foreach ($d_wil as $k_wil => $v_wil) {
                            $id_unit[] = $v_wil['id'];
                        }

                        break;
                    } else {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('kode', $v_ku)->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

                        foreach ($d_wil as $k_wil => $v_wil) {
                            $id_unit[] = $v_wil['id'];
                        }
                    }
                }
            }

            $data = null;
            foreach ($pelanggan as $k_plg => $v_plg) {
                $no_pelanggan = $v_plg;

                $m_pelanggan = new \Model\Storage\Pelanggan_model();
                $d_pelanggan = $m_pelanggan->where('nomor', $no_pelanggan)->where('tipe', 'pelanggan')->orderBy('id', 'desc')->first();

                $nama_pelanggan = $d_pelanggan->nama;

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $d_sp = $m_sp->where('no_pelanggan', $no_pelanggan)->orderBy('id', 'asc')->first();

                $tgl_mulai_bayar = null;
                if ( $d_sp ) {
                    $tgl_mulai_bayar = $d_sp->tgl_mulai_bayar;
                }

                $m_rs = new \Model\Storage\RealSJ_model();
                $data_rs = null;
                if ( !empty($tgl_mulai_bayar) ) {
                    $sql = "
                        select max(id) as id, id_unit, tgl_panen, noreg 
                        from real_sj 
                        where
                            tgl_panen is not null and
                            tgl_panen >= '".$tgl_mulai_bayar."' and
                            id_unit in (".implode(', ', $id_unit).")
                        group by id_unit, tgl_panen, noreg
                    ";

                    $d_rs = $m_rs->hydrateRaw($sql);

                    if ( $d_rs->count() > 0 ) {
                        $data_rs = $d_rs->toArray();
                    }
                }

                $total_tonase = 0;
                $total_tagihan = 0;
                $total_sisa_tagihan = 0;

                if ( !empty($data_rs) > 0 ) {
                    foreach ($data_rs as $k_rs => $v_rs) {
                        $m_drs = new \Model\Storage\DetRealSJ_model();
                        $d_drs = $m_drs->where('no_pelanggan', $no_pelanggan)->where('id_header', $v_rs['id'])->where('harga', '>', 0)->get();

                        if ( $d_drs->count() > 0 ) {
                            $d_drs = $d_drs->toArray();
                            foreach ($d_drs as $k_drs => $v_drs) {
                                $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                                $d_dpp = $m_dpp->where('id_do', $v_drs['id'])->orderBy('id', 'desc')->first();

                                $tampil = false;
                                if ( $d_dpp ) {
                                    if ( $d_dpp->status == 'BELUM' ) {
                                        $tampil = true;
                                    }
                                } else {
                                    $tampil = true;
                                }

                                if ( $tampil ) {
                                    $no_do = $v_drs['no_do'];
                                    $harga = $v_drs['harga'];
                                    $tonase = $v_drs['tonase'];
                                    $jumlah_bayar = !empty($d_dpp) ? $m_dpp->where('id_do', $v_drs['id'])->sum('jumlah_bayar') : 0;
                                    $selisihTanggal = selisihTanggal($v_rs['tgl_panen'], date('Y-m-d'));

                                    // $key = $selisihTanggal.'-'.$v_drs['id'];
                                    $key = $v_rs['tgl_panen'].' | '.$v_drs['id'];

                                    if ( !isset($data[$no_pelanggan]['do'][ $key ]) ) {
                                        $data[$no_pelanggan]['do'][ $key ] = array(
                                            'tgl_panen' => $v_rs['tgl_panen'],
                                            'no_do' => $no_do,
                                            'no_sj' => $v_drs['no_sj'],
                                            'harga' => $harga,
                                            'tonase' => $tonase,
                                            'total_tagihan' => $tonase * $harga,
                                            'total_bayar' => $jumlah_bayar,
                                            'sisa_tagihan' => ($tonase * $harga) - $jumlah_bayar,
                                            'lama_bayar' => $selisihTanggal,
                                        );
                                    } else {
                                        $data[$no_pelanggan]['do'][ $key ]['total_tagihan'] += $tonase * $harga;
                                        $data[$no_pelanggan]['do'][ $key ]['total_bayar'] += $jumlah_bayar;
                                        $data[$no_pelanggan]['do'][ $key ]['sisa_tagihan'] = $data[$no_pelanggan]['do'][ $key ]['total_tagihan'] - $data[$no_pelanggan]['do'][ $key ]['total_bayar'];
                                        $data[$no_pelanggan]['do'][ $key ]['lama_bayar'] = $selisihTanggal;
                                    }

                                    $total_tonase += $tonase;
                                    $total_tagihan += $tonase * $harga;
                                    $total_sisa_tagihan += ($tonase * $harga) - $jumlah_bayar;

                                    $data[$no_pelanggan]['nama'] = $nama_pelanggan;
                                    $data[$no_pelanggan]['total_tonase'] = $total_tonase;
                                    $data[$no_pelanggan]['total_tagihan'] = $total_tagihan;
                                    $data[$no_pelanggan]['total_sisa_tagihan'] = $total_sisa_tagihan;

                                    krsort($data[$no_pelanggan]['do'][ $key ]);
                                }
                            }
                        }
                    }
                }
            }

            $content['data'] = $data;
            $list = $this->load->view('report/sisa_tagihan_per_pelanggan/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['list'] = $list;
        } catch (Exception $e) {
            $this->result['message'] = 'Gagal: '.$e->getMessage();
        }

        display_json( $this->result );
    }

    public function list_data($params)
    {
        $_pelanggan = $params['pelanggan'];
        $perusahaan = $params['kode_perusahaan'];
        $minimal_lama_bayar = $params['minimal_lama_bayar'];
        $tgl_max_do = $params['tanggal'];

        $sql_pelanggan = "";
        if ( !in_array('all', $_pelanggan) ) {
            $sql_pelanggan = "data_rs.no_pelanggan in ('".implode("', '", $_pelanggan)."') and";
        }

        $kode_unit = array();
        if ( in_array('all', $params['kode_unit']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    w.kode 
                from wilayah w 
                where 
                    w.jenis = 'UN' 
                group by 
                    w.kode 
            ";
            $d_wil = $m_conf->hydrateRaw( $sql );

            if ( $d_wil->count() > 0 ) {
                $d_wil = $d_wil->toArray();

                foreach ($d_wil as $k_wil => $v_wil) {
                    $kode_unit[] = $v_wil['kode'];
                }
            }
        } else {
            $kode_unit = $params['kode_unit'];
        }

        $data_rsj = array();
        foreach ($kode_unit as $k_ku => $v_ku) {
            $sql = "
                select 
                    max(id) as id,
                    no_do,
                    no_pelanggan,
                    nama_pelanggan,
                    tonase,
                    ekor,
                    bb,
                    harga,
                    tgl_panen,
                    nama,
                    no_nota
                from (
                    select
                        drs.id as id, 
                        drs.no_do as no_do, 
                        drs.no_pelanggan as no_pelanggan, 
                        plg.nama as nama_pelanggan, 
                        drs.tonase as tonase, 
                        drs.ekor, 
                        drs.bb, 
                        drs.harga as harga,
                        rs.tgl_panen,
                        mitra.nama,
                        mitra.perusahaan,
                        drs.no_nota
                    from det_real_sj drs
                    right join
                        (
                            select 
                                max(id) as id, 
                                id_unit, 
                                tgl_panen, 
                                noreg 
                            from real_sj 
                            where 
                                tgl_panen is not null and
                                tgl_panen <= '".$tgl_max_do."'
                            group by 
                                id_unit, 
                                tgl_panen, 
                                noreg
                        ) as rs
                        on
                            drs.id_header = rs.id
                    left join
                        (
                            select plg1.* from pelanggan plg1
                            right join
                                (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) plg2
                                on
                                    plg1.id = plg2.id
                        ) plg
                        on
                            drs.no_pelanggan = plg.nomor
                    left join
                        (select nim, noreg from rdim_submit group by nim, noreg) rdim
                        on
                            rs.noreg = rdim.noreg
                    left join
                        (select max(mitra) as id_mitra, nim from mitra_mapping group by nim) mm
                        on
                            rdim.nim = mm.nim
                    left join
                        mitra mitra
                        on
                            mm.id_mitra = mitra.id
                    left join
                        (
                            select max(id) as id, no_pelanggan, tgl_mulai_bayar from saldo_pelanggan group by no_pelanggan, tgl_mulai_bayar
                        ) sp
                        on
                            drs.no_pelanggan = sp.no_pelanggan
                    where
                        not exists (
                            select 
                                dpp.* 
                            from det_pembayaran_pelanggan dpp 
                            right join
                                pembayaran_pelanggan pp
                                on
                                    dpp.id_header = pp.id
                            where 
                                dpp.id_do = drs.id and 
                                dpp.status = 'LUNAS' and
                                pp.tgl_bayar <= '".$tgl_max_do."'
                        ) and
                        rs.tgl_panen >= sp.tgl_mulai_bayar and
                        drs.no_do like '%".$v_ku."%'
                ) as data_rs
                where
                    ".$sql_pelanggan."
                    data_rs.tonase > 0 and
                    data_rs.harga > 0 and
                    data_rs.perusahaan in ('".implode("', '", $perusahaan)."')
                group by
                    id,
                    no_do,
                    no_pelanggan,
                    nama_pelanggan,
                    tonase,
                    ekor,
                    bb,
                    harga,
                    tgl_panen,
                    nama,
                    no_nota
            ";

            $m_drs = new \Model\Storage\DetRealSJ_model();
            $d_drs = $m_drs->hydrateRaw($sql);

            if ( $d_drs->count() > 0 ) {
                $d_drs = $d_drs->toArray();

                foreach ($d_drs as $k_drs => $v_drs) {
                    $data_rsj[] = $v_drs;
                }
            }
        }

        $data = null;

        $total_tonase = 0;
        $total_tagihan = 0;
        $total_sisa_tagihan = 0;

        if ( count($data_rsj) > 0 ) {
            foreach ($data_rsj as $k_drs => $v_drs) {
                $no_pelanggan = $v_drs['no_pelanggan'];
                $nama_pelanggan = $v_drs['nama_pelanggan'];

                // $d_dpp = null;
                // if (  $d_pp ) {
                //     $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                //     $d_dpp = $m_dpp->where('id_header', $d_pp->id)->where('id_do', $v_drs['id'])->orderBy('id', 'desc')->first();
                // }

                $no_nota = $v_drs['no_nota'];
                $no_do = $v_drs['no_do'];
                $harga = $v_drs['harga'];
                $tonase = $v_drs['tonase'];
                // $jumlah_bayar = !empty($d_dpp) ? $m_dpp->where('id_do', $v_drs['id'])->sum('jumlah_bayar') : 0;
                $jumlah_bayar = 0;
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select
                        dpp.id_do,
                        sum(dpp.jumlah_bayar) as jumlah_bayar
                    from det_pembayaran_pelanggan dpp
                    right join
                        pembayaran_pelanggan pp
                        on
                            dpp.id_header = pp.id
                    where
                        pp.tgl_bayar <= '".$tgl_max_do."' and
                        dpp.id_do = ".$v_drs['id']."
                    group by
                        dpp.id_do
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );
                if ( $d_conf->count() > 0 ) {
                    $jumlah_bayar = $d_conf->toArray()[0]['jumlah_bayar'];
                }

                $selisihTanggal = selisihTanggal($v_drs['tgl_panen'], $tgl_max_do);

                $key_header = $nama_pelanggan.' | '.$no_pelanggan;
                $key_selisih = $selisihTanggal;
                $key = str_replace('-', '', $v_drs['tgl_panen']).'-'.$v_drs['no_do'].'-'.$v_drs['id'];

                $sisa_tagihan = ($tonase * $harga) - $jumlah_bayar;
                if ( $sisa_tagihan > 0 ) {
                    if ( !isset($data[$key_header]['do'][ $key_selisih ]['list_do'][$key]) ) {
                        $data[$key_header]['do'][ $key_selisih ]['list_do'][$key] = array(
                            'nama' => $v_drs['nama'],
                            'tgl_panen' => $v_drs['tgl_panen'],
                            'no_do' => $no_do,
                            'harga' => $harga,
                            'tonase' => $tonase,
                            'total_tagihan' => $tonase * $harga,
                            'total_bayar' => $jumlah_bayar,
                            'sisa_tagihan' => $sisa_tagihan,
                            'lama_bayar' => $selisihTanggal,
                            'no_nota' => $no_nota
                        );
                    } else {
                        $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['total_tagihan'] += $tonase * $harga;
                        $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['total_bayar'] += $jumlah_bayar;
                        $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['sisa_tagihan'] = $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['total_tagihan'] - $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['total_bayar'];
                        $data[$key_header]['do'][ $key_selisih ]['list_do'][$key]['lama_bayar'] = $selisihTanggal;
                    }

                    $total_tonase = $tonase;
                    $total_tagihan = $tonase * $harga;
                    $total_sisa_tagihan = $sisa_tagihan;

                    if ( !isset($data[$key_header]['nama']) ) {
                        $m_pp = new \Model\Storage\PembayaranPelanggan_model();
                        $d_pp = $m_pp->where('no_pelanggan', $no_pelanggan)->where('tgl_bayar', '<=', $tgl_max_do)->orderBy('tgl_bayar', 'desc')->first();

                        $data[$key_header]['nama'] = $nama_pelanggan;
                        $data[$key_header]['total_tonase'] = $total_tonase;
                        $data[$key_header]['total_tagihan'] = $total_tagihan;
                        $data[$key_header]['total_sisa_tagihan'] = $total_sisa_tagihan;
                        $data[$key_header]['total_pembayaran_terakhir'] = !empty($d_pp) ? $d_pp->jml_transfer : 0;
                        $data[$key_header]['tgl_pembayaran_terakhir'] = !empty($d_pp) ? strtoupper(tglIndonesia($d_pp->tgl_bayar, '-', ' ')) : '-';
                        $data[$key_header]['max_umur_hutang'] = $selisihTanggal;
                    } else {
                        $data[$key_header]['total_tonase'] += $tonase;
                        $data[$key_header]['total_tagihan'] += $total_tagihan;
                        $data[$key_header]['total_sisa_tagihan'] += $total_sisa_tagihan;
                        $data[$key_header]['max_umur_hutang'] = ($data[$key_header]['max_umur_hutang'] < $selisihTanggal) ? $selisihTanggal : $data[$key_header]['max_umur_hutang'];
                    }

                    ksort($data[$key_header]['do'][ $key_selisih ]['list_do']);
                    krsort($data[$key_header]['do']);
                    ksort($data);
                }
            }

            if ( !empty($data) ) {
                foreach ($data as $key => $value) {
                    if ( $value['max_umur_hutang'] < $minimal_lama_bayar ) {
                        unset($data[ $key ]);
                    }
                }
            }

            // cetak_r( $data, 1 );
        }

        return $data;
    }

    public function get_lists()
    {
        $params = $this->input->post('params');

        try {
            $data = $this->list_data( $params );

            $content['data'] = $data;

            // return $content;
            $list = $this->load->view('report/sisa_tagihan_per_pelanggan/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['list'] = $list;
        } catch (Exception $e) {
            $this->result['message'] = 'Gagal: '.$e->getMessage();
        }

        display_json( $this->result );
    }

    public function cekExportExcel()
    {
        $params = $this->input->post('params');

        try {
            $this->result['content'] = exEncrypt(json_encode($params));
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params)
    {
        $data = $this->list_data( json_decode(exDecrypt($params), 1) );

        $content['data'] = $data;
        $res_view_html = $this->load->view('report/sisa_tagihan_per_pelanggan/export_excel', $content, true);

        $filename = 'export-sisa-tagihan-pelanggan.xls';

        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function tes()
    {
        cetak_r( $this->get_lists_new() );
    }
}