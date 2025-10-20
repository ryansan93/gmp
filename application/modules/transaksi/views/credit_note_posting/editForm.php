<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control nomor" placeholder="No. CN" value="<?php echo $data['nomor']; ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding">
		<div class="col-xs-12 no-padding"><label class="label-control">Jenis CN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_cn" data-required="1" disabled>
				<?php foreach ($jenis_cn as $key => $value) { ?>
					<?php
						$selected = null;
						if ( $key == $data['jenis_cn'] ) {
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
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal CN</label></div>
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
		<div class="col-xs-12 no-padding"><label class="label-control">Nilai CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right nilai_cn" data-tipe="decimal" placeholder="Nilai CN" value="<?php echo angkaDecimal($data['tot_cn']); ?>" data-required="1">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding" style="padding-right: 10px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. Dokumen CN</label></div>
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
		<div class="col-xs-12 no-padding"><label class="label-control">Keterangan CN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_cn" placeholder="Keterangan CN" data-required="1"><?php echo $data['ket_cn']; ?></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.edit(this)" data-kode="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div>