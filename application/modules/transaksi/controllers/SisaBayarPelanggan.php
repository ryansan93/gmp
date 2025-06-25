<?php defined('BASEPATH') or exit('No direct script access allowed');

class SisaBayarPelanggan extends Public_Controller
{
    private $pathView = 'transaksi/sisa_bayar_pelanggan/';

    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/transaksi/sisa_bayar_pelanggan/js/sisa-bayar-pelanggan.js'

            ));

            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/transaksi/sisa_bayar_pelanggan/css/sisa-bayar-pelanggan.css'
            ));

            $data = $this->includes;

            $content['title_panel'] = 'Sisa Bayar Pelanggan';
            $content['current_uri'] = $this->current_uri;
            $content['akses'] = $akses;

            $content['add_form'] = $this->add_form();

            $data['title_menu'] = 'Sisa Bayar Pelanggan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $html = $this->add_form();

        echo $html;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $m_sbp = new \Model\Storage\SisaBayarPelanggan_model();
        $d_sbp = $m_sbp->whereBetween('tanggal', [$params['start_date'], $params['end_date']])->get();

        $data = array();
        if ( $d_sbp ) {
            $d_sbp = $d_sbp->toArray();
            foreach ($d_sbp as $k_sbp => $v_sbp) {
                $m_plg = new \Model\Storage\Pelanggan_model();
                $d_plg = $m_plg->where('nomor', $v_sbp['pelanggan'])->where('tipe', 'pelanggan')->where('mstatus', 1)->orderBy('version', 'desc')->first();

                $m_lokasi = new \Model\Storage\Lokasi_model();
                $d_kec = $m_lokasi->where('id', $d_plg->alamat_kecamatan)->first();
                $d_kab = $m_lokasi->where('id', $d_kec->induk)->first();

                $kota_kab = str_replace('Kota ', '', str_replace('Kab ', '', $d_kab->nama));

                $key = str_replace('-', '', $v_sbp['tanggal']).' - '.$d_plg->nama;
                $data[ $key ] = array(
                    'id' => $v_sbp['id'],
                    'tanggal' => $v_sbp['tanggal'],
                    'kab_kota' => $kota_kab,
                    'pelanggan' => $d_plg->nama,
                    'saldo' => $v_sbp['sisa_saldo']
                );
            }
        }

        if ( !empty($data) ) {
            krsort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
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
            left join
                (select * from lokasi l where jenis = 'KC') l_kec
                on
                    l_kec.id = p.alamat_kecamatan
            left join
                (select * from lokasi l where jenis = 'KB' or jenis = 'KT') l_kab
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

    public function add_form()
    {
        $content['pelanggan'] = $this->get_pelanggan();
        $content['perusahaan'] = $this->get_perusahaan();
        $html = $this->load->view($this->pathView . 'add_form', $content, true);

        return $html;
    }

    public function get_saldo()
    {
        $params = $this->input->post('params');

        try {
            $pelanggan = $params['pelanggan'];
            $perusahaan = $params['perusahaan'];

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp = $m_sp->where('no_pelanggan', $pelanggan)->where('perusahaan', $perusahaan)->orderBy('id', 'desc')->first();

            $saldo = 0;
            if ( $d_sp ) {
                $saldo = $d_sp->saldo;
            }

            $this->result['status'] = 1;
            $this->result['content'] = $saldo;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $today = date('Y-m-d');

            $m_sbp = new \Model\Storage\SisaBayarPelanggan_model();
            $m_sbp->tanggal = $today;
            $m_sbp->pelanggan = $params['pelanggan'];
            $m_sbp->perusahaan = $params['perusahaan'];
            $m_sbp->sisa_saldo = $params['sisa_saldo'];
            $m_sbp->save();

            $id = $m_sbp->id;

            $d_sbp = $m_sbp->where('id', $id)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_sbp, $deskripsi_log );

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $m_sp->jenis_saldo = 'K';
            $m_sp->no_pelanggan = $params['pelanggan'];
            $m_sp->id_trans = $id;
            $m_sp->tgl_trans = $today;
            $m_sp->jenis_trans = 'sisa_bayar_pelanggan';
            $m_sp->nominal = (0-$params['sisa_saldo']);
            $m_sp->saldo = 0;
            $m_sp->perusahaan = $params['perusahaan'];
            $m_sp->save();

            $this->result['status'] = 1;
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
            $today = date('Y-m-d');

            $m_sbp = new \Model\Storage\SisaBayarPelanggan_model();
            $d_sbp = $m_sbp->where('id', $params['id'])->first();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_sbp, $deskripsi_log );

            $m_sbp->where('id', $params['id'])->delete();

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp = $m_sp->where('no_pelanggan', $d_sbp->pelanggan)->where('perusahaan', $d_sbp->perusahaan)->orderBy('id', 'desc')->first();

            $m_sp->jenis_saldo = 'D';
            $m_sp->no_pelanggan = $d_sbp->pelanggan;
            $m_sp->id_trans = $params['id'];
            $m_sp->tgl_trans = $today;
            $m_sp->jenis_trans = 'reverse_sisa_bayar_pelanggan';
            $m_sp->nominal = $d_sbp['sisa_saldo'];
            $m_sp->saldo = ($d_sp->saldo + $d_sbp['sisa_saldo']);
            $m_sp->perusahaan = $d_sbp->perusahaan;
            $m_sp->save();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}