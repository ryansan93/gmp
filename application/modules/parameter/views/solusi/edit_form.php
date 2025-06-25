<div class="modal-header header" style="padding-top: 0px;">
	<span class="modal-title">Edit Solusi</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body" style="padding-bottom: 0px;">
	<div class="row">
		<div class="col-lg-12 no-padding" style="padding-bottom: 0px;">
			<div class="col-lg-12">
				<label class="control-label">Keterangan Solusi</label>
			</div>
			<div class="col-lg-12">
				<textarea class="form-control ket" placeholder="Keterangan" data-required="1"><?php echo trim($data['keterangan']); ?></textarea>
			</div>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12 no-padding">
			<div class="col-lg-12">
				<button type="button" class="btn btn-primary cursor-p pull-right" onclick="solusi.edit(this);" data-id="<?php echo $data['id'] ?>"><i class="fa fa-edit"></i> Update</button>
			</div>
		</div>
	</div>
</div>