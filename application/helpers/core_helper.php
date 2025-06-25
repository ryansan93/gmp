<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Outputs an array in a user-readable JSON format
 *
 * @param array $array
 */
if ( ! function_exists('display_json'))
{
    function display_json($array)
    {
        $data = json_indent($array);

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');

        echo $data;
    }
}

/**
 * Convert an array to a user-readable JSON string
 *
 * @param array $array - The original array to convert to JSON
 * @return string - Friendly formatted JSON string
 */
if ( ! function_exists('json_indent'))
{
    function json_indent($array = array())
    {
        // make sure array is provided
        if (empty($array))
            return NULL;

        //Encode the string
        $json = json_encode($array);

        $result        = '';
        $pos           = 0;
        $str_len       = strlen($json);
        $indent_str    = '  ';
        $new_line      = "\n";
        $prev_char     = '';
        $out_of_quotes = true;

        for ($i = 0; $i <= $str_len; $i++)
        {
            // grab the next character in the string
            $char = substr($json, $i, 1);

            // are we inside a quoted string?
            if ($char == '"' && $prev_char != '\\')
            {
                $out_of_quotes = !$out_of_quotes;
            }
            // if this character is the end of an element, output a new line and indent the next line
            elseif (($char == '}' OR $char == ']') && $out_of_quotes)
            {
                $result .= $new_line;
                $pos--;

                for ($j = 0; $j < $pos; $j++)
                {
                    $result .= $indent_str;
                }
            }

            // add the character to the result string
            $result .= $char;

            // if the last character was the beginning of an element, output a new line and indent the next line
            if (($char == ',' OR $char == '{' OR $char == '[') && $out_of_quotes)
            {
                $result .= $new_line;

                if ($char == '{' OR $char == '[')
                {
                    $pos++;
                }

                for ($j = 0; $j < $pos; $j++)
                {
                    $result .= $indent_str;
                }
            }

            $prev_char = $char;
        }

        // return result
        return $result . $new_line;
    }
}


/**
 * Save data to a CSV file
 *
 * @param array $array
 * @param string $filename
 * @return bool
 */
if ( ! function_exists('array_to_csv'))
{
    function array_to_csv($array = array(), $filename = "export.csv")
    {
        $CI = get_instance();

        // disable the profiler otherwise header errors will occur
        $CI->output->enable_profiler(FALSE);

        if ( ! empty($array))
        {
            // ensure proper file extension is used
            if ( ! substr(strrchr($filename, '.csv'), 1))
            {
                $filename .= '.csv';
            }

            try
            {
                // set the headers for file download
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
                header("Cache-Control: no-cache, must-revalidate");
                header("Pragma: no-cache");
                header("Content-type: text/csv");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename={$filename}");

                $output = @fopen('php://output', 'w');

                // used to determine header row
                $header_displayed = FALSE;

                foreach ($array as $row)
                {
                    if ( ! $header_displayed)
                    {
                        // use the array keys as the header row
                        fputcsv($output, array_keys($row));
                        $header_displayed = TRUE;
                    }

                    // clean the data
                    $allowed = '/[^a-zA-Z0-9_ %\|\[\]\.\(\)%&-]/s';
                    foreach ($row as $key => $value)
                    {
                        $row[$key] = preg_replace($allowed, '', $value);
                    }

                    // insert the data
                    fputcsv($output, $row);
                }

                fclose($output);

            }
            catch (Exception $e) {}
        }

        exit;
    }
}


/**
 * Generates a random password
 *
 * @return string
 */
if ( ! function_exists('generate_random_password'))
{
    function generate_random_password()
    {
        $characters = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alpha_length = strlen($characters) - 1;

        for ($i = 0; $i < 8; $i++)
        {
            $n = rand(0, $alpha_length);
            $pass[] = $characters[$n];
        }

        return implode($pass);
    }
}

/**
 * Generates header_table
 *
 * @return string
 */
if ( ! function_exists('generate_row_header_table'))
{
    function generate_row_header_table($arr,$current_uri = NULL){
        $result = array();
        foreach($arr as $k => $val){
            if(!empty($current_uri)){
              $tmp = '<th data-id="'.$k.'"><a href="'.site_url($current_uri).'?ord='.$k.'">'.$val.'</a></th>';
            }else{
              $tmp = '<th data-id="'.$k.'">'.$val.'</th>';
            }

            array_push($result,$tmp);
        }
        return '<tr class="header">'.implode($result,'').'</tr>';
    }
}

