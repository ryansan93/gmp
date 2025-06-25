<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Plasma</label>
	</div>
	<div class="col-xs-12 no-padding">
		<select class="form-control mitra" type="text" data-required="1">
			<option value="">Pilih Plasma</option>
			<?php foreach ($mitra as $k_mitra => $v_mitra): ?>
				<option value="<?php echo $v_mitra['nomor']; ?>"><?php echo strtoupper($v_mitra['nama'].' ('.$v_mitra['kode'].')'); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Kandang</label>
	</div>
	<div class="col-xs-12 no-padding">
		<select class="form-control kandang" type="text" data-required="1">
			<option value="">Pilih Kandang</option>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Plasma</label>
	</div>
	<div class="col-xs-12 no-padding">
		<textarea class="form-control alamat_plasma" placeholder="Alamat" disabled="disabled" data-required="1"></textarea>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<table class="table table-bordered" style="margin-bottom: 0px;">
		<tbody>
			<tr>
				<td class="col-xs-8 data lat_long">
					Get Location
				</td>
				<td class="col-xs-4">
					<button type="button" class="btn btn-default col-xs-12" onclick="pp.getLocation(this)" data-ismobile="<?php echo $isMobile; ?>">
						<i class="fa fa-map-marker"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td class="col-xs-8 data foto_kunjungan">
					Get Photo
				</td>
				<td class="col-xs-4">
					<div class="col-xs-12 no-padding btn btn-default attachment">
						<label class="col-xs-12" style="padding: 0px 5px 0px 0px;">
							<input style="display: none;" class="file_lampiran no-check" accept="image/*" capture="camera" type="file" name="foto_kunjungan" onchange="pp.compress_img(this)" />
		                	<i class="fa fa-camera cursor-p col-xs-12 text-center"></i> 
		              	</label>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary col-xs-12" onclick="pp.save()"><i class="fa fa-save"></i> Simpan Data</button>
</div>