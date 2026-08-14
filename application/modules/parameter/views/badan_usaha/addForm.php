<div class="modal-header header">
	<span class="modal-title">Add Badan Usaha</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-xs-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">NAMA BADAN USAHA</label>
					</div>
					<div class="col-xs-9">
						<input type="text" class="form-control nama_badan_usaha" placeholder="Contoh: PERSEROAN TERBATAS" maxlength="50" data-required="1">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">SINGKATAN</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="form-control singkatan" placeholder="Contoh: PT (boleh kosong)" maxlength="10">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">STATUS HUKUM</label>
					</div>
					<div class="col-xs-6">
						<select class="form-control status_hukum" data-required="1">
							<option value="">- Pilih -</option>
							<option value="1">Berbadan Hukum</option>
							<option value="0">Bukan Berbadan Hukum</option>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">&nbsp;</label>
					</div>
					<div class="col-xs-9">
						<label class="checkbox-inline">
							<input type="checkbox" class="is_terbuka"> Perusahaan Terbuka (Tbk)
						</label>
					</div>
				</div>
			</form>
		</div>
		<div class="col-xs-12 no-padding"><hr></div>
		<div class="col-xs-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right">
					<div class="col-xs-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="bu.save();"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
