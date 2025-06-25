<div class="row content-panel detailed">
	<div class="col-lg-12">
		<div class="col-lg-1 no-padding">Attach File</div>
		<div class="col-lg-9" style="padding-top: 2px;">
            <a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
            <label class="" style="margin-bottom: 0px;">
                <input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="tv.showNameFile(this)" data-name="name" data-allowtypes="xlsx" data-required="1">
                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment"></i> 
            </label>
        </div>
        <div class="col-lg-2 no-padding">
        	<button type="button" class="btn btn-primary pull-right" onclick="tv.upload()"><i class="fa fa-upload"></i> Upload</button>
        </div>
	</div>
	<div class="col-lg-12">
		<hr>
	</div>
	<div class="col-lg-12">
		<b>* Header pada file jangan di hapus dan usahakan semua file jadi text dan tidak ada function .</b><br>
		<b>* Angka dan huruf format sesuai di contoh file .</b><br>
		<b>* Pastikan semua data sudah ada di master dan sama persis (MITRA, KANDANG, UNIT, BARANG, GUDANG, PERUSAHAAN, KONTRAK, RDIM, DLL) .</b>
	</div>
	<div class="col-lg-12">&nbsp;</div>
	<div class="col-lg-12">
		<button type="button" class="btn btn-success" onclick="tv.download()"><i class="fa fa-download"></i> Download Contoh Excel</button>
	</div>
</div>