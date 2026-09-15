<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ProsesAmortisasi extends Public_Controller {

    private $pathView = 'sewa/proses_amortisasi/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index($segment=0)
    {
        if ( $this->hakAkses['a_view'] == 1 ) {

            $this->add_external_js(array(
                "assets/sewa/proses_amortisasi/js/proses_amortisasi.js",
            ));

            $data = $this->includes;
            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Proses Amortisasi Sewa';
            $data['title_menu'] = 'Proses Amortisasi Sewa';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);

        } else {
            showErrorAkses();
        }
    }

    private function getData($statusFilter)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                taj.id,
                taj.kode_amortisasi,
                taj.kode_transaksi,
                taj.nilai,
                taj.periode,
                ms.nama_sewa,
                ms.no_kontrak,
                mjs.nama_jenis_sewa
            from tabel_amortisasi_jadwal taj
            left join ms_sewa ms
                on ms.no_sewa = taj.kode_transaksi
            left join ms_jenis_sewa mjs
                on mjs.kode_jenis_sewa = ms.jenis_sewa
            where
                ".( $statusFilter === 'processed' ? "taj.status = 1" : "(taj.status = 0 or taj.status is null)" )."
            order by
                ".( $statusFilter === 'processed' ? "taj.periode desc, " : "" )."taj.kode_transaksi asc, taj.kode_amortisasi asc
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        return $d_conf->count() > 0 ? $d_conf->toArray() : null;
    }

    public function list_data()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list'] = $this->getData('pending');
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    public function list_processed()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list'] = $this->getData('processed');
        $html = $this->load->view($this->pathView . 'v_list_processed', $content, TRUE);

        echo $html;
    }

    public function proses()
    {
        $ids = $this->input->post('ids');
        $periode = trim($this->input->post('periode'));

        if ( empty($ids) || !is_array($ids) ) {
            $this->result['message'] = 'Pilih minimal satu baris amortisasi yang mau diproses.';
            display_json($this->result);
            return;
        }

        if ( empty($periode) ) {
            $this->result['message'] = 'Periode proses wajib diisi.';
            display_json($this->result);
            return;
        }

        if ( $this->isPeriodeTutupBuku($periode) ) {
            $this->result['message'] = 'Periode '.$periode.' sudah tutup buku, tidak bisa diproses.';
            display_json($this->result);
            return;
        }

        $berhasil = 0;
        $gagal = array();

        foreach ( $ids as $id ) {
            try {
                $m_taj = new \Model\Storage\AmortisasiJadwal_model();
                $d_taj = $m_taj->where('id', $id)->first();

                if ( !$d_taj ) {
                    $gagal[] = 'ID '.$id.' tidak ditemukan.';
                    continue;
                }

                $m_taj = new \Model\Storage\AmortisasiJadwal_model();
                $m_taj->where('id', $id)->update(
                    array(
                        'status' => 1,
                        'periode' => $periode,
                    )
                );

                Modules::run('base/InsertJurnal/exec', $this->url, $id, $id, 2);

                $deskripsi_log = 'diproses oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run('base/event/update', $d_taj, $deskripsi_log, null, $id, $d_taj);

                $berhasil++;
            } catch (Exception $e) {
                $gagal[] = 'ID '.$id.' : '.$e->getMessage();
            }
        }

        if ( $berhasil > 0 && empty($gagal) ) {
            $this->result['status'] = 1;
            $this->result['message'] = $berhasil.' baris amortisasi berhasil diproses.';
        } else if ( $berhasil > 0 && !empty($gagal) ) {
            $this->result['status'] = 1;
            $this->result['message'] = $berhasil.' baris berhasil diproses, tapi ada yang gagal: '.implode('; ', $gagal);
        } else {
            $this->result['message'] = 'Gagal memproses : '.implode('; ', $gagal);
        }

        display_json($this->result);
    }

    public function batal()
    {
        $ids = $this->input->post('ids');

        if ( empty($ids) || !is_array($ids) ) {
            $this->result['message'] = 'Pilih minimal satu baris amortisasi yang mau dibatalkan.';
            display_json($this->result);
            return;
        }

        $berhasil = 0;
        $gagal = array();

        foreach ( $ids as $id ) {
            try {
                $m_taj = new \Model\Storage\AmortisasiJadwal_model();
                $d_taj = $m_taj->where('id', $id)->first();

                if ( !$d_taj ) {
                    $gagal[] = 'ID '.$id.' tidak ditemukan.';
                    continue;
                }

                if ( $this->isPeriodeTutupBuku($d_taj->periode) ) {
                    $gagal[] = $d_taj->kode_amortisasi.' : periode fiskal sudah tutup buku, tidak bisa dibatalkan.';
                    continue;
                }

                Modules::run('base/InsertJurnal/exec', $this->url, $id, $id, 3);

                $m_taj = new \Model\Storage\AmortisasiJadwal_model();
                $m_taj->where('id', $id)->update(
                    array(
                        'status' => 0,
                        'periode' => null,
                    )
                );

                $deskripsi_log = 'dibatalkan oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run('base/event/update', $d_taj, $deskripsi_log, null, $id, $d_taj);

                $berhasil++;
            } catch (Exception $e) {
                $gagal[] = 'ID '.$id.' : '.$e->getMessage();
            }
        }

        if ( $berhasil > 0 && empty($gagal) ) {
            $this->result['status'] = 1;
            $this->result['message'] = $berhasil.' baris amortisasi berhasil dibatalkan.';
        } else if ( $berhasil > 0 && !empty($gagal) ) {
            $this->result['status'] = 1;
            $this->result['message'] = $berhasil.' baris berhasil dibatalkan, tapi ada yang gagal: '.implode('; ', $gagal);
        } else {
            $this->result['message'] = 'Gagal membatalkan : '.implode('; ', $gagal);
        }

        display_json($this->result);
    }

    /*
     * periode_fiskal.status = 0 berarti bulan itu sudah ditutup buku (konvensi yang sama
     * dipakai PostingUlang.php) -- kalau tanggal proses amortisasi jatuh di bulan yang sudah
     * ditutup, jurnalnya tidak boleh dibatalkan lagi.
     */
    private function isPeriodeTutupBuku($tanggal)
    {
        if ( empty($tanggal) ) {
            return false;
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select top 1 status from periode_fiskal
            where '".$tanggal."' between start_date and end_date
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $row = $d_conf->toArray()[0];
            return (int) $row['status'] === 0;
        }

        return false;
    }
}
