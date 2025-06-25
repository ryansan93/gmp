<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TerimaDoc extends Public_Controller {

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
                "assets/import/js/terima-doc.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/terima-doc.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import Terima DOC';
            $data['view'] = $this->load->view('import/terima_doc/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function uploadFile($file, $upload_path = null)
    {
        if ( empty($upload_path) ) {
            $upload_path = FCPATH . "//uploads/";
        }
        $file_name = $file['name'];
        $path_name = ubahNama($file_name, $upload_path);
        $file_path = $upload_path . $path_name;
        $moved = FALSE;

        $moved = move_uploaded_file($file['tmp_name'], $file_path );

        if( $moved ) {
            return array(
                'status' => 1,
                'message' => $file_name . " Successfully uploaded",
                'name' => $file_name,
                'path' => $path_name,
                'directory' => $file_path
            );
        } else {
            return ['status' => 0, 'message'=> "Not uploaded because of error #".$file["error"] ];
        }
    }

    public function upload()
    {
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        try {
            if ( !empty($file) ) {
                $upload_path = FCPATH . "//uploads/import_file/";
                $moved = $this->uploadFile($file, $upload_path);
                if ( $moved ) {
                    $path_name = $moved['path'];

                    //load the excel library
                    $this->load->library('excel');
                     
                    //read file from path
                    $objPHPExcel = PHPExcel_IOFactory::load($upload_path.$path_name);
                     
                    //get only the Cell Collection
                    $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                    $sheet_collection = $objPHPExcel->getSheetNames();

                    $data_tidak_ditemukan = 0;
                    $_data_header = null;
                    foreach ($sheet_collection as $sheet) {
                        $sheet_active = $objPHPExcel->setActiveSheetIndexByName($sheet);
                        $cell_collection = $sheet_active->getCellCollection();

                        foreach ($cell_collection as $cell) {
                            $column = $sheet_active->getCell($cell)->getColumn();
                            $row = $sheet_active->getCell($cell)->getRow();
                            $data_value = $sheet_active->getCell($cell)->getCalculatedValue();

                            if ( !empty($data_value) ) {
                                if ($row == 1) {
                                    $_data_header['header'][$row][$column] = strtoupper($data_value);
                                } else {
                                    if ( isset( $_data_header['header'][1][$column] ) ) {
                                        $_column_val = $_data_header['header'][1][$column];

                                        $val = $data_value;

                                        if ( $_column_val == 'NAMA ITEM' ) {
                                            $m_brg = new \Model\Storage\Barang_model();
                                            $d_brg = $m_brg->where('nama', trim(strtoupper($val)))->orderBy('id', 'desc')->first();

                                            if ( empty($d_brg) ) {
                                                cetak_r('BARANG : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_brg->kode;
                                            }
                                        } else if ( $_column_val == 'SUPPLIER' ) {
                                            $m_supl = new \Model\Storage\Supplier_model();
                                            $d_supl = $m_supl->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('tipe', 'supplier')->orderBy('version', 'desc')->first();

                                            if ( empty($d_supl) ) {
                                                cetak_r('SUPPLIER : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_supl->nomor;
                                            }
                                        } else if ( $_column_val == 'RENCANA TIBA' || $_column_val == 'TGL TERIMA' ) {
                                            if ( $val != '-' && !empty($val) ) {
                                                $split = explode('/', $val);
                                                $year = $split[2]; 
                                                $month = (strlen($split[0]) < 2) ? '0'.$split[0] : $split[0];
                                                $day = (strlen($split[1]) < 2) ? '0'.$split[1] : $split[1];
                                                $tgl = $year.'-'.$month.'-'.$day;

                                                $_data['value'][$row][$_column_val] = $tgl;
                                            }
                                        } else {
                                            $_data['value'][$row][$_column_val] = $val;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ( !empty($_data) && $data_tidak_ditemukan == 0 ) {
                        $data = null;
                        foreach ($_data['value'] as $k_val => $val) {
                            if ( isset($val['TGL TERIMA']) ) {
                                $key = $val['NO SJ'];
                                $data[ $key ]['RENCANA TIBA'] = $val['RENCANA TIBA'];
                                $data[ $key ]['TGL TERIMA'] = $val['TGL TERIMA'].' '.$val['TIME TERIMA'];
                                $data[ $key ]['SUPPLIER'] = $val['SUPPLIER'];
                                $data[ $key ]['MITRA'] = $val['MITRA'];
                                $data[ $key ]['UNIT'] = $val['UNIT'];
                                $data[ $key ]['KANDANG'] = $val['KANDANG'];
                                $data[ $key ]['NO SJ'] = $val['NO SJ'];
                                $data[ $key ]['NO POLISI'] = $val['NO POLISI'];
                                $data[ $key ]['NAMA ITEM'] = $val['NAMA ITEM'];
                                $data[ $key ]['JML EKOR'] = $val['JML EKOR'];
                                $data[ $key ]['JML BOX'] = ($val['JML EKOR'] / 100);
                                $data[ $key ]['BB'] = 0.4;
                                $data[ $key ]['KONDISI'] = 'BAIK';
                                $data[ $key ]['KETERANGAN'] = 'BAIK';

                                $m_mitra = new \Model\Storage\Mitra_model();
                                $d_mitra = $m_mitra->select('id')->where('nama', trim(strtoupper($val['MITRA'])))->get();

                                if ( $d_mitra->count() > 0 ) {
                                    $d_mitra = $d_mitra->toArray();
                                    $m_mm = new \Model\Storage\MitraMapping_model();
                                    $d_mm = $m_mm->whereIn('mitra', $d_mitra)->orderBy('id', 'asc')->get()->toArray();

                                    $cek_kandang = 0;
                                    $nim = null;
                                    $id_kdg = array();
                                    foreach ($d_mm as $k_mm => $v_mm) {
                                        $nim = $v_mm['nim'];
                                        $m_kdg = new \Model\Storage\Kandang_model();
                                        $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->where('kandang', $val['KANDANG'])->first();

                                        if ( $d_kdg ) {
                                            array_push($id_kdg, $d_kdg->id);
                                            $cek_kandang = 1;
                                        }
                                    }

                                    if ( $cek_kandang == 0 ) {
                                        cetak_r('KANDANG : '.$val['KANDANG'].' PETERNAK : '.trim(strtoupper($val['MITRA'])).' tidak ditemukan.', 1);
                                        $data_tidak_ditemukan++;

                                        break;
                                    }

                                    $tgl_docin = $val['RENCANA TIBA'].' 00:00:00.000';

                                    $m_rs = new \Model\Storage\RdimSubmit_model();
                                    $d_rs = $m_rs->where('nim', $nim)->where('tgl_docin', $tgl_docin)->whereIn('kandang', $id_kdg)->first();

                                    if ( $d_rs ) {
                                        $data[ $key ]['MITRA'] = $d_rs->noreg;
                                    } else {
                                        cetak_r('PETERNAK RDIM : '.trim(strtoupper($val['MITRA'])).' tidak ditemukan.', 1);

                                        break;
                                    }
                                } else {
                                    cetak_r('PETERNAK : '.trim(strtoupper($val['MITRA'])).' tidak ditemukan.', 1);

                                    break;
                                }

                                $no_order = null;
                                $harga = null;
                                $m_od = new \Model\Storage\OrderDoc_model();
                                $d_od = $m_od->where('noreg', $data[ $key ]['MITRA'])
                                             ->where('rencana_tiba', '<=', $data[ $key ]['RENCANA TIBA'])
                                             ->where('no_order', 'like', '%'.$data[ $key ]['UNIT'].'%')
                                             ->orderBy('rencana_tiba', 'desc')
                                             ->first();

                                if ( !$d_od ) {
                                    cetak_r('DATA ORDER DENGAN TUJUAN : '.trim(strtoupper($data[ $key ]['MITRA'])).' TGL DOCIN '.$data[ $key ]['RENCANA TIBA'].' UNIT '.$data[ $key ]['UNIT'].' tidak ditemukan.', 1);

                                    break;
                                } else {
                                    $no_order = $d_od->no_order;
                                    $harga = $d_od->harga;
                                }

                                $data[ $key ]['NO ORDER'] = $no_order;
                                $data[ $key ]['HARGA'] = $harga;
                            }
                        }

                        if ( !empty($data) ) {
                            foreach ($data as $k_data => $v_data) {
                                $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                                $now = $m_terima_doc->getDate();

                                $nomor = $m_terima_doc->getNextNomor();

                                $m_terima_doc->id = $m_terima_doc->getNextIdentity();
                                $m_terima_doc->no_terima = $nomor;
                                $m_terima_doc->no_order = $v_data['NO ORDER'];
                                $m_terima_doc->no_sj = $v_data['NO SJ'];
                                $m_terima_doc->nopol = $v_data['NO POLISI'];
                                $m_terima_doc->datang = $v_data['TGL TERIMA'];
                                $m_terima_doc->supplier = $v_data['SUPPLIER'];
                                $m_terima_doc->jml_ekor = $v_data['JML EKOR'];
                                $m_terima_doc->jml_box = $v_data['JML BOX'];
                                $m_terima_doc->user_submit = $this->userid;
                                $m_terima_doc->tgl_submit = $now['waktu'];
                                $m_terima_doc->kondisi = $v_data['KONDISI'];
                                $m_terima_doc->keterangan = $v_data['KETERANGAN'];
                                $m_terima_doc->version = 1;
                                $m_terima_doc->kirim = $v_data['RENCANA TIBA'];
                                $m_terima_doc->bb = $v_data['BB'];
                                $m_terima_doc->harga = $v_data['HARGA'];
                                $m_terima_doc->total = $v_data['JML EKOR'] * $v_data['HARGA'];
                                $m_terima_doc->save();

                                $deskripsi_log_terima_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                Modules::run( 'base/event/save', $m_terima_doc, $deskripsi_log_terima_doc);
                            }
                        }
                    }

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data berhasil di injek.';
                } else {
                    $this->result['message'] = 'Data gagal terupload.';
                }
            }
        } catch (Exception $e) {
            $this->result['message'] = 'GAGAL : '.$e->getMessage();
        }

        display_json( $this->result );
    }

    public function tgl()
    {
        $tgl = array(
            '15/10/2021',
            '11/10/2021'
        );

        foreach ($tgl as $k_tgl => $v_tgl) {
            $split = explode('/', $v_tgl);
            echo $split[1].'/'.$split[0].'/'.$split[2].'<br>';
        }
    }
}