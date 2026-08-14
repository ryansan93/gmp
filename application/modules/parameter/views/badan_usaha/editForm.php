<div class="modal-header header">
	<span class="modal-title">Edit Badan Usaha</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-xs-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">KODE</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="form-control" value="<?php echo $data['id_badan_usaha']; ?>" readonly>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">NAMA BADAN USAHA</label>
					</div>
					<div class="col-xs-9">
						<input type="text" class="form-control nama_badan_usaha" maxlength="50" data-required="1" value="<?php echo $data['nama_badan_usaha']; ?>">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">SINGKATAN</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="form-control singkatan" maxlength="10" placeholder="Contoh: PT (boleh kosong)" value="<?php echo ($data['singkatan'] == '-') ? '' : $data['singkatan']; ?>">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">STATUS HUKUM</label>
					</div>
					<div class="col-xs-6">
						<select class="form-control status_hukum" data-required="1">
							<option value="">- Pilih -</option>
							<option value="1" <?php echo ($data['status_hukum'] == 1) ? 'selected' : ''; ?>>Berbadan Hukum</option>
							<option value="0" <?php echo ($data['status_hukum'] == 0) ? 'selected' : ''; ?>>Bukan Berbadan Hukum</option>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-xs-3">
						<label class="control-label" style="padding-top: 0px;">&nbsp;</label>
					</div>
					<div class="col-xs-9">
						<label class="checkbox-inline">
							<input type="checkbox" class="is_terbuka" <?php echo ($data['is_terbuka'] == 1) ? 'checked' : ''; ?>> Perusahaan Terbuka (Tbk)
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
						<button type="button" class="btn btn-primary cursor-p" onclick="bu.edit(this);" data-id="<?php echo $data['id_badan_usaha']; ?>"><i class="fa fa-save"></i> Update</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
