<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="panel-heading no-padding">
					<ul class="nav nav-tabs nav-justified">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">RIWAYAT KAS MASUK</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">KAS MASUK</a>
						</li>
					</ul>
				</div>
				<div class="panel-body no-padding">
					<div class="tab-content">
						<div id="riwayat" class="tab-pane fade show active" role="tabpanel" style="padding-top: 10px;">
							<?php echo $riwayat; ?>
						</div>

						<div id="action" class="tab-pane fade" role="tabpanel" style="padding-top: 10px;">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<?php echo $add_form; ?>
							<?php } else { ?>
								<h4>KAS MASUK</h4>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>