<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Illuminate\Database\Capsule\Manager as Capsule;
/* diaktifkan jika diperlukan koneksi ke db oracle
use yajra\Oci8\Connectors\OracleConnector;
use yajra\Oci8\Oci8Connection;
*/
class DB_Controller extends MX_Controller{
  private $capsule;
  public function __construct(){
    parent::__construct();
    $this->setDBManager();

  }

  private function setDBManager(){
    $this->capsule = new Capsule;
    /* diaktifkan jika menggunakan koneksi ke oracle
    $manager = $this->capsule->getDatabaseManager();
    $manager->extend('oracle', function($config)
    {
      $connector = new OracleConnector();
      $connection = $connector->connect($config);
      $db = new Oci8Connection($connection, $config["database"], $config["prefix"]);
      // set oracle date format to match PHP's date
      $db->setDateFormat('YYYY-MM-DD HH24:MI:SS');
      return $db;
    });
    */
    $env = $this->config->item('connection');
    foreach($env as $nm => $val){
        $this->capsule->addConnection($val,$nm);
    }
    $this->capsule->setAsGlobal();
    $this->capsule->bootEloquent();
    $events = new Illuminate\Events\Dispatcher;
    $events->listen('illuminate.query', function($query, $bindings, $time, $name){
      // Format binding data for sql insertion
      foreach ($bindings as $i => $binding){
          if ($binding instanceof \DateTime)
          {
              $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
          }
          else if (is_string($binding))
          {
              $bindings[$i] = "'$binding'";
          }
      }

      // Insert bindings into query
      $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
      $query = vsprintf($query, $bindings);
      log_message('ERROR',$query);
    });
    $this->capsule->setEventDispatcher($events);
  }

  public function getConnection($name = null){
    return $this->capsule->getConnection($name);
  }

  public function enableQueryLog($name = null){
    $conn = $this->getConnection($name);
    $conn->enableQueryLog();
  }

  public function getLogQuery($name = null){
    $this->enableQueryLog($name);
    $conn = $this->getConnection($name);
    return $conn->getQueryLog();
  }
}

class MY_Controller extends DB_Controller
{
  protected $result = array(
      'status' => 0,
      'message' => '',
      'content' => ''
  );
  
  protected $dataSinkron = array();
  /**
   * Common data
   */
  public $user;
  public $settings;
  public $includes;
  public $current_uri;
  public $current_base_uri;
  public $theme;
  public $template;
  public $error;

