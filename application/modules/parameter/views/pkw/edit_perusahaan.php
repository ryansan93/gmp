<div class="col-sm-12">
	<form class="form form-horizontal" role="form">
		<div name="data-perusahaan">
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Kode</label>
				<div class="col-sm-2">
					<input value="<?php echo $data->kode; ?>" class="form-control" id="kode" readonly>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Perusahaan</label>
				<div class="col-sm-3">
					<input value="<?php echo $data['perusahaan']; ?>" class="form-control" id="nama_perusahaan" required>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Alamat</label>
				<div class="col-sm-4">
					<textarea class="form-control" id="alamat" required><?php echo $data['alamat']; ?></textarea>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Kota</label>
				<div class="col-sm-2">
					<input value="<?php echo $data['d_kota']['nama']; ?>" data-id="<?php echo $data['kota']; ?>" class="form-control autocomplete_lokasi" id="kota" placeholder="Kabupaten / Kota" required>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">NPWP</label>
				<div class="col-sm-3">
					<input value="<?php echo $data['npwp']; ?>" class="form-control text-left" id="npwp" required>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<hr>
					<button type="button" class="btn btn-primary edit" onclick="pkw.edit_perusahaan(this)" data-id="<?php echo $data->id; ?>" data-version="<?php echo $data->version; ?>" ><i class="fa fa-edit"></i> Edit </button>
					<button type="button" class="btn btn-danger back" data-href="perusahaan" onclick="pkw.cancel(this)"><i class="fa fa-times"></i> Batal </button>
				</div>
			</div>
		</div>
	</form>
</div>