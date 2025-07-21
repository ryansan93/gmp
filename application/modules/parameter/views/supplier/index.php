<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Supplier</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Daftar Supplier</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Master Supplier</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_supl" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="supl.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php // } else if ( $akses['a_ack'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="doc.ack(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> ACK
								</button> -->
							<?php // } else if ( $akses['a_approve'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="doc.approve(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> APPROVE
								</button> -->
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered tbl_supl">
							<thead>
								<tr>
									<th>NIP</th>
									<th>Jenis</th>
									<th>Nama Supplier</th>
									<th>NIK</th>
									<th>Alamat</th>
									<th>Status</th>
									<th>Saldo Awal (Rp)</th>
									<th>Keterangan</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="9"></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $addForm; ?>
						<?php else: ?>
							<h3>Master Supplier.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>