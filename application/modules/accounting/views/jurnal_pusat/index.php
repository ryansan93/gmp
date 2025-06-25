<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link <?php echo ( empty($no_bukti) ) ? 'active' : ''; ?>" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?php echo ( !empty($no_bukti) ) ? 'active' : ''; ?>" data-toggle="tab" href="#action" data-tab="action">Jurnal</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="riwayat" class="tab-pane fade <?php echo ( empty($no_bukti) ) ? 'show active' : ''; ?>">
						<?php echo $riwayat; ?>
					</div>
					<div id="action" class="tab-pane fade <?php echo ( !empty($no_bukti) ) ? 'show active' : ''; ?>">
						<?php echo $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>