<div class="row content-panel detailed sk-wrap">
	<div class="col-lg-12 no-padding detailed">
		<?php if ( $isMobile ): ?>
			<div class="sk-container">
				<div class="sk-search-card">
					<label class="control-label sk-search-label"><i class="fa fa-search"></i> Cari Siklus</label>
					<select class="form-control siklus" data-required="1" style="width: 100%;">
					</select>
					<small class="text-muted">Cari nama plasma atau noreg. Cuma siklus yang belum tutup di unit anda yang muncul.</small>
					<button type="button" class="btn btn-primary btn-block sk-btn-tampilkan" onclick="sk.getData()"><i class="fa fa-search"></i> Tampilkan</button>
				</div>
				<div class="hasil sk-hasil">
					<div class="sk-empty-state">
						<i class="fa fa-list-alt"></i>
						<div>Pilih siklus dulu, lalu klik Tampilkan.</div>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="col-lg-12">
				<h4>Menu anda khusus untuk mobile device.</h4>
			</div>
		<?php endif ?>
	</div>
</div>
