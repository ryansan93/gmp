<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class Event extends Public_Controller {

  /**
  * Constructor
  */
  function __construct() {
    parent::__construct ();
  }

  public function save($model, $message = null, $tbl_name = null, $tbl_id = null, $json = null) {
    if ( empty($tbl_name) ) {
      $tbl_name = $model->getTable();
    }

    if ( empty($tbl_id) ) {
      $tbl_id = $model->id;
    }

    $m_log = new \Model\Storage\LogTables_model();
    $now = $m_log->getDate();
    $m_log->tbl_name = $tbl_name;
    $m_log->tbl_id = $tbl_id;
    $m_log->user_id = $this->userid;
    $m_log->waktu = $now['waktu'];
    $m_log->deskripsi = $message ?: $this->userdata['Nama_User'];
    $m_log->_action = 'insert';
    $m_log->_json = $json;
    $m_log->save();

    $m_data_log = new \Model\Storage\Log\LogTables_model();
    $m_data_log->id_header = $m_log->id;
    $m_data_log->_json = $model->toJson();
    $m_data_log->save();

    return $m_log;
  }

  public function update($model, $message = null) {
    $m_log = new \Model\Storage\LogTables_model();
    $now = $m_log->getDate();
    $m_log->tbl_name = $model->getTable();
    $m_log->tbl_id = $model->id;
    $m_log->user_id = $this->userid;
    $m_log->waktu = $now['waktu'];
    $m_log->deskripsi = $message ?: $this->userdata['Nama_User'];
    $m_log->_action = 'update';
    // $m_log->_json = $model->toJson();
    $m_log->save();

    $m_data_log = new \Model\Storage\Log\LogTables_model();
    $m_data_log->id_header = $m_log->id;
    $m_data_log->_json = $model->toJson();
    $m_data_log->save();

    return $m_log;
  }

  public function delete($model, $message = null) {
    $m_log = new \Model\Storage\LogTables_model();
    $now = $m_log->getDate();
    $m_log->tbl_name = $model->getTable();
    $m_log->tbl_id = $model->id;
    $m_log->user_id = $this->userid;
    $m_log->waktu = $now['waktu'];
    $m_log->deskripsi = $message ?: $this->userdata['Nama_User'];
    $m_log->_action = 'delete';
    // $m_log->_json = $model->toJson();
    $m_log->save();

    $m_data_log = new \Model\Storage\Log\LogTables_model();
    $m_data_log->id_header = $m_log->id;
    $m_data_log->_json = $model->toJson();
    $m_data_log->save();

    return $m_log;
  }
}
