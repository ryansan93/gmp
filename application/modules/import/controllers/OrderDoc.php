<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderDoc extends Public_Controller {

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
                "assets/import/js/order-doc.js"
            ));
            $this->add_external_css(array(
                "assets/import/css/order-doc.css"
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Import Order DOC';
            $data['view'] = $this->load->view('import/order_doc/index', $content, TRUE);
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

                                        if ( $_column_val == 'PERUSAHAAN' ) {
                                            $jml_row++;

                                            $m_perusahaan = new \Model\Storage\Perusahaan_model();
                                            $d_perusahaan = $m_perusahaan->where('perusahaan', 'like', '%'.trim(strtoupper($data_value)).'%')->orderBy('version', 'desc')->first();

                                            if ( empty($d_perusahaan) ) {
                                                cetak_r('PERUSAHAAN : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_perusahaan->kode;
                                            }

                                        } else if ( $_column_val == 'SUPPLIER' ) {
                                            $m_supl = new \Model\Storage\Supplier_model();
                                            $d_supl = $m_supl->where('nama', 'like', '%'.trim(strtoupper($data_value)).'%')->where('tipe', 'supplier')->orderBy('version', 'desc')->first();

                                            if ( empty($d_supl) ) {
                                                cetak_r('PERUSAHAAN : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_supl->nomor;
                                            }

                                        } else if ( $_column_val == 'JENIS DOC' ) {
                                            $m_brg = new \Model\Storage\Barang_model();
                                            $d_brg = $m_brg->where('nama', 'like', '%'.trim(strtoupper($data_value)).'%')->where('tipe', 'doc')->orderBy('version', 'desc')->first();

                                            if ( empty($d_brg) ) {
                                                cetak_r('JENIS DOC : '.trim(strtoupper($data_value)).' tidak ditemukan.', 1);
                                                $data_tidak_ditemukan++;

                                                break;
                                            } else {
                                                $_data['value'][$row][$_column_val] = $d_brg->kode;
                                            }

                                        } else if ( $_column_val == 'RENCANA TIBA' ) {
                                            $split = explode('/', $data_value);
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

                    $data = array();
                    $jml_data_insert = 0;
                    if ( !empty($_data) && $data_tidak_ditemukan == 0 ) {
                        foreach ($_data['value'] as $k_data => $v_data) {
                            $noreg = null;

                            $m_mitra = new \Model\Storage\Mitra_model();
                            $d_mitra = $m_mitra->where('nama', trim(strtoupper($v_data['MITRA'])))->where('mstatus', 1)->with(['dPerwakilans'])->orderBy('id', 'asc')->get();

                            $nama_mitra = null;
                            $nim = null;
                            $id_kdg = array();
                            if ( $d_mitra->count() > 0 ) {
                                $d_mitra = $d_mitra->toArray();
                                foreach ($d_mitra as $k_mitra => $v_mitra) {
                                    $nama_mitra = $v_mitra['nama'];

                                    $m_mm = new \Model\Storage\MitraMapping_model();
                                    $d_mm = $m_mm->select('id')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->get();

                                    if ( $d_mm->count() > 0 ) {
                                        $id_mm = $d_mm->toArray();
                                    }                   

                                    $m_kdg = new \Model\Storage\Kandang_model();
                                    $d_kdg = $m_kdg->whereIn('mitra_mapping', $id_mm)->where('kandang', $v_data['KANDANG'])->orderBy('id', 'desc')->get();

                                    if ( $d_kdg->count() > 0 ) {
                                        $d_kdg = $d_kdg->toArray();
                                        foreach ($d_kdg as $k_kdg => $v_kdg) {
                                            $m_wil = new \Model\Storage\Wilayah_model();
                                            $d_wil = $m_wil->where('id', $v_kdg['unit'])->first();

                                            if ( $d_wil['kode'] == trim(strtoupper($v_data['UNIT'])) ) {
                                                $d_mm = $m_mm->where('id', $v_kdg['mitra_mapping'])->orderBy('id', 'desc')->first();
                                                $nim = $d_mm->nim;

                                                array_push($id_kdg, $v_kdg['id']);
                                            }
                                        }
                                    }
                                }
                            }

                            if ( !empty($nim) && count($id_kdg) > 0 ) {
                                $m_rs = new \Model\Storage\RdimSubmit_model();
                                $d_rs = $m_rs->where('nim', $nim)->whereIn('kandang', $id_kdg)->where('tgl_docin', $v_data['RENCANA TIBA'])->orderBy('id', 'desc')->first();

                                if ( $d_rs ) {
                                    $noreg = $d_rs->noreg;
                                }
                            }

                            if ( !empty($noreg) ) {
                                $m_order_doc = new \Model\Storage\OrderDoc_model();
                                $now = $m_order_doc->getDate();

                                $d_order_doc = $m_order_doc->where('noreg', $noreg)->first();

                                if ( !$d_order_doc ) {
                                    $m_rs = new \Model\Storage\RdimSubmit_model();
                                    $d_rs = $m_rs->where('noreg', $noreg)->first();

                                    $data[] = array(
                                        'noreg' => $noreg,
                                        'supplier' => $v_data['SUPPLIER'],
                                        'item' => $v_data['JENIS DOC'],
                                        'jml_ekor' => $v_data['EKOR'],
                                        'jml_box' => ($v_data['EKOR'] / 100),
                                        'rencana_tiba' => $v_data['RENCANA TIBA'],
                                        'user_submit' => $this->userid,
                                        'tgl_submit' => prev_date($d_rs['tgl_docin']).' '.substr($now['waktu'], 11, 5),
                                        'keterangan' => '-',
                                        'version' => 1,
                                        'perusahaan' => $v_data['PERUSAHAAN'],
                                        'jns_box' => $v_data['JENIS BOX'],
                                        'harga' => $v_data['HARGA'],
                                        'total' => ($v_data['EKOR'] * $v_data['HARGA'])
                                    );

                                    $jml_data_insert++;                                    
                                }
                            }
                        }
                    }

                    if ( !empty($data) && $jml_row > 0 ) {
                        if ( $jml_row == $jml_data_insert ) {
                            foreach ($data as $k_data => $v_data) {
                                $m_order_doc = new \Model\Storage\OrderDoc_model();
                                $now = $m_order_doc->getDate();

                                $m_rs = new \Model\Storage\RdimSubmit_model();
                                $d_rs = $m_rs->where('noreg', $v_data['noreg'])->first();

                                $kode_unit = null;
                                if ( $d_rs ) {
                                    $m_kdg = new \Model\Storage\Kandang_model();
                                    $d_kdg = $m_kdg->where('id', $d_rs->kandang)->first();

                                    $m_wil = new \Model\Storage\Wilayah_model();
                                    $d_wil = $m_wil->where('id', $d_kdg->unit)->first();

                                    $kode_unit = $d_wil->kode;
                                }

                                $nomor = $m_order_doc->getNextNomor('ODC/'.$kode_unit);

                                $m_order_doc->id = $m_order_doc->getNextIdentity();
                                $m_order_doc->no_order = $nomor;
                                $m_order_doc->noreg = $v_data['noreg'];
                                $m_order_doc->supplier = $v_data['supplier'];
                                $m_order_doc->item = $v_data['item'];
                                $m_order_doc->jml_ekor = $v_data['jml_ekor'];
                                $m_order_doc->jml_box = $v_data['jml_box'];
                                $m_order_doc->rencana_tiba = $v_data['rencana_tiba'];
                                $m_order_doc->user_submit = $v_data['user_submit'];
                                $m_order_doc->tgl_submit = $v_data['tgl_submit'];
                                $m_order_doc->keterangan = $v_data['keterangan'];
                                $m_order_doc->version = $v_data['version'];
                                $m_order_doc->perusahaan = $v_data['perusahaan'];
                                $m_order_doc->jns_box = $v_data['jns_box'];
                                $m_order_doc->harga = $v_data['harga'];
                                $m_order_doc->total = $v_data['total'];
                                $m_order_doc->save();

                                $deskripsi_log_order_doc = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                Modules::run( 'base/event/save', $m_order_doc, $deskripsi_log_order_doc);

                            }

                            $this->result['status'] = 1;
                            $this->result['message'] = 'Data berhasil di injek.';
                        } else {
                            $this->result['message'] = 'Jumlah data tidak sama, harap cek kambali.<br>EXCEL : '.$jml_row.'<br>INJEK : '.$jml_data_insert;
                        }
                    } else {
                        $this->result['message'] = 'Tidak ada data yang di injek.';
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

    public function injek_by_terima()
    {
        $m_od = new \Model\Storage\OrderDoc_model();
        $d_noreg = $m_od->distinct('noreg')->select('noreg')->get()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_noreg_not_have_order = $m_rs->distinct('noreg')->select('noreg')->whereNotIn('noreg', $d_noreg)->get()->toArray();

        foreach ($d_noreg_not_have_order as $k_rs => $v_rs) {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $v_rs['noreg'])->with(['mitra'])->orderBy('id', 'desc')->first();

            $m_kdg = new \Model\Storage\Kandang_model();
            $d_kdg = $m_kdg->where('id', $d_rs['kandang'])->first();

            $m_wil = new \Model\Storage\Wilayah_model();
            $d_wil = $m_wil->where('id', $d_kdg->unit)->first();

            $m_td = new \Model\Storage\TerimaDoc_model();
            $d_td = $m_td->where('no_order', 'like', '%'.$d_wil->kode.'%')
                         ->whereBetween('kirim', [prev_date(substr($d_rs['tgl_docin'], 0, 10)).' 00:00:00.000', substr($d_rs['tgl_docin'], 0, 10).' 23:59:59.000'])
                         ->where('jml_ekor', $d_rs['populasi'])
                         ->first();

            if ( $d_td ) {
                cetak_r( $d_td->toArray() );
                cetak_r( 'ADA : '.$v_rs['noreg'] );
            } else {
                cetak_r( $d_rs->mitra->dMitra->nama.' | '.$v_rs['noreg'].' | '.$d_rs['tgl_docin'] );
            }
        }

        // cetak_r( $d_noreg_not_have_order );

        // $m_order_doc = new \Model\Storage\OrderDoc_model();
        // $now = $m_order_doc->getDate();

        // $noreg = '21150160101';

        // $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->where('noreg', $noreg)->with(['dKandang'])->first();

        // $m_order_doc = new \Model\Storage\OrderDoc_model();
        // $m_order_doc->id = $m_order_doc->getNextIdentity();
        // $m_order_doc->no_order = 'ODC/PSR/21/11005';
        // $m_order_doc->noreg = $noreg;
        // $m_order_doc->supplier = '19B005';
        // $m_order_doc->item = 'CK1907001';
        // $m_order_doc->jml_ekor = $d_rs['populasi'];
        // $m_order_doc->jml_box = ($d_rs['populasi'] / 100);
        // $m_order_doc->rencana_tiba = $d_rs['tgl_docin'];
        // $m_order_doc->user_submit = $this->userid;
        // $m_order_doc->tgl_submit = prev_date($d_rs['tgl_docin']).' '.substr($now['waktu'], 11, 5);
        // $m_order_doc->keterangan = '-';
        // $m_order_doc->version = 1;
        // $m_order_doc->perusahaan = 'P001';
        // $m_order_doc->jns_box = 'Plastik';
        // $m_order_doc->harga = 6655;
        // $m_order_doc->total = ($d_rs['populasi'] * 6655);
        // $m_order_doc->save();

        // $deskripsi_log_order_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        // Modules::run( 'base/event/save', $m_order_doc, $deskripsi_log_order_doc);

        // $m_td = new \Model\Storage\TerimaDoc_model();
        // $d_no_terima = $m_td->distinct('no_terima')->select('no_terima')->get()->toArray();

        // foreach ($d_no_terima as $k_td => $v_td) {
        //     $m_td = new \Model\Storage\TerimaDoc_model();
        //     $d_td = $m_td->where('no_terima', $v_td['no_terima'])->orderBy('version', 'desc')->first()->toArray();

        //     $split_no_order = explode('/', $d_td['no_order']);

        //     $m_order_doc = new \Model\Storage\OrderDoc_model();
        //     $m_order_doc->where('supplier', $d_td['supplier'])
        //                 ->where('rencana_tiba', substr($d_td['kirim'], 0, 10))
        //                 ->where('no_order', 'like', '%'.$split_no_order[0].'/'.$split_no_order[1].'%')
        //                 ->where('jml_ekor', $d_td['jml_ekor'])
        //                 ->where('jml_box', $d_td['jml_box'])
        //                 ->where('harga', $d_td['harga'])
        //                 ->where('total', $d_td['total'])
        //                 ->update(
        //                     array(
        //                         'no_order' => $d_td['no_order']
        //                     )
        //                 );

        //     // $m_order_doc = new \Model\Storage\OrderDoc_model();
        //     // $now = $m_order_doc->getDate();

        //     // $m_rs = new \Model\Storage\RdimSubmit_model();
        //     // $d_rs = $m_rs->where('noreg', $noreg)->with(['dKandang'])->first();

        //     // $m_order_doc = new \Model\Storage\OrderDoc_model();
        //     // $m_order_doc->id = $m_order_doc->getNextIdentity();
        //     // $m_order_doc->no_order = $v_td['no_order'];
        //     // $m_order_doc->noreg = $noreg;
        //     // $m_order_doc->supplier = $v_data['SUPPLIER'];
        //     // $m_order_doc->item = $v_data['JENIS DOC'];
        //     // $m_order_doc->jml_ekor = $v_data['EKOR'];
        //     // $m_order_doc->jml_box = ($v_data['EKOR'] / 100);
        //     // $m_order_doc->rencana_tiba = $v_data['RENCANA TIBA'];
        //     // $m_order_doc->user_submit = $this->userid;
        //     // $m_order_doc->tgl_submit = prev_date($d_rs['tgl_docin']).' '.substr($now['waktu'], 11, 5);
        //     // $m_order_doc->keterangan = '-';
        //     // $m_order_doc->version = 1;
        //     // $m_order_doc->perusahaan = $v_data['PERUSAHAAN'];
        //     // $m_order_doc->jns_box = $v_data['JENIS BOX'];
        //     // $m_order_doc->harga = $v_data['HARGA'];
        //     // $m_order_doc->total = ($v_data['EKOR'] * $v_data['HARGA']);
        //     // $m_order_doc->save();

        //     // $deskripsi_log_order_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        //     // Modules::run( 'base/event/save', $m_order_doc, $deskripsi_log_order_doc);
        // }
    }
}