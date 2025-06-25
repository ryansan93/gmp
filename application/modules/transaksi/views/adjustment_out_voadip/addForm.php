<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Gudang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control param_getsj gudang" data-required="1">
				<option value="">-- Pilih Gudang --</option>
				<?php foreach ($gudang as $key => $value): ?>
					<option value="<?php echo $value['id']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Barang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control param_getsj barang" data-required="1">
				<option value="">-- Pilih Barang --</option>
				<?php foreach ($barang as $key => $value): ?>
					<option value="<?php echo $value['kode']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal SJ</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
		        <input type="text" class="form-control text-center param_getsj" placeholder="Tanggal" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">&nbsp;</label></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-primary" onclick="aov.getSj()"><i class="fa fa-search"></i> Tampilkan SJ</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding"><label class="control-label">No. SJ</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control no_sj" data-required="1">
			<option value="">-- Pilih SJ --</option>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right harga" placeholder="Harga" readonly>
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-right: 5px; padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Sisa Stok</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right sisa_stok" placeholder="Sisa Stok" readonly>
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Jumlah Adjust</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1" onblur="aov.cekJumlahAdjust(this)">
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
	<button type="button" class="col-xs-12 btn btn-primary" onclick="aov.save()"><i class="fa fa-save"></i> Simpan</button>
</div>