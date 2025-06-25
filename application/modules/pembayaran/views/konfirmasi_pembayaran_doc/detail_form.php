<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Periode DOC In</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['periode']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Supplier</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_supplier']['nama']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="7"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b><?php echo angkaDecimal($data['total']); ?></b></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl Order</th>
					<th class="col-xs-1">Kota/Kab</th>
					<th class="col-xs-2">Perusahaan</th>
					<th class="col-xs-2">No. Order</th>
					<th class="col-xs-2">Peternak</th>
					<th style="width: 5%;">Kandang</th>
					<th style="width: 5%;">Populasi</th>
					<th style="width: 5%;">Harga</th>
					<th class="col-xs-1">Sub Total</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count($data['detail']) > 0 ): ?>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr data-supplier="<?php echo $data['supplier']; ?>">
							<td class="text-center tgl_order"><?php echo tglIndonesia($v_det['tgl_order'], '-', ' '); ?></td>
							<td class="kota_kab">
								<?php
									$unit = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_det['d_unit']['nama']))));
									echo strtoupper($unit);
								?>
							</td>
							<td><?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></td>
							<td class="no_order"><?php echo strtoupper($v_det['no_order']); ?></td>
							<td class="peternak"><?php echo strtoupper($v_det['d_mitra']['nama']); ?></td>
							<td class="text-center kandang"><?php echo $v_det['kandang']; ?></td>
							<td class="text-right populasi"><?php echo angkaRibuan($v_det['populasi']); ?></td>
							<td class="text-right harga"><?php echo angkaDecimal($v_det['harga']); ?></td>
							<td class="text-right total"><?php echo angkaDecimal($v_det['total']); ?></td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td colspan="10">Data tidak ditemukan.</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<?php if ( empty($data['d_realisasi']) ): ?>
	<div class="col-xs-12 no-padding">
		<?php if ( $akses['a_edit'] == 1 ): ?>
			<button type="button" class="btn btn-primary pull-right" onclick="kpd.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="transaksi" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
		<?php endif ?>
		<?php if ( $akses['a_delete'] == 1 ): ?>
			<button type="button" class="btn btn-danger pull-right" onclick="kpd.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
		<?php endif ?>
	</div>
<?php endif ?>