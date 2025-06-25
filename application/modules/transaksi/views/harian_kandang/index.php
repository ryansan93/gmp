<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master FEED, DOC, VOADIP dan Peralatan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat" role="tab">Riwayat Harian Kandang</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action" role="tab">Harian Kandang</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="riwayat" class="tab-pane fade show active" role="tabpanel">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_sapronak_kesepakatan" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ): ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="Hk.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php else: ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php endif ?>
						</div>
						<div class="col-md-12 no-padding data">
							<?php echo $riwayat; ?>
						</div>
					</div>
					<div id="action" class="tab-pane fade" role="tabpanel">
						<div class="col-md-12">
							<?php echo $action; ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>