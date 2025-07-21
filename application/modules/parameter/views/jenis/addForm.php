<div class="modal-header header">
	<span class="modal-title">Add Biaya Operasional</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-xs-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-2">
						<label class="control-label" style="padding-top: 0px;">KODE</label>
					</div>
					<div class="col-xs-5">
						<input type="text" class="form-control kode" placeholder="KODE (MAX:10)" data-required="1">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-2">
						<label class="control-label" style="padding-top: 0px;">NAMA</label>
					</div>
					<div class="col-xs-10">
						<input type="text" class="form-control nama" placeholder="NAMA (MAX:20)" maxlength="20" data-required="1">
					</div>
				</div>
			</form>
		</div>
		<div class="col-xs-12 no-padding"><hr></div>
		<div class="col-xs-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right">
					<div class="col-xs-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="jns.save();"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>