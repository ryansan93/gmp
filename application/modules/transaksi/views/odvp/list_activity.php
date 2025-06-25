<div class="modal-header">
	<span class="modal-title"><b>List Aktifitas Order <?php echo ($data['jenis'] == 'voadip') ? 'Voadip' : 'Pakan'; ?></b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal activity">
				<?php if ( $data['jenis'] == 'voadip' ): ?>
					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">Tanggal</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['tanggal']; ?></label>
						</div>
					</div>

					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">No. Order</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['no_order']; ?></label>
						</div>
					</div>

					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">Supplier</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['supplier']; ?></label>
						</div>
					</div>
				<?php else: ?>
					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">Tanggal</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['tanggal']; ?></label>
						</div>
					</div>

					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">Supplier</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['supplier']; ?></label>
						</div>
					</div>

					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">Rencana Kirim</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['rcn_kirim']; ?></label>
						</div>
					</div>

					<div class="col-md-12 no-padding">
						<div class="col-lg-2 no-padding pull-left">
							<label class="control-label">No. Order</label>
						</div>
						<div class="col-lg-1 no-padding pull-left" style="max-width: 2%;">
							<label class="control-label">:</label>
						</div>
						<div class="col-lg-9 no-padding action">
							<label class="control-label"><?php echo $data['no_order']; ?></label>
						</div>
					</div>
				<?php endif ?>
				<div class="col-md-12 no-padding"><br></div>
				<div class="col-md-12 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<label class="control-label"><u>Aktifitas</u></label>
					</div>
				</div>

				<div class="col-md-12 no-padding">
					<div class="col-lg-12 no-padding pull-left">
						<ul>
							<?php foreach ($data['logs'] as $k_log => $v_log): ?>
								<li><?php echo strtoupper( $v_log['deskripsi'].' pada '.tglIndonesia(substr($v_log['waktu'], 0, 10), '-', ' ', true).' '.substr($v_log['waktu'], 11, 5) ) ?></li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>