<div id="form_status">
    <form class="form-horizontal" role="form">
    	<input type="hidden" name="nomor_pelanggan" data-nomor="<?php echo $data_detail->nomor; ?>">
    	<div class="form-group">
    		<label class="col-sm-4 text-right">Nama Pelanggan : </label>
    		<div class="col-sm-6"><span id="nonaktif_nama"><?php echo $data_detail['nama']; ?></span></div>
    	</div>
    	<div class="form-group">
    		<label class="col-sm-4 text-right">NIK : </label>
    		<div class="col-sm-6"><span id="nonaktif_nik"><?php echo $data_detail['nik']; ?></span></div>
    	</div>
    	<div class="form-group">
    		<label class="col-sm-4 text-right">Lampiran : </label>
    		<div class="col-sm-6 padding-top-5">
    			<label id="file_nonaktif" data-idnama="<?php // echo $data_detail['lampiran_nonaktif']['id']; ?>">
        			<input type="file" required="required" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="nonaktif_lampiran" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran Non Aktif">
					<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran NPWP"></i>
				</label>
    		</div>
    	</div>
    	<div class="form-group">
    		<label class="col-sm-4 text-right">Keterangan : </label>
    		<div class="col-sm-6">
                <!-- <input class="form-control" type="text" name="nonaktif_keterangan"> -->
                <textarea class="form-control" name="nonaktif_keterangan"></textarea>
            </div>
    	</div>
    </form>
</div>