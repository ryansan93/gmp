<div class="modal-header">
	<span class="modal-title"><b>Add User Mobile</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row content-panel detailed">
		<!-- <h4 class="mb">Add User</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Karyawan</label>
					<div class="col-lg-5">
						<select class="form-control karyawan" data-required="1">
							<option value="">Pilih Karyawan</option>
							<?php foreach ($karyawan as $k_karyawan => $v_karyawan): ?>
								<option value="<?php echo $v_karyawan['id']; ?>" data-nama="<?php echo $v_karyawan['nama']; ?>" data-nik="<?php echo $v_karyawan['nik']; ?>" data-jabatan="<?php echo $v_karyawan['jabatan']; ?>">
									<table class="table">
										<tbody>
											<tr>
												<td class="col-lg-4"><?php echo strtoupper($v_karyawan['jabatan']); ?></td>
												<td class="col-lg-1">&nbsp;|&nbsp;</td>
												<td class="col-lg-7"><?php echo strtoupper($v_karyawan['nama']); ?></td>
											</tr>
										</tbody>
									</table>
								</option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Username</label>
					<div class="col-lg-3">
						<input type="text" placeholder="Username" id="username" class="form-control" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Password</label>
					<div class="col-lg-3">
						<input type="password" placeholder="Password" id="password" class="form-control" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Verifikasi Password</label>
					<div class="col-lg-3">
						<input type="password" placeholder="Password" id="verifikasi_password" class="form-control" data-required="1">
					</div>
				</div>
				<hr>
				<div class="form-group">
					<div class="col-lg-3">
						<button id="btn-add" type="button" class="pull-left btn btn-primary cursor-p" title="SAVE" onclick="um.save(this)"> 
							<i class="fa fa-save" aria-hidden="true"></i> SAVE
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>