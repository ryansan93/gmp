<div class="row content-panel">
	<div class="col-xs-12">
		<hr style="padding-top: 0px; margin-top: 0px;">
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

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="lhk.get_lists(this)"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
	</div>
	<div class="col-xs-12"><hr style="padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 5px;"></div>
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding" style="overflow-x: auto;">
			<small>
				<table class="table table-bordered table-hover tbl_lhk">
					<thead>
						<tr class="v-center">
							<th class="text-center" colspan="2">Timbang</th>
							<th class="text-center" colspan="6">Performa</th>
							<th class="text-center" rowspan="2" style="width: 3%;">Nekropsi</th>
							<th class="text-center" rowspan="2" style="width: 3%;">Solusi</th>
							<th class="text-center" colspan="3">Pakan (Zak)</th>
							<th class="text-center" rowspan="2">Komentar</th>
							<th class="text-center" rowspan="2" style="width: 5%;">Posisi</th>
						</tr>
						<tr>
							<th class="text-center" style="width: 3%; vertical-align: middle;">Umur</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">Tanggal</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">Kons.</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">ADG</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">Deplesi</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">BB</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">FCR</th>
							<th class="text-center" style="width: 5%; vertical-align: middle;">Mati</th>
							<th class="text-center" style="width: 7%; vertical-align: middle;">Kirim</th>
							<th class="text-center" style="width: 7%; vertical-align: middle;">Sisa</th>
							<th class="text-center" style="width: 7%; vertical-align: middle;">Pakai</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="14">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
	</div>
</div>