/**
 * Generates search_header_table
 *
 * @return string
 */
if ( ! function_exists('generate_row_search_header'))
{
    function generate_row_search_header($header,$arr,$default = array()){
        $result = array();
        foreach($header as $id => $h){
           $tmp = '';
           if(isset($arr[$id] )){
                $searchInput = $arr[$id] ;
                $label = $searchInput['label'];
                switch($searchInput['tipe']){
                   case 'dropdown' :
                        $tmp = array();
                        $tmp[] = '<select class="form-control" name="'.$id.'" data-target="'.$id.'"  onchange="filter_content(this)">';
                        $default_selected = isset($default[$id]) ? $default[$id] : NULL;

                        $options = $searchInput['data'];
                            foreach($options as $id_opt => $opt){
                                $selected = !is_null($default_selected) && $default_selected == $id_opt ? 'selected' : '';
                                $tmp[] = '<option '.$selected.' value="'.$id_opt.'">'.$opt.'</option>';
                            }
                        $tmp[] = '</select>';
                        $tmp = implode($tmp,'');
                        break;
                   default :
                        $tmp = '<div class="left-inner-addon"><i class="glyphicon glyphicon-search"></i><input  class="form-control" type="search" data-target="'.$id.'" name="'.$id.'" placeholder="'.$label.'" onkeyup="filter_content(this)" /></div>';
               }
           }
            $td = '<th class="search">'.$tmp.'</th>';
            array_push($result,$td);
        }
        return '<tr class="search">'.implode($result,'').'</tr>';
    }
}

if (! function_exists ( 'npwpFormat' )) {
  function npwpFormat($angka, $default = 0) {
    if ( isset($angka) ) {
      if (is_numeric( $angka )) {
        $ret = substr($angka,0,2).'.'
        .substr($angka,2,3).'.'
        .substr($angka,5,3).'.'
        .substr($angka,8,1).'-'
        .substr($angka,9,3).'.'
        .substr($angka,12,3);
        return $ret;
      } else {
        return $angka;
      }
    } else {
      return $default;
    }
  }
}

if (! function_exists ( 'angkaRibuan' )) {
	function angkaRibuan($angka, $default = 0) {
    if ( isset($angka) ) {
      if (is_numeric( $angka )) {
        return number_format ( $angka, 0, '', '.' );
      } else {
        return $angka;
      }
    } else {
      return $default;
    }
	}
}

if (! function_exists ( 'angkaDecimal' )) {
	function angkaDecimal($angka, $default = 0) {
    if ( isset($angka) ) {
      if (is_numeric( $angka )) {
        return number_format ( ($angka*1), 2, ',', '.' );
      } else {
        return $angka;
      }
    } else {
      return $default;
    }
	}
}

if (! function_exists ( 'tglIndonesia' )) {
	function tglIndonesia($tgldb, $separator_asal = '-', $separator_tujuan = '-', $full = false) {
		if(empty($tgldb) && (strlen($tgldb) < 4) ){
			return null;
		}
		/* $tgldb formatnya 2015-05-29 , rubah menjadi 29-Mei-2015 */
		/* cek apakah mengandung jam atau detik, panjang max = 10 karakter */
		$tgldb = substr ( $tgldb, 0, 10 );

		$tgl = explode ( $separator_asal, $tgldb );
    if (!(count($tgl) > 1) ) {
      return $tgldb;
    }
		$newTgl = array (
				$tgl[2],
				convert_ke_bulan( $tgl[1], $full ),
				$tgl[0]
		);
		return implode ( $separator_tujuan, $newTgl );
	}
}

if (! function_exists ( 'blnIndonesia' )) {
  function blnIndonesia($tgldb, $separator_asal = '-', $separator_tujuan = '-', $full = false) {
    if(empty($tgldb) && (strlen($tgldb) < 4) ){
      return null;
    }
    /* $tgldb formatnya 2015-05-29 , rubah menjadi 29-Mei-2015 */
    /* cek apakah mengandung jam atau detik, panjang max = 10 karakter */
    $tgldb = substr ( $tgldb, 0, 7 );

    $tgl = explode ( $separator_asal, $tgldb );
    if (!(count($tgl) > 1) ) {
      return $tgldb;
    }
    $newTgl = array (
        // $tgl[2],
        convert_ke_bulan( $tgl[1], $full ),
        $tgl[0]
    );
    return implode ( $separator_tujuan, $newTgl );
  }
}

