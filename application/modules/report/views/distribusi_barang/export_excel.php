<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.str { mso-number-format:\@; }
		.decimal_number_format { mso-number-format: "\#\,\#\#0.00"; }
		.number_format { mso-number-format: "\#\,\#\#0"; }
		.bordered {
			border: 1px solid black;
		}
		.str_bordered { 
			mso-number-format:\@;
			border: 1px solid black;
		}
		.decimal_number_format_bordered { 
			mso-number-format: "\#\,\#\#0.00";
			border: 1px solid black;
		}

		table thead tr th {
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="panel-body" style="margin-top: 0px; padding-top: 0px;">
	    <div class="row new-line">
	        <div class="col-sm-12">
	            <form class="form-horizontal" role="form">
					<div class="col-sm-12"><b>DISTRIBUSI <?php echo strtoupper($jenis); ?></b></div>
					<div class="col-sm-12">
						<table>
							<tbody>
								<tr>
									<th align="left">Periode</th>
									<th align="left">:</th>
									<th align="left"><?php echo strtoupper($periode); ?></th>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-sm-12">
						<table>
							<tbody>
								<tr>
									<th align="left">Unit</th>
									<th align="left">:</th>
									<th align="left"><?php echo strtoupper($unit); ?></th>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-sm-12">
						<table>
							<tbody>
								<tr>
									<th align="left">Perusahaan</th>
									<th align="left">:</th>
									<th align="left"></th>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-sm-12"><br></div>
					<div class="col-sm-12">
						<table class="table-bordered" style="width: 100%;">
							<thead>
								<tr>
									<th class="bordered">Tanggal</th>
									<th class="bordered">Unit</th>
									<th class="bordered">Asal</th>
									<th class="bordered">Tujuan</th>
									<th class="bordered">Barang</th>
									<th class="bordered">No. SJ</th>
									<th class="bordered">Jumlah</th>
									<th class="bordered">Hrg Beli</th>
									<th class="bordered">Total Beli</th>
									<th class="bordered">Hrg Jual</th>
									<th class="bordered">Total Jual</th>
								</tr>
							</thead>
							<tbody>
								<?php if ( !empty($data) && count($data) > 0 ): ?>
									<?php foreach ($data as $key => $value): ?>
										<tr>
											<td align="center" class="bordered"><?php echo substr($value['datang'], 0, 10); ?></td>
											<td align="center" class="str_bordered"><?php echo $value['unit']; ?></td>
											<td align="left" class="str_bordered"><?php echo strtoupper($value['nama_asal']); ?></td>
											<td align="left" class="str_bordered"><?php echo strtoupper($value['nama_tujuan']); ?></td>
											<td align="left" class="str_bordered"><?php echo strtoupper($value['barang']); ?></td>
											<td align="left" class="str_bordered"><?php echo strtoupper($value['no_sj']); ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo round($value['jumlah'], 2); ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo round($value['hrg_beli'], 2); ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo round($value['tot_beli'], 2); ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo round($value['hrg_jual'], 2); ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo round($value['tot_jual'], 2); ?></td>
										</tr>
									<?php endforeach ?>
								<?php else: ?>
									<tr>
										<td colspan="11">Data tidak ditemukan.</td>
									</tr>
								<?php endif ?>
							</tbody>
						</table>
					</div>
	            </form>
	        </div>
	    </div>
	</div>
</body>
</html>