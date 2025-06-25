<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="v-center">
			<th class="text-center">Unit</th>
			<td class="text-left" colspan="3"><?php echo $v_data['nama']; ?></td>
		</tr>
		<tr class="v-center">
			<th class="col-sm-1 text-center" rowspan="2">Peternak</th>
			<th class="text-center" rowspan="2">Kandang</th>
			<th class="text-center" rowspan="2">Populasi</th>
			<th class="text-center" rowspan="2">Noreg</th>
			<th class="text-center" rowspan="2">Umur</th>
			<th class="text-center" colspan="5">Obat</th>
			<th class="text-center" colspan="4">Rencana Kirim</th>
		</tr>
		<tr class="v-center">
			<th class="col-sm-1 text-center">Kategori</th>
			<th class="col-sm-1 text-center">Nama</th>
			<th class="text-center">Isi<br>Kemasan</th>
			<th class="text-center">Bentuk</th>
			<th class="col-sm-1 text-center">Supplier</th>
			<th class="col-sm-2 text-center">Tanggal</th>
			<th class="col-sm-1 text-center">Jumlah<br>(Kemasan)</th>
			<th class="col-sm-1 text-center">Jumlah<br>(Isi)</th>
			<th class="col-sm-1 text-center">DO</th>
		</tr>
		<?php foreach ($v_data['detail'] as $k_detail => $v_detail): ?>
			<tr class="child inactive v-center data" style="height: 45px;" data-noreg="<?php echo $v_detail['noreg']; ?>">
				<td class="grey nama" data-idpeternak="<?php echo $v_detail['nomor']; ?>"><?php echo $v_detail['nama']; ?></td>
				<td class="text-center grey kandang"><?php echo strlen($v_detail['kandang']) > 1 ? $v_detail['kandang'] : '0'.$v_detail['kandang']; ?></td>
				<td class="text-right grey populasi"><?php echo angkaRibuan($v_detail['populasi']); ?></td>
				<td class="grey noreg"><?php echo $v_detail['noreg']; ?></td>
				<td class="text-right grey umur">
					<?php
						$tgl_docin = $v_detail['tgl_docin'];
						$today = date('Y-m-d');

						$selisih = selisihTanggal($tgl_docin, $today);

						$umur = $selisih+1;

						echo $umur;
					?>
				</td>
				<td>
					<select class="form-control kategori" onchange="dv.set_item_voadip(this)" data-required="1">
						<option value="">-- Pilih Kategori --</option>
						<?php foreach ($kategori_voadip as $k_kat => $v_kat): ?>
							<option value="<?php echo $k_kat; ?>"><?php echo $v_kat; ?></option>
						<?php endforeach ?>
					</select>
				</td>
				<td>
					<select class="form-control barang" onchange="dv.set_item_value(this)" data-required="1">
						<option value="">-- Pilih Barang --</option>
					</select>
				</td>
				<td class="text-right isi"></td>
				<td class="bentuk"></td>
				<td>
					<select class="form-control supplier" data-required="1">
						<option value="">-- Pilih Supplier --</option>
						<?php foreach ($supplier as $k_supl => $v_supl): ?>
							<option value="<?php echo $v_supl['nomor']; ?>"><?php  echo $v_supl['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</td>
				<td>
					<div class="col-sm-12 no-padding">
						<div class="input-group date datetimepicker" name="tgl_rcn_kirim">
					        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</td>
				<td>
					<input type="text" class="form-control text-right jml_kemasan" placeholder="Kemasan" data-tipe="decimal" maxlength="8" onkeyup="dv.hit_do(this)" data-required="1">
				</td>
				<td>
					<input type="text" class="form-control text-right jml_isi" placeholder="Isi" data-tipe="integer" maxlength="8" onkeyup="dv.hit_do(this)" data-required="1">
				</td>
				<td>
					<input type="text" class="form-control text-right jml_do" placeholder="DO" data-tipe="decimal" maxlength="15" data-required="1" readonly="">
					<div class="btn-ctrl">
						<span onclick="dv.removeRowChild(this)" class="btn_del_row_2x hide"></span>
						<span onclick="dv.addRowChild(this)" class="btn_add_row_2x"></span>
					</div>
				</td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr class="v-center">
		<th class="text-center">Unit</th>
		<td class="text-left" colspan="3">-</td>
	</tr>
	<tr class="v-center">
		<th class="col-sm-2 text-center" rowspan="2">Peternak</th>
		<th class="text-center" rowspan="2">Kandang</th>
		<th class="text-center" rowspan="2">Populasi</th>
		<th class="text-center" rowspan="2">Noreg</th>
		<th class="text-center" rowspan="2">Umur</th>
		<th class="text-center" colspan="6">Obat</th>
		<th class="text-center" colspan="4">Rencana Kirim</th>
	</tr>
	<tr class="v-center">
		<th class="col-sm-1 text-center">Kategori</th>
		<th class="col-sm-1 text-center">Nama</th>
		<th class="text-center">Dosis/Ekor</th>
		<th class="text-center">Isi<br>Kemasan</th>
		<th class="text-center">Bentuk</th>
		<th class="col-sm-2 text-center">Supplier</th>
		<th class="col-sm-1 text-center">Tgl</th>
		<th class="text-center">Jumlah<br>(Kemasan)</th>
		<th class="text-center">Jumlah<br>(Isi)</th>
		<th class="text-center">DO</th>
	</tr>
	<tr>
		<td colspan="15">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>