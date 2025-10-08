<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-success pull-left tambah_lhk" onclick="lhk.change_tab(this)" data-id="" data-edit="" data-href="transaksi"><i class="fa fa-plus"></i> Tambah</button>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 0px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="lhk.list_riwayat(this)"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<span>* Klik pada baris untuk melihat detail.</span>
				<table class="table table-bordered tbl_riwayat" style="margin-bottom: 10px;">
					<thead>
						<tr>
							<th class="col-xs-1">Umur</th>
							<th class="col-xs-2">Pakai Pakan (Zak)</th>
							<th class="col-xs-2">Sisa Pakan (Zak)</th>
							<th class="col-xs-2">Ekor Mati</th>
							<th class="col-xs-1">BB (Kg)</th>
							<th class="col-xs-1">FCR</th>
							<th class="col-xs-1">IP</th>
							<!-- <th>Nekropsi</th>
							<th>Solusi</th> -->
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
	</div>
</div>