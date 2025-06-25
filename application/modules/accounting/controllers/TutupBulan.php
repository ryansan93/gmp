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

    public function getPerusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = $d_perusahaan['kode_gabung_perusahaan'];
                $key_detail = strtoupper($d_perusahaan->perusahaan).' | '.$d_perusahaan->kode;

                $data[ $key ]['kode_gabung_perusahaan'] = $d_perusahaan['kode_gabung_perusahaan'];
                $data[ $key ]['detail'][ $key_detail ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function listData() {
        $list = array(
            array(
                array('BCA - CV. Mitra Gemuk Bersama', array(110201)),
                array('BCA - CV. Mitra Gemilang Bersinar', array(110202)),
                array('Kas Kecil Seluruh Unit', array(110102, 110101)),
            ),
            array(
                array('Piutang Bakul (Tagihan Uang Ayam)', array(130101)),
                array('Piutang Plasma ', array(130202)),
                array('Piutang Karyawan', array(130301)),
                array('Setoran Modal ke Mavendra', array(310301)),
                array('Setoran Modal ke RPA', array(310401)),
                array('CN Pakan Belum Diambil', array(130506)),
                array('Saldo di Malindo', array()),
            ),
            array(
                array('Persediaan DOC', array(160101)),
                array('Persediaan Pakan', array(160202)),
                array('Persediaan Obat', array(160301, 160302)),
            ),
            array(
                array('Bangunan Kantor, Kendaraan dan Peralatan', array(530101, 510101, 510201, 510301, 510302, 510105)),
                array('Sewa dibayar dimuka', array(510103))
            ),
            array(
                array('Pengambilan Prive 2013-2022', array(310201)),
            ),
            array(
                array('Hutang DOC', array(210101, 210102)),
                array('HutangPakan', array(210201, 210202)),
                array('Hutang Obat dan Peralatan', array(210301, 210403)),
                array('Hutang Kredit Kendaraan Bermotor (BCA)', array()),
                array('Pendapatan Plasma Belum Dibayar', array(210451)),
                array('Ongkos Truk Belum Dibayar', array(210401, 210402)),
                array('Pajak Juli Belum Disetorkan', array()),
                array('Saldo Uang Muka Bakul di MGB', array(210502)),
                array('Saldo Lebih Bayar Bakul di MGB', array(210501)),
            ),
            array(
                array('Laba ditahan 2010-2022', array(310603)),
                array('Laba Jan- 31 Juli 2023', array(310602)),
            )
        );

        return $list;
    }

    public function prosesHitung ($start_date, $end_date) {
        $listData = $this->listData();

        $data = null;
        foreach ($listData as $k_ld => $v_ld) {
            foreach ($v_ld as $key => $value) {
                $nilai = 0;

                $data[ $k_ld ]['detail'][ $key ]['keterangan'] = null;
                $data[ $k_ld ]['detail'][ $key ]['nilai'] = null;

                if ( !empty($value[1]) && count($value[1]) > 0 ) {
                    foreach ($value[1] as $k_coa => $v_coa) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select
                                c.coa as no_coa,
                                isnull(sb.saldo_awal, 0) as saldo_awal,
                                sum(isnull(dj.debet, 0)) as debet,
                                sum(isnull(dj.kredit, 0)) as kredit,
                                c.coa_pos
                            from coa c
                            left join
                                (
                                    select 
                                        ".$v_coa." as no_coa,  
                                        case
                                            when coa_tujuan = ".$v_coa." then
                                                nominal
                                            else
                                                0
                                        end as debet,
                                        case
                                            when coa_asal = ".$v_coa." then
                                                nominal
                                            else
                                                0
                                        end as kredit
                                    from det_jurnal where tanggal between '".$start_date."' and '".$end_date."' and (coa_asal = ".$v_coa." or coa_tujuan = ".$v_coa.")
                                ) dj
                                on
                                    dj.no_coa = c.coa
                            left join
                                (select * from saldo_bulanan where tanggal between '".$start_date."' and '".$end_date."') sb
                                on
                                    sb.coa = c.coa
                            where
                                c.coa = ".$v_coa."
                            group by
                                c.coa,
                                isnull(sb.saldo_awal, 0),
                                c.coa_pos
                        ";
                        $d_conf = $m_conf->hydrateRaw( $sql );

                        if ( $d_conf->count() > 0 ) {
                            $d_conf = $d_conf->toArray()[0];

                            $data[ $k_ld ]['detail'][ $key ]['detail'][ $d_conf['no_coa'] ]['saldo_akhir'] = ($d_conf['saldo_awal']+$d_conf['debet'])-$d_conf['kredit'];
                            $data[ $k_ld ]['detail'][ $key ]['detail'][ $d_conf['no_coa'] ]['coa_pos'] = $d_conf['coa_pos'];

                            $nilai += ($d_conf['saldo_awal']+$d_conf['debet'])-$d_conf['kredit'];
                        }
                    }
                }

                $data[ $k_ld ]['detail'][ $key ]['keterangan'] = $value['0'];
                $data[ $k_ld ]['detail'][ $key ]['nilai'] = $nilai;
            }
        }

        return $data;
    }

    public function getData() {
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
                select * from saldo_bulanan where tanggal = '".$tgl_next_saldo."'";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $btn_tutup = 1;
            $btn_hapus = 0;
            if ( $d_conf->count() > 0 ) {
                $btn_tutup = 0;
                $btn_hapus = 1;

                $tgl_next_next_saldo = next_date(date("Y-m-t", strtotime($tgl_next_saldo)));
                $m_conf = new \Model\Storage\Conf();
                $sql = "select * from saldo_bulanan where tanggal = '".$tgl_next_next_saldo."'";
                $d_conf = $m_conf->hydrateRaw( $sql );

                if ( $d_conf->count() > 0 ) {
                    $btn_hapus = 0;
                }
            }

            $data = $this->prosesHitung( $start_date, $end_date );

            $content['data'] = $data;
            $html = $this->load->view($this->pathView . 'listData', $content, true);;

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'html' => $html,
                'btn_tutup' => $btn_tutup,
                'btn_hapus' => $btn_hapus
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
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
            $now = $m_conf->getDate();
            $sql = "select * from saldo_bulanan where tanggal = '".$tgl_next_saldo."'";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $m_sb = new \Model\Storage\SaldoBulanan_model();
                $m_sb->where('tanggal', $tgl_next_saldo)->delete();
            }

            $data = $this->prosesHitung( $start_date, $end_date );

            foreach ($data as $key => $value) {
                foreach ($value['detail'] as $k_det1 => $v_det1) {
                    if ( isset($v_det1['detail']) && !empty($v_det1['detail']) ) {
                        foreach ($v_det1['detail'] as $k_det2 => $v_det2) {
                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $m_sb->where('tanggal', $start_date)->where('coa', $k_det2)->update(
                                array('saldo_akhir' => $v_det2['saldo_akhir'])
                            );

                            $m_sb = new \Model\Storage\SaldoBulanan_model();
                            $m_sb->tgl_trans = $now['waktu'];
                            $m_sb->coa = $k_det2;
                            $m_sb->tanggal = $tgl_next_saldo;
                            $m_sb->saldo_awal = $v_det2['saldo_akhir'];
                            $m_sb->saldo_akhir = null;
                            $m_sb->posisi = $v_det2['coa_pos'];
                            $m_sb->save();
                        }
                    }
                }
            }

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