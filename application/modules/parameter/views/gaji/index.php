<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Kontrak, Bonus Dan Denda</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat Gaji</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Gaji</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="riwayat" class="tab-pane fade show active" role="tabpanel">
						<?php echo $riwayat; ?>
					</div>

					<div id="action" class="tab-pane fade" role="tabpanel">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $addForm; ?>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>