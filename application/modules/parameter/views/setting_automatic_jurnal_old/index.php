<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Kontrak, Bonus Dan Denda</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Setting Automatic Jurnal</a>
					</li>
				</ul>
			</div>
			<?php // cetak_r($list); ?>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active" role="tabpanel">
						<?php echo $riwayat; ?>
					</div>

					<div id="action" class="tab-pane fade" role="tabpanel">
						<?php // if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $addForm; ?>
						<?php // else: ?>
							<!-- <h3>Data Kosong.</h3> -->
						<?php // endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>