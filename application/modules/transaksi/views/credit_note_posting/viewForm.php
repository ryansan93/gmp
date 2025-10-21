<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Tanggal Pakai CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo $data['no_dok']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Jenis CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($jenis_cn[$data['jenis_cn']]['nama']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Nilai CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo angkaDecimal($data['sisa']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Total Pakai CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo angkaDecimal($data['tot_pakai']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">No. SJ</th>
					<th class="col-xs-2">Nominal</th>
					<th class="col-xs-2">Sisa Tagihan</th>
					<th class="col-xs-3">Pakai CN</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($detail as $k_det => $v_det) { ?>
					<tr>
						<td><?php echo strtoupper($v_det['no_sj']); ?></td>
						<td class="text-right tagihan"><?php echo angkaDecimal($v_det['tagihan']); ?></td>
						<td class="text-right sisa"><?php echo angkaDecimal($v_det['sisa']); ?></td>
						<td class="text-right pakai"><?php echo angkaDecimal($v_det['pakai']); ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding lock_btn_fiskal" data-date="<?php echo substr($data['tanggal'], 0, 10); ?>">
	<?php if ( $akses['a_edit'] == 1 ) { ?>
		<button type="button" class="btn btn-primary pull-right btn_tutup_bulan" onclick="cn.changeTabActive(this)" data-href="action" data-edit="edit" data-kode="<?php echo $data['id']; ?>" style="margin-left: 5px;">
			<i class="fa fa-edit"></i>
			Edit
		</button>
	<?php } ?>
	<?php if ( $akses['a_delete'] == 1 ) { ?>
		<button type="button" class="btn btn-danger pull-right btn_tutup_bulan" onclick="cn.delete(this)" data-kode="<?php echo $data['id']; ?>" style="margin-right: 5px;">
			<i class="fa fa-trash"></i>
			Delete
		</button>
	<?php } ?>
</div>