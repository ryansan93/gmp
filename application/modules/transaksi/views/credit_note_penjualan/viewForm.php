<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo $data['nomor']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Tanggal CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Nilai CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo angkaDecimal($data['tot_cn']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Pelanggan</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($data['nama_supplier']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Keterangan CN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($data['ket_cn']); ?></label></div>
	</div>
</div>
<?php if ( !empty($data['path']) ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-3 no-padding"><label class="label-control">Lampiran</label></div>
			<div class="col-xs-9 no-padding"><label class="label-control">: <a href="uploads/<?php echo $data['path']; ?>" target="_blank">LIHAT LAMPIRAN</a></label></div>
		</div>
	</div>
<?php } ?>
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
