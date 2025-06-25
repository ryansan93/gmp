<div class="modal-header no-padding">
	<span class="modal-title"><b><?php echo $judul; ?></b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<?php foreach ($url as $k_url => $v_url): ?>
					<div class="form-group">
						<img class="cursor-p" src="<?php echo $v_url; ?>" style="width: 100%; height: 500px;" title="KLIK UNTUK PREVIEW REAL GAMBAR" onclick="window.open('<?php echo $v_url; ?>', '_blank');">
					</div>
				<?php endforeach ?>
			</form>
		</div>
	</div>
</div>