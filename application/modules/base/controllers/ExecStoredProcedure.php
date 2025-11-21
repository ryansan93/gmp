<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class ExecStoredProcedure extends Public_Controller {

  /**
  * Constructor
  */
  function __construct() {
    parent::__construct ();
  }

  public function exec($query, $db = 'default') {
    try {
        $DB2 = $this->load->database($db, TRUE);
        $query2 = $DB2->query($query);
        $reports = $query2->result();

        $result['status'] = 1;
        $result['content'] = $reports;
    } catch (Exception $e) {
        $result['message'] = $e->getMessage();
    }

    return $result;
  }
}