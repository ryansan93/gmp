<div class="row">
	<div class="col-xs-12">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<select id="select_mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<select id="select_noreg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="btn btn-primary pull-left tambah_penerimaan" onclick="ppm.change_tab(this)" data-id="" data-edit="" data-href="transaksi"><i class="fa fa-plus"></i> Tambah</button>
			</div>
			<div class="col-xs-6 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="btn btn-primary pull-right tampilkan_riwayat" onclick="ppm.list_riwayat(this)"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
	</div>
	<div class="col-xs-12"><hr style="padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 5px;"></div>
	<div class="col-xs-12">
		<small>
			<span>Klik pada baris untuk melihat detail.</span>
			<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-3">No. SJ</th>
						<th class="col-xs-3">Tiba</th>
						<th class="col-xs-6">Asal</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="3">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>