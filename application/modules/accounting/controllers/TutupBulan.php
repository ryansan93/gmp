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

    public function tutupBulan() {
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
            $sql = "
                select
                    d_jurnal.coa,
                    d_jurnal.kode_trans,
                    d_jurnal.kode_jurnal,
                    sum(d_jurnal.debet) as debet,
                    sum(d_jurnal.kredit) as kredit
                from
                (
                    select
                        sb.coa,
                        sb.kode_trans,
                        sb.kode_jurnal,
                        sb.saldo_awal as debet,
                        0 as kredit
                    from saldo_bulanan sb
                    where
                        sb.tanggal between '".$start_date."' and '".$end_date."'

                    union all

                    select
                        dj.coa_asal as coa,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        0 as debet,
                        sum(dj.nominal) as kredit
                    from det_jurnal dj
                    where
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        dj.coa_asal,
                        dj.kode_trans,
                        dj.kode_jurnal

                    union all

                    select
                        dj.coa_tujuan as coa,
                        dj.kode_trans,
                        dj.kode_jurnal,
                        sum(dj.nominal) as debet,
                        0 as kredit
                    from det_jurnal dj
                    where
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        dj.coa_tujuan,
                        dj.kode_trans,
                        dj.kode_jurnal
                ) d_jurnal
                group by
                    d_jurnal.coa,
                    d_jurnal.kode_trans,
                    d_jurnal.kode_jurnal
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_conf => $v_conf) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "select * from saldo_bulanan where tanggal = '".$start_date."' and coa = '".$v_conf['coa']."' and kode_trans = '".$v_conf['kode_trans']."'";
                    $d_sb_now = $m_conf->hydrateRaw( $sql );

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "select * from saldo_bulanan where tanggal = '".$tgl_next_saldo."' and coa = '".$v_conf['coa']."' and kode_trans = '".$v_conf['kode_trans']."'";
                    $d_sb_next = $m_conf->hydrateRaw( $sql );

                    if ( $d_sb_now->count() > 0 ) {
                        $d_sb_now = $d_sb_now->toArray()[0];

                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $m_sb->where('id', $d_sb_now['id'])->where('coa', $v_conf['coa'])->update(
                            array(
                                'saldo_akhir' => $v_conf['debet']-$v_conf['kredit'],
                                'kode_trans' => $v_conf['kode_trans'],
                                'kode_jurnal' => $v_conf['kode_jurnal']
                            )
                        );
    
                        if ( $d_sb_next->count() > 0 ) {
                            $d_sb_next = $d_sb_next->toArray()[0];

                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $m_sb->where('id', $d_sb_next['id'])->update(
                                array(
                                    'saldo_awal' => $v_conf['debet']-$v_conf['kredit'],
                                    'kode_trans' => $v_conf['kode_trans'],
                                    'kode_jurnal' => $v_conf['kode_jurnal']
                                )
                            );
                        } else {
                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $now = $m_conf->getDate();

                            $m_sb->tgl_trans = $now['waktu'];
                            $m_sb->coa = $v_conf['coa'];
                            $m_sb->tanggal = $tgl_next_saldo;
                            $m_sb->saldo_awal = $v_conf['debet']-$v_conf['kredit'];
                            $m_sb->saldo_akhir = 0;
                            $m_sb->kode_trans = $v_conf['kode_trans'];
                            $m_sb->kode_jurnal = $v_conf['kode_jurnal'];
                            $m_sb->save();
                        }
                    } else {
                        $m_sb = new \Model\Storage\SaldoBulanan_model();
                        $now = $m_conf->getDate();
    
                        $m_sb->tgl_trans = $now['waktu'];
                        $m_sb->coa = $v_conf['coa'];
                        $m_sb->tanggal = $start_date;
                        $m_sb->saldo_awal = 0;
                        $m_sb->saldo_akhir = $v_conf['debet']-$v_conf['kredit'];
                        $m_sb->kode_trans = $v_conf['kode_trans'];
                        $m_sb->kode_jurnal = $v_conf['kode_jurnal'];
                        $m_sb->save();

                        if ( $d_sb_next->count() > 0 ) {
                            $d_sb_next = $d_sb_next->toArray()[0];

                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $m_sb->where('id', $d_sb_next['id'])->update(
                                array(
                                    'saldo_awal' => $v_conf['debet']-$v_conf['kredit'],
                                    'kode_trans' => $v_conf['kode_trans'],
                                    'kode_jurnal' => $v_conf['kode_jurnal']
                                )
                            );
                        } else {
                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $now = $m_conf->getDate();

                            $m_sb->tgl_trans = $now['waktu'];
                            $m_sb->coa = $v_conf['coa'];
                            $m_sb->tanggal = $tgl_next_saldo;
                            $m_sb->saldo_awal = $v_conf['debet']-$v_conf['kredit'];
                            $m_sb->saldo_akhir = 0;
                            $m_sb->kode_trans = $v_conf['kode_trans'];
                            $m_sb->kode_jurnal = $v_conf['kode_jurnal'];
                            $m_sb->save();
                        }
                    }
                }
            }

            /* PERIODE FISKAL */
            $m_conf = new \Model\Storage\Conf();
            $sql = "select * from periode_fiskal where start_date = '".$tgl_next_saldo."'";
            $d_pf_next = $m_conf->hydrateRaw( $sql );

            if ( $d_pf_next->count() > 0 ) {
                $d_pf_next = $d_pf_next->toArray()[0];

                $m_bo = new \Model\Storage\PeriodeFiskal_model();
                $m_bo->where('id', $d_pf_next['id'])->update(
                    array(
                        'status' => 1
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

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}