if (! function_exists ( 'convert_ke_bulan' )) {
	function convert_ke_bulan($idbulan, $full = false) {
		$shortName = array (
				'Jan',
				'Feb',
				'Mar',
				'Apr',
				'Mei',
				'Jun',
				'Jul',
				'Ags',
				'Sep',
				'Okt',
				'Nov',
				'Des'
		);

    if ($full) {
      $fullName = array (
  				'Januari',
  				'Februari',
  				'Maret',
  				'April',
  				'Mei',
  				'Juni',
  				'Juli',
  				'Agustus',
  				'September',
  				'Oktober',
  				'November',
  				'Desember'
  		);
      return $fullName [$idbulan - 1];
    }
		return $shortName [$idbulan - 1];
	}
}
if (! function_exists ( 'convert_hari' )) {
	function convert_hari($idhari) {
		$shortName = array (
        'Minggu',
				'Senin',
				'Selasa',
				'Rabu',
				'Kamis',
				'Jumat',
				'Sabtu'
		);
		return $shortName [$idhari];
	}
}

if (! function_exists ( 'tglKeHari' )) {
	function tglKeHari($date) {
    if (empty($date)) {
        $date = date('Y-m-d');
    }
    $in = date('w', strtotime($date));
		return convert_hari($in);
	}
}

/**
  * grouping berdasarkan tanggal panen
  */
if (! function_exists ( 'groupingArr' )) {
  function groupingArr($arr,$column){
    $result = array();
    foreach($arr as $r){
      $key = $r[$column];
      if(!isset($result[$key])){
        $result[$key] = array();
      }
      array_push($result[$key],$r);
    }
    return $result;
  }
}

if (! function_exists ( 'timeToMinute' )) {
  function timeToMinute($time){
    $tmp = explode(':',$time);
    $result = $tmp[0] * 60 + $tmp[1];
    return $result;
  }
}

if (! function_exists ( 'MinuteToTime' )) {
  function MinuteToTime($time){
    $h = $time / 60;
    $m = $time % 60;

    return sprintf("%02d:%02d:00",$h,$m);
  }
}

if (! function_exists ( 'timeFormat' )) {
  function timeFormat($str){
    if (strpos($str, '-') !== false && $str != '-') {
      $str = date("Y-m-d H:i:s", strtotime($str) );
      return substr($str,11,5);
    }
    return substr($str,0,5);
  }
}

if (! function_exists ( 'dateTimeFormat' )) {
  function dateTimeFormat($str){
    if (strpos($str, '-') !== false && $str != '-') {
      $str = date("Y-m-d H:i:s", strtotime($str) );
      return tglIndonesia($str, '-', ' ') . ' ' . substr($str,11,5);
    }
    return substr($str,0,5);
  }
}


if (! function_exists ( 'convertStatusKend' )) {
  function convertStatusKend($value){
    switch($value){
      case 1:
        $result = 'AKTIF_APPROVED<span class="hide">1</span>';
        break;
      case 2:
        $result = 'NONAKTIF_APPROVED<span class="hide">2</span>';
        break;
      case 3:
        $result = 'AKTIF_REJECT<span class="hide">3</span>';
        break;
      default:
        $result = 'AKTIF_SUBMIT<span class="hide">0</span>';
    }
    return $result;
  }
}

if (! function_exists ( 'spanStatusKend' )) {
  function spanStatusKend($value){
    switch($value){
      case 'AKTIF_APPROVED':
        $result = '<span class="hide">1</span>';
        break;
      case 'NONAKTIF_APPROVED':
        $result = '<span class="hide">2</span>';
        break;
      case 'AKTIF_REJECT':
        $result = '<span class="hide">3</span>';
        break;
      default:
        $result = '<span class="hide">0</span>';
    }
    return $result;
  }
}

if(!function_exists('tanpaExt')){
  function tanpaExt($filename){
    $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
    return $withoutExt;
  }
}

