<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase kode" placeholder="kode" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date" id="tanggal">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok Pinjaman</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase pokok_pinjaman" data-required="1" placeholder="Pinjaman" data-tipe="decimal" onblur="kb.hitungAngsuran()">
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
						<option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase bunga" data-required="1" placeholder="Bunga" data-tipe="decimal" onblur="kb.hitungAngsuran()">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Jenis Kredit</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase jenis_kredit" data-required="1" placeholder="Jenis Kredit">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga Per Tahun (%)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase bunga_per_tahun" data-required="1" placeholder="Bunga" data-tipe="decimal" maxlength="6">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Bank Pemberi Pinjaman</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase bank" data-required="1" placeholder="BANK">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase tenor" data-required="1" placeholder="Tenor" data-tipe="angka" maxlength="3" onblur="kb.hitungAngsuran()">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Agunan</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control text-left uppercase agunan" data-required="1" placeholder="Agunan">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok + Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase angsuran" data-required="1" placeholder="Angsuran" data-tipe="decimal" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">No. Dokumen</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control text-left uppercase no_dokumen" data-required="1" placeholder="No. Dokumen">
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
		<div class="col-xs-3 no-padding">
			<div class="input-group date" id="tgl_jatuh_tempo">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" />
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
				<tr>
					<td colspan="6">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kb.save()"><i class="fa fa-save"></i> Simpan</button>
</div>