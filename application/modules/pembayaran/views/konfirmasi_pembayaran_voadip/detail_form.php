<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Periode Kirim</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['periode']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Supplier</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_supplier']['nama']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="6"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b><?php echo angkaDecimal($data['total']); ?></b></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl SJ</th>
					<th class="col-xs-1">Kota/Kab</th>
					<th class="col-xs-3">Perusahaan</th>
					<th class="col-xs-3">Supplier</th>
					<th class="col-xs-1">No. Order</th>
					<th class="col-xs-1">No. SJ</th>
					<th style="width: 5%;">Jumlah</th>
					<th class="col-xs-1">Sub Total</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count($data['detail']) > 0 ): ?>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr class="cursor-p header" title="Klik untuk melihat detail">
							<td class="text-center tgl_sj"><?php echo tglIndonesia($v_det['tgl_sj'], '-', ' '); ?></td>
							<td class="kota_kab">
								<?php
									$unit = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_det['d_unit']['nama']))));
									echo strtoupper($unit);
								?>
							</td>
							<td class="perusahaan"><?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></td>
							<td class="supplier"><?php echo strtoupper($data['d_supplier']['nama']); ?></td>
							<td class="no_order"><?php echo strtoupper($v_det['no_order']); ?></td>
							<td class="no_sj"><?php echo strtoupper($v_det['no_sj']); ?></td>
							<td class="text-right jumlah"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
							<td class="text-right total"><?php echo angkaDecimal($v_det['total']); ?></td>
						</tr>
						<tr class="detail" style="display: none;">
							<td colspan="8" style="background-color: #ccc;">
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<th class="col-xs-3" style="background-color: #adb3ff;">Tujuan</th>
											<th class="col-xs-3" style="background-color: #adb3ff;">Jenis OVK</th>
											<th class="col-xs-2" style="background-color: #adb3ff;">Jumlah</th>
											<th class="col-xs-2" style="background-color: #adb3ff;">Harga</th>
											<th class="col-xs-2" style="background-color: #adb3ff;">Sub Total</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($v_det['detail'] as $k_det2 => $v_det2): ?>
											<tr>
												<td class="gudang"><?php echo strtoupper($v_det2['d_gudang']['nama']); ?></td>
												<td class="barang"><?php echo strtoupper($v_det2['d_barang']['nama']); ?></td>
												<td class="text-right jumlah"><?php echo angkaDecimal($v_det2['jumlah']); ?></td>
												<td class="text-right harga"><?php echo angkaDecimal($v_det2['harga']); ?></td>
												<td class="text-right total"><?php echo angkaDecimal($v_det2['total']); ?></td>
											</tr>
										<?php endforeach ?>
									</tbody>
								</table>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td colspan="10">Data tidak ditemukan.</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<?php if ( empty($data['d_realisasi']) ): ?>
	<div class="col-xs-12 no-padding">
		<?php if ( $akses['a_edit'] == 1 ): ?>
			<button type="button" class="btn btn-primary pull-right" onclick="kpv.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="transaksi" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
		<?php endif ?>
		<?php if ( $akses['a_delete'] == 1 ): ?>
			<button type="button" class="btn btn-danger pull-right" onclick="kpv.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
		<?php endif ?>
	</div>
<?php endif ?>