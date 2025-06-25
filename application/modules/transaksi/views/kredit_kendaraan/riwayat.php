<div class="col-xs-12 no-padding">
	<?php if ( $akses['a_submit'] == 1 ): ?>
		<div class="col-xs-12 no-padding" style="padding-top: 10px;">
			<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.changeTabActive(this)" data-href="action" data-edit=""><i class="fa fa-plus"></i> ADD</button>
		</div>
	<?php endif ?>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding"><label class="control-label">STATUS KREDIT</label></div>
	<div class="col-xs-12 no-padding">
		<select class="col-xs-12 form-control status_kredit" data-required="1">
			<option value="">-- Pilih Status --</option>
			<option value="all">ALL</option>
			<option value="0">KREDIT</option>
			<option value="1">LUNAS</option>
		</select>
	</div>
	<div class="col-xs-12 no-padding" style="padding-top: 10px;">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<span>* Klik pada baris untuk melihat detail</span>
	<div class="col-xs-12 no-padding">
		<small>
			<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-1" style="max-width: 5%;"></th>
						<th class="col-xs-1">TANGGAL</th>
						<th class="col-xs-1">PERUSAHAAN</th>
						<th class="col-xs-1">UNIT</th>
						<th class="col-xs-1">PERUNTUKAN</th>
						<th class="col-xs-2">JENIS</th>
						<th class="col-xs-1" style="max-width: 5%;">TAHUN</th>
						<th class="col-xs-1">HARGA</th>
						<th class="col-xs-1" style="max-width: 5%;">TENOR</th>
						<th class="col-xs-1">ANGSURAN</th>
						<th class="col-xs-1">BPKB</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="11">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>