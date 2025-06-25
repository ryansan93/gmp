<div class="modal-header">
	<span class="modal-title"><b>PENANGGUNG JAWAB KANDANG</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Noreg</label>
			</div>
			<div class="col-xs-10 no-padding">
				<label class="control-label">: <?php echo $data['noreg']; ?></label>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Nama</label>
			</div>
			<div class="col-xs-10 no-padding">
				<label class="control-label">: <?php echo $data['nama_mitra']; ?></label>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-2 no-padding">
				<label class="control-label">Tgl DOC In</label>
			</div>
			<div class="col-xs-10 no-padding">
				<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_docin'], '-', ' ')); ?></label>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">PPL</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control ppl" data-required="1" disabled="disabled" data-val="<?php echo $data['nik_ppl']; ?>">
					<option value="">-- Pilih PPL --</option>
					<?php foreach ($data['ppl'] as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['nik'] == $data['nik_ppl'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Kanit</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control kanit" data-required="1" disabled="disabled" data-val="<?php echo $data['nik_kanit']; ?>">
					<option value="">-- Pilih Kanit --</option>
					<?php foreach ($data['kanit'] as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['nik'] == $data['nik_kanit'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Marketing</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control marketing" data-required="1" disabled="disabled" data-val="<?php echo $data['nik_marketing']; ?>">
					<option value="">-- Pilih Marketing --</option>
					<?php foreach ($data['marketing'] as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['nik'] == $data['nik_marketing'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Koordinator Area</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control korwil" data-required="1" disabled="disabled" data-val="<?php echo $data['nik_koordinator']; ?>">
					<option value="">-- Pilih KoAr --</option>
					<?php foreach ($data['koordinator'] as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['nik'] == $data['nik_koordinator'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding edit">
			<button type="button" class="col-xs-12 btn btn-primary" onclick="rdim.cekForm(this)" data-tujuan="edit"><i class="fa fa-edit"></i> Edit</button>
		</div>
		<div class="col-xs-12 no-padding not-edit hide">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" onclick="rdim.cekForm(this)" data-tujuan="not-edit"><i class="fa fa-times"></i> Batal</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rdim.editPenanggungJawab(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
			</div>
		</div>
	</div>
</div>