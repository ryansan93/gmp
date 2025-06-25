<div class="col-sm-12">
	<form class="form form-horizontal" role="form">
		<div name="data-korwil">
			<div class="form-group perwakilan">
				<label class="col-sm-1 control-label label-right">Korwil</label>
				<div class="col-sm-2">
					<input class="form-control autocomplete_wilayah" id="perwakilan" placeholder="Nama Korwil" data-tipe="PW" required>
				</div>
			</div>
			<div class="form-group kota">
				<label class="col-sm-1 control-label label-right">Nama Unit</label>
				<div class="col-sm-2">
					<input class="form-control autocomplete_kota_kab" id="unit" placeholder="Nama Kota / Kab" onchange="pkw.set_required(this)" data-prev="perwakilan">
				</div>
				<div class="col-sm-2">
					<input class="form-control autocomplete_kota_kab" id="kode_unit" placeholder="Kode" maxlength="3">
				</div>
				<div class="col-sm-2">
					<button type="button" class="btn btn-default add" onclick="pkw.add_row_unit(this)"><i class="fa fa-plus"></i></button>
					<button type="button" class="btn btn-default remove hide" onclick="pkw.remove_row_unit(this)"><i class="fa fa-minus"></i></button>
					<!-- <button type="button" class="btn btn-default save" onclick="pkw.save_wilayah(this)" data-tipe="kecamatan"><i class="fa fa-check"></i> Simpan </button> -->
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<hr>
					<button type="button" class="btn btn-primary save" onclick="pkw.save_korwil()" data-tipe="korwil"><i class="fa fa-save"></i> Simpan </button>
					<button type="button" class="btn btn-danger back" data-href="korwil" onclick="pkw.cancel(this)"><i class="fa fa-times"></i> Batal </button>
				</div>
			</div>
		</div>
	</form>
</div>