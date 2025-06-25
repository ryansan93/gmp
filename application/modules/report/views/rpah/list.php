<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="unit">
			<th colspan="8">
				<div class="col-md-12 no-padding">
					<div class="col-md-6 no-padding">
						<?php echo $val['unit']; ?>
					</div>
					<div class="col-md-6 no-padding text-right">
						Bottom Price : <?php echo angkaRibuan($val['bottom_price']); ?>
					</div>
				</div>
			</th>
		</tr>
		<?php foreach ($val['mitra'] as $k_mitra => $v_mitra): ?>
			<tr class="head">
				<td class="col-sm-3"><?php echo $v_mitra['mitra']; ?></td>
				<td class="col-sm-2"><?php echo $v_mitra['noreg']; ?></td>
				<td class="col-sm-1 text-right"><b><?php echo angkaDecimal($v_mitra['tonase']) . ' Kg'; ?></b></td>
				<td class="col-sm-1 text-right"><b><?php echo angkaRibuan($v_mitra['ekor']) . ' Ekor'; ?></b></td>
				<td class="col-sm-1"></td>
				<td class="col-sm-1">Penjualan</td>
				<td class="col-sm-1 text-right"><b><?php echo angkaDecimal($v_mitra['tonase_jual']) . ' Kg'; ?></b></td>
				<td class="col-sm-1 text-right"><b><?php echo angkaRibuan($v_mitra['ekor_jual']) . ' Ekor'; ?></b></td>
			</tr>
			<tr class="detail">
				<td colspan="8">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="col-md-2">Nama Pelanggan</th>
								<th class="col-md-2">Outstanding</th>
								<th class="col-md-1">Tonase</th>
								<th class="col-md-1">Ekor</th>
								<th class="col-md-1">BB</th>
								<th class="col-md-1">Harga</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($v_mitra['pelanggan'] as $k_plg => $v_plg): ?>
								<tr>
									<td><?php echo $v_plg['plg']; ?></td>
									<td><?php echo $v_plg['outstanding']; ?></td>
									<td class="text-right"><?php echo angkaDecimal($v_plg['tonase']); ?></td>
									<td class="text-right"><?php echo angkaRibuan($v_plg['ekor']); ?></td>
									<td class="text-right"><?php echo angkaDecimal($v_plg['bb']); ?></td>
									<td class="text-right"><?php echo angkaRibuan($v_plg['harga']); ?></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr class="unit">
		<td colspan="8">Data tidak ditemukan.</td>
	</th>
<?php endif ?>