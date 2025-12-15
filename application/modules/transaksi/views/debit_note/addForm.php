<?php if ( $akses['a_submit'] == 1 ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control nomor" placeholder="No. DN" disabled>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Jenis DN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control jenis_dn" data-required="1">
					<?php foreach ($jenis_dn as $key => $value) { ?>
						<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>"><?php echo strtoupper($value['nama']); ?></option>
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
					<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
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
				<input type="text" class="form-control text-right nilai_dn" data-tipe="decimal" placeholder="Nilai DN" data-required="1">
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding" style="padding-right: 10px;">
			<div class="col-xs-12 no-padding"><label class="label-control">No. Dokumen DN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control no_dok uppercase" placeholder="No. Dokumen (MAX:50)" data-required="1">
			</div>
		</div>
		<div class="col-xs-9 no-padding">
			<div class="col-xs-12 no-padding">&nbsp;</div>
			<div class="col-xs-12 no-padding">
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
						<option value="<?php echo $value['nomor']; ?>" data-jenis="supplier"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
					<?php foreach ($pelanggan as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>" data-jenis="bakul"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
					<?php foreach ($ekspedisi as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>" data-jenis="ekspedisi"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
					<?php foreach ($mitra as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>" data-jenis="mitra"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="label-control">Keterangan DN</label></div>
			<div class="col-xs-12 no-padding">
				<textarea class="form-control ket_dn" placeholder="Keterangan DN" data-required="1"></textarea>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
<?php } else { ?>
	<h4>DEBIT NOTE</h4>
<?php } ?>