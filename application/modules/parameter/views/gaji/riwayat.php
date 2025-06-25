<div class="col-lg-12 no-padding">
	<?php if ( $akses['a_submit'] == 1 ): ?>
		<button type="button" data-href="action" class="col-md-12 btn btn-primary cursor-p btn-add" title="ADD" onclick="gaji.changeTabActive(this)"> 
			<i class="fa fa-plus" aria-hidden="true"></i> ADD
		</button>
	<?php else: ?>
		<div class="col-lg-2 no-padding pull-right">
			&nbsp
		</div>
	<?php endif ?>
</div>
<div class="col-md-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<select class="form-control pegawai">
		<option value="">-- Pilih Pegawai --</option>
		<?php foreach ($pegawai as $k => $val): ?>
			<option value="<?php echo $val['nik']; ?>"><?php echo strtoupper($val['jabatan']).' | '.strtoupper($val['nama']); ?></option>
		<?php endforeach ?>
	</select>
</div>
<div class="col-md-12 no-padding">
	<button type="button" class="col-md-12 btn btn-primary cursor-p" onclick="gaji.getLists(this)"> 
		<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
	</button>
</div>
<div class="col-md-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-lg-12 search right-inner-addon no-padding" style="margin-bottom: 10px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_gaji" placeholder="Search" onkeyup="filter_all(this)">
</div>
<div class="col-md-12 no-padding">
	<small>
		<table class="table table-bordered tbl_gaji" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-1">NIK</th>
					<th class="col-lg-3">Nama</th>
					<th class="col-lg-4">Unit</th>
					<th class="col-lg-2">Tgl Berlaku</th>
					<th class="col-lg-2">Gaji</th>
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