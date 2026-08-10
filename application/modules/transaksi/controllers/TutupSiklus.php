<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fitur Tutup Siklus berdiri sendiri (terpisah dari penyimpanan RHPP di TSDRHPP).
 * Hanya bertanggung jawab menandai sebuah noreg sebagai siklus tutup dengan
 * insert 1 baris ke tabel tutup_siklus. Perhitungan/penyimpanan RHPP tetap
 * dilakukan terpisah di TSDRHPP, dan akan memakai baris tutup_siklus yang
 * sudah ada di sini (bukan bikin baru) kalau noreg-nya sudah pernah ditutup.
 */
class TutupSiklus extends Public_Controller {

    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/tutup_siklus/js/tutup-siklus.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $data['title_menu'] = 'Tutup Siklus';
            $data['view'] = $this->load->view('transaksi/tutup_siklus/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] != 1 ) {
            echo '<tr><td colspan="9">Anda tidak memiliki akses untuk melihat data ini.</td></tr>';
            return;
        }

        $params = $this->input->get('params');

        $filter = $params['filter'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $sql_filter = "";
        if ( $filter != 0 ) {
            $sql_filter = "where data.tutup_siklus = ".$filter."";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.mitra,
                data.noreg,
                data.kandang,
                data.populasi,
                data.tgl_docin_real,
                data.tgl_panen,
                data.tutup_siklus,
                data.status_rhpp,
                data.deskripsi,
                data.waktu
            from
            (
                select
                    m.nama as mitra,
                    _noreg.noreg,
                    SUBSTRING(_noreg.noreg, 10, 2) as kandang,
                    rdim.populasi,
                    CONVERT(varchar(10), _noreg.tgl_docin, 120) as tgl_docin_real,
                    rs.tgl_panen,
                    case
                        when ts.id is not null then
                            2
                        else
                            1
                    end as tutup_siklus,
                    case
                        when rhpp.noreg is not null then
                            2
                        else
                            1
                    end as status_rhpp,
                    case
                        when ts.id is not null then
                            lt.deskripsi
                        else
                            null
                    end as deskripsi,
                    case
                        when ts.id is not null then
                            lt.waktu
                        else
                            null
                    end as waktu
                from
                (
                    select
                        od.noreg,
                        td.datang as tgl_docin
                    from
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                        where
                            td1.datang between '".$start_date.' 00:00:00.000'."' and '".$end_date.' 23:59:59.999'."'
                    ) td
                    left join
                        (
                            select od1.* from order_doc od1
                            right join
                                (select max(id) as id, no_order from order_doc group by no_order) od2
                                on
                                    od1.id = od2.id
                        ) od
                        on
                            td.no_order = od.no_order

                    union all

                    select
                        rs.noreg,
                        rs.tgl_docin
                    from rdim_submit rs
                    left join
                        (
                            select
                                od.noreg
                            from
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            left join
                                (
                                    select od1.* from order_doc od1
                                    right join
                                        (select max(id) as id, no_order from order_doc group by no_order) od2
                                        on
                                            od1.id = od2.id
                                ) od
                                on
                                    td.no_order = od.no_order
                        ) td
                        on
                            td.noreg = rs.noreg
                    where
                        rs.tgl_docin between '".$start_date."' and '".$end_date."' and
                        td.noreg is null
                ) _noreg
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        SUBSTRING(_noreg.noreg, 1, 7) = mm.nim
                left join
                    mitra m
                    on
                        m.id = mm.mitra
                left join
                    (
                        select min(tgl_panen) as tgl_panen, noreg from real_sj rs group by noreg
                    ) rs
                    on
                        _noreg.noreg = rs.noreg
                left join
                    rdim_submit rdim
                    on
                        _noreg.noreg = rdim.noreg
                left join
                    tutup_siklus ts
                    on
                        ts.noreg = _noreg.noreg
                left join
                    (
                        select distinct noreg from rhpp
                    ) rhpp
                    on
                        rhpp.noreg = _noreg.noreg
                left join
                    (
                        select lt1.* from log_tables lt1
                        right join
                            (select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'tutup_siklus' group by tbl_name, tbl_id) lt2
                            on
                                lt1.id = lt2.id
                    ) lt
                    on
                        lt.tbl_id = cast(ts.id as varchar(15))
            ) data
            ".$sql_filter."
            group by
                data.mitra,
                data.noreg,
                data.kandang,
                data.populasi,
                data.tgl_docin_real,
                data.tgl_panen,
                data.tutup_siklus,
                data.status_rhpp,
                data.deskripsi,
                data.waktu
            order by
                data.tgl_docin_real asc,
                data.mitra asc,
                data.kandang asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/tutup_siklus/list', $content, TRUE);

        echo $html;
    }

