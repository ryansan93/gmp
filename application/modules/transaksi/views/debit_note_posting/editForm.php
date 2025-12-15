<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal Pakai DN</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Jenis DN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_dn" data-required="1">
				<?php foreach ($jenis_dn as $key => $value) { ?>
					<?php
						$selected = null;
						if ( $key == $data['jenis_dn'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control dn" data-required="1" data-kode="<?php echo $data['id']; ?>">
				<option value="<?php echo $data['no_dn']; ?>" data-totdn=<?php echo $data['sisa']; ?> selected ><?php echo str_replace('-', '/', $data['tgl_dn']).' | '.$data['no_dok']; ?></option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Nilai DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right nilai_dn" data-tipe="decimal" placeholder="Nilai DN" data-required="1" value="<?php echo angkaDecimal($data['sisa']); ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Total Pakai DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right pakai_dn" data-tipe="decimal" placeholder="Total Pakai DN" data-required="1" value="<?php echo angkaDecimal($data['tot_pakai']); ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">No. SJ</th>
					<th class="col-xs-2">Nominal</th>
					<th class="col-xs-2">Sisa Tagihan</th>
					<th class="col-xs-3">Pakai DN</th>
					<th class="col-xs-1">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($detail as $k_det => $v_det) { ?>
					<tr>
						<td>
							<select class="form-control no_sj" data-required="1">
								<option value="<?php echo $v_det['nomor']; ?>" data-tagihan="<?php echo $v_det['tagihan']; ?>" data-sisatagihan="<?php echo $v_det['sisa']; ?>" selected ><?php echo $v_det['no_sj']; ?></option>
							</select>
						</td>
						<td class="text-right tagihan"><?php echo angkaDecimal($v_det['tagihan']); ?></td>
						<td class="text-right sisa"><?php echo angkaDecimal($v_det['sisa']); ?></td>
						<td>
							<div class="col-xs-2 no-padding" style="padding-right: 5px;">
								<button type="button" class="col-xs-12 btn btn-default" onclick="dn.samakanSisaTagihan(this)"><i class="fa fa-arrow-right"></i></button>
							</div>
							<div class="col-xs-10 no-padding" style="padding-left: 5px;">
								<input type="text" class="col-xs-12 form-control pakai text-right" data-tipe="decimal" data-required="1" onblur="dn.hitTotalPakai()" value="<?php echo angkaDecimal($v_det['pakai']); ?>" >
							</div>
						</td>
						<td>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-6 no-padding" style="padding-right: 5px;">
									<button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRow(this)"><i class="fa fa-minus"></i></button>
								</div>
								<div class="col-xs-6 no-padding" style="padding-left: 5px;">
									<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRow(this)"><i class="fa fa-plus"></i></button>
								</div>
							</div>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.edit(this)" data-kode="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div>

<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control nomor" placeholder="No. DN" value="<?php echo $data['nomor']; ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Jenis DN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_dn" data-required="1" disabled>
				<?php foreach ($jenis_dn as $key => $value) { ?>
					<?php
						$selected = null;
						if ( $key == $data['jenis_dn'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal DN</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Nilai DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right nilai_dn" data-tipe="decimal" placeholder="Nilai DN" value="<?php echo angkaDecimal($data['tot_dn']); ?>" data-required="1">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding" style="padding-right: 10px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. Dokumen DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control no_dok uppercase" placeholder="No. Dokumen (MAX:50)" data-required="1" value="<?php echo $data['no_dok']; ?>">
		</div>
	</div>
	<div class="col-xs-9 no-padding">
		<div class="col-xs-12 no-padding">&nbsp;</div>
		<div class="col-xs-12 no-padding">
			<a href="uploads/<?php echo $data['path']; ?>" target="_blank"><?php echo $data['path']; ?></a>
			<label class="">
				<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Credit Note" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
				<i class="glyphicon glyphicon-paperclip cursor-p"></i>
			</label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php foreach ($supplier as $key => $value): ?>
					<?php
						$selected = null;
						if ( $data['jenis'] == 'supplier' && $value['nomor'] == $data['supplier'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $value['nomor']; ?>" data-jenis="supplier" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php endforeach ?>
				<?php foreach ($pelanggan as $key => $value): ?>
					<?php
						$selected = null;
						if ( $data['jenis'] == 'bakul' && $value['nomor'] == $data['supplier'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $value['nomor']; ?>" data-jenis="bakul" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php endforeach ?>
				<?php foreach ($ekspedisi as $key => $value): ?>
					<?php
						$selected = null;
						if ( $data['jenis'] == 'ekspedisi' && $value['nomor'] == $data['supplier'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $value['nomor']; ?>" data-jenis="ekspedisi" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php endforeach ?>
				<?php foreach ($mitra as $key => $value): ?>
					<?php
						$selected = null;
						if ( $data['jenis'] == 'mitra' && $value['nomor'] == $data['supplier'] ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $value['nomor']; ?>" data-jenis="mitra" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Keterangan DN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_dn" placeholder="Keterangan DN" data-required="1"><?php echo $data['ket_dn']; ?></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.edit(this)" data-kode="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div> -->