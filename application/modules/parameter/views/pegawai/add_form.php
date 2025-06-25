<div class="modal-header header">
	<span class="modal-title">Add Pegawai</span>
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
						<input type="text" placeholder="Nama Pegawai" class="form-control nama_pegawai" data-required="1">
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
								<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
							<?php endforeach ?>
							<!-- <option value="coo">C.O.O. (Chief Official Officer)</option>
							<option value="admin pusat">Admin Pusat</option>
							<option value="penanggung jawab">Penanggung Jawab</option>
							<option value="marketing">Marketing</option>
							<option value="koordinator">Koordinator</option>
							<option value="kepala unit">Kepala Unit</option>
							<option value="penimbang">Penimbang</option>
							<option value="admin">Admin</option>
							<option value="ppl">PPL</option> -->
						</select>
						<!-- <input type="text" class="form-control jabatan" data-required="1"> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Atasan</span>
					</div>
					<div class="col-lg-4">
						<select class="form-control atasan" data-required="1" disabled>
							<option value="">-- Pilih Atasan --</option>
						</select>
						<!-- <input type="text" class="form-control jabatan" data-required="1"> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Marketing</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control marketing" data-required="1">
							<option value="">-- Pilih Marketing --</option>
							<option value="all">All</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
						</select>
						<!-- <input type="text" placeholder="Marketing" class="form-control marketing" data-required="1" data-tipe="integer" maxlength="2" disabled> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Koordinator</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control koordinator" data-required="1">
							<option value="">-- Pilih Koordinator --</option>
							<option value="all">All</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
						</select>
						<!-- <input type="text" placeholder="Koordinator" class="form-control koordinator" data-required="1" data-tipe="integer" maxlength="2" disabled> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Wilayah</span>
					</div>
					<div class="col-lg-6">
						<select class="wilayah" name="wilayah[]" multiple="multiple" width="100%" placeholder="Pilih Wilayah" data-required="1">
							<option value="all" > All </option>
							<?php foreach ($list_wilayah as $key => $v_wilayah): ?>
								<option value="<?php echo $v_wilayah['id']; ?>" > <?php echo $v_wilayah['nama']; ?> </option>
							<?php endforeach ?>
						</select>
						<!-- <input type="text" placeholder="Unit" class="form-control unit" data-required="1" disabled> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Unit</span>
					</div>
					<div class="col-lg-6">
						<select class="unit" name="unit[]" multiple="multiple" width="100%" placeholder="Pilih Unit" data-required="1">
							<option value="all" > All </option>
							<?php foreach ($list_unit as $key => $v_unit): ?>
								<option value="<?php echo $v_unit['id']; ?>" > <?php echo $v_unit['nama']; ?> </option>
							<?php endforeach ?>
						</select>
						<!-- <input type="text" placeholder="Unit" class="form-control unit" data-required="1" disabled> -->
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">
						<span>Level</span>
					</div>
					<div class="col-lg-3">
						<select class="form-control level" data-required="1">
							<option value="">-- Pilih Level --</option>
							<option value="0">0</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
						</select>
						<!-- <input type="text" placeholder="Wilayah" class="form-control wilayah" data-required="1" disabled> -->
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12 no-padding">
			<div class="col-lg-12">
				<button type="button" class="btn btn-primary cursor-p pull-right" onclick="pegawai.save();"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</div>
	</div>
</div>