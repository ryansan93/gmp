<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
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
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Gudang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control gudang" data-required="1">
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
			<select class="form-control barang" data-required="1">
				<option value="">-- Pilih Barang --</option>
				<?php foreach ($barang as $key => $value): ?>
					<option value="<?php echo $value['kode']; ?>" data-decimal="<?php echo $value['desimal_harga']; ?>" data-hithrgjual="<?php echo $value['hit_hrg_jual']; ?>" ><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Harga Beli</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right hrg_beli" placeholder="Harga" onblur="aiv.hitHrgJual(this)">
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-left: 5px; padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Harga Jual</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right hrg_jual" placeholder="Harga">
		</div>
	</div>
	<div class="col-xs-4 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Jumlah Adjust</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1">
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
	<button type="button" class="col-xs-12 btn btn-primary" onclick="aiv.save()"><i class="fa fa-save"></i> Simpan</button>
</div>