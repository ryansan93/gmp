<div class="col-lg-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Report Jurnal</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jurnal_report" data-required="1">
				<option value="" >-- Pilih --</option>
				<?php foreach ($jurnal_report as $k_jr => $v_jr): ?>
					<option value="<?php echo $v_jr['id']; ?>" > <?php echo strtoupper($v_jr['nama']); ?> </option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
		<button id="btn-tampil" type="button" data-href="action" class="col-sm-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="mj.getLists()">Tampilkan</button>
	</div>
	<div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
		<button type="button" class="col-sm-12 btn btn-success pull-right" onclick="mj.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
	</div>
</div>
<div class="col-lg-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-md-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
	<input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="mj.filter_all(this)">
</div>
<div class="col-lg-12 no-padding">
	<span>* Klik pada baris untuk melihat detail</span>
	<small>
		<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-5 text-center">Jurnal Trans</th>
					<th class="col-lg-5 text-center">Jurnal Report</th>
					<th class="col-lg-2 text-center">Posisi</th>
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