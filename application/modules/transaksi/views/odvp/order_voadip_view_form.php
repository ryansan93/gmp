<div class="modal-header header">
	<span class="modal-title">Order OVK</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-xs-12 detailed">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-2 no-padding">
					<label class="control-label">No. Order</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo $data['no_order']; ?></label>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-2 no-padding">
					<label class="control-label">No. PO</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo $data['no_po']; ?></label>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-2 no-padding">
					<label class="control-label">Tanggal</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo tglIndonesia($data['tanggal'], '-', ' ', true); ?></label>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-2 no-padding">
					<label class="control-label">Supplier</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo $data['nama_supl']; ?></label>
				</div>
			</div>
			<div class="col-xs-12 no-padding">
				<hr style="margin-top: 10px; margin-bottom: 10px;">
			</div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered table-hover tbl_voadip" id="dataTable" width="100%" cellspacing="0" style="padding-right: 30px;">
						<thead>
							<?php 
								$total_beli = 0;
								foreach ($data['detail'] as $k_detail => $v_detail) {
									$total_beli += $v_detail['total'];
								}
							?>

							<tr>
								<td class="text-right" colspan="7"><b>Total</b></td>
								<td class="total_beli text-right"><b><?php echo angkaDecimal( $total_beli );?></b></td>
								<td colspan="4"></td>
							</tr>
							<tr class="v-center">
								<th class="col-xs-1 text-center" rowspan="2">Perusahaan</th>
								<th class="col-xs-1 text-center" rowspan="2">Kategori</th>
								<th class="col-xs-2 text-center" rowspan="2">Nama Item</th>
								<th class="col-xs-1 text-center" rowspan="2">Kemasan</th>
								<th class="col-xs-1 text-center" rowspan="2">Harga Beli</th>
								<th class="col-xs-1 text-center" rowspan="2">Harga Jual</th>
								<th class="col-xs-1 text-center" rowspan="2">Jumlah</th>
								<th class="col-xs-1 text-center" rowspan="2">Total Beli</th>
								<th class="col-xs-1 text-center" rowspan="2">Tgl Kirim</th>
								<th class="text-center" colspan="2">Kirim</th>
								<th class="text-center hide" rowspan="2">Alamat</th>
							</tr>
							<tr class="v-center">
								<th class="col-xs-1 text-center">Gudang</th>
								<th class="col-xs-2 text-center">Peternak</th>
							</tr>
						</thead>
						<tbody class="list">
							<?php foreach ($data['detail'] as $k_detail => $v_detail): ?>
								<tr class="child inactive">
									<td>
										<?php echo $v_detail['perusahaan']; ?>
									</td>
									<td>
										<?php echo $v_detail['kategori']; ?>
									</td>
									<td>
										<?php echo $v_detail['barang']; ?>
									</td>
									<td>
										<?php echo $v_detail['kemasan']; ?>
									</td>
									<td class="text-right">
										<?php echo angkaDecimal($v_detail['harga']); ?>
									</td>
									<td class="text-right">
										<?php echo angkaDecimal($v_detail['harga_jual']); ?>
									</td>
									<td class="text-right">
										<?php echo angkaRibuan($v_detail['jumlah']); ?>
									</td>
									<td class="text-right">
										<?php echo angkaDecimal($v_detail['total']); ?>
									</td>
									<td class="text-center">
										<?php echo !empty($v_detail['tgl_kirim']) ? tglIndonesia($v_detail['tgl_kirim'], '-', ' ') : '-'; ?>
									</td>
									<td class="text-center">
										<?php
											if ( $v_detail['kirim_ke'] == 'gudang' ) {
												echo $v_detail['kirim'];
											} else {
												echo '-';
											}
										?>
									</td>
									<td>
										<?php
											if ( $v_detail['kirim_ke'] == 'peternak' ) {
												echo $v_detail['kirim'];
											} else {
												echo '-';
											}
										?>
									</td>
									<td class="hide">
										<?php echo $v_detail['alamat']; ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</small>
			</div>
		</div>
	</div>
</div>