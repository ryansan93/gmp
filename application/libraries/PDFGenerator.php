<?php

use Dompdf\Dompdf;

class PDFGenerator
{
    public function generate($html,$filename, $kertas = 'a4', $type = 'portrait')
    {
        // include autoloader
        // if (!defined('DOMPDF_ENABLE_AUTOLOAD')) define('DOMPDF_ENABLE_AUTOLOAD', true);
        // require_once("./vendor/dompdf/dompdf_config.inc.php");

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html);
        $dompdf->setPaper($kertas, $type);
        $dompdf->render();

        ob_end_clean();
        $dompdf->stream($filename.'.pdf',array("Attachment"=>0));

        // if ($stream) {
        //     $dompdf->stream($filename.".pdf", array("Attachment" => 0));
        // } else {
        //     $dompdf->output();
        // }
    }

    public function upload($html, $filename, $kertas = 'a4', $type = 'portrait')
    {
        // include autoloader
        // if (!defined('DOMPDF_ENABLE_AUTOLOAD')) define('DOMPDF_ENABLE_AUTOLOAD', true);
        // require_once("./vendor/dompdf/dompdf_config.inc.php");

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($kertas, $type);
        $dompdf->render();

        $output = $dompdf->output();
        file_put_contents('uploads/'.$filename.".pdf", $output);
    }
}
