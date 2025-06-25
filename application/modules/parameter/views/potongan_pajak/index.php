<div class="row content-panel">
	<div class="col-lg-12">
		<div class="col-lg-8 search left-inner-addon no-padding">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_potongan" placeholder="Search" onkeyup="filter_all(this)">
		</div>
		<div class="col-lg-4 action no-padding">
			<?php if ( $akses['a_submit'] == 1 ) { ?>
				<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pp.add_form(this)"> 
					<i class="fa fa-plus" aria-hidden="true"></i> ADD
				</button>
			<?php } ?>
		</div>
	</div>
	<div class="col-lg-12 data">
		<small>
			<table class="table table-bordered tbl_potongan">
				<thead>
					<tr>
						<th class="col-md-1">No</th>
						<th class="col-md-10">Potongan (%)</th>
						<th class="col-md-1">Action</th>
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