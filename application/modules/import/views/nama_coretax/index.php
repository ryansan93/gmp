<div class="row content-panel detailed">
	<div class="col-lg-12">
		<div class="col-lg-1 no-padding">Jenis Data</div>
		<div class="col-lg-3">
			<select class="form-control" id="tipe_import_nama_coretax">
				<option value="">- Pilih -</option>
				<option value="peternak">Peternak</option>
				<option value="pelanggan">Pelanggan</option>
				<option value="ekspedisi">Ekspedisi</option>
			</select>
		</div>
		<div class="col-lg-3">
			<button type="button" class="btn btn-success" onclick="ncx.download_template()"><i class="fa fa-download"></i> Download Template</button>
		</div>
	</div>
	<div class="col-lg-12">&nbsp;</div>
	<div class="col-lg-12">
		<div class="col-lg-1 no-padding">Attach File</div>
		<div class="col-lg-9" style="padding-top: 2px;">
            <a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
            <label class="" style="margin-bottom: 0px;">
                <input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="ncx.showNameFile(this)" data-name="name" data-allowtypes="xlsx" data-required="1">
                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment"></i>
            </label>
        </div>
        <div class="col-lg-2 no-padding">
        	<button type="button" class="btn btn-primary pull-right" onclick="ncx.upload()"><i class="fa fa-upload"></i> Upload</button>
        </div>
	</div>
	<div class="col-lg-12">
		<hr>
	</div>
	<div class="col-lg-12">
		<b>* Pilih Jenis Data (Peternak / Pelanggan / Ekspedisi), lalu klik Download Template untuk mendapatkan data NOMOR, NIK, NPWP terbaru.</b><br>
		<b>* Isi/lengkapi kolom NAMA CORETAX pada file template tersebut, lalu upload kembali di sini.</b><br>
		<b>* Header pada file jangan dihapus. Kolom yang dipakai: <u>NOMOR</u>, <u>NIK</u>, <u>NPWP</u>, <u>NAMA CORETAX</u> (kolom lain diabaikan).</b><br>
		<b>* Data dicocokkan berdasarkan NOMOR terlebih dahulu; jika NOMOR kosong maka dicocokkan berdasarkan NIK; jika NIK juga kosong maka dicocokkan berdasarkan NPWP.</b><br>
		<b>* Baris yang tidak ditemukan padanannya (NOMOR/NIK/NPWP tidak cocok) akan dilaporkan setelah proses upload.</b>
	</div>
</div>
