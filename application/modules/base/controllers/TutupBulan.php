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
        $params = $this->input->post('params');

        $url = str_replace('/gmp_erp', '', str_replace('/gmperp', '', $params['url']));
        $url_kas_bank = array(
            '/accounting/BankKeluar',
            '/accounting/BankMasuk',
            '/accounting/KasKeluar',
            '/accounting/KasMasuk',
            '/accounting/Memorial',

        );

        $sql = null;
        if ( in_array($url, $url_kas_bank) ) {
            $sql = "
                select start_date, end_date from periode_fiskal where status = 1 and kas_bank = 1 order by start_date asc
            ";
        } else {
            $sql = "
                select start_date, end_date from periode_fiskal where status = 1 and opr = 1 order by start_date asc
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $data = array(
                'minDate' => $d_conf['start_date'],
                'maxDate' => $d_conf['end_date']
            );
        } else {
            $sql = null;
            if ( in_array($url, $url_kas_bank) ) {
                $sql = "
                    select start_date, end_date from periode_fiskal where kas_bank = 0 order by start_date desc
                ";
            } else {
                $sql = "
                    select start_date, end_date from periode_fiskal where opr = 0 order by start_date desc
                ";
            }

            $m_conf = new \Model\Storage\Conf();
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $data = array(
                    'minDate' => next_date($d_conf['end_date']),
                    'maxDate' => null
                );
            }
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }
}
