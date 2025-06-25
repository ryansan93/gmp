<?php
use \setasign\Fpdi\Fpdi;

require_once('fpdf/fpdf.php');
require_once('fpdi/src/autoload.php');

class Fpdi
{
	public function generate($source, $output, $status, $by=null, $datetime=null)
	{
		$pdf = new Fpdi();
		$pdf->AddPage();

		//Set the source PDF file
		$pagecount = $pdf->setSourceFile($source);

		//Import the first page of the file
		$tpl = $pdf->importPage(1);
		//Use this page as template
		$pdf->useTemplate($tpl);
		$size = $pdf->getTemplateSize($tpl);

		//Print centered cell with a text in it
		if ( $status == 3 ) {
			// APPROVE
			$pdf->Image('assets/images/kotak_approved.png', 150, 7, 50, 15);
			$pdf->SetFont('Arial','B',12);
			$pdf->SetXY(152, 9.5);
			$pdf->MultiCell(50, 3, 'APPROVED', 0, 'L', 0, 1, '', '', true);
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(152, 14);
			$pdf->MultiCell(50, 3, $by, 0, 'L', 0, 1, '', '', true);
			$pdf->SetXY(152, 17);
			$pdf->MultiCell(50, 3, $datetime, 0, 'L', 0, 1, '', '', true);
		} else {
			// REJECT
			$pdf->Image('assets/images/rejected.png', $size['width']/6, $size['height']/6, 130, 200);
		}

		for ($i=2; $i <= $pagecount; $i++) { 
			$pdf->AddPage();
			$tpl = $pdf->importPage($i);
			$pdf->useTemplate($tpl);
		}

		$pdf->Output($output, "F");
	}
}
