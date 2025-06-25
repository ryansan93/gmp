<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TerimaVoadip extends Public_Controller {

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
                "assets/import/js/terima-voadip.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/terima-voadip.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import Terima Voadip';
            $data['view'] = $this->load->view('import/terima_voadip/index', $content, TRUE);
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
                                                $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val)).'%')->where('jenis', 'OBAT')->orderBy('id', 'desc')->first();

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
                                        } else if ( $_column_val == 'TGL DOCIN' || $_column_val == 'TGL KIRIM' || $_column_val == 'TGL TERIMA' ) {
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
                            $key = $val['NAMA ASAL'].' - '.$val['NAMA TUJUAN'].' - '.str_replace('-', '', $val['TGL KIRIM']).' - '.str_replace('-', '', $val['TGL TERIMA']).'-'.$val['KANDANG'];
                            $data[ $key ]['JENIS KIRIM'] = ($val['ASAL'] == 'gudang') ? 'opkg' : 'opks';
                            $data[ $key ]['NAMA ASAL'] = $val['NAMA ASAL'];
                            $data[ $key ]['TGL KIRIM'] = $val['TGL KIRIM'];
                            $data[ $key ]['TGL TERIMA'] = $val['TGL TERIMA'];
                            if ( stristr($val['TUJUAN'], 'gudang') !== FALSE ) {
                                $m_gdg = new \Model\Storage\Gudang_model();
                                $d_gdg = $m_gdg->where('nama', 'like', '%'.trim(strtoupper($val['NAMA TUJUAN'])).'%')->where('jenis', 'OBAT')->orderBy('id', 'desc')->first();

                                if ( empty($d_gdg) ) {
                                    cetak_r('GUDANG : '.trim(strtoupper($val)).' tidak ditemukan.', 1);

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
                                    $d_rs = $m_rs->where('nim', $nim)->where('tgl_docin', $tgl_docin)->whereIn('kandang', $id_kdg)->first();

                                    if ( $d_rs ) {
                                        $data[ $key ]['NAMA TUJUAN'] = $d_rs->noreg;
                                    } else {
                                        cetak_r('PETERNAK RDIM : '.trim(strtoupper($val)).' tidak ditemukan.', 1);

                                        break;
                                    }
                                } else {
                                    cetak_r('PETERNAK : '.trim(strtoupper($val)).' tidak ditemukan.', 1);

                                    break;
                                }
                            }
                            $data[ $key ]['JENIS TUJUAN'] = $val['TUJUAN'];
                            $data[ $key ]['EKSPEDISI'] = $val['EKSPEDISI'];
                            $data[ $key ]['NO POLISI'] = $val['NO POLISI'];
                            $data[ $key ]['SOPIR'] = $val['SOPIR'];
                            $data[ $key ]['UNIT'] = $val['UNIT'];
                            $data[ $key ]['DETAIL'][] = $val;

                            $id_kirim_voadip = null;
                            $m_kv = new \Model\Storage\KirimVoadip_model();
                            $d_kv = $m_kv->where('tgl_kirim', $val['TGL KIRIM'])
                                         ->where('jenis_kirim', $data[ $key ]['JENIS KIRIM'])
                                         ->where('asal', $data[ $key ]['NAMA ASAL'])
                                         ->where('jenis_tujuan', $data[ $key ]['JENIS TUJUAN'])
                                         ->where('tujuan', $data[ $key ]['NAMA TUJUAN'])
                                         ->where('ekspedisi', $data[ $key ]['EKSPEDISI'])
                                         ->where('no_polisi', $data[ $key ]['NO POLISI'])
                                         ->where('sopir', $data[ $key ]['SOPIR'])
                                         ->where('no_order', 'like', '%'.$data[ $key ]['UNIT'].'%')
                                         ->orderBy('id', 'desc')
                                         ->first();

                            if ( !$d_kv ) {
                                $tujuan = null;
                                $asal = null;
                                if ( stristr($data[ $key ]['JENIS TUJUAN'], 'gudang') ) {
                                    $m_gdg = new \Model\Storage\Gudang_model();
                                    $d_gdg = $m_gdg->where('id', $data[ $key ]['NAMA TUJUAN'])->where('jenis', 'OBAT')->orderBy('id', 'desc')->first();

                                    $tujuan = $d_gdg->nama;
                                } else {
                                    $m_rs = new \Model\Storage\RdimSubmit_model();
                                    $d_rs = $m_rs->where('noreg', $data[ $key ]['NAMA TUJUAN'])->first();

                                    $m_mm = new \Model\Storage\MitraMapping_model();
                                    $d_mm = $m_mm->select('mitra')->where('nim', $d_rs->nim)->get()->toArray();

                                    $m_mitra = new \Model\Storage\Mitra_model();
                                    $d_mitra = $m_mitra->whereIn('id', $d_mm)->orderBy('id', 'desc')->first();

                                    $tujuan = $d_mitra->nama;
                                }

                                if ( stristr($data[ $key ]['JENIS KIRIM'], 'opkg') !== FALSE ) {
                                    $m_gdg = new \Model\Storage\Gudang_model();
                                    $d_gdg = $m_gdg->where('id', $data[ $key ]['NAMA ASAL'])->where('jenis', 'OBAT')->orderBy('id', 'desc')->first();

                                    $asal = $d_gdg->nama;
                                } else if ( stristr($data[ $key ]['JENIS KIRIM'], 'opks') !== FALSE ) {
                                    $m_supl = new \Model\Storage\Supplier_model();
                                    $d_supl = $m_supl->where('nomor', $data[ $key ]['NAMA ASAL'])->where('tipe', 'supplier')->orderBy('version', 'desc')->first();

                                    $asal = $d_supl->nama;
                                }

                                cetak_r('DATA PENGIRIMAN DENGAN TUJUAN : '.trim(strtoupper($data[ $key ]['JENIS TUJUAN'])).'('.$tujuan.') DARI : '.trim(strtoupper($data[ $key ]['JENIS ASAL'])).'('.$asal.') tidak ditemukan.', 1);

                                break;
                            } else {
                                $id_kirim_voadip = $d_kv->id;
                            }

                            $data[ $key ]['ID KIRIM VOADIP'] = $id_kirim_voadip;
                        }

                        if ( !empty($data) ) {
                            foreach ($data as $k_data => $v_data) {
                                $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
                                $now = $m_terima_voadip->getDate();

                                $m_terima_voadip->id_kirim_voadip = $v_data['ID KIRIM VOADIP'];
                                $m_terima_voadip->tgl_trans = $now['waktu'];
                                $m_terima_voadip->tgl_terima = $v_data['TGL TERIMA'];
                                $m_terima_voadip->save();

                                $id_header = $m_terima_voadip->id;

                                foreach ($v_data['DETAIL'] as $k_detail => $v_detail) {
                                    $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();
                                    $m_terima_voadip_detail->id_header = $id_header;
                                    $m_terima_voadip_detail->item = $v_detail['NAMA ITEM'];
                                    $m_terima_voadip_detail->jumlah = $v_detail['JUMLAH'];
                                    $m_terima_voadip_detail->kondisi = $v_detail['KONDISI'];
                                    $m_terima_voadip_detail->save();
                                }

                                $d_terima_voadip = $m_terima_voadip->where('id', $id_header)->with(['detail'])->first();

                                $deskripsi_log_terima_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                Modules::run( 'base/event/save', $d_terima_voadip, $deskripsi_log_terima_voadip);

                                $this->result['status'] = 1;
                                $this->result['message'] = 'Data Penerimaan Voadip berhasil di simpan.';
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
}