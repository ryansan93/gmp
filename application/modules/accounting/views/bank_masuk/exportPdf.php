<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?php echo base_url() ?>" />
  <!-- <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo.png"> -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

  <title><?php echo $this->config->item('judul_aplikasi'); ?></title>

  	<?php // CSS files ?>
  	<style type="text/css">
		@media print {
			html, body {
				height: 99%;
				width: 99.7%;
				max-width: 100%;
			}

			.noPrint {
				display: none;
				padding-bottom: 0px;
			}

			div.contain {
				padding: 0px;
				width: 210mm;
				height: 148mm;
				margin-bottom: 1rem;
			}

			table.maintable tbody { page-break-inside:auto }
			table.maintable tbody tr.data { page-break-inside:avoid; page-break-after:auto }
		}

		@media screen {
			html, body {
				height: 99%;
				width: 99.5%;
				max-width: 100%;
			}

			.noPrint {
				border-radius: 3px;
				padding: 10px;
				position: fixed;
				right: 1rem;
				top: 1rem;
				background-color: #ffffff;
			}

			div.contain {
				padding: 10px;
				width: 210mm;
				height: 148mm;
				margin-left: auto;
				margin-right: auto;
				margin-bottom: 1rem;
				font-family: arial;
			}
		}

		body {
			background-color: #666666;
		}

		div.contain {
			font-size: 9pt;
			background-color: #ffffff;
			/* padding: 10px; */
		}

		div.page-break {
			page-break-after: always;
		}

		div.page-break-avoid {
			page-break-after: auto;
		}

		p {
			margin: 0px;
		}

		ol { 
			counter-reset: item;
			margin: 0px;
			vertical-align: top;
		}
		li { 
			display: block; 
			margin: 0px;
			padding: 0px;
			vertical-align: top;
		}
		li:before { 
			content: counters(item, ".") ". ";
			counter-increment: item;
			vertical-align: top;
		}

		table.border-field td, table.border-field th {
			border-collapse: collapse;
			padding-left: 3px;
			padding-right: 3px;
			padding-top: 3px;
			padding-bottom: 3px;
		}
		
		table.border-field th {
			border: 1px solid;
		}

		table.border-field tr:not(.keterangan) td {        
			border-right: 1px solid;
		}

		table.border-field tr.keterangan td {        
			border: 1px solid;
		}

		table.border-field tr.total td {        
			border: 1px solid;
			font-size: 11pt;
			font-weight: bold;
		}

		table.border-field {
			border-collapse: collapse;
			border: 1px solid;
		}

		tr.foot td {
			padding-top: 0px;
			padding-bottom: 2px;
		}
		
		td.no-border-non-top {
			border-bottom : hidden!important;
		}

		td.no-border-top {
			border-top : hidden!important;
		}

		tr.foot td table tbody td.no-border {
			border: hidden;
		}

		/* td.no-border {
			border-top : hidden!important;
			border-bottom : hidden!important;
		} */

		.text-center {
			text-align: center;
		}

		.text-right {
			text-align: right;
		}

		.text-left {
			text-align: left;
		}

		.top td {
			border-top: 1px solid black;
		}

		.bottom td {
			border-bottom: 1px solid black;
		}

		td.kiri {
			border-left: 1px solid black;
			padding-left: 3px;
			padding-right: 3px;
		}

		td.kanan {
			border-right: 1px solid black;
			padding-left: 3px;
			padding-right: 3px;
		}

		.col-xs-1 {
			width: 8.33333333%;
		}
		.col-xs-2 {
			width: 16.66666667%;
		}
		.col-xs-3 {
			width: 25%;
		}
		.col-xs-4 {
			width: 33.33333333%;
		}
		.col-xs-5 {
			width: 41.66666667%;
		}
		.col-xs-6 {
			width: 50%;
		}
		.col-xs-7 {
			width: 58.33333333%;
		}
		.col-xs-8 {
			width: 66.66666667%;
		}
		.col-xs-9 {
			width: 75%;
		}
		.col-xs-10 {
			width: 83.33333333%;
		}
		.col-xs-11 {
			width: 91.66666667%;
		}
		.col-xs-12 {
			width: 100%;
		}
	</style>
