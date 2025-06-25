<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Pembayaran Piutang Plasma</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="riwayat" class="tab-pane fade show active">
						<?php echo $riwayat; ?>
					</div>
					<div id="action" class="tab-pane fade">
                        <?php if ( $akses['a_submit'] == 1 ) : ?>
                            <?php echo $add_form; ?>
                        <?php else : ?>
                            <h3>PEMBAYARAN PIUTANG PLASMA</h3>
                        <?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>