if(!function_exists('ubahNama')){
  function ubahNama($filename, $location = null){
    if ( empty($location) ) {
      $location = FCPATH . "//uploads/";
    }
    
    $ext_pos = strrpos($filename, '.');
    $ext = substr($filename, $ext_pos);
    $filename = substr($filename, 0, $ext_pos);
    $filename = preg_replace('/\s+/', '_', $filename);
    $filename = str_replace('.', '_', $filename);
    $filename = preg_replace('/\s+/', '_', $filename);
    $filename = str_replace(',', '_', $filename);

    $loc = $location.$filename.$ext;
    if(file_exists($loc)){
      $increment = 0;
      list($name, $ext) = explode('.', $loc);
      while(file_exists($loc)) {
          $increment++;
          // $loc is now "userpics/example1.jpg"
          $loc = $name.'_'.$increment . '.' . $ext;
          $filename = $name.'_'.$increment . '.' . $ext;
      }
    } else {
      $filename .= $ext;
    }

    $filename = str_replace($location, '', $filename);

    return $filename;
  }
}

if(!function_exists('prev_date')){
  function prev_date($date, $minus = 1){
		return Date("Y-m-d",strtotime('-'. $minus. ' day', strtotime($date)));
  }
}

if(!function_exists('next_date')){
  function next_date($date, $plus = 1){
		return Date("Y-m-d",strtotime('+'. $plus. ' day', strtotime($date)));
  }
}

if(!function_exists('next_month')){
  function next_month($date, $plus = 1){
    return Date("Y-m-d",strtotime('+'. $plus. ' month', strtotime($date)));
  }
}


if (! function_exists ( 'hasAkses' )) {
	function hasAkses($route) {
        $CI = & get_instance();
        $permission = json_decode($CI->session->userdata('url'),1);
        foreach ($permission as $value) {
            if($value == $route){
                return true;
                break;
            }
        }
        return false;
	}
}

if (! function_exists ( 'hakAkses' )) {
    function hakAkses($url) {
        $CI = & get_instance();
        $fitur = $CI->session->userdata('Fitur');
        $akses = null;
        foreach ($fitur as $v_fitur) {
            foreach ($v_fitur['detail'] as $v_dfitur) {
                if( trim($v_dfitur['path_detfitur']) == trim(substr($url, 1)) ){
                    $akses = $v_dfitur['akses'];
                    break;
                }
            }
        }

        return $akses;
    }
}

if (! function_exists ( 'hakAksesKhusus' )) {
    function hakAksesKhusus($url) {
        $CI = & get_instance();
        $akses_khusus = $CI->session->userdata('AksesKhusus');

        $akses = false;
        foreach ($akses_khusus as $ak) {
            if( trim($ak['akses_khusus']) == trim($url) ){
                $akses = true;
                break;
            }
        }

        return $akses;
    }
}

if (! function_exists ( 'cetak_r' )) {
	function cetak_r($value, $die = NULL) {
        echo "<pre>";
        print_r($value);
        if ($die <> NULL) {
          die();
        }

        echo "</pre>";
	}
}

if (! function_exists ( 'getValueIsset' )) {
	function getValueIsset($value, $returnValue = "") {
      return isset($value) ? $value : $returnValue;
	}
}

if (! function_exists ( 'batas_jam' )) {
	function batas_jam($jam) {
      return time() < strtotime($jam) ? true : false;
	}
}

if (! function_exists ( 'convertTimeSql' )) {
	function convertTimeSql($time) {
      return substr($time,0,5);
	}
}



if (! function_exists ( 'batas_tanggal' )) {
	function batas_tanggal($tanggal) {
    $dt = new DateTime($tanggal);
    return date('Y-m-d') > $dt->format('Y-m-d') ? true : false;
	}
}

if (! function_exists ( 'hasPermission' )) {
	function hasPermission($route){
    $CI = & get_instance();
		$listUrl = json_decode($CI->session->userdata('url'),1);
		return in_array($route,$listUrl);
	}
}


if (! function_exists ( 'selisihMenit' )) {
  function selisihMenit($time1, $time2){
    return abs(timeToMinute($time1) - timeToMinute($time2));
  }
}

if (! function_exists ( 'selisihWaktu' )) {
	function selisihWaktu($time1, $time2){
    $t1 = timeToMinute($time1);
    $t2 = timeToMinute($time2);
    $t24 = timeToMinute( '24:00:00' );

    $val_t = 0;
    if( $t1 > $t2 ){
      $val_t =  $t1 - $t2;
    }else{
      $val_t = $t1 + ( $t24 - $t2 );
    }

    return $val_t;
	}
}

