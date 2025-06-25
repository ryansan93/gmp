<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Fitur</h4> -->
	<div class="col-md-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-md-2 action no-padding">
				<?php // if ( $akses['a_submit'] == 1 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="ADD" onclick="fitur.add_form(this)"> 
						<i class="fa fa-plus" aria-hidden="true"></i> ADD
					</button>
				<?php // else: ?>
					<!-- <div class="col-md-2 action no-padding">
						&nbsp
					</div> -->
				<?php // endif ?>
			</div>
			<div class="col-md-2 search left-inner-addon pull-right no-padding">
				<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_fitur" placeholder="Search" onkeyup="filter_all(this)">
			</div>
			<small>
				<table class="table table-bordered table-hover tbl_fitur" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>Kode</th>
							<th>Parent</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<!-- UNTUK ISI DARI LIST FITUR -->
						<tr>
							<td></td>
							<td></td>
							<td></td>
	                   </tr>
					</tbody>
				</table>
			</small>
		</form>
	</div>
</div>