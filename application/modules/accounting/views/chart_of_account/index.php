<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Fitur</h4> -->
	<div class="col-md-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-md-2 action no-padding">
				<?php if ( $akses['a_submit'] == 1 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="ADD" onclick="coa.add_form(this)"> 
						<i class="fa fa-plus" aria-hidden="true"></i> ADD
					</button>
				<?php endif ?>
			</div>
			<div class="col-md-2 search left-inner-addon pull-right no-padding">
				<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_coa" placeholder="Search" onkeyup="filter_all(this)">
			</div>
			<small>
				<table class="table table-bordered table-hover tbl_coa" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th class="col-md-3 hide">Perusahaan</th>
							<th class="col-md-1 hide">Unit</th>
							<th class="col-md-1">COA</th>
							<th class="col-md-2">Nama</th>
							<th class="col-md-1">Gol 1</th>
							<th class="col-md-1">Gol 2</th>
							<th class="col-md-1">Gol 3</th>
							<th class="col-md-1">Gol 4</th>
							<th class="col-md-1">Gol 5</th>
							<th class="col-md-1">Laporan</th>
							<th class="col-md-1">Posisi COA</th>
							<th class="col-md-1">Status</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="10">Data tidak ditemukan.</td>
	                   </tr>
					</tbody>
				</table>
			</small>
		</form>
	</div>
</div>