if (! function_exists ( 'selisihTanggal' )) {
  function selisihTanggal($time1, $time2){
    $datetime1 = new DateTime($time1);
    $datetime2 = new DateTime($time2);

    $interval = $datetime1->diff($datetime2);

    return $interval->format("%r%a");
  }
}


if (! function_exists ( 'send_email' )) {
	function send_email($subject, $d_email = array(), $msg){
		$CI =& get_instance();
		//$CI->load->model("employee/m_employee");

		$from_name = "Wonokoyo";

		$from_name = "Tim MUS-Premi Ekspedisi";
		$from_address = "itos@wonokoyo.co.id";

		$CI->load->library('email');
		$CI->email->set_newline("\r\n");

		foreach ($emp_email as $keynikatasan=>$nikatasan) {
			foreach ($nikatasan as $keynikdetails=>$details) {
				if($details["EMAIL"]!=""){
					$CI->email->from($from_address, $from_name);
					$CI->email->to($details["EMAIL"]);
					$CI->email->subject($subject);

					$dear  = "Dear Bp./Ibu ".ucwords(strtolower($details["NAMABP"])).",<br/><br/>";
					$message = '
					<HTML>
					<HEAD>
					<TITLE>
						Absence Form Confirmation
					</TITLE>
					</HEAD>
					<BODY>
						'.$dear.$details["text"].'
						Perhatian:
						Email ini dikirim otomatis oleh sistem, mohon tidak dibalas.
					</BODY>
					</HTML>
					';
					$CI->email->message($message);
					if($CI->email->send()){
						//Email berhasil dikirim
					}else{
						log_message('error','Failed send email to : '.$details["NAMABP"]);
					}
				}

				$CI->email->clear(TRUE);

		$title = 'Review Master Sopir';

		foreach ($d_email as $key=>$det_email) {
			if($det_email["email_user"]!=""){
				$from_name = $det_email["email_user"];
				$nama_user = $det_email["Nama_User"];
				$CI->email->from($from_address);
				$CI->email->to($from_name);
				$CI->email->subject($subject);

				$dear  = "Dear Bp./Ibu ".ucwords($nama_user).",<br/><br/>";
				$message = '
				<HTML>
				<HEAD>
				<TITLE>
					'.$title.'
				</TITLE>
				</HEAD>
				<BODY>
					'.$dear.str_replace('nama_fitur', $det_email["Nama_Fitur"], $msg).'
					<BR><BR>Terima kasih,<BR>
					Tim MUS-Premi Ekspedisi

					<BR><BR><BR><BR>Perhatian:<BR>
					Email ini dikirim otomatis oleh sistem, mohon tidak dibalas.
				</BODY>
				</HTML>
				';
				$CI->email->message($message);
				if($CI->email->send()){
					//Email berhasil dikirim
					log_message('error','Succeed send email to : '.$det_email["Nama_User"]);
				}else{
					log_message('error','Failed send email to : '.$det_email["Nama_User"]);
				}
			}
		}
		$CI->email->clear(TRUE);
	}
}
}}


if (! function_exists ( 'formatURL' )) {
  function formatURL($str, $format = '_URT_F'){
    return str_replace('/', $format, $str);
  }
}

if (! function_exists ( 'unFormatURL' )) {
  function unFormatURL($str, $format = '_URT_F'){
    return str_replace($format, '/' , $str);
  }
}

if (! function_exists ( 'showErrorAkses' )) {
  function showErrorAkses(){
        show_error('Anda tidak memiliki akses ke halaman '. $_SERVER['REQUEST_URI'] .'<br ><a href="'.base_url().'" class="btn btn-default">Home</a>',401,'Halaman terlarang');
  }
}