  function __construct()
  {
    parent::__construct();

    $this->settings = new stdClass();

    // get settings
    //  $settings = $this->settings_model->get_settings();
    $settings = array(
      array('name' => 'site_name', 'value' => 'Ekspedisi Ayam Hidup'),
      array('name' => 'timezones' , 'value' =>  'UP7'),
      array('name' => 'meta_keywords' , 'value' =>  ''),
      array('name' => 'meta_description' , 'value' =>  ''),
      // array('name' => 'theme' , 'value' =>  'admin'),
    );
    foreach ($settings as $setting)
    {
      $this->settings->{$setting['name']} = $setting['value'];
    }

    $this->settings->site_version = $this->config->item('site_version');

    // get current uri
    $this->current_uri = "/" . uri_string();
    $this->current_base_uri = "/" . $this->uri->segment(1) . '/' .$this->uri->segment(2);

    $this->add_external_js(
        array(
          "assets/themes/lib/jquery/jquery.min.js",
          "assets/themes/lib/jquery.mCustomScrollbar.concat.min.js",
          // "assets/jquery/jquery-2.0.0.min.js",
          "assets/jquery-ui/js/jquery-ui.min.js",
          "assets/jquery-ui/js/jquery.ui.datepicker-id.js",
          "assets/jquery-ui/js/MonthPicker.js",
          "assets/themes/vendor/bootstrap/js/bootstrap.bundle.min.js",
          "assets/themes/vendor/bootstrap/js/bootstrap-select.min.js",
          // "assets/bootstrap-3.3.5/js/bootstrap.min.js",
          // "assets/bootstrap-3.3.5/js/bootstrap-multiselect.js",
          "assets/moments/moment.js",
          "assets/moments/moment-with-locales.js",
          "assets/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js",
          "assets/bootbox/js/bootbox.js",
          "assets/jquery/jquery.price_format.min.js",
          "assets/crypto/sha1.js",
          "assets/crypto/lib-typedarrays-min.js",
          "assets/base/config.js",
          "assets/base/common.js",
          "assets/base/index.js",
          "assets/base/app.js",
        ));
    $this->add_external_css(
        array(
          // "assets/bootstrap-3.3.5/css/bootstrap-theme.min.css",
          // "assets/bootstrap-3.3.5/css/bootstrap-multiselect.css",
          // "assets/themes/lib/bootstrap/css/bootstrap.min.css",
          // "assets/themes/vendor/bootstrap/css/bootstrap.css",
          "assets/themes/vendor/bootstrap/css/bootstrap.min.css",
          "assets/themes/vendor/bootstrap/css/bootstrap-select.min.css",
          "assets/bootstrap-3.3.5/css/bootstrap.min.css",
          "assets/themes/css/simple-sidebar.css",
          "assets/themes/css/jquery.mCustomScrollbar.css",
          "assets/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css",
          "assets/themes/lib/font-awesome/css/font-awesome.css",
          "assets/jquery-ui/css/jquery-ui.min.css", // YES
          "assets/base/css/base.css",
        ));
    
    $this->user = $this->session->userdata('logged_in');

    $this->template = "../../themes/index.php";
  }

  // --------------------------------------------------------------------

  /**
   * Add CSS from external source or outside folder theme
   *
   * This function used to easily add css files to be included in a template.
   * with this function, we can just add css name as parameter and their external path,
   * or add css complete with path. See example.
   *
   * We can add one or more css files as parameter, either as string or array.
   * If using parameter as string, it must use comma separator between css file name.
   * -----------------------------------
   * Example:
   * -----------------------------------
   * 1. Using string as first parameter
   *     $this->add_external_css( "global.css, color.css", "http://example.com/assets/css/" );
   *    or
   *    $this->add_external_css(  "http://example.com/assets/css/global.css, http://example.com/assets/css/color.css" );
   *
   * 2. Using array as first parameter
   *     $this->add_external_css( array( "global.css", "color.css" ),  "http://example.com/assets/css/" );
   *    or
   *    $this->add_external_css(  array( "http://example.com/assets/css/global.css", "http://example.com/assets/css/color.css") );
   *
   * --------------------------------------
   * @author  Arif Rahman Hakim
   * @since Version 3.1.0
   * @access  public
   * @param mixed
   * @param string, default = NULL
   * @return  chained object
   */

  function add_external_css( $css_files, $path = NULL )
  {
    // make sure that $this->includes has array value
    if ( ! is_array( $this->includes ) )
      $this->includes = array();

    // if $css_files is string, then convert into array
    $css_files = is_array( $css_files ) ? $css_files : explode( ",", $css_files );

    foreach( $css_files as $css )
    {
      // remove white space if any
      $css = trim( $css );

      // go to next when passing empty space
      if ( empty( $css ) ) continue;

      // using sha1( $css ) as a key to prevent duplicate css to be included
      $this->includes[ 'css_files' ][ sha1( $css ) ] = is_null( $path ) ? $css : $path . $css;
    }

    return $this;
  }

  // --------------------------------------------------------------------

