<div class="row content-panel detailed">
	<!-- <h4 class="mb">Personal Information</h4> -->
	<div class="col-lg-8 col-lg-offset-1 detailed">
		<div role="form" class="form-horizontal">
			<div class="form-group">
				<label class="col-lg-3 control-label"> Avatar</label>
				<div class="col-lg-3">
					<?php
						$src = 'uploads/icon-user.png';
						if ( isset($this->session->userdata()['detail_user']['avatar_detuser']) ) {
							$src = 'uploads/'.$this->session->userdata()['detail_user']['avatar_detuser'];
						}
            		?>
            		<img data-toggle="dropdown" src="<?php echo $src; ?>" class="img-circle" width="100" height="100">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">Nama User</label>
				<div class="col-lg-6">
					<input type="text" placeholder="Nama User" id="nama_user" class="form-control" data-id="<?php echo $data_user['id_user']; ?>" value="<?php echo $data_user['detail_user']['nama_detuser']; ?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">Jenis Kelamin</label>
				<div class="col-lg-2">
					<?php
						$jk = $data_user['detail_user']['jk_detuser'];
						$ket_jk = null;
						if ( $jk == 'L' ) {
							$ket_jk = 'Laki - Laki';
						} else {
							$ket_jk = 'Perempuan';
						}
					?>
					<input type="text" placeholder="Jenis Kelamin" id="jk" class="form-control" value="<?php echo $ket_jk; ?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">No. Telp</label>
				<div class="col-lg-3">
					<input type="text" placeholder="No. Telp" id="no_tlp" class="form-control" value="<?php echo $data_user['detail_user']['telp_detuser']; ?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">E-mail</label>
				<div class="col-lg-5">
					<input type="text" placeholder="E-mail" id="email" class="form-control" value="<?php echo $data_user['detail_user']['email_detuser']; ?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">Username</label>
				<div class="col-lg-3">
					<input type="text" placeholder="Username" id="username" class="form-control uppercase" value="<?php echo $data_user['username_user']; ?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">Old Password</label>
				<div class="col-lg-4">
					<input type="password" placeholder="Old Password" id="old_password" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-3 control-label">New Password</label>
				<div class="col-lg-4">
					<input type="password" placeholder="New Password" id="new_password" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-3 col-lg-10">
					<!-- <button class="btn btn-theme" type="update" onclick="user.change_password()">Change Password</button> -->
					<button id="btn-edit" type="button" class="pull-left btn btn-success cursor-p" title="EDIT" onclick="user.change_password()"> 
						<i class="fa fa-edit" aria-hidden="true"></i> Change Password
					</button>
				</div>

			</div>
		</div>
	</div>
</div