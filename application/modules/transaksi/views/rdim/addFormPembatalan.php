<div class="modal-header">
	<span class="modal-title"><b>TAMBAH PEMBATALAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Nama</label>
			</div>
			<div class="col-xs-10 no-padding">
				<select class="form-control mitra" data-required="1" >
                    <option value="">-- Pilih Plasma --</option>
                    <?php foreach ($mitra as $key => $value) { ?>
                        <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nama'].' ('.$value['kab_kota'].')'); ?></option>
                    <?php } ?>
                </select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Noreg</label>
			</div>
			<div class="col-xs-10 no-padding">
				<select class="form-control noreg" data-required="1" disabled>
                    <option value="">-- Pilih Noreg --</option>
                </select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Alasan Batal</label>
			</div>
			<div class="col-xs-10 no-padding">
				<textarea class="form-control alasan_batal" data-required="1" placeholder="Alasan Batal"></textarea>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="rdim.savePembatalan(this)"><i class="fa fa-save"></i> SIMPAN</button>
	</div>
</div>