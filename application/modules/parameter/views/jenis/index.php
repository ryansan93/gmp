<div class="row content-panel">
	<div class="col-xs-12">
		<div class="col-xs-8 search left-inner-addon no-padding">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_bo" placeholder="Search" onkeyup="filter_all(this)">
		</div>
		<div class="col-xs-4 action no-padding">
			<?php if ( $akses['a_submit'] == 1 ) { ?>
				<button id="btn-add" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="jns.addForm(this)"> 
					<i class="fa fa-plus" aria-hidden="true"></i> ADD
				</button>
			<?php } else { ?>
				<div class="col-xs-2 action no-padding pull-right">
					&nbsp
				</div>
			<?php } ?>
		</div>
	</div>
	<div class="col-xs-12 data">
		<span>Klik pada baris untuk edit data.</span>
		<small>
			<table class="table table-bordered tbl_riwayat">
				<thead>
					<tr>
						<th class="col-xs-2">Kode</th>
						<th class="col-xs-10">Nama</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="2">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>