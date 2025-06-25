<div class="col-sm-12">
	<form class="form form-horizontal" role="form">
		<div name="data-wilayah">
			<div class="form-group">
				<label class="col-sm-2 control-label label-right">Provinsi</label>
				<div class="col-sm-2">
					<input class="form-control autocomplete_lokasi" id="provinsi" placeholder="Nama Provinsi" data-tipe="PV" onchange="pkw.autocomplete_kota_kab()">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label label-right">Kota / Kab</label>
				<div class="col-sm-2">
					<select class="form-control jenis" placeholder="Kota / Kabupaten" onchange="pkw.autocomplete_kota_kab()">
						<option value="KT">Kota</option>
						<option value="KB">Kabupaten</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label label-right">Nama</label>
				<div class="col-sm-2">
					<input class="form-control autocomplete_lokasi" placeholder="Nama Kota / Kabupaten" id="nama" data-prev="provinsi" onchange="pkw.set_required(this)">
				</div>
			</div>
			<div class="form-group kecamatan">
				<div class="form-group">
					<div class="col-sm-12">
						<label class="col-sm-2 control-label label-right">Kecamatan</label>
						<div class="col-sm-2">
							<input class="form-control" id="kecamatan" placeholder="Nama Kecamatan" onchange="pkw.set_required(this)" data-prev="nama">
						</div>
						<div class="col-sm-2">
							<button type="button" class="btn btn-default add" onclick="pkw.add_row_kecamatan(this)"><i class="fa fa-plus"></i></button>
							<button type="button" class="btn btn-default remove hide" onclick="pkw.remove_row_kecamatan(this)"><i class="fa fa-minus"></i></button>
							<!-- <button type="button" class="btn btn-default save" onclick="pkw.save_wilayah(this)" data-tipe="kecamatan"><i class="fa fa-check"></i> Simpan </button> -->
						</div>
					</div>
					<div class="col-sm-5 no-padding" style="border-bottom:1px solid black; margin-top: 10px; margin-left: 35px;"></div>
				</div>
				<div class="form-group kelurahan">
					<div class="col-sm-12">
						<div class="col-sm-1"></div>
						<label class="col-sm-1 control-label label-right">Kelurahan</label>
						<div class="col-sm-2">
							<input class="form-control" id="kelurahan" placeholder="Nama Kelurahan" onchange="pkw.set_required(this)" data-prev="kecamatan">
						</div>
						<div class="col-sm-2">
							<button type="button" class="btn btn-default add" onclick="pkw.add_row_kelurahan(this)"><i class="fa fa-plus"></i></button>
							<button type="button" class="btn btn-default remove hide" onclick="pkw.remove_row_kelurahan(this)"><i class="fa fa-minus"></i></button>
							<!-- <button type="button" class="btn btn-default save" onclick="pkw.save_wilayah(this)" data-tipe="kelurahan"><i class="fa fa-check"></i> Simpan </button> -->
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-1"></div>
					<div class="col-sm-4 no-padding" style="border-bottom:1px solid black; margin-left: 35px;"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<hr>
					<button type="button" class="btn btn-primary save" onclick="pkw.save_lokasi()" data-tipe="kelurahan"><i class="fa fa-save"></i> Simpan </button>
					<button type="button" class="btn btn-danger back" data-href="wilayah" onclick="pkw.cancel(this)"><i class="fa fa-times"></i> Batal </button>
				</div>
			</div>
		</div>
	</form>
</div>