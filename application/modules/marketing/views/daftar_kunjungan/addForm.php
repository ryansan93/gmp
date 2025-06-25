<!-- <div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-success col-xs-12 not-plg-baru" onclick="dk.plgBaru(this)"><i class="fa fa-plus"></i> Pelanggan Baru</button>
	<button type="button" class="btn btn-danger col-xs-12 plg-baru hide" onclick="dk.notPlgBaru(this)"><i class="fa fa-times"></i> Bukan Pelanggan Baru</button>
</div> -->
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding not-plg-baru" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Kab / Kota Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<select class="form-control lokasi" data-required="1">
			<option>-- Pilih Kab / Kota --</option>
			<?php foreach ($lokasi as $k_lok => $v_lok): ?>
				<option value='<?php echo json_encode($v_lok['id']); ?>'><?php echo strtoupper($v_lok['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru">
		<select class="form-control pelanggan not-plg-baru" data-required="1" disabled>
			<option value="">-- Pilih Pelanggan --</option>
			<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
				<option value="<?php echo $v_plg['nomor']; ?>" data-kabkota="<?php echo $v_plg['kab_kota'] ?>"><?php echo strtoupper($v_plg['nama'].' ('.$v_plg['nama_kab_kota'].')'); ?></option>
			<?php endforeach ?>
		</select>
	</div>
	<div class="col-xs-12 no-padding plg-baru hide">
		<input type="text" class="form-control nama_pelanggan plg-baru hide" placeholder="Nama Pelanggan" data-required="1">
	</div>
</div>
<div class="col-xs-12 no-padding not-plg-baru" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<textarea class="form-control alamat_plg" placeholder="Alamat" disabled="disabled" data-required="1"></textarea>
	</div>
</div>
<div class="col-xs-12 no-padding plg-baru hide" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Pelanggan</label>
	</div>
	<div class="col-xs-12" style="border: 1px solid black; border-radius: 3px; padding-top: 10px; padding-bottom: 15px;">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Kecamatan</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select class="form-control kecamatan_plg" data-required="1">
						<option value="">-- Pilih Kecamatan --</option>
						<?php foreach ($kecamatan as $k_kec => $v_kec): ?>
							<option value="<?php echo $v_kec['id']; ?>"><?php echo strtoupper($v_kec['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-6 no-padding" style="padding-right: 5px;">
					<div class="col-xs-12 no-padding">
						<label class="control-label">RT</label>
					</div>
					<div class="col-xs-12 no-padding">
						<input type="text" class="form-control rt_plg" data-required="1" placeholder="RT" data-tipe="angka" maxlength="3">
					</div>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px;">
					<div class="col-xs-12 no-padding">
						<label class="control-label">RW</label>
					</div>
					<div class="col-xs-12 no-padding">
						<input type="text" class="form-control rw_plg" data-required="1" placeholder="RW" data-tipe="angka" maxlength="3">
					</div>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Alamat</label>
				</div>
				<div class="col-xs-12 no-padding">
					<textarea class="form-control alamat_plg" placeholder="Alamat" disabled="disabled" data-required="1"></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding not-plg-baru" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Usaha</label>
	</div>
	<div class="col-xs-12 no-padding">
		<textarea class="form-control alamat_usaha" placeholder="Alamat" disabled="disabled" data-required="1"></textarea>
	</div>
</div>
<div class="col-xs-12 no-padding plg-baru hide" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Usaha</label>
	</div>
	<div class="col-xs-12" style="border: 1px solid black; border-radius: 3px; padding-top: 10px; padding-bottom: 15px;">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Kecamatan</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select class="form-control kecamatan_usaha" data-required="1">
						<option value="">-- Pilih Kecamatan --</option>
						<?php foreach ($kecamatan as $k_kec => $v_kec): ?>
							<option value="<?php echo $v_kec['id']; ?>"><?php echo strtoupper($v_kec['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-6 no-padding" style="padding-right: 5px;">
					<div class="col-xs-12 no-padding">
						<label class="control-label">RT</label>
					</div>
					<div class="col-xs-12 no-padding">
						<input type="text" class="form-control rt_usaha" data-required="1" placeholder="RT" data-tipe="angka" maxlength="3">
					</div>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px;">
					<div class="col-xs-12 no-padding">
						<label class="control-label">RW</label>
					</div>
					<div class="col-xs-12 no-padding">
						<input type="text" class="form-control rw_usaha" data-required="1" placeholder="RW" data-tipe="angka" maxlength="3">
					</div>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Alamat</label>
				</div>
				<div class="col-xs-12 no-padding">
					<textarea class="form-control alamat_usaha" placeholder="Alamat" disabled="disabled" data-required="1"></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Catatan Kunjungan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<textarea class="form-control catatan" placeholder="Catatan Kunjungan" data-required="1"></textarea>
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
					<button type="button" class="btn btn-default col-xs-12" onclick="dk.getLocation(this)" data-ismobile="<?php echo $isMobile; ?>">
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
							<input style="display: none;" class="file_lampiran no-check" accept="image/*" capture="camera" type="file" name="foto_kunjungan" onchange="dk.compress_img(this)" />
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
	<button type="button" class="btn btn-primary col-xs-12" onclick="dk.save()"><i class="fa fa-save"></i> Simpan Data</button>
</div>