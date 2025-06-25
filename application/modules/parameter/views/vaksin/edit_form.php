<div class="modal-header header">
	<span class="modal-title">Edit Vaksin</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">
						<span>Nama Vaksin</span>
					</div>
					<div class="col-lg-9">
						<input type="text" class="form-control nama_vaksin" value="<?php echo $data['nama_vaksin']; ?>" data-required="1">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">
						<span>Harga Vaksin</span>
					</div>
					<div class="col-lg-3">
						<input type="text" class="form-control text-right hrg_vaksin" data-tipe="integer" maxlength="6" value="<?php echo angkaRibuan($data['harga']); ?>" data-required="1">
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right">
					<div class="col-lg-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="vaksin.edit(this);" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Update</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>