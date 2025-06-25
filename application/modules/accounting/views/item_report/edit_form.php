<div class="modal-header header" style="padding-left: 0px;">
	<span class="modal-title"><label class="control-label" style="padding-top: 0px;">Edit Item Report</label></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Nama</label></div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="col-xs-12 form-control nama uppercase" placeholder="NAMA" data-required="1" value="<?php echo $data['nama']; ?>">
				</div>
			</div>
		</div>
		<div class="col-sm-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" onclick="ir.batal(this)" data-id="<?php echo $data['id']; ?>">
					<i class="fa fa-times"></i> Batal
				</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="ir.edit(this)" data-id="<?php echo $data['id']; ?>">
					<i class="fa fa-save"></i> Simpan Perubahan
				</button>
			</div>
		</div>
	</div>
</div>