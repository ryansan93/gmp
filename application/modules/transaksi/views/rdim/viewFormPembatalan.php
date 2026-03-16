<div class="modal-header">
	<span class="modal-title"><b>VIEW PEMBATALAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
			<div class="col-xs-3 no-padding">
				<label class="control-label">Nama</label>
			</div>
			<div class="col-xs-9 no-padding">
				<label class="control-label">: <?php echo strtoupper($data['nama_mitra']); ?></label>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
			<div class="col-xs-3 no-padding">
				<label class="control-label">Noreg</label>
			</div>
			<div class="col-xs-9 no-padding">
				<label class="control-label">: <?php echo $data['noreg_view']; ?></label>
			</div>
		</div>
        <div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
			<div class="col-xs-3 no-padding">
				<label class="control-label">Tgl Rencana Chick In</label>
			</div>
			<div class="col-xs-9 no-padding">
				<label class="control-label">: <?php echo tglIndonesia($data['tgl_docin'], '-', ' '); ?></label>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-3 no-padding">
				<label class="control-label">Alasan Batal</label>
			</div>
			<div class="col-xs-9 no-padding">
				<label class="control-label">: <?php echo $data['alasan_batal']; ?></label>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <button type="button" class="col-xs-12 btn btn-danger" onclick="rdim.deletePembatalan(this)" data-noreg="<?php echo $data['noreg']; ?>"><i class="fa fa-trash"></i> HAPUS</button>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <button type="button" class="col-xs-12 btn btn-primary" onclick="rdim.editFormPembatalan(this)" data-noreg="<?php echo $data['noreg']; ?>"><i class="fa fa-edit"></i> EDIT</button>
        </div>
	</div>
</div>