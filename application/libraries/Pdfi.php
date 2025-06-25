<?php
use \setasign\Fpdi\Fpdi;

require_once('fpdf/fpdf.php');
require_once('fpdi/src/autoload.php');

class Pdfi
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
			$pdf->Image('assets/images/box_approved.png', 160, 7, 40, 16);
			$pdf->SetFont('Arial','B',14);
			$pdf->SetTextColor(0, 0, 255);
			$pdf->SetXY(162, 9.5);
			$pdf->MultiCell(35, 3, 'APPROVED', 0, 'C', 0, 1, '', '', true);
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(162, 15);
			$pdf->MultiCell(35, 3, $by, 0, 'C', 0, 1, '', '', true);
			$pdf->SetXY(162, 18);
			$pdf->MultiCell(35, 3, $datetime, 0, 'C', 0, 1, '', '', true);
			$pdf->Image('assets/images/watermark_approved.png', $size['width']/6, $size['height']/4, 130, 150);
		} else if ( $status == 4 ) {
			// REJECT
			$pdf->Image('assets/images/box_rejected.png', 160, 7, 40, 16);
			$pdf->SetFont('Arial','B',14);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->SetXY(162, 9.5);
			$pdf->MultiCell(35, 3, 'REJECTED', 0, 'C', 0, 1, '', '', true);
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(162, 15);
			$pdf->MultiCell(35, 3, $by, 0, 'C', 0, 1, '', '', true);
			$pdf->SetXY(162, 18);
			$pdf->MultiCell(35, 3, $datetime, 0, 'C', 0, 1, '', '', true);
			$pdf->Image('assets/images/watermark_rejected.png', $size['width']/6, $size['height']/4, 130, 150);
		}

		for ($i=2; $i <= $pagecount; $i++) { 
			$pdf->AddPage();
			$tpl = $pdf->importPage($i);
			$pdf->useTemplate($tpl);
			if ( $status == 3 ) {
				$pdf->Image('assets/images/watermark_approved.png', $size['width']/6, $size['height']/4, 130, 150);
			} else if ( $status == 4 ) {
				$pdf->Image('assets/images/watermark_rejected.png', $size['width']/6, $size['height']/4, 130, 150);
			}
		}

		$pdf->Output($output, "F");
	}
}
