<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Fitur</h4> -->
	<div class="col-md-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-md-2 action no-padding">
				<?php if ( $akses['a_submit'] == 1 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="ADD" onclick="stj.add_form(this)"> 
						<i class="fa fa-plus" aria-hidden="true"></i> ADD
					</button>
				<?php endif ?>
			</div>
			<div class="col-md-2 search left-inner-addon pull-right no-padding">
				<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_data" placeholder="Search" onkeyup="filter_all(this)">
			</div>
			<small>
				<table class="table table-bordered table-hover tbl_data" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th class="col-md-4">Jurnal Trans</th>
							<th class="col-md-8">Sumber / Tujuan</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2">Data tidak ditemukan.</td>
	                   </tr>
					</tbody>
				</table>
			</small>
		</form>
	</div>
</div>