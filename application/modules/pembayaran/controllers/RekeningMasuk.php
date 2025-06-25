<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class RekeningMasuk extends Public_Controller
{
	private $url;
	private $hakAkses;

	function __construct()
	{
		parent::__construct();
		$this->url = 'pembayaran/rekening_masuk';
		$this->hakAkses = hakAkses($this->current_base_uri);
	}

	public function index()
	{
		if ( $this->hakAkses['a_view'] == 1 ) {
			$this->add_external_js(array(
				"assets/jquery/list.min.js",
				'assets/select2/js/select2.min.js',
				'assets/pembayaran/rekening_masuk/js/rekening-masuk.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/pembayaran/rekening_masuk/css/rekening-masuk.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Rekening Masuk';

			// $content['add_form'] = $this->add_form();
			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view($this->url.'/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

    public function getPelanggan()
	{
		$data = null;

        $m_plg = new \Model\Storage\Conf();
        $sql = "
            select
                data.nomor as kode,
                data.nama,
                data.kab_kota
            from
            (
                select
                    p.*,
                    REPLACE(REPLACE(kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota
                from pelanggan p
                right join
                    ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p1
                    on
                        p.id = p1.id
                right join
                    lokasi kec
                    on
                        kec.id = p.alamat_kecamatan
                right join
                    lokasi kab_kota
                    on
                        kab_kota.id = kec.induk
                where
                    p.mstatus = 1 and
                    p.tipe = 'pelanggan'
            ) data
            order by
                data.nama asc,
                data.kab_kota asc
        ";
        $d_plg = $m_plg->hydrateRaw( $sql );
        if ( $d_plg->count() > 0 ) {
            $data = $d_plg->toArray();
        }

		return $data;
	}

    public function getPerusahaan()
	{
		$data = null;

        $m_prs = new \Model\Storage\Conf();
        $sql = "
            select
                p1.kode,
                p1.perusahaan as nama
            from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
            order by
                p1.perusahaan asc
        ";
        $d_prs = $m_prs->hydrateRaw( $sql );
        if ( $d_prs->count() > 0 ) {
            $data = $d_prs->toArray();
        }

		return $data;
	}

    public function getLists() {
        $data = $this->input->get('params');

        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rm.kode,
                rm.tanggal,
                rm.perusahaan,
                prs.nama as nama_perusahaan,
                rm.pelanggan,
                plg.nama as nama_pelanggan,
                rm.jml_transfer,
                isnull(pp.jml_transfer, 0) as terpakai,
                rm.jml_transfer - isnull(pp.jml_transfer, 0) as sisa,
                rm.ket,
                rm.no_bukti
            from rekening_masuk rm
            left join
                (
                    select p1.* from pelanggan p1
                    right join
                        ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    plg.nomor = rm.pelanggan
            left join
                (
                    select
                        p1.kode,
                        p1.perusahaan as nama
                    from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = rm.perusahaan
            left join
                (
                    select sum(pp.jml_transfer) as jml_transfer, kode_umb from pembayaran_pelanggan pp group by kode_umb
                ) pp
                on
                    pp.kode_umb = rm.kode
            where
                rm.tanggal between '".$start_date."' and '".$end_date."'
            order by
                rm.tanggal desc,
                rm.kode desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $d_content['data'] = $data;
        $html = $this->load->view($this->url.'/list', $d_content, true);

        echo $html;
    }

    public function addForm()
    {
        $d_content['akses'] = $this->hakAkses;
    	$d_content['perusahaan'] = $this->getPerusahaan();
    	$d_content['pelanggan'] = $this->getPelanggan();
		$html = $this->load->view($this->url.'/addForm', $d_content, true);

		echo $html;
    }

    public function viewForm()
    {
        $params = $this->input->get('params');
        $kode = $params['kode'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rm.kode,
                rm.tanggal,
                rm.perusahaan,
                prs.nama as nama_perusahaan,
                rm.pelanggan,
                plg.nama as nama_pelanggan,
                rm.jml_transfer,
                rm.ket
            from rekening_masuk rm
            left join
                (
                    select p1.* from pelanggan p1
                    right join
                        ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    plg.nomor = rm.pelanggan
            left join
                (
                    select
                        p1.kode,
                        p1.perusahaan as nama
                    from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = rm.perusahaan
            where
                rm.kode = '".$kode."'
            order by
                rm.tanggal desc,
                rm.kode desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $d_content['akses'] = $this->hakAkses;
    	$d_content['data'] = $data;
		$html = $this->load->view($this->url.'/viewForm', $d_content, true);

		echo $html;
    }

    public function editForm()
    {
        $params = $this->input->get('params');
        $kode = $params['kode'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rm.kode,
                rm.tanggal,
                rm.perusahaan,
                rm.pelanggan,
                rm.jml_transfer,
                rm.ket
            from rekening_masuk rm
            where
                rm.kode = '".$kode."'
            order by
                rm.tanggal desc,
                rm.kode desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $d_content['akses'] = $this->hakAkses;
        $d_content['perusahaan'] = $this->getPerusahaan();
    	$d_content['pelanggan'] = $this->getPelanggan();
    	$d_content['data'] = $data;
		$html = $this->load->view($this->url.'/editForm', $d_content, true);

		echo $html;
    }

    public function save()
	{
		$data = $this->input->post('params');

        try {
            $m_rm = new \Model\Storage\RekeningMasuk_model();
            $kode = $m_rm->getNextIdRibuan();
            
            $m_rm->kode = $kode;
            $m_rm->tanggal = $data['tanggal'];
            $m_rm->perusahaan = $data['perusahaan'];
            $m_rm->pelanggan = (isset($data['pelanggan']) && !empty($data['pelanggan'])) ? $data['pelanggan'] : 0;
            $m_rm->jml_transfer = $data['jml_transfer'];
            $m_rm->ket = $data['ket'];
            $m_rm->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_rm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

    public function edit()
	{
		$data = $this->input->post('params');

        try {
            $m_rm = new \Model\Storage\RekeningMasuk_model();
            $m_rm->where('kode', $data['kode'])->update(
                array(
                    'tanggal' => $data['tanggal'],
                    'perusahaan' => $data['perusahaan'],
                    'pelanggan' => (isset($data['pelanggan']) && !empty($data['pelanggan'])) ? $data['pelanggan'] : 0,
                    'jml_transfer' => $data['jml_transfer'],
                    'ket' => $data['ket'],
                )
            );

            $d_rm = $m_rm->where('kode', $data['kode'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

    public function delete()
	{
		$data = $this->input->post('params');

        try {
            $m_rm = new \Model\Storage\RekeningMasuk_model();

            $d_rm = $m_rm->where('kode', $data['kode'])->first();

            $m_rm->where('kode', $data['kode'])->delete();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di delete.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

    public function importForm()
    {
        $d_content['akses'] = $this->hakAkses;
    	$d_content['perusahaan'] = $this->getPerusahaan();
		$html = $this->load->view($this->url.'/importForm', $d_content, true);

		echo $html;
    }

    public function import() {
        $params = json_decode($this->input->post('params'),TRUE);
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        try {
            if ( !empty($file) ) {
                $upload_path = FCPATH . "//uploads/import_file/";
                $moved = uploadFile($file, $upload_path);
                if ( $moved ) {
                    $path_name = $moved['path'];

                    $data = $this->getDataExcelUsingSpreadSheet( $path_name );

                    if ( !empty($data) && count($data) > 0 ) {
                        foreach ($data as $key => $value) {
                            if ( stristr($key, 'CR') !== false ) {
                                // cetak_r( $value['tanggal'], 1 );
                                
                                $_tanggal = explode('/',trim(preg_replace('/\s/u', ' ', $value['tanggal'])));

                                // cetak_r( $value );
                                // cetak_r( $value['tanggal'] );
                                // cetak_r( $_tanggal, 1 );
                                
                                $tahun = null;
                                $bulan = null;
                                $hari = null;
                                if ( count($_tanggal) < 3 ) {
                                    $tahun = date("Y");
                                    $bulan = ( strlen(preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-1])) > 1 ) ? preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-1]) : '0'.preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-1]);
                                    $hari = ( strlen(preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2])) > 0 ) ? preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2]) : '0'.preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2]);
                                } else {
                                    $tahun = preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-1]);
                                    $bulan = ( strlen(preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2])) > 1 ) ? preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2]) : '0'.preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-2]);
                                    $hari = ( strlen(preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-3])) > 0 ) ? preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-3]) : '0'.preg_replace('/\s/u', ' ', $_tanggal[count($_tanggal)-3]);
                                }
                                $tanggal = $tahun.'-'.$bulan.'-'.$hari;

                                $m_rm = new \Model\Storage\RekeningMasuk_model();
                                $kode = $m_rm->getNextIdRibuan();

                                $m_conf = new \Model\Storage\Conf();
                                $sql = "
                                    DECLARE @no_bukti varchar(50)

                                    EXECUTE generate_no_bukti_bank_jurnal '".$params['perusahaan']."', '".$tanggal."', 'BBM', @no_bukti = @no_bukti OUTPUT;

                                    select @no_bukti;
                                ";
                                $d_conf = $m_conf->hydrateRaw( $sql );

                                $no_bukti = null;
                                if ( $d_conf->count() > 0 ) {
                                    $no_bukti = $d_conf->toArray()[0][''];
                                }

                                // cetak_r( $kode );
                                // cetak_r( $tanggal );
                                // cetak_r( $params['perusahaan'] );
                                // cetak_r( $value['nominal'] );
                                // cetak_r( $value['keterangan'] );
                                // cetak_r( $no_bukti );

                                // cetak_r( $tanggal, 1 );
                                
                                $m_rm = new \Model\Storage\RekeningMasuk_model();
                                $m_rm->kode = $kode;
                                $m_rm->tanggal = $tanggal;
                                $m_rm->perusahaan = $params['perusahaan'];
                                $m_rm->jml_transfer = $value['nominal'];
                                $m_rm->ket = $value['keterangan'];
                                $m_rm->no_bukti = $no_bukti;
                                $m_rm->save();

                                $deskripsi_log = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                Modules::run( 'base/event/save', $m_rm, $deskripsi_log );
                            }
                        }

                        $this->result['status'] = 1;
                        $this->result['message'] = 'Data berhasil di import.';
                    } else {
                        $this->result['message'] = 'Data yang anda upload kosong.';
                    }
                } else {
                    $this->result['message'] = 'File gagal terupload, segera hubungi tim IT.';
                }
            }
        } catch (Exception $e) {
            $this->result['message'] = 'GAGAL : '.$e->getMessage();
        }

        display_json( $this->result );
    }

    public function getDataExcelUsingSpreadSheet( $path_name ) {
        $path = 'uploads/import_file/'.$path_name;

        $data = null;

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($path, \PhpOffice\PhpSpreadsheet\Reader\IReader::LOAD_WITH_CHARTS); // Load file yang tadi diupload ke folder tmp
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path, \PhpOffice\PhpSpreadsheet\Reader\IReader::LOAD_WITH_CHARTS);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $numrow = 1;
        $key = null;

        foreach($sheet as $row){
            // Ambil data pada excel sesuai Kolom
            $tanggal = $row['A']; // Ambil data tanggal
            $keterangan = $row['B']; // Ambil data keterangan
            $kode_trans = substr($row['D'], -2); // Ambil data kode_trans
            $nominal = substr(str_replace(',', '', $row['D']), 0, strlen(str_replace(',', '', $row['D']))-3); // Ambil data nominal
            // Cek jika semua data tidak diisi
            if($tanggal == "" && $keterangan == "" && $nominal == "")
            continue; // Lewat data pada baris ini (masuk ke looping selanjutnya / baris selanjutnya)
            // Cek $numrow apakah lebih dari 1
            // Artinya karena baris pertama adalah nama-nama kolom
            // Jadi dilewat saja, tidak usah diimport
            if($numrow >= 1){
                if ( !empty($tanggal) && $tanggal != "" ) {
                    $key = ($numrow-1).'-'.$kode_trans;
                    $data[ $key ] = array(
                        'tanggal' => $tanggal,
                        'keterangan' => $keterangan,
                        'nominal' => $nominal
                    );
                } else {
                    $data[ $key ]['keterangan'] .= '<br>'.$keterangan;
                }
            }
            $numrow++; // Tambah 1 setiap kali looping
        }

        return $data;
    }
}