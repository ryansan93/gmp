<div class="row content-panel">
	<div class="col-lg-12">
		<div class="col-lg-8 search left-inner-addon no-padding">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_pegawai" placeholder="Search" onkeyup="filter_all(this)">
		</div>
		<div class="col-lg-4 action no-padding">
			<?php if ( $akses['a_submit'] == 1 ) { ?>
				<button id="btn-add" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pegawai.add_form(this)"> 
					<i class="fa fa-plus" aria-hidden="true"></i> ADD
				</button>
			<?php } ?>
		</div>
	</div>
	<div class="col-lg-12 data">
		<small>
			<table class="table table-bordered tbl_pegawai">
				<thead>
					<tr>
						<th class="col-md-1">Level</th>
						<th class="col-md-1">NIK</th>
						<th class="col-md-2">Nama Pegawai</th>
						<th class="col-md-1">Jabatan</th>
						<th class="col-md-1">Atasan</th>
						<th class="col-md-1">Marketing</th>
						<th class="col-md-1">Koordinator</th>
						<th class="col-md-1">Wilayah</th>
						<th class="col-md-1">Unit</th>
						<th class="col-md-1">Status</th>
						<th class="col-md-1"></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="10">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>