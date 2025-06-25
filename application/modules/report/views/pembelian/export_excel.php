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
		.decimal_number_format_bordered { 
			mso-number-format: "\#\,\#\#0.00";
			border: 1px solid black;
		}
	</style>
</head>
<body>
	<div class="panel-body" style="margin-top: 0px; padding-top: 0px;">
	    <div class="row new-line">
	        <div class="col-sm-12">
	            <form class="form-horizontal" role="form">
	            	<b>
		            	<table class="table-bordered" style="width: 100%;">
		            		<thead>
		            			<tr>
		            				<?php $total_ekor_tonase = 0; $total_nilai = 0; ?>
		            				<?php if ( !empty($data) && count($data) > 0 ) { ?>
										<?php foreach ($data as $key => $value): ?>
											<?php 
												$total_ekor_tonase += $value['jumlah']; 
												$total_nilai += $value['total']; 
											?>
										<?php endforeach ?>
									<?php } ?>

									<td colspan="7" align="right" class="bordered"><b>TOTAL</b></td>
									<td align="right" class="decimal_number_format_bordered"><b><?php echo $total_ekor_tonase;?></b></td>
									<td align="right" class="bordered"></td>
									<td align="right" class="decimal_number_format_bordered"><b><?php echo $total_nilai;?></b></td>
								</tr>
		            			<tr>
		            				<th class="bordered">Tanggal</th>
		            				<th class="bordered">Kandang</th>
		            				<th class="bordered">Periode</th>
		            				<th class="bordered">Unit</th>
		            				<th class="bordered">Supplier</th>
		            				<th class="bordered">Jenis</th>
		            				<th class="bordered">DO</th>
		            				<th class="bordered">Ekor / Tonase</th>
		            				<th class="bordered">Harga Beli</th>
		            				<th class="bordered">Total</th>
		            			</tr>
		            		</thead>
		            		<tbody>
		            			<?php if ( !empty($data) && count($data) > 0 ) { ?>
									<?php foreach ($data as $key => $value): ?>
										<tr>
											<td align="center" class="bordered"><?php echo tglIndonesia($value['datang'], '-', ' '); ?></td>
											<td class="bordered"><?php echo !empty($value['nama']) ? $value['nama'].' (KDG : '.(int)$value['kandang'].')' : '-'; ?></td>
											<td class="bordered"><?php echo '-'; ?></td>
											<td class="bordered"><?php echo $value['unit']; ?></td>
											<td class="bordered"><?php echo $value['supplier']; ?></td>
											<td class="bordered"><?php echo $value['barang']; ?></td>
											<td class="bordered"><?php echo $value['nama_perusahaan']; ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo $value['jumlah']; ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo $value['harga']; ?></td>
											<td align="right" class="decimal_number_format_bordered"><?php echo $value['total']; ?></td>
										</tr>
									<?php endforeach ?>
		            			<?php } else { ?>
		            				<tr>
		            					<td colspan="10">Data tidak ditemukan.</td>
		            				</tr>
		            			<?php } ?>
		            		</tbody>
		            	</table>
		            </b>
	            </form>
	        </div>
	    </div>
	</div>
</body>
</html>