  /**
   * Add JS from external source or outside folder theme
   *
   * This function used to easily add js files to be included in a template.
   * with this function, we can just add js name as parameter and their external path,
   * or add js complete with path. See example.
   *
   * We can add one or more js files as parameter, either as string or array.
   * If using parameter as string, it must use comma separator between js file name.
   * -----------------------------------
   * Example:
   * -----------------------------------
   * 1. Using string as first parameter
   *     $this->add_external_js( "global.js, color.js", "http://example.com/assets/js/" );
   *    or
   *    $this->add_external_js(  "http://example.com/assets/js/global.js, http://example.com/assets/js/color.js" );
   *
   * 2. Using array as first parameter
   *     $this->add_external_js( array( "global.js", "color.js" ),  "http://example.com/assets/js/" );
   *    or
   *    $this->add_external_js(  array( "http://example.com/assets/js/global.js", "http://example.com/assets/js/color.js") );
   *
   * --------------------------------------
   * @author  Arif Rahman Hakim
   * @since Version 3.1.0
   * @access  public
   * @param mixed
   * @param string, default = NULL
   * @return  chained object
   */

  function add_external_js( $js_files, $path = NULL )
  {
    // make sure that $this->includes has array value
    if ( ! is_array( $this->includes ) )
      $this->includes = array();

    // if $js_files is string, then convert into array
    $js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

    foreach( $js_files as $js )
    {
      // remove white space if any
      $js = trim( $js );

      // go to next when passing empty space
      if ( empty( $js ) ) continue;

      // using sha1( $css ) as a key to prevent duplicate css to be included
      $this->includes[ 'js_files' ][ sha1( $js ) ] = is_null( $path ) ? $js : $path . $js;
    }

    return $this;
  }

  // --------------------------------------------------------------------

  /**
   * Add CSS from Active Theme Folder
   *
   * This function used to easily add css files to be included in a template.
   * with this function, we can just add css name as parameter
   * and it will use default css path in active theme.
   *
   * We can add one or more css files as parameter, either as string or array.
   * If using parameter as string, it must use comma separator between css file name.
   * -----------------------------------
   * Example:
   * -----------------------------------
   * 1. Using string as parameter
   *     $this->add_css_theme( "bootstrap.min.css, style.css, admin.css" );
   *
   * 2. Using array as parameter
   *     $this->add_css_theme( array( "bootstrap.min.css", "style.css", "admin.css" ) );
   *
   * --------------------------------------
   * @author  Arif Rahman Hakim
   * @since Version 3.0.5
   * @access  public
   * @param mixed
   * @return  chained object
   */

  function add_css_theme( $css_files )
  {
    // make sure that $this->includes has array value
    if ( ! is_array( $this->includes ) )
      $this->includes = array();

    // if $css_files is string, then convert into array
    $css_files = is_array( $css_files ) ? $css_files : explode( ",", $css_files );

    foreach( $css_files as $css )
    {
      // remove white space if any
      $css = trim( $css );

      // go to next when passing empty space
      if ( empty( $css ) ) continue;

      // using sha1( $css ) as a key to prevent duplicate css to be included
      $this->includes[ 'css_files' ][ sha1( $css ) ] = base_url( "/themes/{$this->settings->theme}/css" ) . "/{$css}";
    }

    return $this;
  }

  /**
   * Add JS from Active Theme Folder
   *
   * This function used to easily add js files to be included in a template.
   * with this function, we can just add js name as parameter
   * and it will use default js path in active theme.
   *
   * We can add one or more js files as parameter, either as string or array.
   * If using parameter as string, it must use comma separator between js file name.
   *
   * The second parameter is used to determine wether js file is support internationalization or not.
   * Default is FALSE
   * -----------------------------------
   * Example:
   * -----------------------------------
   * 1. Using string as parameter
   *     $this->add_js_theme( "jquery-1.11.1.min.js, bootstrap.min.js, another.js" );
   *
   * 2. Using array as parameter
   *     $this->add_js_theme( array( "jquery-1.11.1.min.js", "bootstrap.min.js,", "another.js" ) );
   *
   * --------------------------------------
   * @author  Arif Rahman Hakim
   * @since Version 3.0.5
   * @access  public
   * @param mixed
   * @param boolean
   * @return  chained object
   */

