<div class="modal-header header">
	<span class="modal-title">Add Biaya Operasional</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">
						<label class="control-label" style="padding-top: 0px;">Tanggal Berlaku</label>
					</div>
					<div class="col-lg-5">
						<div class="input-group date datetimepicker" name="tgl_berlaku" id="tgl_berlaku">
					        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">
						<label class="control-label" style="padding-top: 0px;">Biaya Operasional</label>
					</div>
					<div class="col-lg-3">
						<input type="text" class="form-control text-right biaya_opr" data-tipe="integer" maxlength="6" placeholder="Nilai" data-required="1">
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right">
					<div class="col-lg-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="bo.save();"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>