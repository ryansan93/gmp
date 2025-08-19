<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase kode" placeholder="kode" value="<?php echo $data['kode']; ?>" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date" id="tanggal">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" data-val="<?php echo $data['tanggal']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Kendaraan</label></div>
		<div class="col-xs-8 no-padding">
			<select class="form-control kendaraan" data-required="1">
				<option value="">-- Pilih Kendaraan --</option>
				<?php if ( !empty($kendaraan) ): ?>
					<?php foreach ($kendaraan as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['id'] == $data['kendaraan_id'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['kode_unit'].' | '.$value['merk'].' '.$value['tipe'].' ('.$value['tahun'].')'); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase harga" data-required="1" placeholder="Harga" data-tipe="decimal" value="<?php echo angkaDecimal($data['harga']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control uppercase perusahaan" data-required="1" placeholder="Perusahaan" data-val="<?php echo $data['d_perusahaan']['kode']; ?>" value="<?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?>" readonly>
			<!-- <select class="form-control perusahaan" data-required="1">
				<option value="">-- Pilih Perusahaan --</option>
				<?php if ( !empty($perusahaan) ): ?>
					<?php foreach ($perusahaan as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['kode'] == $data['perusahaan'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tanda Jadi</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase tanda_jadi" data-required="1" placeholder="Tanda Jadi" data-tipe="decimal" value="<?php echo angkaDecimal($data['tanda_jadi']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Merk & Jenis</label></div>
		<div class="col-xs-10 no-padding">
			<input type="text" class="form-control uppercase merk_jenis" data-required="1" placeholder="Merk & Jenis" value="<?php echo $data['merk_jenis']; ?>" readonly >
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">DP & Angsuran ke 1</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase dp" data-required="1" placeholder="DP" data-tipe="decimal" value="<?php echo angkaDecimal($data['dp']); ?>" >
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Warna</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase warna" data-required="1" placeholder="Warna" value="<?php echo $data['warna']; ?>" readonly>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Bayar</label></div>
		<div class="col-xs-6 no-padding">
			<div class="input-group date" id="tgl_bayar_angsuran1">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" data-val="<?php echo $data['tgl_bayar']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
		<div class="col-xs-2 no-padding" style="padding-left: 10px;">
			<div class="col-xs-12 no-padding attachment">
				<a name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $data['lampiran']; ?>">
					<i class="fa fa-file" style="font-size: 16px;"></i>
				</a>
				<label class="control-label">
					<input style="display: none;" class="file_lampiran no-check lampiran_angsuran1" type="file" data-name="name" data-required="1" onchange="kk.showNameFile(this, 0)" data-key="bayar_angsuran1" />
					<i class="fa fa-paperclip cursor-p text-center" title="Lampiran" style="font-size: 20px;"></i> 
				</label>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Tahun</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase tahun" data-required="1" placeholder="Tahun" data-tipe="angka" maxlength="4" value="<?php echo $data['tahun']; ?>" readonly>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Angsuran Per Bulan</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase angsuran" data-required="1" placeholder="Angsuran" data-tipe="decimal" value="<?php echo angkaDecimal($data['angsuran']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase unit" data-required="1" placeholder="Unit" data-val="<?php echo $data['d_unit']['kode']; ?>" value="<?php echo strtoupper(str_replace('Kota ', '', str_replace('Kab ', '', $data['d_unit']['nama']))); ?>" readonly>
			<!-- <select class="form-control unit" data-required="1">
				<option value="">-- Pilih Unit --</option>
				<?php if ( !empty($unit) ): ?>
					<?php foreach ($unit as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['kode'] == $data['unit'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase tenor" data-required="1" placeholder="Tenor" data-tipe="angka" maxlength="3" onblur="kk.generateRowAngsuran()" value="<?php echo $data['tenor']; ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Peruntukan</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase karyawan" data-required="1" placeholder="Karyawan" data-val="<?php echo $data['d_peruntukan']['nik']; ?>" value="<?php echo strtoupper($data['d_peruntukan']['nama']); ?>" readonly>
			<!-- <select class="form-control karyawan" data-required="1">
				<option value="">-- Pilih Karyawan --</option>
				<?php if ( !empty($karyawan) ): ?>
					<?php foreach ($karyawan as $key => $value): ?>
						<?php
							$selected = null;
							if ( $value['nik'] == $data['peruntukan'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['jabatan'].' | '.$value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
		<div class="col-xs-6 no-padding">
			<div class="input-group date" id="tgl_jatuh_tempo">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" data-val="<?php echo $data['tgl_jatuh_tempo']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
	</div>
</div>
<?php
	$hide_bpkb = 'hide';
	if ( $data['lunas'] == 1 ) {
		$hide_bpkb = null;
	}
?>
<div class="col-xs-12 no-padding <?php echo $hide_bpkb; ?>">
	<div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">BPKB</label></div>
		<div class="col-xs-8 no-padding">
			<button type="button" class="btn btn-default" onclick="kk.modalBpkb(this)" data-kode="<?php echo $data['kode']; ?>">BPKB</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_angsuran" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-3">ANGSURAN</th>
					<th class="col-xs-2">JATUH TEMPO</th>
					<th class="col-xs-2">JUMLAH</th>
					<th class="col-xs-2">TANGGAL BAYAR</th>
					<th class="col-xs-1">LAMPIRAN</th>
					<th class="col-xs-2"></th>
				</tr>
			</thead>
			<tbody>
				<?php $row_isi = 0; ?>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<?php 
						$disabled = null;
						if ( !empty($v_det['tgl_bayar']) ) {
							$row_isi++;
						} else {
							if ( $v_det['angsuran_ke'] > ($row_isi+1) ) {
								$disabled = 'disabled';
							}
						}
					?>
					<tr class="data" data-kode="<?php echo $data['kode']; ?>" data-no="<?php echo $v_det['angsuran_ke']; ?>">
						<td><?php echo 'ANGSURAN KE '.$v_det['angsuran_ke']; ?></td>
						<td class="tgl_jatuh_tempo" data-val="<?php echo $v_det['tgl_jatuh_tempo']; ?>"><?php echo strtoupper(tglIndonesia($v_det['tgl_jatuh_tempo'], '-', ' ', true)); ?></td>
						<td class="text-right jumlah" data-val="<?php echo $v_det['jumlah_angsuran']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran']); ?></td>
						<td>
							<div class="input-group date tgl_bayar">
				                <input type="text" class="form-control uppercase text-center" placeholder="Tanggal" data-val="<?php echo $v_det['tgl_bayar']; ?>" <?php echo $disabled; ?> />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
						</td>
						<td class="text-center">
							<div class="col-xs-12 no-padding attachment">
								<?php
									$hide_lampiran = 'hide';
									$path_lampiran = null;
									if ( !empty($v_det['lampiran']) ) {
										$hide_lampiran = null;
										$path_lampiran = $v_det['lampiran'];
									}
								?>
								<a name="dokumen" class="text-right <?php echo $hide_lampiran; ?>" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $path_lampiran; ?>">
									<i class="fa fa-file" style="font-size: 16px;"></i>
								</a>
								<label class="control-label">
									<input style="display: none;" class="file_lampiran no-check lampiran_angsuran" type="file" data-name="name" onchange="kk.showNameFile(this, 0)" data-key="<?php echo 'ANGSURAN KE '.$v_det['angsuran_ke']; ?>" />
									<i class="fa fa-paperclip cursor-p text-center" title="Lampiran" style="font-size: 20px;"></i> 
								</label>
							</div>
						</td>
						<td></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kk.edit(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div>