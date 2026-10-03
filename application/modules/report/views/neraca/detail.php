<div class="modal-header">
	<span class="modal-title"><b>DETAIL <?php echo strtoupper($data['item_report_nama']); ?></b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<div class="col-xs-12 no-padding">
				<hr style="margin-top: 0px; margin-bottom: 10px;">
			</div>
			<?php $tot_saldo_akhir = 0; ?>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered table-hover" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="col-xs-3">No. COA</th>
								<th class="col-xs-6">Nama COA</th>
								<th class="col-xs-3 text-right">Saldo Akhir</th>
							</tr>
						</thead>
						<tbody>
							<?php if ( !empty($detail) ) { ?>
								<?php foreach ($detail as $v_det) { ?>
									<?php $s = (float)($v_det['saldo_akhir'] ?? 0); $tot_saldo_akhir += $s; ?>
									<tr>
										<td><?php echo $v_det['no_coa']; ?></td>
										<td><?php echo strtoupper($v_det['nama_coa']); ?></td>
										<td class="text-right"><?php echo ($s >= 0) ? angkaDecimal($s) : '('.angkaDecimal(abs($s)).')'; ?></td>
									</tr>
								<?php } ?>
							<?php } else { ?>
								<tr>
									<td colspan="3" class="text-center">Data tidak ditemukan.</td>
								</tr>
							<?php } ?>
						</tbody>
						<?php if ( !empty($detail) ) { ?>
							<tfoot>
								<tr>
									<td colspan="2" class="text-right"><b>Total</b></td>
									<td class="text-right"><b><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></b></td>
								</tr>
							</tfoot>
						<?php } ?>
					</table>
				</small>
			</div>
			<div class="col-xs-12 no-padding">
				<hr style="margin-top: 10px; margin-bottom: 10px;">
			</div>
		</div>
	</div>
</div>
