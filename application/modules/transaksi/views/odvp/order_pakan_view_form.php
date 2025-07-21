<div class="modal-header header">
	<span class="modal-title">Order Pakan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body" style="padding-top: 0px;">
	<div class="row">
		<div class="col-xs-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding"><label class="control-label">No Order</label></div>
						<div class="col-xs-10 no-padding">
							<label class="control-label">: <?php echo $data['no_order']; ?></label>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding"><label class="control-label">No PO</label></div>
						<div class="col-xs-10 no-padding">
							<label class="control-label">: <?php echo $data['no_po']; ?></label>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding"><label class="control-label">Tanggal</label></div>
						<div class="col-xs-10 no-padding">
							<label class="control-label">: <?php echo tglIndonesia($data['tgl_trans'], '-', ' '); ?></label>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding"><label class="control-label">Supplier</label></div>
						<div class="col-xs-10 no-padding">
							<label class="control-label">: <?php echo $data['nama_supplier']; ?></label>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding"><label class="control-label">Rencana Kirim</label></div>
						<div class="col-xs-10 no-padding">
							<label class="control-label">: <?php echo tglIndonesia($data['rcn_kirim'], '-', ' '); ?></label>
						</div>
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<hr style="margin-top: 10px; margin-bottom: 10px;">
				</div>
				<div class="col-xs-12 no-padding">
					<small>
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<thead>
								<?php 
									$total_beli = 0;
									foreach ($data['detail'] as $k_det => $v_det) {
										$total_beli += $v_det['total'];
									}
								?>

								<tr>
									<td class="text-right" colspan="5"><b>Total</b></td>
									<td class="total_beli text-right"><b><?php echo angkaRibuan( $total_beli );?></b></td>
									<td colspan="2"></td>
								</tr>
								<tr>
									<th class="text-center col-xs-2">Perusahaan</th>
									<th class="text-center col-xs-1">Nama Item</th>
									<th class="text-center col-xs-1">Bentuk</th>
									<th class="text-center col-xs-1">Harga Beli</th>
									<th class="text-center col-xs-1">Jumlah</th>
									<th class="text-center col-xs-1">Total Beli</th>
									<th class="text-center col-xs-2">Kirim</th>
									<th class="text-center col-xs-3">Alamat</th>
								</tr>
							</thead>
							<tbody class="list">
								<?php foreach ($data['detail'] as $k_det => $v_det): ?>
									<tr>
										<td><?php echo $v_det['nama_perusahaan']; ?></td>
										<td><?php echo $v_det['nama_barang']; ?></td>
										<td><?php echo $v_det['bentuk_barang']; ?></td>
										<td class="text-right"><?php echo angkaDecimal($v_det['harga']); ?></td>
										<td class="text-right"><?php echo angkaRibuan($v_det['jumlah']); ?></td>
										<td class="text-right"><?php echo angkaRibuan($v_det['total']); ?></td>
										<td><?php echo $v_det['nama_kirim']; ?></td>
										<td><?php echo $v_det['alamat']; ?></td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</small>
				</div>
			</form>
		</div>
	</div>
</div>