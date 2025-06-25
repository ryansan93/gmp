<?php if ( count($data_setting) > 0 ): ?>
	<?php foreach ($data_setting as $k_data => $v_data): ?>
		<tr class="v-center">
			<th class="text-center">Unit</th>
			<td class="nama_unit" colspan="3"><?php echo $v_data['nama']; ?></td>
			<td colspan="8">
				<div class="col-md-2"><b>Total</b></div>
				<div class="col-md-3"><?php echo angkaDecimal($v_data['tot_kg']); ?> <b>Kg</b></div>
				<div class="col-md-3"><?php echo angkaRibuan($v_data['tot_zak']); ?> <b>Zak</b></div>
			</td>
			<td>
				<button type="button" class="form-control btn-primary" title="Save Per Unit" data-id="<?php echo $v_data['id']; ?>" onclick="spm.save_per_unit(this)"><i class="fa fa-plus"></i></button>
			</td>
		</tr>
		<tr class="v-center">
			<th class="text-center col-md-2" rowspan="2">Peternak</th>
			<th class="text-center" rowspan="2">Kandang</th>
			<th class="text-center" rowspan="2">Populasi</th>
			<th class="text-center" rowspan="2">Noreg</th>
			<th class="text-center" rowspan="2">Umur</th>
			<th class="text-center col-md-1" rowspan="2">Pakan</th>
			<th class="text-center" colspan="3">Setting Kirim</th>
			<th class="text-center" colspan="3">Rencana Kirim</th>
			<th class="text-center col-md-1" rowspan="2">Ekspedisi</th>
		</tr>
		<tr class="v-center">
			<th class="text-center">Tgl</th>
			<th class="text-center">Jml<br>(Kg)</th>
			<th class="text-center">Jml<br>(Zak)</th>
			<th class="text-center col-md-1">Tgl</th>
			<th class="text-center">Jml<br>(Kg)</th>
			<th class="text-center">Jml<br>(Zak)</th>
		</tr>
		<?php foreach ($v_data['detail'] as $k_detail => $v_detail): ?>
			<tr class="data v-center" data-id="<?php echo $v_data['id']; ?>">
				<td><?php echo $v_detail['peternak']; ?></td>
				<td class="text-right kandang"><?php echo $v_detail['kandang']; ?></td>
				<td class="text-right populasi"><?php echo angkaRibuan($v_detail['populasi']); ?></td>
				<td class="noreg"><?php echo $v_detail['noreg']; ?></td>
				<td class="text-right umur"><?php echo $v_detail['umur']; ?></td>
				<td class="pakan" data-kode="<?php echo $v_detail['kode_pakan']; ?>"><?php echo $v_detail['pakan']; ?></td>
				<td class="setting_tgl text-center"><?php echo tglIndonesia($v_detail['tgl_kirim'], '-', ' '); ?></td>
				<td class="text-right setting_kg"><?php echo angkaDecimal($v_detail['jml_kg']); ?></td>
				<td class="text-right setting_zak"><?php echo angkaRibuan($v_detail['jml_zak']); ?></td>
				<td class="rcn_tgl">
					<div class="input-group date" name="tglRcnKirim">
				        <input type="text" class="form-control text-center" placeholder="Date" id="tglRcnKirim" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</td>
				<td class="rcn_kg">
					<input type="text" class="form-control text-right" placeholder="Kg" id="rcn_kg" data-required="1" data-tipe="decimal" maxlength="9" onblur="spm.hit_jml_zak(this)" />
				</td>
				<td class="rcn_zak">
					<input type="text" class="form-control text-right" placeholder="Zak" id="rcn_zak" data-required="1" data-tipe="integer" maxlength="7" readonly />
				</td>
				<td>
					<select class="form-control" name="ekspedisi">
						<option value="">-- Pilih --</option>
						<?php foreach ($ekspedisi as $k_ekspedisi => $v_ekspedisi): ?>
							<option value="<?php echo $v_ekspedisi['nomor']; ?>"><?php echo $v_ekspedisi['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="13">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>