</head>

<body>
	<?php $cls_page_break = null; ?>
	<div class="noPrint">
		<button type="button" onclick="window.print()">PRINT</button>
	</div>

	<?php 
		$jml_data = 10;
		$print_tax = 1;
		$jml_baris = count($detail);
		$jumlah_page = (isset($detail) && !empty($detail)) ? ceil($jml_baris / $jml_data) : 0; 
	?>
	<?php $jumlah_cetak = 1; $grand_total = 0; ?>
	<?php $total = 0; ?>
	<?php for ($i=0; $i < $jumlah_page; $i++) { ?>
		<?php
			$cls_page_break = "page-break";
			if ( $jumlah_cetak == $jumlah_page ) {
				$cls_page_break = "page-break-avoid";
			}
		?>
		<div class="contain <?php echo $cls_page_break; ?>">
			<div class="col-xs-12" style="display: inline; margin: 0px; padding: 0px;">
				<div class="col-xs-12" style="display: inline-block; text-align: left;">
					<div class="col-xs-12 head" style="display: inline-block; text-align: left;">
						<table class="col-xs-12">
							<tbody>
								<tr>
									<td colspan="2">
										<label class="col-xs-12" style="font-size: 18pt; display: inline-block; margin-bottom: 10px; text-decoration: underline"><b>BUKTI BANK MASUK</b></label>
									</td>
								</tr>
								<tr>
									<td class="col-xs-6" style="vertical-align: top;">
										<div class="col-xs-12" style="display: inline; text-align: left; font-size: 12pt;">
											<label style="display: inline-block; width: 100%;"><b><?php echo strtoupper($this->config->item('nama_perusahaan')); ?></b></label>
										</div>
										<div class="col-xs-12" style="display: inline; text-align: left; font-size: 10pt;">
											<label style="display: inline-block; width: 100%;"><?php echo strtoupper($this->config->item('alamat').'<br>'.$this->config->item('kota').', '.$this->config->item('provinsi').'<br>'.$this->config->item('telp')); ?></label>
										</div>
									</td>
									<td class="col-xs-6" style="vertical-align: top; font-size: 10pt;">
										<div class="col-xs-12" style="display: inline; text-align: left;">
											<label style="display: inline-block; width: 18%;">No. Bukti</label>
											<label style="display: inline-block; width: 2%;">:</label>
											<label style="display: inline-block; width: 75.5%;"><?php echo strtoupper($data['no_km']); ?></label>
										</div>
										<div class="col-xs-12" style="display: inline; text-align: left;">
											<label style="display: inline-block; width: 18%;">Tanggal</label>
											<label style="display: inline-block; width: 2%;">:</label>
											<label style="display: inline-block; width: 75.5%;"><?php echo strtoupper(tglIndonesia(substr($data['tgl_km'], 0, 10), '-', ' ')); ?></label>
										</div>
										<div class="col-xs-12" style="display: inline; text-align: left;">
											<label style="display: inline-block; width: 18%;">Dari</label>
											<label style="display: inline-block; width: 2%;">:</label>
											<label style="display: inline-block; width: 75.5%;"><?php echo $data['nama_cust']; ?></label>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<br>
					<div class="col-xs-12" style="display: inline-block; text-align: left; font-size: 10pt;">
						<table class="border-field" style="width: 100%;">
							<thead>
								<tr>
									<td colspan="3">
										<label style="display: inline-block; width: 50%; text-align: left;">
											<label class="col-xs-12" style="display: inline-block;">Nama Bank : <?php echo $data['nama_bank']; ?></label>
										</label>
										<label style="display: inline-block; width: 50%; text-align: left;">
											<label class="col-xs-12" style="display: inline-block;">No. Giro : <?php echo $data['no_giro']; ?></label>
										</label>
									</td>
								</tr>
								<tr>
									<th class="text-center" style="width: 5%;">No.</th>
									<th class="text-center" style="width: 75%;">Uraian</th>
									<th class="text-center" style="width: 20%;">Jumlah</th>
								</tr>
							</thead>
							<tbody>
								<?php $tot_bruto = 0; $tot_ppn = 0; ?>
								<?php $kosong = 0; ?>
								<?php for ($j=1; $j <= $jml_data; $j++) { ?>
									<?php $idx = (($i*$jml_data)+$j) - 1; ?>
									<?php if ( isset($detail[ $idx ]) ) { ?>
										<tr>
											<td align="center"><?php echo (($i*$jml_data)+$j); ?></td>
											<td align="left">
												<?php
													$ket = '';
													if ( !empty($detail[ $idx ]['no_faktur']) ) {
														if ( !empty($detail[ $idx ]['keterangan']) ) {
															$ket = strtoupper($detail[ $idx ]['keterangan']);
														} else {
															$ket = strtoupper('Pelunasan Piutang a.n '.$data['nama_cust'].' / '.$detail[ $idx ]['no_faktur']);
														}
													} else {
														$ket = !empty($detail[ $idx ]['keterangan']) ? strtoupper($detail[ $idx ]['keterangan']) : '-';
													}
													echo $ket; 
												?>
												<?php // echo strtoupper($detail[ $idx ]['keterangan']); ?>
											</td>
											<td align="right"><?php echo formatAngka($detail[ $idx ]['nilai']); ?></td>
										</tr>
										<?php 
											$total += $detail[ $idx ]['nilai']; 
										?>
									<?php } else { ?>
										<?php
											$_class = "no-border";
											if ( $kosong == 0 ) {
												$_class = "no-border-non-top";
											}
										?>
										<tr>
											<td class="<?php echo $_class; ?>">&nbsp;</td>
											<td class="<?php echo $_class; ?>">&nbsp;</td>
											<td class="<?php echo $_class; ?>">&nbsp;</td>
										</tr>
										<?php $kosong++; ?>
									<?php } ?>
								<?php } ?>
								<tr class="foot total">
									<td class="text-right" colspan="2" style="vertical-align: top;">TOTAL</td>
									<td class="text-right"><?php echo formatAngka($total); ?></td>
								</tr>
								<tr class="foot keterangan">
									<td colspan="4">
										<table style="width: 100%;">
											<tbody>
												<tr>
													<td class="text-center no-border" style="width: 25%;">
														<label style="display: inline-block; text-align: center;">
															<label class="col-xs-12" style="display: inline-block;">Menyetujui,</label>
															<br>
															<br>
															<br>
															<br>
															<br>
															<label class="col-xs-12" style="display: inline-block;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</label>
														</label>
													</td>
													<td class="text-center no-border" style="width: 25%;">
														<label style="display: inline-block; text-align: center;">
															<label class="col-xs-12" style="display: inline-block;">Diperikas Oleh,</label>
															<br>
															<br>
															<br>
															<br>
															<br>
															<label class="col-xs-12" style="display: inline-block;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</label>
														</label>
													</td>
													<td class="text-center no-border" style="width: 25%;">
														<label style="display: inline-block; text-align: center;">
															<label class="col-xs-12" style="display: inline-block;">Kasir (Adm),</label>
															<br>
															<br>
															<br>
															<br>
															<br>
															<label class="col-xs-12" style="display: inline-block;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</label>
														</label>
													</td>
													<td class="text-center no-border" style="width: 25%;">
														<label style="display: inline-block; text-align: center;">
															<label class="col-xs-12" style="display: inline-block;">Penerima,</label>
															<br>
															<br>
															<br>
															<br>
															<br>
															<label class="col-xs-12" style="display: inline-block;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</label>
														</label>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php $jumlah_cetak++; ?>
	<?php } ?>
</body>
</html>