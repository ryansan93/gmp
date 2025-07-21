<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class TutupBulan extends Public_Controller {

    /**
     * Constructor
    */
    function __construct() {
        parent::__construct ();
    }

    public function getData() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select start_date, end_date from periode_fiskal where status = 1
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $data = array(
                'minDate' => $d_conf['start_date'],
                'maxDate' => $d_conf['end_date']
            );
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }
}
