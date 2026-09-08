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
			<select class="form-control param_getstok mitra" data-required="1">
				<option value="">-- Pilih Plasma --</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Noreg</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control param_getstok noreg" data-required="1">
				<option value="">-- Pilih Noreg --</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label">Barang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control param_getstok barang" data-required="1">
				<option value="">-- Pilih Barang --</option>
				<?php foreach ($barang as $key => $value) { ?>
					<option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<button type="button" class="btn btn-primary" onclick="aivs.getStokSiklus()"><i class="fa fa-search"></i> Cek Stok</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Sisa Stok Kandang</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right sisa_stok" placeholder="Sisa Stok" readonly>
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-right: 5px; padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Jumlah Adjust</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1">
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right harga" placeholder="Harga" data-tipe="decimal">
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
	<button type="button" class="col-xs-12 btn btn-primary" onclick="aivs.save()"><i class="fa fa-save"></i> Simpan</button>
</div>