  function add_js_theme( $js_files, $is_i18n = FALSE )
  {
    if ( $is_i18n )
      return $this->add_jsi18n_theme( $js_files );

    // make sure that $this->includes has array value
    if ( ! is_array( $this->includes ) )
      $this->includes = array();

    // if $css_files is string, then convert into array
    $js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

    foreach( $js_files as $js )
    {
      // remove white space if any
      $js = trim( $js );

      // go to next when passing empty space
      if ( empty( $js ) ) continue;

      // using sha1( $js ) as a key to prevent duplicate js to be included
      $this->includes[ 'js_files' ][ sha1( $js ) ] = base_url( "/themes/{$this->settings->theme}/js" ) . "/{$js}";
    }

    return $this;
  }

  /**
   * Add JSi18n files from Active Theme Folder
   *
   * This function used to easily add jsi18n files to be included in a template.
   * with this function, we can just add jsi18n name as parameter
   * and it will use default js path in active theme.
   *
   * We can add one or more jsi18n files as parameter, either as string or array.
   * If using parameter as string, it must use comma separator between jsi18n file name.
   * -----------------------------------
   * Example:
   * -----------------------------------
   * 1. Using string as parameter
   *     $this->add_jsi18n_theme( "dahboard_i18n.js, contact_i18n.js" );
   *
   * 2. Using array as parameter
   *     $this->add_jsi18n_theme( array( "dahboard_i18n.js", "contact_i18n.js" ) );
   *
   * 3. Or we can use add_js_theme function, and add TRUE for second parameter
   *     $this->add_js_theme( "dahboard_i18n.js, contact_i18n.js", TRUE );
   *      or
   *     $this->add_js_theme( array( "dahboard_i18n.js", "contact_i18n.js" ), TRUE );
   * --------------------------------------
   * @author  Arif Rahman Hakim
   * @since Version 3.0.5
   * @access  public
   * @param mixed
   * @return  chained object
   */

  function add_jsi18n_theme( $js_files )
  {
    // make sure that $this->includes has array value
    if ( ! is_array( $this->includes ) )
      $this->includes = array();

    // if $css_files is string, then convert into array
    $js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

    foreach( $js_files as $js )
    {
      // remove white space if any
      $js = trim( $js );

      // go to next when passing empty space
      if ( empty( $js ) ) continue;

      // using sha1( $js ) as a key to prevent duplicate js to be included
      $this->includes[ 'js_files_i18n' ][ sha1( $js ) ] = $this->jsi18n->translate( "assets/themes/js/core_i18n.js" );
    }

    return $this;
  }
}

class Public_Controller extends MY_Controller
{
  protected $userid;
  protected $userdata;
  function __construct()
  {
    parent::__construct();

    $this->template = "../../themes/index.php";

    // cetak_r( $this->session->userdata, 1 );

    $isLogin = $this->session->userdata('isLogin') || 0;

    if(!$isLogin){
      redirect('user/Login');
    };

    $this->userid = $this->session->userdata('id_user');
    $this->userdata = $this->session->userdata();
  }

  public function do_upload($file){
    $config = $this->config->item('upload_param');
    $result = array();
    $this->load->library('upload', $config);
    if (!$this->upload->do_upload($file)){
        $result['status'] = 0;
        $result['data'] = array('error' => $this->upload->display_errors());
    }else{
        $result['status'] = 1;
        $result['data'] = array('upload_data' => $this->upload->data());
    }
    return $result;
  }

  public function check_attachment($files){
    /* jika ada attachment, maka cek dulu apakah pernah diupload atau tidak */
    $config_upload = $this->config->item('upload_param');
    $max_memo_length = $this->config->item('max_memo_length');
    $result = null;
    $filename = $config_upload['upload_path'].ubahNama($files['name']);
    // echo $filename;
    if(file_exists($filename)){
      $result = $filename;
    }
    return $result;
  }
}

class Admin_Controller extends MY_Controller
{
  function __construct()
  {
    parent::__construct();
  }
}

class API_Controller extends MY_Controller
{
  function __construct()
  {
      parent::__construct();
  }
}