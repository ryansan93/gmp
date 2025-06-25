<div class="modal-header">
	<span class="modal-title"><b>Edit User Mobile</b></span>
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
								<?php
									$selected = null;
									if ( $v_karyawan['id'] == $data_user->id_karyawan ) {
										$selected = 'selected';
									}
								?>
								<option value="<?php echo $v_karyawan['id']; ?>" data-nama="<?php echo $v_karyawan['nama']; ?>" data-nik="<?php echo $v_karyawan['nik']; ?>" data-jabatan="<?php echo $v_karyawan['jabatan']; ?>" <?php echo $selected; ?> >
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
						<input type="text" placeholder="Username" id="username" class="form-control" data-required="1" value="<?php echo $data_user->username; ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Old Password</label>
					<div class="col-lg-3">
						<input type="password" placeholder="Password" id="old_password" class="form-control" data-required="1" value="<?php echo $data_user->password; ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">New Password</label>
					<div class="col-lg-3">
						<input type="password" placeholder="Password" id="password" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Verifikasi Password</label>
					<div class="col-lg-3">
						<input type="password" placeholder="Password" id="verifikasi_password" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label text-left" style="padding-right: 0px;">Status</label>
					<div class="col-lg-3">
						<select class="form-control status" data-required="1" <?php echo ($data_user->status == 1) ? 'disabled' : ''; ?> >
							<option value="1" <?php echo ($data_user->status == 1) ? 'selected' : ''; ?> >AKTIF</option>
							<option value="2" <?php echo ($data_user->status == 0) ? 'selected' : ''; ?> >NON AKTIF</option>
						</select>
					</div>
				</div>
				<hr>
				<div class="form-group">
					<div class="col-lg-3">
						<button id="btn-add" type="button" class="pull-left btn btn-primary cursor-p" title="SAVE" onclick="um.edit(this)" data-id="<?php echo $data_user['id']; ?>"> 
							<i class="fa fa-save" aria-hidden="true"></i> EDIT
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>