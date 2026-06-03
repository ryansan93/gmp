<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12 no-padding" style="margin-bottom: 8px;">
				<div class="col-lg-6 no-padding">
					<?php if ( $akses['a_submit'] == 1 ): ?>
						<button type="button" class="btn btn-primary cursor-p" onclick="aksesKhusus.add_form()">
							<i class="fa fa-plus"></i> Tambah
						</button>
					<?php endif ?>
				</div>
				<div class="col-lg-4 search left-inner-addon pull-right no-padding">
					<i class="glyphicon glyphicon-search"></i>
					<input class="form-control" type="search" data-table="tbl_akses_khusus" placeholder="Cari..." onkeyup="filter_all(this)">
				</div>
			</div>
			<small>
				<table class="table table-bordered table-hover tbl_akses_khusus" width="100%" cellspacing="0">
					<thead>
						<tr class="abu">
							<th class="text-center" style="width:40px;">No</th>
							<th>Group</th>
							<th>Fitur</th>
							<th>Path Fitur</th>
							<th>Akses Khusus</th>
							<?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1 ): ?>
								<th class="text-center" style="width:80px;">Aksi</th>
							<?php endif ?>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</small>
		</form>
	</div>
</div>
