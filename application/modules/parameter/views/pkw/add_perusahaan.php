<div class="col-sm-12">
	<form class="form form-horizontal" role="form">
		<div name="data-perusahaan">
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Kode</label>
				<div class="col-sm-2">
					<input class="form-control" id="kode" placeholder="Kode" readonly>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Perusahaan</label>
				<div class="col-sm-3">
					<input class="form-control" id="nama_perusahaan" placeholder="Nama Perusahaan" required>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Alamat</label>
				<div class="col-sm-4">
					<textarea class="form-control" id="alamat" placeholder="Alamat Perusahaan" required></textarea>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">Kota</label>
				<div class="col-sm-2">
					<input class="form-control autocomplete_lokasi" id="kota" placeholder="Kabupaten / Kota" required>
				</div>
			</div>
			<div class="form-group d-flex align-items-center">
				<label class="col-sm-2 label-left">NPWP</label>
				<div class="col-sm-3">
					<input class="form-control text-left" id="npwp" placeholder="No. NPWP" required>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<hr>
					<button type="button" class="btn btn-primary save" onclick="pkw.save_perusahaan()"><i class="fa fa-save"></i> Simpan </button>
					<button type="button" class="btn btn-danger back" data-href="perusahaan" onclick="pkw.cancel(this)"><i class="fa fa-times"></i> Batal </button>
				</div>
			</div>
		</div>
	</form>
</div>