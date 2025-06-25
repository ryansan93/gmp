<div class="modal-header header">
	<span class="modal-title">Edit Pegawai</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Nama Pegawai</span>
					</div>
					<div class="col-lg-6">
						<input type="text" placeholder="Nama Pegawai" class="form-control nama_pegawai" data-required="1" value="<?php echo $data['nama']; ?>">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Jabatan</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control jabatan" onchange="pegawai.set_disable_by_jabatan(this)" data-required="1">
							<option value="">-- Pilih Jabatan --</option>
							<?php
								$CI = & get_instance();
								$jabatan = $CI->config->item('jabatan');
							?>
							<?php foreach ($jabatan as $key => $val): ?>
								<?php
									$selected = '';
									if ( $key == $data['jabatan'] ) {
										$selected = 'selected';
									}
								?>
								<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $val; ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Atasan</span>
					</div>
					<div class="col-lg-4">
						<select class="form-control atasan" data-required="1" disabled data-atasan="<?php echo $data['atasan']; ?>">
							<option value="">-- Pilih Atasan --</option>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Marketing</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control marketing" data-required="1">
							<option value="">-- Pilih Marketing --</option>
							<option value="all" <?php echo ($data['marketing'] == "all") ? 'selected' : ''; ?> >All</option>
							<option value="1" <?php echo ($data['marketing'] == "1") ? 'selected' : ''; ?> >1</option>
							<option value="2" <?php echo ($data['marketing'] == "2") ? 'selected' : ''; ?> >2</option>
							<option value="3" <?php echo ($data['marketing'] == "3") ? 'selected' : ''; ?> >3</option>
							<option value="4" <?php echo ($data['marketing'] == "4") ? 'selected' : ''; ?> >4</option>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Koordinator</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control koordinator" data-required="1">
							<option value="">-- Pilih Koordinator --</option>
							<option value="all" <?php echo ($data['kordinator'] == "all") ? 'selected' : ''; ?> >All</option>
							<option value="1" <?php echo ($data['kordinator'] == "1") ? 'selected' : ''; ?> >1</option>
							<option value="2" <?php echo ($data['kordinator'] == "2") ? 'selected' : ''; ?> >2</option>
							<option value="3" <?php echo ($data['kordinator'] == "3") ? 'selected' : ''; ?> >3</option>
							<option value="4" <?php echo ($data['kordinator'] == "4") ? 'selected' : ''; ?> >4</option>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Wilayah</span>
					</div>
					<div class="col-lg-6">
						<select class="wilayah" name="wilayah[]" multiple="multiple" width="100%" placeholder="Pilih Wilayah" data-required="1">
							<?php
								$selected_all = '';
								foreach ($data['d_wilayah'] as $k_all => $v_all) {
									if ( stristr($v_all['wilayah'], 'all') !== FALSE ) {
										$selected_all = 'true';
									}
								}
							?>
							<option value="all" data-selected="<?php echo $selected_all; ?>"> All </option>
							<?php foreach ($list_wilayah as $key => $v_wilayah): ?>
								<?php
									$selected = '';
									foreach ($data['d_wilayah'] as $k => $v) {
										if ( $v['wilayah'] == $v_wilayah['id'] ) {
											$selected = 'true';
										}
									}
								?>
								<option value="<?php echo $v_wilayah['id']; ?>" data-selected="<?php echo $selected; ?>" > <?php echo $v_wilayah['nama']; ?> </option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Unit</span>
					</div>
					<div class="col-lg-6">
						<select class="unit" name="unit[]" multiple="multiple" width="100%" placeholder="Pilih Unit" data-required="1">
							<?php
								$selected_all = '';
								foreach ($data['unit'] as $k_all => $v_all) {
									if ( $v_all['unit'] == 'all' ) {
										$selected_all = 'true';
									}
								}
							?>
							<option value="all" data-selected="<?php echo $selected_all; ?>"> All </option>
							<?php foreach ($list_unit as $key => $v_unit): ?>
								<?php
									$selected = '';
									foreach ($data['unit'] as $k => $v) {
										if ( $v['unit'] == $v_unit['id'] ) {
											$selected = 'true';
										}
									}
								?>
								<option value="<?php echo $v_unit['id']; ?>" data-selected="<?php echo $selected; ?>" > <?php echo $v_unit['nama']; ?> </option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Level</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control level" data-required="1">
							<option value="">-- Pilih Level --</option>
							<option value="0" <?php echo ($data['level'] == '0') ? 'selected' : ''; ?> >0</option>
							<option value="1" <?php echo ($data['level'] == '1') ? 'selected' : ''; ?> >1</option>
							<option value="2" <?php echo ($data['level'] == '2') ? 'selected' : ''; ?> >2</option>
							<option value="3" <?php echo ($data['level'] == '3') ? 'selected' : ''; ?> >3</option>
							<option value="4" <?php echo ($data['level'] == '4') ? 'selected' : ''; ?> >4</option>
						</select>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12 no-padding">
			<div class="col-lg-12">
				<button type="button" class="btn btn-primary cursor-p pull-right" onclick="pegawai.edit(this)" data-id="<?php echo $data['id']; ?>" data-nik="<?php echo $data['nik']; ?>" ><i class="fa fa-edit"></i> Update</button>
			</div>
		</div>
	</div>
</div>