<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderPakan extends Public_Controller {

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
                "assets/import/js/order-pakan.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/order-pakan.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import Order Pakan';
            $data['view'] = $this->load->view('import/order_pakan/index', $content, TRUE);
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

                    $jml_row = 0;

                    $_data = null;

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

                                            $jml_row++;
                                        } else if ( $_column_val == 'PERUSAHAAN' ) {
                                            $m_perusahaan = new \Model\Storage\Perusahaan_model();
                                            $d_perusahaan = $m_perusahaan->where('perusahaan', 'like', '%'.trim(strtoupper($val)).'%')->orderBy('version', 'desc')->first();

                                            if ( empty($d_perusahaan) ) {
                                                cetak_r('PERUSAHAAN : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_perusahaan->kode;
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
                                        } else if ( $_column_val == 'GUDANG' ) {
                                            $m_gdg = new \Model\Storage\Gudang_model();
                                            // $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('jenis', 'PAKAN')->orderBy('id', 'desc')->first();
                                            $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('jenis', 'PAKAN')->where('perusahaan', $_data['value'][$row]['PERUSAHAAN'])->orderBy('id', 'desc')->first();

                                            if ( empty($d_gdg) ) {
                                                cetak_r('GUDANG : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = !empty($d_gdg) ? $d_gdg->id : null;
                                                $_data['value'][$row]['ALAMAT'] = !empty($d_gdg) ? $d_gdg->alamat : null;
                                                $_data['value'][$row]['KIRIM KE'] = 'gudang';
                                            }

                                        } else if ( $_column_val == 'TGL ORDER' || $_column_val == 'TGL KIRIM' ) {
                                            $split = explode('/', $val);
                                            $year = $split[2]; 
                                            $month = (strlen($split[0]) < 2) ? '0'.$split[0] : $split[0];
                                            $day = (strlen($split[1]) < 2) ? '0'.$split[1] : $split[1];
                                            $tgl = $year.'-'.$month.'-'.$day;

                                            $_data['value'][$row][$_column_val] = $tgl;
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
                        $jml_data_insert = 0;
                        foreach ($_data['value'] as $k_val => $val) {
                            $m_gdg = new \Model\Storage\Gudang_model();
                            $d_gdg = $m_gdg->where('id', $val['GUDANG'])->with(['dUnit'])->first();

                            $kode_unit = null;
                            if ( $d_gdg ) {
                                $d_gdg = $d_gdg->toArray();
                                $kode_unit = $d_gdg['d_unit']['kode'];
                            }

                            // $m_op = new \Model\Storage\OrderPakan_model();
                            // $d_op = $m_op->where('no_order', 'like', '%'.$kode_unit.'%')->where('tgl_trans', $val['TGL ORDER'])->where('rcn_kirim', $val['TGL KIRIM'])->where('supplier', $val['SUPPLIER'])->first();

                            // if ( !$d_op ) {
                                $key = $val['SUPPLIER'].' - '.str_replace('-', '', $val['TGL ORDER']).' - '.$val['GUDANG'].' - '.$val['RIT'];
                                $data[ $key ]['SUPPLIER'] = $val['SUPPLIER'];
                                $data[ $key ]['TGL ORDER'] = $val['TGL ORDER'];
                                $data[ $key ]['TGL KIRIM'] = $val['TGL KIRIM'];
                                $data[ $key ]['DETAIL'][] = $val;

                                $jml_data_insert++;
                            // }
                        }

                        if ( !empty($data) ) {
                            if ( $jml_row == $jml_data_insert ) {
                                foreach ($data as $k_data => $v_data) {
                                    $m_op = new \Model\Storage\OrderPakan_model();

                                    $kode_unit = null;
                                    $id_kirim = null;
                                    $jenis_kirim = null;
                                    foreach ($v_data['DETAIL'] as $k => $val) {
                                        $id_kirim = $val['GUDANG'];
                                        $jenis_kirim = $val['KIRIM KE'];
                                    }

                                    if ( stristr($jenis_kirim, 'gudang') !== FALSE ) {
                                        $m_gdg = new \Model\Storage\Gudang_model();
                                        $d_gdg = $m_gdg->where('id', $id_kirim)->with(['dUnit'])->first();

                                        if ( $d_gdg ) {
                                            $d_gdg = $d_gdg->toArray();
                                            $kode_unit = $d_gdg['d_unit']['kode'];
                                        }
                                    }

                                    $nomor = $m_op->getNextNomor('OPK/'.$kode_unit);

                                    $m_op->no_order = $nomor;
                                    $m_op->tgl_trans = $v_data['TGL ORDER'];
                                    $m_op->rcn_kirim = $v_data['TGL KIRIM'];
                                    $m_op->supplier = $v_data['SUPPLIER'];
                                    $m_op->save();

                                    $id = $m_op->id;

                                    foreach ($v_data['DETAIL'] as $k => $val) {
                                        $m_opd = new \Model\Storage\OrderPakanDetail_model();
                                        $m_opd->id_header = $id;
                                        $m_opd->barang = $val['NAMA ITEM'];
                                        $m_opd->harga = $val['HARGA BELI'];
                                        $m_opd->harga_jual = $val['HARGA JUAL'];
                                        $m_opd->jumlah = $val['JUMLAH'];
                                        $m_opd->total = $val['HARGA BELI'] * $val['JUMLAH'];
                                        $m_opd->tujuan_kirim  = $val['KIRIM KE'];
                                        $m_opd->id_tujuan_kirim = $val['GUDANG'];
                                        $m_opd->alamat = $val['ALAMAT'];
                                        $m_opd->perusahaan = $val['PERUSAHAAN'];
                                        $m_opd->save();
                                    }

                                    $d_order_pakan = $m_op->where('id', $id)->with(['detail'])->first();

                                    $deskripsi_log_order_pakan = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                    Modules::run( 'base/event/save', $d_order_pakan, $deskripsi_log_order_pakan);
                                }

                                $this->result['status'] = 1;
                                $this->result['message'] = 'Data berhasil di injek.';
                            } else {
                                $this->result['message'] = 'Jumlah data tidak sama, harap cek kambali.<br>EXCEL : '.$jml_row.'<br>INJEK : '.$jml_data_insert;
                            }
                        }
                    }
                } else {
                    $this->result['message'] = 'Data gagal terupload.';
                }
            }
        } catch (Exception $e) {
            $this->result['message'] = 'GAGAL : '.$e->getMessage();
        }

        display_json( $this->result );
    }
}