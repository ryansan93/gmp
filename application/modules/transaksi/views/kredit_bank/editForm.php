<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase kode" placeholder="kode" value="<?php echo $data['kode']; ?>" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date" id="tanggal">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" data-val="<?php echo $data['tanggal']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok Pinjaman</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase pokok_pinjaman" data-required="1" placeholder="Pinjaman" data-tipe="decimal" onblur="kb.hitungAngsuran()" value="<?php echo angkaDecimal($data['pokok_pinjaman']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-6 no-padding">
			<select class="form-control perusahaan" data-required="1">
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
			</select>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase bunga" data-required="1" placeholder="Bunga" data-tipe="decimal" onblur="kb.hitungAngsuran()" value="<?php echo angkaDecimal($data['bunga']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Jenis Kredit</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase jenis_kredit" data-required="1" placeholder="Jenis Kredit" value="<?php echo angkaDecimal($data['jenis_kredit']); ?>">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga Per Tahun (%)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase bunga_per_tahun" data-required="1" placeholder="Bunga" data-tipe="decimal" maxlength="6" value="<?php echo angkaDecimal($data['bunga_per_tahun']); ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Bank Pemberi Pinjaman</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase bank" data-required="1" placeholder="BANK" value="<?php echo $data['bank']; ?>">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase tenor" data-required="1" placeholder="Tenor" data-tipe="angka" maxlength="3" onblur="kb.hitungAngsuran()" value="<?php echo $data['tenor']; ?>">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Agunan</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control text-left uppercase agunan" data-required="1" placeholder="Agunan" value="<?php echo $data['agunan']; ?>">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok + Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase angsuran" data-required="1" placeholder="Angsuran" data-tipe="decimal" value="<?php echo angkaDecimal($data['angsuran']); ?>" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">No. Dokumen</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control text-left uppercase no_dokumen" data-required="1" placeholder="No. Dokumen" value="<?php echo $data['no_dokumen']; ?>">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
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
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_angsuran" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th colspan="2">ANGSURAN</th>
					<th class="col-xs-1">POKOK</th>
					<th class="col-xs-1">BUNGA</th>
					<th class="col-xs-2">JATUH TEMPO</th>
					<th class="col-xs-2">TANGGAL BAYAR</th>
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
						<td class="text-right jumlah" data-val="<?php echo $v_det['jumlah_angsuran']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran']); ?></td>
						<td class="text-right pokok" data-val="<?php echo $v_det['jumlah_angsuran_pokok']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran_pokok']); ?></td>
						<td class="text-right bunga" data-val="<?php echo $v_det['jumlah_angsuran_bunga']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran_bunga']); ?></td>
						<td class="tgl_jatuh_tempo" data-val="<?php echo $v_det['tgl_jatuh_tempo']; ?>"><?php echo strtoupper(tglIndonesia($v_det['tgl_jatuh_tempo'], '-', ' ', true)); ?></td>
						<td>
							<div class="input-group date tgl_bayar">
				                <input type="text" class="form-control uppercase text-center" placeholder="Tanggal" data-val="<?php echo $v_det['tgl_bayar']; ?>" <?php echo $disabled; ?> />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kb.edit(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div>