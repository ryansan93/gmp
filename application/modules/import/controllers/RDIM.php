<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RDIM extends Public_Controller {

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
                "assets/import/js/rdim.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/rdim.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import RDIM';
            $data['view'] = $this->load->view('import/rdim/index', $content, TRUE);
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

                    $data_tidak_ditemukan = 0;
                    $_data = null;
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

                                        if ( $_column_val == 'TGL DOCIN' || $_column_val == 'KONTRAK' ) {
                                            $split = explode('/', $data_value);
                                            $year = $split[2]; 
                                            $month = (strlen($split[0]) < 2) ? '0'.$split[0] : $split[0];
                                            $day = (strlen($split[1]) < 2) ? '0'.$split[1] : $split[1];
                                            $tgl = $year.'-'.$month.'-'.$day;

                                            $_data['value'][$row][$_column_val] = $tgl;

                                            if ( $_column_val == 'KONTRAK' ) {
                                                $jml_row++;

                                                $m_pm = new \Model\Storage\PerwakilanMaping_model();
                                                $d_pm = $m_pm->where('id_pwk', $_data['value'][$row]['PERWAKILAN'])->get();

                                                if ( $d_pm->count() > 0 ) {
                                                    $d_pm = $d_pm->toArray();

                                                    foreach ($d_pm as $k_pm => $v_pm) {
                                                        $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                                                        $d_hbi = $m_hbi->where('id', $v_pm['id_hbi'])->first();

                                                        if ( $d_hbi ) {
                                                            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
                                                            $d_sk = $m_sk->where('id', $d_hbi->id_sk)->where('mulai', '<=', $tgl)->orderBy('version', 'desc')->first();

                                                            if ( $d_sk ) {
                                                                $m_pk = new \Model\Storage\PolaKerjasama_model();
                                                                $d_pk = $m_pk->where('id', $d_sk['pola'])->first();

                                                                $pola_kerjasama = $d_pk->item_code.' ('.trim($d_sk['item_pola']).')';

                                                                $_data['value'][$row]['FORMAT PB'] = $v_pm['id'];
                                                                $_data['value'][$row]['POLA MITRA'] = $pola_kerjasama;
                                                                $_data['value'][$row]['PERUSAHAAN'] = $d_sk['perusahaan'];
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                $_data['value'][$row][$_column_val] = $tgl;
                                            }
                                        } else if ( $_column_val == 'PERWAKILAN' ) {
                                            $nama_perwakilan = null;
                                            if ( stristr($data_value, 'jatim') !== FALSE ) {
                                                $nama_perwakilan = 'Jawa Timur '.substr(trim($data_value), -2);
                                            }
                                            if ( stristr($data_value, 'jateng') !== FALSE ) {
                                                $nama_perwakilan = 'Jawa Tengah '.substr(trim($data_value), -2);
                                            }

                                            $m_wilayah = new \Model\Storage\Wilayah_model();
                                            $d_wilayah = $m_wilayah->where('nama', 'like', '%'.$nama_perwakilan.'%')->where('jenis', 'PW')->first();

                                            if ( !$d_wilayah ) {
                                                cetak_r('PERWAKILAN : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_wilayah->id;
                                            }

                                        } else if ( $_column_val == 'VAKSIN' ) {
                                            $m_vaksin = new \Model\Storage\Vaksin_model();
                                            $d_vaksin = $m_vaksin->where('nama_vaksin', 'like', '%'.trim($data_value).'%')->first();

                                            if ( !$d_vaksin ) {
                                                cetak_r('VAKSIN : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_vaksin->id;
                                            }
                                        } else if ( $_column_val == 'PENGAWAS' || $_column_val == 'TIM SAMPLING' || $_column_val == 'TIM PANEN' || $_column_val == 'KOAR' ) {
                                            $m_karyawan = new \Model\Storage\Karyawan_model();
                                            $d_karyawan = $m_karyawan->where('nama', 'like', '%'.trim(strtolower($data_value)).'%')->first();

                                            if ( !$d_karyawan ) {
                                                cetak_r('KARYAWAN : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_karyawan->nik;
                                            }
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
                                        } else if ( $_column_val == 'MITRA' ) {
                                            $m_mitra = new \Model\Storage\Mitra_model();
                                            $d_mitra = $m_mitra->select('nomor')->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('perusahaan', $_data['value'][$row]['PERUSAHAAN'])->where('mstatus', 1)->first();

                                            if ( !$d_mitra ) {
                                                cetak_r('PETERNAK : '.trim(strtoupper($val)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $val;
                                            }
                                        } else if ( $_column_val == 'KANDANG' ) {
                                            $m_mitra = new \Model\Storage\Mitra_model();
                                            $d_mitra = $m_mitra->select('nomor')->where('nama', 'like', '%'.trim(strtoupper($_data['value'][$row]['MITRA'])).'%')->where('perusahaan', $_data['value'][$row]['PERUSAHAAN'])->where('mstatus', 1)->first();

                                            if ( $d_mitra ) {
                                                $m_mm = new \Model\Storage\MitraMapping_model();
                                                $d_mm = $m_mm->select('id')->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

                                                if ( $d_mm ) {
                                                    $m_kdg = new \Model\Storage\Kandang_model();
                                                    $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->where('kandang', $val)->first();
    
                                                    if ( $d_kdg ) {
                                                        $_data['value'][$row][$_column_val] = $val;
                                                    } else {
                                                        cetak_r('KANDANG : '.$val.' pada PETERNAK : '.trim(strtoupper($_data['value'][$row]['MITRA'])).' tidak ditemukan.', 1);
                                                        $data_tidak_ditemukan++;
    
                                                        break;
                                                    }
                                                } else {
                                                    cetak_r('KANDANG : '.$val.' pada PETERNAK : '.trim(strtoupper($_data['value'][$row]['MITRA'])).' tidak ditemukan.', 1);
                                                    $data_tidak_ditemukan++;

                                                    break;
                                                }
                                            } else {
                                                cetak_r('KANDANG : '.$val.' pada PETERNAK : '.trim(strtoupper($_data['value'][$row]['MITRA'])).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            }
                                        } else {
                                            $_data['value'][$row][$_column_val] = $data_value;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $data = null;
                    $jml_data_insert = 0;
                    if ( !empty($_data) && $data_tidak_ditemukan == 0 ) {
                        $idx = 0;
                        foreach ($_data['value'] as $k_data => $v_data) {
                            $noreg = null;

                            $m_mitra = new \Model\Storage\Mitra_model();
                            $d_mitra = $m_mitra->select('id')->where('nama', trim(strtoupper($v_data['MITRA'])))->where('mstatus', 1)->get();

                            $nim = null;
                            $id_kdg = array();
                            $grup = null;
                            if ( $d_mitra->count() > 0 ) {
                                $d_mitra = $d_mitra->toArray();

                                $m_mmp = new \Model\Storage\MitraMapping_model();
                                $d_mmp = $m_mmp->select('id')->whereIn('mitra', $d_mitra)->get()->toArray();

                                $m_kandang = new \Model\Storage\Kandang_model();
                                $d_kandang = $m_kandang->whereIn('mitra_mapping', $d_mmp)->where('kandang', $v_data['KANDANG'])->where('status', 1)->get();

                                $cek_kandang = 1;

                                if ( $d_kandang->count() > 0 ) {
                                    $d_kandang = $d_kandang->toArray();
                                    foreach ($d_kandang as $k_kdg => $v_kdg) {
                                        $m_wil = new \Model\Storage\Wilayah_model();
                                        $d_wil = $m_wil->where('id', $v_kdg['unit'])->first();

                                        if ( $d_wil['kode'] == trim(strtoupper($v_data['UNIT'])) ) {
                                            $m_mm = new \Model\Storage\MitraMapping_model();
                                            $d_mm = $m_mm->where('id', $v_kdg['mitra_mapping'])->first();

                                            array_push($id_kdg, $v_kdg['id']);

                                            $nim = trim($d_mm->nim);
                                            $grup = $v_kdg['grup'];

                                            $cek_kandang = 0;
                                        }
                                    }
                                } else {
                                    $cek_kandang = 1;
                                }

                                if ( $cek_kandang == 1 ) {
                                    cetak_r('MITRA : '.trim(strtoupper($v_data['MITRA'])).' KANDANG : '.$v_data['KANDANG'].' tidak ditemukan.', 1);
                                    $data_tidak_ditemukan++;

                                    break;
                                }
                            } else {
                                cetak_r('MITRA : '.trim(strtoupper($v_data['MITRA'])).' tidak ditemukan.', 1);
                                $data_tidak_ditemukan++;

                                break;
                            }

                            $m_rs = new \Model\Storage\RdimSubmit_model();
                            $d_rs = $m_rs->where('nim', $nim)->whereIn('kandang', $id_kdg)->where('tgl_docin', $v_data['TGL DOCIN'])->first();
                            if ( empty($d_rs) ) {
                                $data[$idx]['TGL DOCIN'] = $v_data['TGL DOCIN'];
                                $data[$idx]['NIM'] = $nim;
                                $data[$idx]['KANDANG'] = $id_kdg;
                                $data[$idx]['POPULASI'] = $v_data['POPULASI'];
                                $data[$idx]['PENGAWAS'] = $v_data['PENGAWAS'];
                                $data[$idx]['TIM SAMPLING'] = $v_data['TIM SAMPLING'];
                                $data[$idx]['TIM PANEN'] = $v_data['TIM PANEN'];
                                $data[$idx]['KOAR'] = $v_data['KOAR'];
                                $data[$idx]['FORMAT PB'] = $v_data['FORMAT PB'];
                                $data[$idx]['POLA MITRA'] = $v_data['POLA MITRA'];
                                $data[$idx]['GROUP'] = $grup;
                                $data[$idx]['PERUSAHAAN'] = $v_data['PERUSAHAAN'];
                                $data[$idx]['VAKSIN'] = $v_data['VAKSIN'];

                                $idx++;

                                $jml_data_insert++;
                            }
                        }
                    }

                    if ( !empty($data) && $data_tidak_ditemukan == 0 ) {
                        if ( $jml_row == $jml_data_insert ) {
                            foreach ($data as $k_data => $v_data) {
                                $_nim = $v_data['NIM'];

                                // $m_rs = new \Model\Storage\RdimSubmit_model();
                                // $d_rs = $m_rs->where('nim', $_nim)->whereIn('kandang', $v_data['KANDANG'])->where('tgl_docin', $v_data['TGL DOCIN'])->first();
                                // if ( empty($d_rs) ) {
                                $id_kdg = null;
                                foreach ($v_data['KANDANG'] as $k_kdg => $v_kdg) {
                                    if ( empty($id_kdg) ) {
                                        $id_kdg = $v_kdg;
                                    } else {
                                        if ( $id_kdg < $v_kdg ) {
                                            $id_kdg = $v_kdg;
                                        }
                                    }
                                }

                                $m_rdims = new \Model\Storage\RdimSubmit_model();
                                $d_rdims = $m_rdims->where('nim', $_nim)
                                                   ->whereIn('kandang', $v_data['KANDANG'])
                                                   ->orderBy('tgl_docin', 'DESC')
                                                   ->first();

                                $m_kandang = new \Model\Storage\Kandang_model();
                                $d_kandang = $m_kandang->where('id', $id_kdg)->first();

                                if ( empty($d_rdims) ) {
                                    $str_kandang = null;
                                    if ( strlen(number_format($d_kandang->kandang)) < 2) {
                                        $str_kandang = '0'.number_format($d_kandang->kandang);
                                    } else {
                                        $str_kandang = $d_kandang->kandang;
                                    }

                                    $noreg = trim($_nim) . '01' . $str_kandang;
                                } else {
                                    $_noreg = $d_rdims->noreg;
                                    $jml_nim = strlen(trim($_nim));

                                    $_siklus = trim(substr($_noreg, $jml_nim, 2));
                                    $siklus = $_siklus + 1;

                                    $str_siklus = null;
                                    if ( strlen($siklus) == 1) {
                                        $str_siklus = '0'.$siklus;
                                    } else {
                                        $str_siklus = $siklus;
                                    }

                                    $str_kandang = null;
                                    if ( strlen(number_format($d_kandang->kandang)) < 2) {
                                        $str_kandang = '0'.number_format($d_kandang->kandang);
                                    } else {
                                        $str_kandang = $d_kandang->kandang;
                                    }

                                    $noreg = $_nim . $str_siklus . $str_kandang;
                                }

                                $strtotime = strtotime($v_data['TGL DOCIN']);
                                $weekStartDate = date('Y-m-d',strtotime("last Sunday", $strtotime));
                                $weekEndDate = date('Y-m-d',strtotime("first Saturday", $strtotime));

                                $week = array(
                                    'start_date' => $weekStartDate,
                                    'end_date' => $weekEndDate,
                                );

                                // NOTE: 1. save header -> rdim
                                $m_rdim = new \Model\Storage\Rdim_model();
                                $d_rdim = $m_rdim->where('mulai', $weekStartDate)->where('selesai', $weekEndDate)->first();

                                if ( !$d_rdim ) {
                                    $m_rdim = new \Model\Storage\Rdim_model();
                                    $next_doc_number = $m_rdim->getNextDocNum('ADM/RDIM');

                                    $m_rdim->nomor = $next_doc_number;
                                    $m_rdim->mulai = $weekStartDate;
                                    $m_rdim->selesai = $weekEndDate;
                                    $m_rdim->g_status = getStatus('submit');
                                    $m_rdim->save();

                                    $deskripsi_log = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                    Modules::run( 'base/event/save', $m_rdim, $deskripsi_log);
                                    $id_rdim = $m_rdim->id;
                                } else {
                                    $id_rdim = $d_rdim->id;
                                }

                                $m_rs = new \Model\Storage\RdimSubmit_model();
                                $m_rs->id_rdim = $id_rdim;
                                $m_rs->tgl_docin = $v_data['TGL DOCIN'];
                                $m_rs->nim = $_nim;
                                $m_rs->kandang = $id_kdg;
                                $m_rs->populasi = $v_data['POPULASI'];
                                $m_rs->noreg = $noreg;
                                $m_rs->pengawas = $v_data['PENGAWAS'];
                                $m_rs->sampling = $v_data['TIM SAMPLING'];
                                $m_rs->tim_panen = $v_data['TIM PANEN'];
                                $m_rs->koar = $v_data['KOAR'];
                                $m_rs->format_pb = $v_data['FORMAT PB'];
                                $m_rs->pola_mitra = $v_data['POLA MITRA'];
                                $m_rs->grup = $v_data['GROUP'];
                                $m_rs->status = 1;
                                $m_rs->tipe_densitas = null;
                                $m_rs->perusahaan = $v_data['PERUSAHAAN'];
                                $m_rs->vaksin = $v_data['VAKSIN'];
                                $m_rs->save();

                                $deskripsi_log = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                Modules::run( 'base/event/save', $m_rs, $deskripsi_log);
                                // }
                            }

                            $this->result['status'] = 1;
                            $this->result['message'] = 'Data berhasil di injek.';
                        } else {
                            $this->result['message'] = 'Jumlah data tidak sama, harap cek kambali.<br>EXCEL : '.$jml_row.'<br>INJEK : '.$jml_data_insert;
                        }
                    } else {
                        $this->result['message'] = 'Cek kembali data yang anda masukkan.';
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