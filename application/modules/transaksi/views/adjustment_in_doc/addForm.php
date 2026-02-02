<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal Adjust</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Plasma</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control mitra" data-required="1">
				<option value="">-- Pilih Plasma --</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Noreg</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control noreg" data-required="1">
				<option value="">-- Pilih Noreg --</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Jenis DOC</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control barang" data-required="1">
				<option value="">-- Pilih Barang --</option>
				<?php foreach ($barang as $key => $value) { ?>
					<option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right harga" placeholder="Harga" data-tipe="decimal">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Jumlah Adjust</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Keterangan</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control keterangan" data-required="1"></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="aid.save()"><i class="fa fa-save"></i> Simpan</button>
</div>