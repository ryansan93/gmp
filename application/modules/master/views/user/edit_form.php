<div class="modal-header">
	<span class="modal-title"><b>Edit User</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row content-panel detailed">
		<!-- <h4 class="mb">Edit User</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<label class="col-lg-2 control-label">Status</label>
					<div class="col-lg-2">
						<select id="status" class="form-control" data-required="1">
							<?php
								$selected_a = null; 
								$selected_n = null;
								if ( $data_user['status_user'] == 1 ) {
									$selected_a = 'selected';
								} else {
									$selected_n = 'selected';
								}
							?>
							<option value="">-- Status User --</option>
							<option value="1" <?php echo $selected_a; ?> >Aktif</option>
							<option value="0" <?php echo $selected_n; ?> >Tidak Aktif</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label"> Avatar</label>
					<div class="col-lg-6">
						<input type="file" id="exampleInputFile" class="file" onchange="cek_attachment(this)" value="uploads/<?php echo $data_user['detail_user']['avatar_detuser']; ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Nama User</label>
					<div class="col-lg-4">
						<!-- <input type="text" placeholder="Nama User" id="nama_user" class="form-control" data-id="<?php echo $data_user['id_user']; ?>" data-iddet="<?php echo $data_user['detail_user']['id_detuser']; ?>" value="<?php echo $data_user['detail_user']['nama_detuser']; ?>" data-required="1"> -->
						<select id="nama_user" data-placeholder="Pilih Karyawan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-id="<?php echo $data_user['id_user']; ?>" data-iddet="<?php echo $data_user['detail_user']['id_detuser']; ?>">
							<option value="">Pilih Karyawan</option>
							<?php foreach ($data_karyawan as $k_dk => $v_dk): ?>
								<?php
									$selected = null;
									if ( $v_dk['nama'] == $data_user['detail_user']['nama_detuser'] ) {
										$selected = 'selected';
									}
								?>
								<option data-tokens="<?php echo strtoupper($v_dk['nama'].' ('.$v_dk['jabatan'].')'); ?>" value="<?php echo $v_dk['nama']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dk['nama'].' ('.$v_dk['jabatan'].')'); ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Jenis Kelamin</label>
					<div class="col-lg-3">
						<select id="jenis_kelamin" class="form-control" data-required="1">
							<?php
								$selected_l = null; 
								$selected_p = null;
								if ( $data_user['detail_user']['jk_detuser'] == 'L' ) {
									$selected_l = 'selected';
								} else {
									$selected_p = 'selected';
								}
							?>
							<option value="">-- Jenis Kelamin --</option>
							<option value="L" <?php echo $selected_l; ?> >Laki-Laki</option>
							<option value="P" <?php echo $selected_p; ?> >Perempuan</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">No. Telp</label>
					<div class="col-lg-2">
						<input type="text" placeholder="No. Telp" id="no_tlp" class="form-control" value="<?php echo $data_user['detail_user']['telp_detuser']; ?>" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">E-mail</label>
					<div class="col-lg-4">
						<input type="text" placeholder="E-mail" id="email" class="form-control" value="<?php echo $data_user['detail_user']['email_detuser']; ?>" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Username</label>
					<div class="col-lg-2">
						<input type="text" placeholder="Username" id="username" class="form-control uppercase" value="<?php echo $data_user['detail_user']['username_detuser']; ?>" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Group</label>
					<div class="col-lg-4">
						<select id="id_group" class="form-control" data-required="1">
							<option value="">-- Pilih Group --</option>
							<?php foreach ($data_group as $key => $val): ?>
								<?php 
									$selected = null;
									if ( $val['id_group'] == $data_user['detail_user']['id_group'] ) {
										$selected = 'selected';
									}
								?>
								<option value="<?php echo $val['id_group']; ?>" <?php echo $selected; ?> ><?php echo $val['id_group'] . ' | ' . $val['nama_group'];?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<hr>
					<div class="col-lg-3">
						<button id="btn-add" type="button" class="pull-left btn btn-primary cursor-p" title="EDIT" onclick="user.edit(this)"> 
							<i class="fa fa-save" aria-hidden="true"></i> EDIT
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>