    /**
     * Validasi kesiapan tutup siklus untuk sebuah noreg :
     * - Data nekropsi akhir siklus sudah di-submit (utk siklus >= 2023-11-01)
     * - Data LHK akhir siklus sudah di-submit (tgl_lhk >= tgl_panen)
     * - Sisa stok pakan di kandang sudah 0
     * Sama persis dengan validasi yang dipakai TSDRHPP sebelum tombol
     * TUTUP SIKLUS di sana bisa dipakai, supaya aturan penutupan siklus
     * konsisten di kedua tempat.
     */
    private function _validasiSiapTutup( $noreg )
    {
        $status = 1;
        $message = null;

        $m_conf = new \Model\Storage\Conf();
        $now = $m_conf->getDate();

        $sql = "
            select ln.* from lhk_nekropsi ln
            right join
                lhk l
                on
                    ln.id_header = l.id
            where
                l.noreg = '".$noreg."'
            order by
                l.umur desc
        ";
        $d_ln = $m_conf->hydrateRaw( $sql );

        if ( $d_ln->count() == 0 && $now['tanggal'] >= '2023-11-01' ) {
            $status = 0;
            $message = 'Belum ada data nekropsi yang di submit, harap konfirmasi pada bagian terkait.';
            return array('status' => $status, 'message' => $message);
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rs.noreg,
                l.tanggal as tgl_lhk,
                r_sj.tanggal as tgl_panen
            from rdim_submit rs
            left join
                (
                    select l1.* from lhk l1
                    right join
                        (select noreg, max(tanggal) as tanggal from lhk where noreg = '".$noreg."' group by noreg) l2
                        on
                            l1.noreg = l2.noreg and
                            l1.tanggal = l2.tanggal
                ) l
                on
                    l.noreg = rs.noreg
            left join
                (
                    select noreg, max(tgl_panen) as tanggal, sum(netto_ekor) as jml_panen from real_sj where ekor > 0 and noreg = '".$noreg."' group by noreg
                ) r_sj
                on
                    r_sj.noreg = rs.noreg
            where
                rs.noreg = '".$noreg."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $tgl_lhk = $d_conf['tgl_lhk'];
            $tgl_panen = $d_conf['tgl_panen'];

            if ( $tgl_lhk < $tgl_panen ) {
                $status = 0;
                $message = 'Data LHK akhir siklus belum di submit, segera hubungi PPL yang bersangkutan untuk melakukan submit data LHK akhir siklus.';
                return array('status' => $status, 'message' => $message);
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select ts.*, dss.stok from tutup_siklus ts
                left join
                    (select dss.noreg, sum(dss.jml_stok) as stok from det_stok_siklus dss where dss.jenis_barang = 'pakan' group by dss.noreg) dss
                    on
                        ts.noreg = dss.noreg
                where
                    ts.noreg = '".$noreg."'
                    and dss.stok > 0
            ";
            $d_stok = $m_conf->hydrateRaw( $sql );

            if ( $d_stok->count() > 0 ) {
                $d_stok = $d_stok->toArray()[0]['stok'];

                $status = 0;
                $message = 'Data pakan anda masih belum 0.';
                $message .= '<br>';
                $message .= '<b><u>Pakan</u></b><br>';
                $message .= 'Sisa Stok Pakan di Kandang : '.angkaRibuan($d_stok).'<br>';
                $message .= '<br>';
                $message .= 'Cek data di laporan kartu stok siklus, dan segera perbaiki data.';
                return array('status' => $status, 'message' => $message);
            }
        }

        return array('status' => $status, 'message' => $message);
    }

    public function cek_lhk()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] != 1 ) {
            $this->result['message'] = 'Anda tidak memiliki akses untuk melakukan aksi ini.';
            display_json($this->result);
            return;
        }

        $params = $this->input->post('params');

        try {
            $cek = $this->_validasiSiapTutup( $params['noreg'] );

            $this->result['status'] = $cek['status'];
            $this->result['message'] = $cek['message'];
        } catch (\Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function simpan()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_submit'] != 1 ) {
            $this->result['message'] = 'Anda tidak memiliki akses untuk menutup siklus.';
            display_json($this->result);
            return;
        }

        $params = $this->input->post('params');

        try {
            $m_ts = new \Model\Storage\TutupSiklus_model();
            $d_ts_exist = $m_ts->where('noreg', $params['noreg'])->first();

            if ( !empty($d_ts_exist) ) {
                $this->result['message'] = 'Noreg <b>'.$params['noreg'].'</b> sudah pernah ditutup siklusnya.';
                display_json($this->result);
                return;
            }

            $cek = $this->_validasiSiapTutup( $params['noreg'] );

            if ( $cek['status'] != 1 ) {
                $this->result['message'] = $cek['message'];
                display_json($this->result);
                return;
            }

            // Biaya Materai & Potongan Pajak tetap diisi belakangan di TSDRHPP
            // (saat simpan RHPP), bukan di sini.
            $m_ts->noreg = $params['noreg'];
            $m_ts->tgl_docin = $params['tgl_docin'];
            $m_ts->tgl_tutup = $params['tgl_tutup_siklus'];
            $m_ts->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_ts, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Siklus berhasil ditutup';
            $this->result['content'] = array('id' => $m_ts->id);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * Cek apakah baris tutup_siklus untuk sebuah noreg boleh dihapus :
     * - Datanya harus ada
     * - RHPP untuk noreg tsb belum pernah disimpan sama sekali (kalau
     *   sudah ada, harus dihapus lewat menu RHPP / TSDRHPP supaya semua
     *   data turunannya ikut ditangani)
     */
    private function _cekBolehHapus( $noreg )
    {
        $status = 1;
        $message = null;

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $noreg)->first();

        if ( empty($d_ts) ) {
            return array('status' => 0, 'message' => 'Data tidak ditemukan.', 'data' => null);
        }

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $rhpp_ada = $m_rhpp->where('noreg', $noreg)->count() > 0;

        if ( $rhpp_ada ) {
            $status = 0;
            $message = 'Tidak bisa dihapus, RHPP untuk noreg <b>'.$noreg.'</b> sudah pernah disimpan. Hapus RHPP-nya terlebih dahulu lewat menu RHPP.';
        }

        return array('status' => $status, 'message' => $message, 'data' => $d_ts);
    }

    public function cek_hapus()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_delete'] != 1 ) {
            $this->result['message'] = 'Anda tidak memiliki akses untuk menghapus data ini.';
            display_json($this->result);
            return;
        }

        $params = $this->input->post('params');

        try {
            $cek = $this->_cekBolehHapus( $params['noreg'] );

            $this->result['status'] = $cek['status'];
            $this->result['message'] = $cek['message'];
        } catch (\Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_delete'] != 1 ) {
            $this->result['message'] = 'Anda tidak memiliki akses untuk menghapus data ini.';
            display_json($this->result);
            return;
        }

        $params = $this->input->post('params');

        try {
            $cek = $this->_cekBolehHapus( $params['noreg'] );

            if ( $cek['status'] != 1 ) {
                $this->result['message'] = $cek['message'];
                display_json($this->result);
                return;
            }

            $d_ts = $cek['data'];
            $id = $d_ts->id;

            $m_ts = new \Model\Storage\TutupSiklus_model();
            $m_ts->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_ts, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil dihapus';
            $this->result['content'] = array('id' => $id);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }
}
