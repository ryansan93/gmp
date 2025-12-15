<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo $data['nomor']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Jenis DN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($jenis_dn[$data['jenis_dn']]['nama']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Tanggal DN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Nilai DN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo angkaDecimal($data['tot_dn']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">No. Dokumen DN</label></div>
		<div class="col-xs-9 no-padding">
			<?php if ( !empty($data['path']) ) { ?>
				<label class="label-control">: <a href="uploads/<?php echo $data['path']; ?>" target="_blank"><?php echo strtoupper($data['no_dok']); ?></a></label>
			<?php } else { ?>
				<label class="label-control">: <?php echo strtoupper($data['no_dok'].' (TIDAK ADA LAMPIRAN)'); ?></label>
			<?php } ?>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Supplier</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($data['nama_supplier'].' ('.$data['jenis'].')'); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-3 no-padding"><label class="label-control">Keterangan DN</label></div>
		<div class="col-xs-9 no-padding"><label class="label-control">: <?php echo strtoupper($data['ket_dn']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding lock_btn_fiskal" data-date="<?php echo substr($data['tanggal'], 0, 10); ?>">
	<?php if ( $akses['a_edit'] == 1 ) { ?>
		<button type="button" class="btn btn-primary pull-right btn_tutup_bulan" onclick="dn.changeTabActive(this)" data-href="action" data-edit="edit" data-kode="<?php echo $data['id']; ?>" style="margin-left: 5px;">
			<i class="fa fa-edit"></i>
			Edit
		</button>
	<?php } ?>
	<?php if ( $akses['a_delete'] == 1 ) { ?>
		<button type="button" class="btn btn-danger pull-right btn_tutup_bulan" onclick="dn.delete(this)" data-kode="<?php echo $data['id']; ?>" style="margin-right: 5px;">
			<i class="fa fa-trash"></i>
			Delete
		</button>
	<?php } ?>
</div>