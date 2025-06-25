<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KirimPakan extends Public_Controller {

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
                "assets/import/js/kirim-pakan.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/kirim-pakan.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import Kirim Pakan';
            $data['view'] = $this->load->view('import/kirim_pakan/index', $content, TRUE);
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
                                        } else if ( $_column_val == 'NAMA ASAL' ) {
                                            if ( stristr($_data['value'][$row]['ASAL'], 'gudang') !== FALSE ) {
                                                $m_gdg = new \Model\Storage\Gudang_model();
                                                $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('jenis', 'PAKAN')->orderBy('id', 'desc')->first();

                                                if ( empty($d_gdg) ) {
                                                    cetak_r('GUDANG : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                    $data_tidak_ditemukan++;

                                                    break;
                                                } else {
                                                    $_data['value'][$row][$_column_val] = !empty($d_gdg) ? $d_gdg->id : null;
                                                }
                                            } else if ( stristr($_data['value'][$row]['ASAL'], 'supplier') !== FALSE ) {
                                                $m_supl = new \Model\Storage\Supplier_model();
                                                $d_supl = $m_supl->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('tipe', 'supplier')->orderBy('version', 'desc')->first();

                                                if ( empty($d_supl) ) {
                                                    cetak_r('SUPPLIER : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                    $data_tidak_ditemukan++;

                                                    break;
                                                } else {
                                                    $_data['value'][$row][$_column_val] = $d_supl->nomor;
                                                }
                                            }
                                        } else if ( $_column_val == 'TGL DOCIN' || $_column_val == 'TGL KIRIM' ) {
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

                    $jml_row = 0;
                    if ( !empty($_data) && $data_tidak_ditemukan == 0 ) {
                        $data = null;
                        foreach ($_data['value'] as $k_val => $val) {
                            $jml_row++;

                            $key = $val['NAMA ASAL'].' - '.$val['NAMA TUJUAN'].' - '.str_replace('-', '', $val['TGL KIRIM']).' - '.$val['KANDANG'].' - '.$val['NO POLISI'];
                            if ( !isset($data[ $key ]) ) {
                                $data[ $key ]['TGL KIRIM'] = $val['TGL KIRIM'];
                                $data[ $key ]['JENIS KIRIM'] = ($val['ASAL'] == 'gudang') ? 'opkg' : 'opks';
                                $data[ $key ]['NAMA ASAL'] = $val['NAMA ASAL'];
                                if ( stristr($val['TUJUAN'], 'gudang') !== FALSE ) {
                                    $m_gdg = new \Model\Storage\Gudang_model();
                                    $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val['NAMA TUJUAN'])).'%')->where('jenis', 'PAKAN')->orderBy('id', 'desc')->first();

                                    if ( empty($d_gdg) ) {
                                        cetak_r('GUDANG : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                        $data_tidak_ditemukan++;

                                        break;
                                    } else {
                                        $data[ $key ]['NAMA TUJUAN'] = !empty($d_gdg) ? $d_gdg->id : null;
                                    }
                                } else if ( stristr($val['TUJUAN'], 'peternak') !== FALSE ) {
                                    $m_mitra = new \Model\Storage\Mitra_model();
                                    $d_mitra = $m_mitra->select('nomor')->where('nama', trim(strtoupper($val['NAMA TUJUAN'])))->where('mstatus', 1)->get();

                                    if ( $d_mitra->count() > 0 ) {
                                        $d_mitra = $d_mitra->toArray();
                                        $m_mm = new \Model\Storage\MitraMapping_model();
                                        $d_mm = $m_mm->select('id')->whereIn('nomor', $d_mitra)->orderBy('id', 'asc')->get()->toArray();

                                        $m_kdg = new \Model\Storage\Kandang_model();
                                        $d_kdg = $m_kdg->whereIn('mitra_mapping', $d_mm)->where('kandang', $val['KANDANG'])->get()->toArray();

                                        $cek_kandang = 0;
                                        $nim = null;
                                        $id_kdg = array();

                                        foreach ($d_kdg as $k_kdg => $v_kdg) {
                                            $m_wil = new \Model\Storage\Wilayah_model();
                                            $d_wil = $m_wil->where('id', $v_kdg['unit'])->first();

                                            if ( $d_wil['kode'] == trim(strtoupper($val['UNIT'])) ) {
                                                $d_mm = $m_mm->where('id', $v_kdg['mitra_mapping'])->orderBy('id', 'asc')->first();

                                                $nim = $d_mm->nim;

                                                array_push($id_kdg, $v_kdg['id']);
                                                $cek_kandang = 1;
                                            }
                                        }

                                        if ( $cek_kandang == 0 ) {
                                            cetak_r('KANDANG : '.$val['KANDANG'].' PETERNAK : '.trim(strtoupper($val['NAMA TUJUAN'])).' tidak ditemukan.', 1);
                                            $data_tidak_ditemukan++;

                                            break;
                                        }

                                        $tgl_docin = $val['TGL DOCIN'].' 00:00:00.000';

                                        $m_rs = new \Model\Storage\RdimSubmit_model();
                                        $d_rs = $m_rs->where('nim', $nim)->where('tgl_docin', $tgl_docin)->whereIn('kandang', $id_kdg)->orderBy('id', 'desc')->first();

                                        if ( $d_rs ) {
                                            $data[ $key ]['NAMA TUJUAN'] = $d_rs->noreg;
                                        } else {
                                            // cetak_r($id_kdg);
                                            // cetak_r('NIM : '.trim(strtoupper($nim)).', TGL DOCIN : '.$tgl_docin.', KANDANG : '.$val['KANDANG'].' tidak ditemukan.');
                                            cetak_r('PETERNAK RDIM : '.trim(strtoupper($val['NAMA TUJUAN'])).' tidak ditemukan.', 1);
                                            $data_tidak_ditemukan++;

                                            break;
                                        }
                                    } else {
                                        cetak_r('PETERNAK : '.trim(strtoupper($val['NAMA TUJUAN'])).' tidak ditemukan.', 1);
                                        $data_tidak_ditemukan++;

                                        break;
                                    }
                                }

                                $data[ $key ]['JENIS TUJUAN'] = $val['TUJUAN'];
                                $data[ $key ]['EKSPEDISI'] = $val['EKSPEDISI'];
                                $data[ $key ]['NO POLISI'] = $val['NO POLISI'];
                                $data[ $key ]['SOPIR'] = $val['SOPIR'];
                                $data[ $key ]['ONGKOS ANGKUT'] = isset($val['ONGKOS ANGKUT']) ? $val['ONGKOS ANGKUT'] : 0;
                                $data[ $key ]['UNIT'] = $val['UNIT'];
                                $data[ $key ]['NO SJ'] = isset($val['NO SJ']) ? $val['NO SJ'] : '-';
                                // $data[ $key ]['NO SJ'] = $val['NO SJ'];
                                $data[ $key ]['DETAIL'][] = $val;
                            } else {
                                $data[ $key ]['DETAIL'][] = $val;
                            }
                        }

                        $data_injek = array();
                        $jml_data_insert = 0;
                        if ( !empty($data) && $data_tidak_ditemukan == 0 ) {
                            foreach ($data as $k_data => $v_data) {
                                $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
                                $now = $m_kirim_pakan->getDate();

                                $no_order = null;
                                $no_sj = null;
                                $kode_unit = null;
                                if ( $v_data['JENIS KIRIM'] == 'opks' ) {
                                    $m_op = new \Model\Storage\OrderPakan_model();
                                    $d_op = $m_op->where('rcn_kirim', $v_data['TGL KIRIM'])->where('supplier', $v_data['NAMA ASAL'])->where('no_order', 'like', '%'.$v_data['UNIT'].'%')->orderBy('id', 'desc')->first();

                                    $no_order = !empty($d_op) ? $d_op->no_order : null;
                                    $no_sj = $v_data['NO SJ'];
                                    $kode_unit = 1;
                                } else {
                                    $kode_unit = $v_data['UNIT'];

                                    $no_order = $m_kirim_pakan->getNextIdOrder('OP/'.$kode_unit);
                                    $no_sj = $m_kirim_pakan->getNextIdSj('SJ/'.$kode_unit);
                                }

                                if ( !empty($no_order) ) {
                                    $data_injek_detail = array();
                                    foreach ($v_data['DETAIL'] as $k_det => $v_det) {
                                        $data_injek_detail[] = array(
                                            'item' => $v_det['NAMA ITEM'],
                                            'jumlah' => $v_det['JUMLAH'],
                                            'kondisi' => $v_det['KONDISI'],
                                        );

                                        $jml_data_insert++;
                                    }

                                    $data_injek[] = array(
                                        'tgl_trans' => $now['waktu'],
                                        'tgl_kirim' => $v_data['TGL KIRIM'],
                                        'no_order' => $no_order,
                                        'jenis_kirim' => $v_data['JENIS KIRIM'],
                                        'asal' => $v_data['NAMA ASAL'],
                                        'jenis_tujuan' => $v_data['JENIS TUJUAN'],
                                        'tujuan' => $v_data['NAMA TUJUAN'],
                                        'ekspedisi' => $v_data['EKSPEDISI'],
                                        'no_polisi' => $v_data['NO POLISI'],
                                        'sopir' => $v_data['SOPIR'],
                                        'no_sj' => $no_sj,
                                        'ongkos_angkut' => $v_data['ONGKOS ANGKUT'],
                                        'detail' => $data_injek_detail
                                    );
                                }
                            }

                            if ( count($data_injek) > 0 ) {
                                if ( $jml_row == $jml_data_insert ) {
                                    foreach ($data_injek as $key => $val) {
                                        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();

                                        $m_kirim_pakan->tgl_trans = $val['tgl_trans'];
                                        $m_kirim_pakan->tgl_kirim = $val['tgl_kirim'];
                                        $m_kirim_pakan->no_order = $val['no_order'];
                                        $m_kirim_pakan->jenis_kirim = $val['jenis_kirim'];
                                        $m_kirim_pakan->asal = $val['asal'];
                                        $m_kirim_pakan->jenis_tujuan = $val['jenis_tujuan'];
                                        $m_kirim_pakan->tujuan = $val['tujuan'];
                                        $m_kirim_pakan->ekspedisi = $val['ekspedisi'];
                                        $m_kirim_pakan->no_polisi = $val['no_polisi'];
                                        $m_kirim_pakan->sopir = $val['sopir'];
                                        $m_kirim_pakan->no_sj = $val['no_sj'];
                                        $m_kirim_pakan->ongkos_angkut = $val['ongkos_angkut'];
                                        $m_kirim_pakan->save();

                                        $id_header = $m_kirim_pakan->id;

                                        foreach ($val['detail'] as $k_detail => $v_detail) {
                                            $m_kirim_pakan_detail = new \Model\Storage\KirimPakanDetail_model();
                                            $m_kirim_pakan_detail->id_header = $id_header;
                                            $m_kirim_pakan_detail->item = $v_detail['item'];
                                            $m_kirim_pakan_detail->jumlah = $v_detail['jumlah'];
                                            $m_kirim_pakan_detail->kondisi = $v_detail['kondisi'];
                                            $m_kirim_pakan_detail->save();
                                        }

                                        $d_kirim_pakan = $m_kirim_pakan->where('id', $id_header)->with(['detail'])->first();

                                        $deskripsi_log_kirim_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                        Modules::run( 'base/event/save', $d_kirim_pakan, $deskripsi_log_kirim_pakan);
                                    }

                                    $this->result['status'] = 1;
                                    $this->result['message'] = 'Data berhasil di injek.';
                                } else {
                                    $this->result['message'] = 'Jumlah data tidak sama, harap cek kambali.<br>EXCEL : '.$jml_row.'<br>INJEK : '.$jml_data_insert;
                                }
                            } else {
                                $this->result['message'] = 'Tidak ada data yang akan di injek.';
                            }
                        } else {
                            $this->result['message'] = 'Tidak ada data yang akan di injek.';
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