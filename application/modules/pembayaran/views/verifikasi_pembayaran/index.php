<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#outstanding" data-tab="outstanding">OUTSTANDING</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#history" data-tab="history">HISTORY</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="outstanding" class="tab-pane fade show active">
						<?php echo $outstanding; // $riwayat; ?>
					</div>
					<div id="history" class="tab-pane fade">
						<?php echo $history; // $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>