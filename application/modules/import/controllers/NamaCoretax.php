<?php defined('BASEPATH') OR exit('No direct script access allowed');

class NamaCoretax extends Public_Controller {

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
    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/import/js/nama-coretax.js"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            $data['title_menu'] = 'Import Nama Coretax';
            $data['view'] = $this->load->view('import/nama_coretax/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    private function mapTipe()
    {
        return array(
            'peternak'  => array('model' => '\Model\Storage\Mitra_model', 'where' => array(), 'nik_field' => 'ktp'),
            'pelanggan' => array('model' => '\Model\Storage\Pelanggan_model', 'where' => array('tipe' => 'pelanggan'), 'nik_field' => 'nik'),
            'ekspedisi' => array('model' => '\Model\Storage\Ekspedisi_model', 'where' => array(), 'nik_field' => 'nik'),
        );
    }

    public function download_template()
    {
        $tipe = $this->input->get('tipe');
        $map_tipe = $this->mapTipe();

        if ( empty($tipe) || !isset($map_tipe[$tipe]) ) {
            show_error('Jenis Data tidak valid.');
        }

        $model_class = $map_tipe[$tipe]['model'];
        $extra_where = $map_tipe[$tipe]['where'];
        $nik_field = $map_tipe[$tipe]['nik_field'];

        $m_nomor = new $model_class();
        $q_nomor = $m_nomor->select('nomor')->distinct('nomor');
        foreach ($extra_where as $k_where => $v_where) {
            $q_nomor = $q_nomor->where($k_where, $v_where);
        }
        $list_nomor = $q_nomor->get()->toArray();

        $data = array();
        foreach ($list_nomor as $v_nomor) {
            $nomor = $v_nomor['nomor'];

            $m_row = new $model_class();
            $q_row = $m_row->where('nomor', $nomor);
            foreach ($extra_where as $k_where => $v_where) {
                $q_row = $q_row->where($k_where, $v_where);
            }
            $row = $q_row->orderBy('version', 'desc')->orderBy('id', 'desc')->first();

            if ( $row ) {
                $row = $row->toArray();
                $data[ $nomor ] = array(
                    'nomor' => $row['nomor'],
                    'nik' => $row[ $nik_field ],
                    'npwp' => $row['npwp'],
                    'nama_coretax' => $row['nama_coretax'],
                );
            }
        }

        ksort($data);

        $content['data'] = $data;
        $res_view_html = $this->load->view('import/nama_coretax/template', $content, true);

        $filename = 'template-import-nama-coretax-'.$tipe.'-'.str_replace('-', '', date('Y-m-d')).'.xls';

        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function upload()
    {
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;
        $tipe = $this->input->post('tipe');

        $map_tipe = $this->mapTipe();

        try {
            if ( empty($tipe) || !isset($map_tipe[$tipe]) ) {
                $this->result['message'] = 'Harap pilih Jenis Data terlebih dahulu.';
            } else if ( !empty($file) ) {
                $upload_path = FCPATH . "//uploads/import_file/";
                $file_name = $file['name'];
                $path_name = ubahNama($file_name, $upload_path);
                $moved = move_uploaded_file($file['tmp_name'], $upload_path.$path_name);

                if ( $moved ) {
                    $this->load->library('excel');
                    $objPHPExcel = PHPExcel_IOFactory::load($upload_path.$path_name);

                    $sheet_collection = $objPHPExcel->getSheetNames();

                    $_data_header = null;
                    $_data = null;
                    foreach ($sheet_collection as $sheet) {
                        $sheet_active = $objPHPExcel->setActiveSheetIndexByName($sheet);
                        $cell_collection = $sheet_active->getCellCollection();

                        foreach ($cell_collection as $cell) {
                            $column = $sheet_active->getCell($cell)->getColumn();
                            $row = $sheet_active->getCell($cell)->getRow();
                            $data_value = $sheet_active->getCell($cell)->getCalculatedValue();

                            if ( $row == 1 ) {
                                if ( !empty($data_value) ) {
                                    $_data_header['header'][$row][$column] = strtoupper(trim($data_value));
                                }
                            } else if ( isset($_data_header['header'][1][$column]) ) {
                                $_column_val = $_data_header['header'][1][$column];

                                if ( in_array($_column_val, array('NOMOR', 'NIK', 'NPWP', 'NAMA CORETAX')) ) {
                                    $_data['value'][$row][$_column_val] = trim($data_value);
                                }
                            }
                        }
                    }

                    $model_class = $map_tipe[$tipe]['model'];
                    $extra_where = $map_tipe[$tipe]['where'];
                    $nik_field = $map_tipe[$tipe]['nik_field'];

                    $jml_row = 0;
                    $jml_update = 0;
                    $not_found = array();

                    if ( !empty($_data['value']) ) {
                        foreach ($_data['value'] as $k_row => $v_row) {
                            $nomor = isset($v_row['NOMOR']) ? trim($v_row['NOMOR']) : null;
                            $nik = isset($v_row['NIK']) ? trim($v_row['NIK']) : null;
                            $npwp = isset($v_row['NPWP']) ? trim($v_row['NPWP']) : null;

                            $field = null;
                            $value = null;
                            $label = null;
                            if ( !empty($nomor) ) {
                                $field = 'nomor';
                                $value = $nomor;
                                $label = 'NOMOR:'.$nomor;
                            } else if ( !empty($nik) ) {
                                $field = $nik_field;
                                $value = $nik;
                                $label = 'NIK:'.$nik;
                            } else if ( !empty($npwp) ) {
                                $field = 'npwp';
                                $value = $npwp;
                                $label = 'NPWP:'.$npwp;
                            }

                            if ( !empty($field) ) {
                                $jml_row++;

                                $nama_coretax = isset($v_row['NAMA CORETAX']) ? trim($v_row['NAMA CORETAX']) : null;

                                $m_data = new $model_class();
                                $q_count = $m_data->where($field, $value);
                                foreach ($extra_where as $k_where => $v_where) {
                                    $q_count = $q_count->where($k_where, $v_where);
                                }
                                $jml_ditemukan = $q_count->count();

                                if ( $jml_ditemukan > 0 ) {
                                    $m_data = new $model_class();
                                    $q_update = $m_data->where($field, $value);
                                    foreach ($extra_where as $k_where => $v_where) {
                                        $q_update = $q_update->where($k_where, $v_where);
                                    }
                                    $q_update->update(array(
                                        'nama_coretax' => $nama_coretax ?: null
                                    ));

                                    $jml_update++;
                                } else {
                                    $not_found[] = $label;
                                }
                            }
                        }
                    }

                    if ( $jml_row > 0 ) {
                        $this->result['status'] = 1;
                        $this->result['message'] = 'Berhasil update Nama Coretax untuk '.$jml_update.' dari '.$jml_row.' data.';
                        if ( !empty($not_found) ) {
                            $this->result['message'] .= '<br>Tidak ditemukan: '.implode(', ', $not_found);
                        }
                    } else {
                        $this->result['message'] = 'Tidak ada data yang bisa diproses. Pastikan salah satu kolom NOMOR / NIK / NPWP terisi.';
                    }
                } else {
                    $this->result['message'] = 'Data gagal terupload.';
                }
            } else {
                $this->result['message'] = 'Harap pilih file terlebih dahulu.';
            }
        } catch (Exception $e) {
            $this->result['message'] = 'GAGAL : '.$e->getMessage();
        }

        display_json( $this->result );
    }

}
