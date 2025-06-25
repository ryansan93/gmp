<div class="modal-header no-padding" style="padding-bottom: 10px;">
	<span class="modal-title"><b>REKENING MASUK</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal</label></div>
			<div class="col-xs-12 no-padding">
				<?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ', true)); ?>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
			<div class="col-xs-12 no-padding">
				<?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Nominal (Rp.)</label></div>
			<div class="col-xs-12 no-padding">
				<?php echo angkaDecimal($data['nominal']); ?>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Lampiran</label></div>
			<div class="col-lg-12 no-padding">
				<?php if ( !empty($data['lampiran']) ): ?>
					<a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo $data['lampiran']; ?></a>
				<?php else: ?>
					-
				<?php endif ?>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Keterangan</label></div>
			<div class="col-xs-12 no-padding">
				<?php if ( !empty($data['keterangan']) ): ?>
					<?php echo $data['keterangan']; ?>
				<?php else: ?>
					-
				<?php endif ?>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" data-dismiss="modal" onclick="rt.deleteRm(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-trash"></i> Hapus</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rt.editFormRm(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-edit"></i> Edit</button>
			</div>
		</div>
	</div>
</div>