if (! function_exists ( 'renderHTMLDateJSTree' )) {
  function renderHTMLDateJSTree($list){
    $html = '';
    if (count($list) > 0) {

      $lists_riwayat = [];
      foreach ($list as $key => $value) {
          $lists_riwayat[$value['tahun']][ convert_ke_bulan($value['bulan'], true) ][] = ['hari'=>$value['hari'], 'tanggal'=>$value['tanggal']];
      }

      $html .= '<ul>';
      foreach ($lists_riwayat as $key_tahun => $tahun) {
        $html .= '<li id="'.$key_tahun.'">' . $key_tahun;
        $html .= '<ul>';
        foreach ($tahun as $key_bulan => $bulan) {
          $html .= '<li id="'.$key_bulan.'">' . $key_bulan;
          $html .= '<ul>';
          foreach ($bulan as $key => $hari) {
            $html .= '<li id="' . $hari['tanggal'] .'" data-tanggal="'. $hari['tanggal'] .'" >' . $hari['hari'] . '</li>';
          }
          $html .= '</ul></li>';
        }
        $html .= '</ul></li>';
      }
      $html .= '</ul>';
    }
    return $html;
  }
}


if (! function_exists ( 'fJam' )) {
  function fJam($value){
    if( ( strlen($value) > 3 )  &&  $value{2} === ':'){
      $value = substr($value,0,5);
    }
    return $value;
  }
}

if (! function_exists ( 'fZero' )) {
  function fZero($value, $default = 0){
    if ( empty($value) || !is_numeric($value) || !isset($value) ) {
      return $default;
    }else {
      return $value;
    }
  }
}

if (! function_exists ( 'fDecimal' )) {
  function fDecimal($value, $default = 2){
    if ( !empty($value) && is_numeric($value) && isset($value) ) {
      return number_format( $value, $default);
    }else {
      return $value;
    }
  }
}

if (! function_exists ( 'uploadFile' )) {
  function uploadFile($file, $upload_path = null)
  {
    if ( empty($upload_path) ) {
      $upload_path = FCPATH . "//uploads/";
    }
    $file_name = $file['name'];
    $path_name = ubahNama($file_name, $upload_path);
    $file_path = $upload_path . $path_name;
    $moved = 0;
    if( file_exists($file_path) ){
      $moved = 1;
    }else{
      $moved = move_uploaded_file($file['tmp_name'], $file_path );
    }

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
}

if (! function_exists ( 'getStatus' )) {
  function getStatus($status) {
    $CI = & get_instance();
    $config = $CI->config->item('g_status');
    $result = null;
    if ( is_numeric($status) ) {
      $result = 'not found';
      foreach ($config as $key => $val) {
        if ($key == $status) {
          $result = $val;
          break;
        }
      }
    }else{
      $result = -1;
      foreach ($config as $key => $val) {
        if ($val == $status) {
          $result = $key;
          break;
        }
      }
    }
    return $result;

  }
}

if (! function_exists ( 'getJabatan' )) {
  function getJabatan($status) {
    $CI = & get_instance();
    $config = $CI->config->item('jabatan');
    $result = null;

    $result = -1;
    foreach ($config as $key => $val) {
      if ($key == $status) {
        $result = $val;
        break;
      }
    }

    return $result;
  }
}

if (! function_exists ( 'getLevelJabatan' )) {
  function getLevelJabatan($status) {
    $CI = & get_instance();
    $config = $CI->config->item('level_jabatan');
    $result = null;

    $result = -1;
    foreach ($config as $key => $val) {
      if (trim($key) == trim($status)) {
        $result = $val;
        break;
      }
    }

    return $result;
  }
}

if (! function_exists ( 'getAtasan' )) {
  function getAtasan($status) {
    $CI = & get_instance();
    $config = $CI->config->item('atasan');
    $result = null;

    $result = -1;
    foreach ($config as $key => $val) {
      if (trim($key) == trim($status)) {
        $result = $val;
        break;
      }
    }

    return $result;
  }
}

if (! function_exists ( 'toAlpha' )) {
  function toAlpha($data){
    $alphabet =   array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AX','AY','AZ');
    $alpha_flip = array_flip($alphabet);

    return $alphabet[$data-1];
    
    /*if($data <= 25){
    } elseif ($data > 25) {
      $alpha = '';
      return $alpha;
    }*/
  }
}

if (! function_exists ( 'toNum' )) {
  function toNum($data){
    $alphabet =   array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $alpha_flip = array_flip($alphabet);

    $_num = 0;
    $num = '';
    for ($i=0; $i < strlen($data); $i++) {
      $_num = $alpha_flip[$data[$i]]+1;
      $num .= ($_num != 0) ? $_num : FALSE;
    }

    return $num;
  }
}

if (! function_exists ( 'dateAlpha' )) {
  function dateAlpha($data){
    $_year = substr($data, 2, 2);
    $_month = toAlpha((int)substr($data, 5, 2));
    $_date = substr($data, 8, 2);

    $date = $_year.$_month.$_date;

    return $date;
  }
}

if (! function_exists ( 'numToRoman' )) {
  function numToRoman($num,$isUpper=true) {
    $n = intval($num);
    $res = '';

    /*** roman_numerals array ***/
    $roman_numerals = array(
      'M' => 1000,
      'CM' => 900,
      'D' => 500,
      'CD' => 400,
      'C' => 100,
      'XC' => 90,
      'L' => 50,
      'XL' => 40,
      'X' => 10,
      'IX' => 9,
      'V' => 5,
      'IV' => 4,
      'I' => 1
    );

    foreach ($roman_numerals as $roman => $number)
    {
      /*** divide to get matches ***/
      $matches = intval($n / $number);

      /*** assign the roman char * $matches ***/
      $res .= str_repeat($roman, $matches);

      /*** substract from the number ***/
      $n = $n % $number;
    }

    /*** return the res ***/
    if($isUpper) return $res;
    else return strtolower($res);
  }
}

if (! function_exists ( 'mappingFiles' )) {
  function mappingFiles($files)
  {
    $mappingFiles = [];
    foreach ($files['tmp_name'] as $key => $file) {
      $sha1 = sha1_file($file);

      $index = $sha1 . '_' . $files['name'][$key];

      $mappingFiles[$index] = [
        'name' => $files['name'][$key],
        'tmp_name' => $file,
        'type' => $files['type'][$key],
        'size' => $files['size'][$key],
        'error' => $files['error'][$key]
      ];
    }
    return $mappingFiles;
  }
}

if (! function_exists ( 'angkaDecimalFormat' )) {
  function angkaDecimalFormat($angka, $format = 2, $default = 0) {
    if ( isset($angka) ) {
      if (is_numeric( $angka )) {
        if ($angka != 0) {
          return number_format ( ($angka*1), $format, ',', '.' );
        }else{
          return $default;
        }
      } else {
        return $angka;
      }
    } else {
      return $default;
    }
  }
}

if (! function_exists ( 'getJenisMitra' )) {
  function getJenisMitra($jenis_mitra) {
    $CI = & get_instance();
    $config = $CI->config->item('jenis_mitra');
    $result = null;
    foreach ($config as $key => $val) {
      if ($key == $jenis_mitra) {
        $result = $val;
        break;
      }
    }
    return $result;
  }
}

if (! function_exists ( 'encrypt_decrypt' )) {
    function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'Goyang-Sitik key';
        $secret_iv = 'Goyang-Sitik iv';
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            try {
              $b64 = base64_decode($string);
              $output = openssl_decrypt($b64, $encrypt_method, $key, 0, $iv);
            } catch (Exception $e) {
                echo "Error : " . $e;
                die();
            }
        }
        return $output;

  }
}

