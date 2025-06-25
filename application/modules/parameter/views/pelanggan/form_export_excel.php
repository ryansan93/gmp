<div class="modal-header header">
	<span class="modal-title">Verifikasi Hak Akses Export Data</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="col-lg-12 no-padding">
		<div class="col-lg-4 no-padding">
			<div class="col-lg-4">
				<label class="label-control" style="margin-top: 7px;">User</label>
			</div>
			<div class="col-lg-8">
				<input class="form-control not-uppercase" type="text" name="username" placeholder="Username" data-required="1" style="text-transform: none !important;">
			</div>
		</div>
		<div class="col-lg-4 no-padding">
			<div class="col-lg-4">
				<label class="label-control" style="margin-top: 7px;">Password</label>
			</div>
			<div class="col-lg-8">
				<input class="form-control not-uppercase" type="password" name="password" placeholder="Password" data-required="1" style="text-transform: none !important;">
			</div>
		</div>
		<div class="col-lg-2 no-padding">
			<button type="button" class="btn btn-primary" onclick="plg.verifikasi_export_excel(this)"><i class="fa fa-sign-in"></i></button>
		</div>
	</div>
</div>