<div class="modal-header">
	<span class="modal-title"><b>Add User</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row content-panel detailed">
		<!-- <h4 class="mb">Add User</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<label class="col-lg-2 control-label"> Avatar</label>
					<div class="col-lg-6">
						<input type="file" id="exampleInputFile" class="file" onchange="cek_attachment(this)">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Nama User</label>
					<div class="col-lg-4">
						<!-- <input type="text" placeholder="Nama User" id="nama_user" class="form-control" data-required="1"> -->
						<select id="nama_user" data-placeholder="Pilih Karyawan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
							<option value="">Pilih Karyawan</option>
							<?php foreach ($data_karyawan as $k_dk => $v_dk): ?>
								<option data-tokens="<?php echo strtoupper($v_dk['nama'].' ('.$v_dk['jabatan'].')'); ?>" value="<?php echo $v_dk['nama']; ?>"><?php echo strtoupper($v_dk['nama'].' ('.$v_dk['jabatan'].')'); ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Jenis Kelamin</label>
					<div class="col-lg-3">
						<select id="jenis_kelamin" class="form-control" data-required="1">
							<option value="">-- Jenis Kelamin --</option>
							<option value="L">Laki-Laki</option>
							<option value="P">Perempuan</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">No. Telp</label>
					<div class="col-lg-2">
						<input type="text" placeholder="No. Telp" id="no_tlp" class="form-control" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">E-mail</label>
					<div class="col-lg-4">
						<input type="text" placeholder="E-mail" id="email" class="form-control" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Username</label>
					<div class="col-lg-2">
						<input type="text" placeholder="Username" id="username" class="form-control uppercase" data-required="1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Group</label>
					<div class="col-lg-4">
						<select id="id_group" class="form-control" data-required="1">
							<option value="">-- Pilih Group --</option>
							<?php foreach ($data_group as $key => $val): ?>
								<option value="<?php echo $val['id_group']; ?>"><?php echo $val['id_group'] . ' | ' . $val['nama_group'];?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<hr>
				<div class="form-group">
					<div class="col-lg-3">
						<button id="btn-add" type="button" class="pull-left btn btn-primary cursor-p" title="SAVE" onclick="user.save(this)"> 
							<i class="fa fa-save" aria-hidden="true"></i> SAVE
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>