<div class="row content-panel">
	<div class="col-lg-12">
		<div class="col-lg-8 search left-inner-addon no-padding">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_vaksin" placeholder="Search" onkeyup="filter_all(this)">
		</div>
		<div class="col-lg-4 action no-padding">
			<?php if ( $akses['a_submit'] == 1 ) { ?>
				<button id="btn-add" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="vaksin.add_form(this)"> 
					<i class="fa fa-plus" aria-hidden="true"></i> ADD
				</button>
				<button id="btn-save" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="fdvp.save_peralatan(this)"> 
					<i class="fa fa-save" aria-hidden="true"></i> SAVE
				</button>

				<?php if ( $akses['a_edit'] == 1 ) { ?>
					<button id="btn-edit" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="fdvp.edit_peralatan(this)"> 
						<i class="fa fa-edit"></i> EDIT
					</button>
				<?php } ?>
			<?php } else { ?>
				<div class="col-lg-2 action no-padding pull-right">
					&nbsp
				</div>
			<?php } ?>
		</div>
	</div>
	<div class="col-lg-12 data">
		<small>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th class="col-md-3">Nama Vaksin</th>
						<th class="col-md-2">Harga Vaksin</th>
						<th class="col-md-7">Status</th>
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