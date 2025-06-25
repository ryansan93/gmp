<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase kode" placeholder="kode" readonly>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date" id="tanggal">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" />
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
						<option value="<?php echo $value['id']; ?>"><?php echo strtoupper($value['kode_unit'].' | '.$value['merk'].' '.$value['tipe'].' ('.$value['tahun'].')'); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control uppercase perusahaan" data-required="1" placeholder="Perusahaan" readonly>
			<!-- <select class="form-control perusahaan" data-required="1">
				<option value="">-- Pilih Perusahaan --</option>
				<?php if ( !empty($perusahaan) ): ?>
					<?php foreach ($perusahaan as $key => $value): ?>
						<option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase harga" data-required="1" placeholder="Harga" data-tipe="decimal">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Merk & Jenis</label></div>
		<div class="col-xs-10 no-padding">
			<input type="text" class="form-control uppercase merk_jenis" data-required="1" placeholder="Merk & Jenis" readonly>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">DP & Angsuran ke 1</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase dp" data-required="1" placeholder="DP" data-tipe="decimal">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Warna</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase warna" data-required="1" placeholder="Warna" readonly>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Bayar</label></div>
		<div class="col-xs-6 no-padding">
			<div class="input-group date" id="tgl_bayar_angsuran1">
                <input type="text" class="form-control uppercase text-center" data-required="1" placeholder="Tanggal" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
		</div>
		<div class="col-xs-2 no-padding" style="padding-left: 10px;">
			<div class="col-xs-12 no-padding attachment">
				<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;">
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
			<input type="text" class="form-control text-right uppercase tahun" data-required="1" placeholder="Tahun" data-tipe="angka" maxlength="4" readonly>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Angsuran Per Bulan</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="form-control text-right uppercase angsuran" data-required="1" placeholder="Angsuran" data-tipe="decimal">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="form-control uppercase unit" data-required="1" placeholder="Unit" readonly>
			<!-- <select class="form-control unit" data-required="1">
				<option value="">-- Pilih Unit --</option>
				<?php if ( !empty($unit) ): ?>
					<?php foreach ($unit as $key => $value): ?>
						<option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-2 no-padding">
			<input type="text" class="form-control text-right uppercase tenor" data-required="1" placeholder="Tenor" data-tipe="angka" maxlength="3" onblur="kk.generateRowAngsuran()">
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Peruntukan</label></div>
		<div class="col-xs-8 no-padding">
			<input type="text" class="form-control uppercase karyawan" data-required="1" placeholder="Karyawan" readonly>
			<!-- <select class="form-control karyawan" data-required="1" disabled>
				<option value="">-- Pilih Karyawan --</option>
			</select> -->
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
		<div class="col-xs-6 no-padding">
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
					<th class="col-xs-3">ANGSURAN</th>
					<th class="col-xs-2">JATUH TEMPO</th>
					<th class="col-xs-2">JUMLAH</th>
					<th class="col-xs-2">TANGGAL BAYAR</th>
					<th class="col-xs-1">LAMPIRAN</th>
					<th class="col-xs-2"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan=6>Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kk.save()"><i class="fa fa-save"></i> Simpan</button>
</div>