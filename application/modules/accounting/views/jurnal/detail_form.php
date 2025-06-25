<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Tanggal</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Transaksi</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['jurnal_trans']['nama']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['unit']); ?></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1">TANGGAL</th>
					<th class="col-xs-1">DETAIL TRANS</th>
					<th class="col-xs-2">SUMBER / TUJUAN</th>
					<th class="col-xs-2">PERUSAHAAN</th>
					<th class="col-xs-3">KETERANGAN</th>
					<th class="col-xs-2">NOMINAL</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
						    <?php echo strtoupper(tglIndonesia($v_det['tanggal'], '-', ' ')); ?>
						</td>
						<td>
						    <?php echo strtoupper($v_det['jurnal_trans_detail']['nama']); ?>
						</td>
						<td>
							<?php if ( !empty($v_det['supplier']) ): ?>
								<?php echo strtoupper($v_det['d_supplier']['nama']); ?>
							<?php else: ?>
								<?php echo strtoupper($v_det['jurnal_trans_sumber_tujuan']['nama']); ?>
							<?php endif ?>
						</td>
						<td>
							<?php echo strtoupper($v_det['d_perusahaan']['perusahaan']); ?>
						</td>
						<td>
							<?php echo strtoupper($v_det['keterangan']); ?>
						</td>
						<td class="text-right">
							<?php echo angkaDecimal($v_det['nominal']); ?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="jurnal.changeTabActive(this)" data-href="action" data-edit="edit" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
	<button type="button" class="btn btn-danger pull-right" onclick="jurnal.delete(this)" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
</div> -->