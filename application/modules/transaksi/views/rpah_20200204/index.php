<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat_rpah" data-tab="riwayat_rpah">Riwayat RPAH</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#rpah" data-tab="rpah">RPAH</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="riwayat_rpah" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rpah" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="rpah" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="rpah.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered tbl_rpah" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-md-2">Tanggal</th>
									<th class="col-md-2">Unit</th>
									<th class="col-md-2">Bottom Price</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="4">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id="rpah" class="tab-pane fade">
						<?php echo $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>