<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#rekening_masuk" data-tab="rekening_masuk">Rekening Masuk</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#rekening_keluar" data-tab="rekening_keluar">Rekening Keluar</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="rekening_masuk" class="tab-pane fade show active">
						<?php echo $rekening_masuk; ?>
					</div>
					<div id="rekening_keluar" class="tab-pane fade">
						<?php echo $rekening_keluar; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>