if (! function_exists ( 'exEncrypt' )) {
  function exEncrypt($str) {
      return encrypt_decrypt('encrypt', $str);
  }
}

if (! function_exists ( 'exDecrypt' )) {
  function exDecrypt($str) {
      return encrypt_decrypt('decrypt', $str);
  }
}

if (! function_exists ( 'renameWin' )) {
  function renameWin($oldfile,$newfile) {
    if (!rename($oldfile,$newfile)) {
        if (copy ($oldfile,$newfile)) {
            unlink($oldfile);
            return TRUE;
        }
        return FALSE;
    }
    return TRUE;
  }
}

if (! function_exists ( 'deleteDirectory' )) {
  function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
  }
}

if (! function_exists ( 'copyDiectory' )) {
  function copyDiectory($src, $dst) {  
    
    // open the source directory 
    $dir = opendir($src);  

    // Make the destination directory if not exist 
    @mkdir($dst);  

    // Loop through the files in source directory 
    while( $file = readdir($dir) ) {  

        if (( $file != '.' ) && ( $file != '..' )) {  
            if ( is_dir($src . '/' . $file) )  
            {  

                // Recursively calling custom copy function 
                // for sub directory  
                copyDiectory($src . '/' . $file, $dst . '/' . $file);  

            }  
            else {  
                copy($src . '/' . $file, $dst . '/' . $file);  
            }  
        }  
    }  

    closedir($dir); 
  }
}