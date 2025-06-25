<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KasKecil extends Public_Controller {

    private $pathView = 'report/kas_kecil/';
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
    public function index($params = null)
    {
        $akses = $this->akses;
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/kas_kecil/js/kas-kecil.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kas_kecil/css/kas-kecil.css",
            ));

            $data = $this->includes;

            $kode_unit = null;
            $periode = null;

            if ( !empty($params) ) {
                $params = json_decode(exDecrypt($params), true);

                $kode_unit = $params['kode_unit'];
                $periode = $params['periode'];
            }

            $content['akses'] = $akses;
            $content['kode_unit'] = $kode_unit;
            $content['periode'] = $periode;
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laporan Kas Kecil';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                UPPER(REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '')) as nama,
                UPPER(w1.kode) as kode
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah where jenis = 'UN' and kode is not null group by kode) w2
                on
                    w1.id = w2.id
            order by
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') asc,
                w1.kode asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                UPPER(prs1.kode) as kode,
                UPPER(prs1.perusahaan) as nama_perusahaan
            from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
            order by
                prs1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getCoa($unit)
    {
        $_unit = null;
        if ( stristr($unit, 'all') === false ) {
            $_unit = $unit;
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select coa from coa where nama_coa like '%kas kecil ".$_unit."%'
        ";
        $d_coa = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_coa->count() > 0 ) {
            $data = $d_coa->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $akses = $this->akses;

            $unit = $params['unit'];
            $perusahaan = $params['perusahaan'];
            $periode = $params['periode'];

            $startDate = substr($periode, 0, 7).'-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $status_btn_tutup_bulan = 1;
            $status_btn_submit = 1;
            $status_btn_ack = 1;
            $g_status = 0;

            if ( $akses['a_ack'] == 1 ) {
                $status_btn_submit = 0;
            } else {
                if ( $akses['a_submit'] == 1 ) {
                    $status_btn_ack = 0;
                }
            }

            $sql_saldo_unit = "and sk.unit = '".$unit."'";
            $sql_saldo_perusahaan = "and sk.perusahaan = '".$perusahaan."'";
            $sql_group_by_saldo_unit = ", sk.unit";
            $sql_group_by_saldo_perusahaan = ", sk.perusahaan";
            $sql_unit = "and j.unit = '".$unit."'";
            $sql_perusahaan = "and dj.perusahaan = '".$perusahaan."'";

            $_unit = null;
            if ( stristr($unit, 'all') !== false ) {
                $_unit = 'all';
                $sql_saldo_unit = null;
                $sql_group_by_saldo_unit = null;
                $sql_unit = null;
            } else {
                if ( stristr($unit, 'pusat') !== false ) {
                    $_unit = 'pusat';

                    $sql_unit = "and dj.unit = '".$unit."'";
                }

                if ( stristr($unit, 'pusat') === false ) {
                    $_unit = 'unit';
                }
            }

            $_no_coa = $this->getCoa( $_unit );
            $no_coa = null;
            foreach ($_no_coa as $key => $value) {
                $no_coa[] = $value['coa'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    sk.periode as tanggal,
                    '' as no_akun_transaksi,
                    'SALDO AWAL' as nama_akun_transaksi,
                    '' as pic,
                    'SALDO AWAL '+CONVERT(varchar(10), sk.periode, 103) as keterangan,
                    sum(sk.saldo_awal) as debit,
                    0 as kredit,
                    min(sk.saldo_akhir) as saldo_akhir,
                    sk.g_status
                from saldo_kas sk
                where
                    sk.periode between '".$startDate."' and '".$endDate."'
                    ".$sql_saldo_unit."
                    ".$sql_saldo_perusahaan."
                group by
                    sk.periode,
                    sk.g_status
                    ".$sql_group_by_saldo_unit."
                    ".$sql_group_by_saldo_perusahaan."
            ";
            $d_sk = $m_conf->hydrateRaw( $sql );

            $data_saldo = null;
            $id = null;
            if ( $d_sk->count() > 0 ) {
                $data_saldo = $d_sk->toArray()[0];

                $g_status = $data_saldo['g_status'];

                if ( $data_saldo['saldo_akhir'] > 0 ) {
                    if ( $akses['a_ack'] == 1 ) {
                        if ( $data_saldo['g_status'] == getStatus('ack') ) {
                            $status_btn_tutup_bulan = 0;
                            $status_btn_ack = 0;
                        }
                    } else {
                        $status_btn_tutup_bulan = 0;
                        if ( $data_saldo['g_status'] == getStatus('submit') ) {
                            $status_btn_submit = 0;
                        }
                    }
                } else {
                    if ( $akses['a_ack'] == 1 ) {
                        if ( $data_saldo['g_status'] == getStatus('ack') ) {
                            $status_btn_tutup_bulan = 0;
                            $status_btn_ack = 0;
                        }
                    } else {
                        if ( $data_saldo['g_status'] == getStatus('submit') ) {
                            $status_btn_tutup_bulan = 0;
                            $status_btn_submit = 0;
                        }
                    }
                }
            } else {
                $data_saldo = array(
                    'tanggal' => $startDate,
                    'no_akun_transaksi' => '',
                    'nama_akun_transaksi' => 'SALDO AWAL',
                    'pic' => '',
                    'keterangan' => 'SALDO AWAL '.date('d/m/Y', strtotime($startDate)),
                    'debit' => 0,
                    'kredit' => 0,
                    'status' => 0
                );
            }

            $data = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select * from
                (
                    select 
                        djt.id,
                        dj.tanggal,
                        djt.sumber_coa as no_akun_transaksi,
                        djt.nama as nama_akun_transaksi,
                        dj.id as det_jurnal_id,
                        dj.pic,
                        dj.keterangan,
                        dj.nominal as debit,
                        0 as kredit
                    from det_jurnal dj
                    right join
                        det_jurnal_trans djt
                        on
                            dj.det_jurnal_trans_id = djt.id
                    right join
                        jurnal j
                        on
                            dj.id_header = j.id
                    where
                        djt.tujuan_coa in ('".implode("', '", $no_coa)."') and
                        dj.tanggal between '".$startDate."' and '".$endDate."'
                        ".$sql_unit."
                        ".$sql_perusahaan."

                    union all

                    select 
                        djt.id,
                        dj.tanggal,
                        djt.tujuan_coa as no_akun_transaksi,
                        djt.nama as nama_akun_transaksi,
                        dj.id as det_jurnal_id,
                        dj.pic,
                        dj.keterangan,
                        0 as debit,
                        dj.nominal as kredit
                    from det_jurnal dj
                    right join
                        det_jurnal_trans djt
                        on
                            dj.det_jurnal_trans_id = djt.id
                    right join
                        jurnal j
                        on
                            dj.id_header = j.id
                    where
                        djt.sumber_coa in ('".implode("', '", $no_coa)."') and
                        dj.tanggal between '".$startDate."' and '".$endDate."'
                        ".$sql_unit."
                        ".$sql_perusahaan."
                ) _data
                order by
                    _data.tanggal asc,
                    _data.id asc
            ";
            $d_debit = $m_conf->hydrateRaw( $sql );

            if ( $d_debit->count() > 0 ) {
                $data = $d_debit->toArray();
            }

            $content['data_saldo'] = $data_saldo;
            $content['data'] = $data;
            $content['g_status'] = $g_status;
            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['status_btn_tutup_bulan'] = $status_btn_tutup_bulan;
            $this->result['status_btn_submit'] = $status_btn_submit;
            $this->result['status_btn_ack'] = $status_btn_ack;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $unit = $params['unit'];
            $perusahaan = $params['perusahaan'];
            $periode = $params['periode'];

            $startDate = substr($periode, 0, 7).'-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1
                    sk.id
                from saldo_kas sk
                where
                    sk.periode between '".$startDate."' and '".$endDate."' and
                    sk.unit = '".$unit."' and
                    sk.perusahaan = '".$perusahaan."'
            ";
            $d_sk = $m_conf->hydrateRaw( $sql );

            $id = 0;

            if ( $d_sk->count() > 0 ) {
                $id = $d_sk->toArray()[0]['id'];

                $m_sk1 = new \Model\Storage\SaldoKas_model();
                $now = $m_sk1->getDate();

                $waktu = $now['waktu'];

                $m_sk1->where('id', $id)->update(
                    array(
                        'saldo_akhir' => $params['saldo_akhir'],
                        'g_status' => getStatus('submit')
                    )
                );

                $d_sk1 = $m_sk1->where('id', $id)->first();

                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_sk1, $deskripsi_log );

                $m_sk2 = new \Model\Storage\SaldoKas_model();
                $m_sk2->tgl_trans = $waktu;
                $m_sk2->unit = $unit;
                $m_sk2->perusahaan = $perusahaan;
                $m_sk2->periode = date('Y-m-d', strtotime($startDate. ' + 1 months'));
                $m_sk2->saldo_awal = $params['saldo_akhir'];
                $m_sk2->saldo_akhir = 0;
                $m_sk2->g_status = 0;
                $m_sk2->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sk2, $deskripsi_log );
            } else {
                $m_sk1 = new \Model\Storage\SaldoKas_model();
                $now = $m_sk1->getDate();

                $waktu = $now['waktu'];

                $m_sk1->tgl_trans = $waktu;
                $m_sk1->unit = $unit;
                $m_sk1->perusahaan = $perusahaan;
                $m_sk1->periode = $startDate;
                $m_sk1->saldo_awal = 0;
                $m_sk1->saldo_akhir = $params['saldo_akhir'];
                $m_sk1->g_status = getStatus('ack');
                $m_sk1->save();

                $id = $m_sk1->id;

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sk1, $deskripsi_log );

                $m_sk2 = new \Model\Storage\SaldoKas_model();
                $m_sk2->tgl_trans = $waktu;
                $m_sk2->unit = $unit;
                $m_sk2->perusahaan = $perusahaan;
                $m_sk2->periode = date('Y-m-d', strtotime($startDate. ' + 1 months'));
                $m_sk2->saldo_awal = $params['saldo_akhir'];
                $m_sk2->saldo_akhir = 0;
                $m_sk1->g_status = 0;
                $m_sk2->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sk2, $deskripsi_log );
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'materai', NULL, NULL, 0, 'saldo_kas', ".$id.", NULL, 1, 1";
            $m_conf->hydrateRaw( $sql );

            $m_sk = new \Model\Storage\SewaKantor_model();
            // $d_sk = $m_sk->where('mulai', '<=', $startDate)->where('akhir', '>=', $endDate)->where('unit', $unit)->first();
            $d_sk = $m_sk->where('akhir', '>=', $endDate)->where('unit', $unit)->where('perusahaan', $perusahaan)->first();
            if ( $d_sk ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'OPERASIONAL UNIT', NULL, NULL, 0, 'sewa_kantor', ".$d_sk->id.", NULL, 1, 1, '".$endDate."'";
                $m_conf->hydrateRaw( $sql );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di tutup.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function ack()
    {
        $params = $this->input->post('params');

        try {
            $unit = $params['unit'];
            $perusahaan = $params['perusahaan'];
            $periode = $params['periode'];

            $startDate = substr($periode, 0, 7).'-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1
                    sk.id
                from saldo_kas sk
                where
                    sk.periode between '".$startDate."' and '".$endDate."' and
                    sk.unit = '".$unit."' and
                    sk.perusahaan = '".$perusahaan."'
            ";
            $d_sk = $m_conf->hydrateRaw( $sql );

            $id = 0;

            if ( $d_sk->count() > 0 ) {
                $id = $d_sk->toArray()[0]['id'];

                $m_sk = new \Model\Storage\SaldoKas_model();
                $now = $m_sk->getDate();

                $m_sk->where('id', $id)->update(
                    array(
                        'saldo_akhir' => $params['saldo_akhir'],
                        'g_status' => getStatus('ack')
                    )
                );

                $d_sk = $m_sk->where('id', $id)->first();

                $deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_sk, $deskripsi_log );
            } 

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'materai', NULL, NULL, 0, 'saldo_kas', ".$id.", ".$id.", 2, 1";
            $m_conf->hydrateRaw( $sql );

            $m_sk = new \Model\Storage\SewaKantor_model();
            // $d_sk = $m_sk->where('mulai', '<=', $startDate)->where('akhir', '>=', $endDate)->where('unit', $unit)->first();
            $d_sk = $m_sk->where('akhir', '>=', $endDate)->where('unit', $unit)->where('perusahaan', $perusahaan)->first();
            if ( $d_sk ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'OPERASIONAL UNIT', NULL, NULL, 0, 'sewa_kantor', ".$d_sk->id.", ".$d_sk->id.", 2, 1, '".$endDate."'";
                $m_conf->hydrateRaw( $sql );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di tutup.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getDataDetJurnal($id) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.id,
                w.nama as nama_unit,
                prs.perusahaan as nama_perusahaan,
                dj.tanggal,
                jt.nama as transaksi_jurnal,
                jt.id as transaksi_jurnal_id,
                djt.nama as detail_transaksi_jurnal,
                dj.pic,
                dj.keterangan,
                dj.asal,
                dj.coa_asal,
                dj.tujuan,
                dj.coa_tujuan
            from det_jurnal dj
            left join
                jurnal j
                on
                    j.id = dj.id
            left join
                det_jurnal_trans djt
                on
                    dj.det_jurnal_trans_id = djt.id
            left join
                jurnal_trans jt
                on
                    djt.id_header = jt.id
            left join
                (
                    select 
                        w1.kode,
                        REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                    from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                        on
                            w1.id = w2.id
                    
                    union all

                    select 'pusat' as kode, 'PUSAT' as nama
                ) w
                on
                    dj.unit = w.kode
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    dj.perusahaan = prs.kode
            where
                dj.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    public function detailForm() {
        $params = $this->input->get('params');

        $id = $params['id'];
        $g_status = $params['g_status'];

        $data = $this->getDataDetJurnal($id);

        $content['akses'] = $this->akses;
        $content['g_status'] = $g_status;
        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'detailForm', $content, TRUE);

        echo $html;
    }

    public function editForm() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $data = $this->getDataDetJurnal($id);

        $content['data'] = $data;
        $content['det_jurnal_trans'] = $this->getDetJurnalTrans( $data['transaksi_jurnal_id'] );
        $html = $this->load->view($this->pathView.'editForm', $content, TRUE);

        echo $html;
    }

    public function getDetJurnalTrans( $id_jurnal_trans ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select djt.* from det_jurnal_trans djt
            right join
                (
                    select jt1.* from jurnal_trans jt1
                    right join
                        (
                            select * from jurnal_trans
                            where
                                id = ".$id_jurnal_trans."
                        ) jt2
                        on
                            jt1.kode = jt2.kode
                    where
                        jt1.mstatus = 1
                ) jt
                on
                    djt.id_header = jt.id
            order by
                djt.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getSumberTujuanCoa()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select * from det_jurnal_trans djt
                where
                    id = ".$params."
            ";
            $d_djt = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_djt->count() > 0 ) {
                $d_djt = $d_djt->toArray()[0];

                $data = array(
                    'sumber' => $d_djt['sumber'],
                    'sumber_coa' => $d_djt['sumber_coa'],
                    'tujuan' => $d_djt['tujuan'],
                    'tujuan_coa' => $d_djt['tujuan_coa'],
                );
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function editDetJurnal() {
        $params = $this->input->post('params');

        try {
            $m_dj = new \Model\Storage\DetJurnal_model();
            $m_dj->where('id', $params['id'])->update(
                array(
                    'det_jurnal_trans_id' => $params['det_jurnal_trans_id'],
                    'asal' => $params['sumber'],
                    'coa_asal' => $params['sumber_coa'],
                    'tujuan' => $params['tujuan'],
                    'coa_tujuan' => $params['tujuan_coa']
                )
            );

            $d_dj = $m_dj->where('id', $params['id'])->first();

            $m_jurnal = new \Model\Storage\Jurnal_model();
            $d_jurnal = $m_jurnal->where('id', $d_dj->id_header)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jurnal, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di edit';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function model($status)
    {
        $m_sk = new \Model\Storage\SaldoKas_model();
        $dashboard = $m_sk->getDashboard($status);

        cetak_r( $dashboard );

        return $dashboard;
    }
}