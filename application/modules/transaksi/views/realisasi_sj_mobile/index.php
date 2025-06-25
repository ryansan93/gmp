<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<?php if ( $isMobile ): ?>
				<div class="panel-heading">
					<ul class="nav nav-tabs nav-justified">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#transaksi" data-tab="transaksi">Realisasi SJ</a>
						</li>
					</ul>
				</div>
				<div class="panel-body" style="padding-top: 0px;">
					<div class="tab-content">
						<div id="riwayat" class="tab-pane fade show active">
							<?php echo $riwayat; ?>
						</div>
						<div id="transaksi" class="tab-pane fade">
							<?php if ( $akses['a_submit'] == 0 || (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ) : ?>
								<h4>Realisasi SJ</h4>
							<?php else: ?>
								<?php echo $add_form; ?>
							<?php endif ?>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="col-xs-12">
					<h4>Menu anda khusus untuk mobile device.</h4>
				</div>
			<?php endif ?>
		</form>
	</div>
</div>