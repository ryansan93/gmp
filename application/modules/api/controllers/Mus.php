<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class Mus extends API_Controller {

  /**
  * Constructor
  */
  function __construct() {
    parent::__construct ();
  }

  public function index()
  {
    $this->result['message'] = 'api index';
    $this->result['content'] = [];
    display_json($this->result);
  }

  public function get_do_by_date()
  {
    $tanggal = $this->input->get('date');

    $id_sopir = $this->input->get('id_sopir');
    $type = $this->input->get('type');
    $tanggal = $tanggal ?: date('Y-m-d');
    $id_sopir = $id_sopir ?: "";

    // NOTE: data do untuk RPA
    $m_do = new \Model\Storage\TransDo();
    $d_do = $m_do ->where('tanggal_do', $tanggal)
                  ->where('NoDO_DO', 'LIKE', $type . '%')
                  ->whereNotNull('TimeApprove_DO')
                  ->with(['data_spj'])
                  ->get();
                  // cetak_r($d_do->toArray());

    // NOTE: data do untuk bakul
    $m_dob = new \Model\Storage\Trans_SoDoBakul();
    $d_dob = $m_dob->where('Tgl_DoBakul', $tanggal)
                    ->where('No_DoBakul', 'LIKE', '%' . $type . '%')
                    ->with(['drpah', 'tim_panen'])
                    ->get();

    // NOTE: merge/concat kedua data do + dob
    $do = $this->mapping_data_do($d_do, $id_sopir);
    $dob = $this->mapping_data_do_bakul($d_dob);
    // $contents = $do;
    $contents = array_merge($do, $dob);

    $this->result['status'] = 1;
    $this->result['message'] = 'data do tanggal : ' . $tanggal;
    $this->result['content'] = $contents;
    display_json($this->result);
  }

   public function get_count_do() {
      $tanggal = $this->input->get('date');
      $id_sopir = $this->input->get('id_sopir');
      $type = $this->input->get('type');
      $tanggal = $tanggal ?: date('Y-m-d');
      $id_sopir = $id_sopir ?: "";

      $m_do = new \Model\Storage\TransDo();
      $d_do = $m_do->where('tanggal_do', $tanggal)
                   ->where('NoDO_DO', 'LIKE', $type . '%')
                   ->whereNotNull('TimeApprove_DO')
                   ->with(['data_spj'])
                   ->get();

      $contents = count($this->mapping_data_do($d_do, $id_sopir));

      $this->result['status'] = 1;
      $this->result['message'] = 'jumlah do tanggal : ' . $tanggal;
      $this->result['content'] = $contents;
      display_json($this->result);
   }

  public function get_do_between_date()
  {
    $start = $this->input->get('start') ?: date('Y-m-d');
    $end = $this->input->get('end')  ?: date('Y-m-d');
    $type = $this->input->get('type') ?: '';

    // NOTE: data do untuk RPA
    $m_do = new \Model\Storage\TransDo();
    $d_do = $m_do ->whereBetween('tanggal_do', [$start, $end])
                  ->where('NoDO_DO', 'LIKE', $type . '%')
                  ->whereNotNull('TimeApprove_DO')
                  ->with(['data_spj'])
                  ->get();
                  // cetak_r($d_do->toArray());

    // NOTE: data do untuk bakul
    $m_dob = new \Model\Storage\Trans_SoDoBakul();
    $d_dob = $m_dob ->whereBetween('Tgl_DoBakul', [$start, $end])
                    ->where('No_DoBakul', 'LIKE', $type . '%')
                    ->with(['drpah', 'pelanggan'])
                    ->get();
                    // cetak_r($d_dob->toArray(), 1);

    // NOTE: merge/concat kedua data do + dob
    $do = $this->mapping_data_do($d_do);
    $dob = $this->mapping_data_do_bakul($d_dob);
    $contents = array_merge($do, $dob);

    $this->result['status'] = 1;
    $this->result['message'] = 'data do tanggal : ' . $start . ' s.d ' . $end;
    $this->result['content'] = $contents;
    display_json($this->result);
  }

   public function login() {
      $username = $this->input->get('username');
      $password = $this->input->get('password');
      $device_id = $this->input->get('device_id');

      $m_user_mobile = new \Model\Storage\User_Mobile_Ekspedisi();
      $d_user_mobile = $m_user_mobile->where('username', $username)->where('password', $password)->where('device_id', $device_id)->get();

      $content = $d_user_mobile;

      $this->result['status'] = 1;
      $this->result['message'] = 'data login sopir';
      $this->result['content'] = $content;
      display_json($this->result);
   }

   public function login_fingerprint() {
      $device_id = $this->input->get('device_id');

      $m_user_mobile = new \Model\Storage\User_Mobile_Ekspedisi();
      $d_user_mobile = $m_user_mobile->where('device_id', $device_id)->with(['sopir'])->get();

      $content = $this->mapping_login($d_user_mobile);

      $this->result['status'] = 1;
      $this->result['message'] = 'data login sopir';
      $this->result['content'] = $content;
      display_json($this->result);
   }

   public function mapping_login($m_user) {
      $contents = array();
      foreach ($m_user as $m) {
         $content = array(
            "id_sopir" => $m->id_sopir,
            "nama" => $m->sopir->Nama_Sopir,
            "username" => $m->username,
            "device_id" => $m->device_id
         );

         array_push($contents, $content);
      }

      return $contents;
   }

   public function save_realisasi() {
      // $data = @file_get_contents("php://input");
      $data = $_GET['data_realisasi'];
      $data_json = json_decode($data);

      try {
         foreach ($data_json as $dj) {
            $m_realpanen = new \Model\Storage\Real_Panen();
            
            $m_realpanen->no_do = $dj->no_do;
            $m_realpanen->rit = $dj->rit;
            $m_realpanen->tgl_panen = $dj->tgl_panen;
            $m_realpanen->jam_mulai_panen = $dj->jam_mulai_panen;
            $m_realpanen->jam_selesai_panen = $dj->jam_selesai_panen;
            $m_realpanen->bb_rata = $dj->bb_rata;
            $m_realpanen->tara_total = $dj->tara_total;
            $m_realpanen->bruto = $dj->bruto;
            $m_realpanen->netto = $dj->netto;
            $m_realpanen->ekor = $dj->ekor;
            $m_realpanen->save();
         }

         $this->result['status'] = 1;
         $this->result['message'] = 'data login sopir';
         $this->result['content'] = true;
      } catch (\Illuminate\Database\QueryException $e) {
         $this->result['content'] = false;
      }
      display_json($this->result);
   }

   public function save_tara() {
      $data = $_GET['data_tara'];
      $data_json = json_decode($data);
      $no_do = $this->input->get('no_do');

      $m_realpanen = new \Model\Storage\Real_Panen();
      $d_realpanen = $m_realpanen->where('no_do', $no_do)->first();

      try {
         foreach ($data_json as $dj) {
            $m_tara = new \Model\Storage\Tara_Keranjang();

            $m_tara->id_realpanen = $d_realpanen->id_realpanen;
            $m_tara->urut = $dj->urut;
            $m_tara->tara_kg = $dj->tara_kg;
            $m_tara->save();
         }

         $this->result['status'] = 1;
         $this->result['message'] = 'data tara keranjang';
         $this->result['content'] = true;
      } catch (\Illuminate\Database\QueryException $e) {
         $this->result['content'] = false;
      }
      display_json($this->result);
   }

   public function save_timbang() {
      $data = $_GET['data_timbang'];
      $data_json = json_decode($data);
      $no_do = $this->input->get('no_do');

      $m_realpanen = new \Model\Storage\Real_Panen();
      $d_realpanen = $m_realpanen->where('no_do', $no_do)->first();

      try {
         foreach ($data_json as $dj) {
            $m_timbang = new \Model\Storage\Timbang_Ayam();

            $m_timbang->id_realpanen = $d_realpanen->id_realpanen;
            $m_timbang->urut = $dj->urut;
            $m_timbang->kg = $dj->kg;
            $m_timbang->ekor = $dj->ekor;
            $m_timbang->save();
         }

         $this->result['status'] = 1;
         $this->result['message'] = 'data timbang ayam';
         $this->result['content'] = true;
      } catch (\Illuminate\Database\QueryException $e) {
         $this->result['content'] = false;
      }
      display_json($this->result);
   }

  public function mapping_data_do($d_do, $id_sopir) {
      $contents = array();
      foreach ($d_do as $do) {
         if ($do->data_spj->data_sopir->sopir->ID_Sopir == $id_sopir) {
            $content = array(
               'tanggal' => $do->Tanggal_DO,
               'mitra' => $do->data_spj->data_konfirmasi->mitra->Nama_Forecast,
               'alamat_farm' => $do->data_spj->data_konfirmasi->mitra->AlmtKdg_Forecast,
               'no_do' => trim($do->NoDO_DO),
               'no_sj' => trim($do->NoSJ_DO),
               'nomor_do' => trim($do->NoDO_DO),
               'nomor_sj' => trim($do->NoSJ_DO),
               'noreg' => $do->data_spj->data_konfirmasi->NoReg_Konfir,
               'ekor' => $do->data_spj->Ekor_DetSPJ,
               'kg' => $do->data_spj->Kg_DetSPJ,
               'rit' => $do->data_spj->Rit_DetSPJ,
               'rcn_mulai_panen' => $do->data_detspj->WaktuPanen_DetSPJ,
               'rcn_selesai_panen' => $do->data_detspj->Selesai_DetSPJ,
               'jam_brngkt' => $do->data_spj->Brgkt_DetSPJ,
               'jam_tiba_farm' => $do->data_spj->DiFarm_DetSPJ,
               'mulai_panen' => $do->data_detspj->WaktuPanen_DetSPJ,
               'selesai_panen' => $do->data_detspj->Selesai_DetSPJ,
               'jam_tiba_rpa' => $do->data_spj->TibaRPA_DetSPJ,
               'jam_siap_potong' => $do->data_spj->Ready_DetSPJ,
               'nopol' => $do->data_spj->data_sopir->kendaraan_pakai->NoPol_Kend,
               'id_sopir' => $do->data_spj->data_sopir->sopir->ID_Sopir,
               'sopir' => $do->data_spj->data_sopir->sopir->Nama_Sopir,
               'nik_timpanen' => $do->data_spj->data_tim_panen->nik_timpanen,
               'nama_timpanen' => $do->data_spj->data_tim_panen->nama_timpanen,
               'status_timpanen' => $do->data_spj->data_tim_panen->status_timpanen,
               'rfid_panen' => $do->data_spj->data_tim_panen->rfid,
               'tipe' => 'DO'
            );
            array_push($contents, $content);
         } else if ($id_sopir == "") {
            $content = array(
               'tanggal' => $do->Tanggal_DO,
               'mitra' => $do->data_spj->data_konfirmasi->mitra->Nama_Forecast,
               'alamat_farm' => $do->data_spj->data_konfirmasi->mitra->AlmtKdg_Forecast,
               'no_do' => trim($do->NoDO_DO),
               'no_sj' => trim($do->NoSJ_DO),
               'nomor_do' => trim($do->NoDO_DO),
               'nomor_sj' => trim($do->NoSJ_DO),
               'noreg' => $do->data_spj->data_konfirmasi->NoReg_Konfir,
               'ekor' => $do->data_spj->Ekor_DetSPJ,
               'kg' => $do->data_spj->Kg_DetSPJ,
               'rit' => $do->data_spj->Rit_DetSPJ,
               'rcn_mulai_panen' => $do->data_detspj->WaktuPanen_DetSPJ,
               'rcn_selesai_panen' => $do->data_detspj->Selesai_DetSPJ,
               'jam_brngkt' => $do->data_spj->Brgkt_DetSPJ,
               'jam_tiba_farm' => $do->data_spj->DiFarm_DetSPJ,
               'mulai_panen' => $do->data_detspj->WaktuPanen_DetSPJ,
               'selesai_panen' => $do->data_detspj->Selesai_DetSPJ,
               'jam_tiba_rpa' => $do->data_spj->TibaRPA_DetSPJ,
               'jam_siap_potong' => $do->data_spj->Ready_DetSPJ,
               'nopol' => $do->data_spj->data_sopir->kendaraan_pakai->NoPol_Kend,
               'id_sopir' => $do->data_spj->data_sopir->sopir->ID_Sopir,
               'sopir' => $do->data_spj->data_sopir->sopir->Nama_Sopir,
               'nik_timpanen' => $do->data_spj->data_tim_panen->nik_timpanen,
               'nama_timpanen' => $do->data_spj->data_tim_panen->nama_timpanen,
               'status_timpanen' => $do->data_spj->data_tim_panen->status_timpanen,
               'rfid_panen' => $do->data_spj->data_tim_panen->rfid,
               'tipe' => 'DO'
            );

            array_push($contents, $content);
         }
      }
      return $contents;
  }

  public function mapping_data_do_bakul($d_dob)
  {
      $contents = array();
      foreach ($d_dob as $do) {
        $m_fc = new \Model\Storage\Trans_forecast();
        $d_fc = $m_fc->where('NoReg_Forecast', 'LIKE', '%'.$do->drpah->Noreg_DetRPAH.'%')->first();

        $content = array(
          'tanggal' => $do->Tgl_DoBakul,
          'mitra' => $do->drpah->NamaFarm_DetRPAH,
          'alamat_farm' => !empty($d_fc) ? $d_fc->AlmtKdg_Forecast : NULL,
          'no_do' => trim($do->No_DoBakul),
          'no_sj' => trim($do->No_SjBakul),
          'nomor_do' => trim($do->No_DoBakul),
          'nomor_sj' => trim($do->No_SjBakul),
          'noreg' => $do->drpah->Noreg_DetRPAH,
          'ekor' => $do->Ekor_SoDOBakul,
          'kg' => $do->Kg_SoDOBakul,
          'rit' => NULL,
          'rcn_mulai_panen' => NULL,
          'rcn_selesai_panen' => NULL,
          'jam_brngkt' => NULL,
          'jam_tiba_farm' => NULL,
          'mulai_panen' => NULL,
          'selesai_panen' => NULL,
          'jam_tiba_rpa' => NULL,
          'jam_siap_potong' => NULL,
          'nopol' => $do->Nopol_SoDoBakul,
          'id_sopir' => NULL,
          'sopir' => $do->Sopir_SoDoBakul,
          'nik_timpanen' => $do->tim_panen->nik_timpanen,
          'nama_timpanen' => $do->tim_panen->nama_timpanen,
          'status_panen' => $do->tim_panen->status_timpanen,
          'rfid_panen' => $do->tim_panen->rfid,
          'tipe' => 'BAKUL'
        );

        array_push($contents, $content);
      }
      return $contents;
  }

  public function get_sopir()
  {
    // NOTE: data sopir
    $m_sopir = new \Model\Storage\Sopir();
    $d_sopir = $m_sopir->with(['detail', 'detail_kend'])->get()->toArray();

    // NOTE: mapping sopir
    $sopir = $this->mapping_data_sopir($d_sopir);

    $this->result['status'] = 1;
    $this->result['message'] = 'data sopir';
    $this->result['content'] = $sopir;
    display_json($this->result);
  }

  public function mapping_data_sopir($d_sopir)
  {
      $contents = array();
      foreach ($d_sopir as $sopir) {
        if ( $sopir['detail_kend'][0]['Status_Sopir'] != 'PHK' ) {
          $content = array(
            'nopol' => $sopir['detail_kend'][0]['kendaraan']['NoPol_Kend'],
            'id_sopir' => $sopir['ID_Sopir'],
            'sopir' => $sopir['Nama_Sopir']
          );

          array_push($contents, $content);
        }
      }

      return $contents;
  }

  public function get_timpanen()
  {
    // NOTE: data tim panen
    $m_timpanen = new \Model\Storage\MsTimPanen();
    $d_timpanen = $m_timpanen ->where('status_timpanen', 1)
                              ->get()->toArray();

    // NOTE: mapping tim panen
    $timpanen = $this->mapping_data_timpanen($d_timpanen);

    $this->result['status'] = 1;
    $this->result['message'] = 'data tim panen';
    $this->result['content'] = $timpanen;
    display_json($this->result);
  }

  public function mapping_data_timpanen($d_timpanen)
  {
      $contents = array();
      foreach ($d_timpanen as $timpanen) {
        $content = array(
          'nik_timpanen' => $timpanen['nik_timpanen'],
          'timpanen' => $timpanen['nama_timpanen']
        );

        array_push($contents, $content);
      }

      return $contents;
  }   

   public function get_data_sopir() {
      $m_sopir = new \Model\Storage\Sopir();
      $d_sopir = $m_sopir->with('detail')->get()->toArray();

      $sopirs = $this->mapping_sopir($d_sopir);

      $this->result['status'] = 1;
      $this->result['message'] = 'daftar sopir';
      $this->result['content'] = $sopirs;
      display_json($this->result);
   }

   public function mapping_sopir($data_sopir) {
      $contents = array();
      foreach ($data_sopir as $sopir) {
         if ( $sopir['detail'][0]['Status_Sopir'] == 'AVAILABLE' ) {
            $content = array(
               'id_sopir' => $sopir['ID_Sopir'],
               'nama_sopir' => $sopir['Nama_Sopir']
            );

            array_push($contents, $content);
         }
      }

      return $contents;
   }

   public function register_user() {
      $nama = $this->input->get('nama');
      $username = $this->input->get('username');
      $password = $this->input->get('password');
      $device_id = $this->input->get('device_id');
      $nomor_telp = $this->input->get('nomor_telp');

      // $nama_baru = str_replace(search, replace, subject)

      $m_sopir = new \Model\Storage\Sopir();
      $d_sopir = $m_sopir->where("Nama_Sopir", $nama)->whereNotNull('id_absensi')->first();

      $m_user_mobile = new \Model\Storage\User_Mobile_Ekspedisi();
      $m_user_mobile->id_sopir = $d_sopir->ID_Sopir;
      $m_user_mobile->username = $username;
      $m_user_mobile->password = $password;
      $m_user_mobile->device_id = $device_id;
      $m_user_mobile->nomor_telp = $nomor_telp;
      $m_user_mobile->save();

      $this->result['status'] = 1;
      $this->result['message'] = 'Berhasil simpan user baru';
      $this->result['content'] = true;
      display_json($this->result);
   }

   public function get_mitra()
   {
      $m_rdim_submit = new \Model\Storage\RdimSubmit();
      $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra'])->get()->toArray();

      // $data = array();
      // if ( !empty($d_rdim_submit) ) {
      //   foreach ($d_rdim_submit as $k_val => $val) {
      //     $docin = substr($val['tgl_docin'], 0, 10);
      //     $today = date('Y-m-d');

      //     $umur = selisihTanggal($docin, $today);

      //     $data[ $val['id'] ] = array(
      //         'id' => $val['id'],
      //         'nama' => $val['mitra']['d_mitra']['nama'],
      //         'noreg' => $val['noreg'],
      //         'populasi' => $val['populasi'],
      //         'umur' => $umur
      //       );
      //   }
      // }

      cetak_r($d_rdim_submit);

      // $this->result['status'] = 1;
      // $this->result['message'] = 'Data mitra';
      // $this->result['content'] = $d_rdim_submit;
      // display_json($this->result);
   }

   public function tes()
   {
      $tgl_awal = '2017-01-23';
      $tgl_akhir = '2019-10-20';

      $sls = selisihTanggal($tgl_awal, $tgl_akhir);

      cetak_r($sls);
   }
}
