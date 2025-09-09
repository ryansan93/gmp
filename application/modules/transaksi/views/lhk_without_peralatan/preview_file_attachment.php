<div class="modal-header no-padding">
	<span class="modal-title"><b><?php echo $judul; ?></b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<div class="col-xs-12 detailed">
			<form role="form" class="form-horizontal">
				<?php if ( !empty($url) && count($url) > 0 ) : ?>
					<?php foreach ($url as $k_url => $v_url): ?>
						<div class="contain-img">
							<img src="<?php echo $v_url; ?>" style="width: 100%; height: 250px;">
							<div class="col-xs-12 no-padding contain-btn">
								<?php if ( isset($jenis) && !empty($jenis) ) : ?>
									<div class="col-xs-12 no-padding">
										<button type="button" class="btn btn-primary" onclick="lhk.viewImage(this)"><i class="fa fa-file"></i></button>
									</div>
								<?php else : ?>
									<div class="col-xs-6 no-padding" style="padding-right: 5px;">
										<button type="button" class="btn btn-primary" onclick="lhk.viewImage(this)"><i class="fa fa-file"></i></button>
									</div>
									<div class="col-xs-6 no-padding" style="padding-left: 5px;">
										<button type="button" class="btn btn-danger" onclick="lhk.deleteFile(this)"><i class="fa fa-trash"></i></button>
									</div>
								<?php endif ?>
							</div>
						</div>
					<?php endforeach ?>
				<?php else : ?>
					<div class="col-xs-12 no-padding">
						<label class="control-label">Data foto tidak ditemukan.</label>
					</div>
				<?php endif ?>
			</form>
		</div>
	</div>
</div>