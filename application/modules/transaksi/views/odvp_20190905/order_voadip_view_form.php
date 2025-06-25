<div class="modal-header header">
	<span class="modal-title">Order VOADIP</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed no-padding">
			<div class="col-lg-8 action d-flex align-items-center">
				<div class="col-sm-1 no-padding">
					<span>No. Order</label>
				</div>
				<div class="col-sm-3">
					<span>:</span>
					<span><?php echo $data['no_order']; ?></span>
				</div>
				<div class="col-sm-1 no-padding">
					<span>Tanggal</label>
				</div>
				<div class="col-sm-3">
					<span>:</span>
					<span><?php echo tglIndonesia($data['tanggal'], '-', ' ', true); ?></span>
				</div>
			</div>
			<div class="col-lg-8 action left-inner-addon d-flex align-items-center">
				<div class="col-sm-1 no-padding">
					<span>Supplier</label>
				</div>
				<div class="col-sm-3">
					<span>:</span>
					<span><?php echo $data['nama_supl']; ?></span>
				</div>
			</div>
			<div class="col-sm-12">
				<small>
					<table class="table table-bordered table-hover tbl_voadip" id="dataTable" width="100%" cellspacing="0" style="padding-right: 30px;">
						<thead>
							<tr class="v-center">
								<th class="col-sm-1 text-center" rowspan="2">Kategori</th>
								<th class="col-sm-1 text-center" rowspan="2">Nama Item</th>
								<th class="col-sm-1 text-center" rowspan="2">Kemasan</th>
								<th class="col-sm-1 text-center" rowspan="2">Harga</th>
								<th class="col-sm-1 text-center" rowspan="2">Jumlah</th>
								<th class="col-sm-1 text-center" rowspan="2">Total</th>
								<th class="text-center" colspan="2">Kirim</th>
								<th class="col-sm-3 text-center" rowspan="2">Alamat</th>
							</tr>
							<tr class="v-center">
								<th class="col-sm-1 text-center">Kantor</th>
								<th class="col-sm-2 text-center">Peternak</th>
							</tr>
						</thead>
						<tbody class="list">
							<?php foreach ($data['detail'] as $k_detail => $v_detail): ?>
								<tr class="child inactive">
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
										<?php echo angkaRibuan($v_detail['jumlah']); ?>
									</td>
									<td class="text-right">
										<?php echo angkaDecimal($v_detail['total']); ?>
									</td>
									<td>
										<?php echo $v_detail['kantor']; ?>
									</td>
									<td>
										<?php echo $v_detail['peternak']; ?>
									</td>
									<td>
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