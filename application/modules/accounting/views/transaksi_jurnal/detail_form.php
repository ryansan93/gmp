<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Nama</label></div>
	<div class="col-xs-10 no-padding">
		<label class="control-label">: <?php echo $data['nama']; ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Peruntukan</label></div>
	<div class="col-xs-10 no-padding">
		<label class="control-label">: <?php echo ($data['unit'] == 1) ? 'UNIT' : 'NON UNIT'; ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Detail Transaksi</label>
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">Nama</th>
					<th class="col-xs-3">Sumber</th>
					<th class="col-xs-3">Tujuan</th>
					<th class="col-xs-2 text-center">Submit Periode</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
							<?php echo $v_det['nama']; ?>
						</td>
						<td>
							<?php echo $v_det['sumber_coa'].' | '.$v_det['sumber']; ?>
						</td>
						<td>
							<?php echo $v_det['tujuan_coa'].' | '.$v_det['tujuan']; ?>
						</td>
						<td class="text-center">
							<?php if ( $v_det['submit_periode'] == 1 ) { ?>
								<i class="fa fa-check"></i>
							<?php } else { ?>
								<i class="fa fa-minus"></i>
							<?php } ?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Sumber / Tujuan</label>
	<small>
		<table class="table table-bordered sumber_tujuan" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-12">Nama</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty($data['sumber_tujuan']) ): ?>
					<?php foreach ($data['sumber_tujuan'] as $k_det => $v_det): ?>
						<tr>
							<td>
								<?php echo $v_det['nama']; ?>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td colspan="1">
							Data tidak ditemukan.
						</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div> -->
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="tj.changeTabActive(this)" data-href="action" data-id="<?php echo $data['id']; ?>" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
	<button type="button" class="btn btn-danger pull-right" onclick="tj.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
</div>