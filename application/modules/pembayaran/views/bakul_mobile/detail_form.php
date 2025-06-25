<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-4 no-padding">
		<label class="control-label text-left">Tanggal Bayar</label>
	</div>
	<div class="col-xs-8 no-padding">
		<label class="control-label">: <?php echo tglIndonesia($data['tgl_bayar'], '-', ' ', true); ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-4 no-padding">
		<label class="control-label text-left">Pelanggan</label>
	</div>
	<div class="col-xs-8 no-padding">
		<label class="control-label">: <?php echo strtoupper($data['pelanggan']['nama']); ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-4 no-padding">
		<label class="control-label text-left">Jumlah Transfer</label>
	</div>
	<div class="col-xs-8 no-padding">
		<label class="control-label">: <?php echo angkaRibuan($data['jml_transfer']); ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-4 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
	<div class="col-xs-8 no-padding">
		<label class="control-label">: <a href="uploads/<?php echo $data['lampiran_transfer']; ?>"><?php echo strtoupper($data['lampiran_transfer']); ?></a></label>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Saldo</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" value="<?php echo angkaDecimal($data['saldo']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Uang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" value="<?php echo angkaDecimal($data['total_uang']); ?>" readonly>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['total_penyesuaian']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['total_bayar']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['lebih_kurang']); ?>" readonly>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1 text-center">Tanggal Panen</th>
					<th class="col-xs-2 text-center">No. DO</th>
					<th class="col-xs-2 text-center">No. SJ</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty( $data['detail'] ) ): ?>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr class="data header" data-id="<?php echo $v_det['id']; ?>">
							<td class="text-center"><?php echo tglIndonesia($v_det['data_do']['header']['tgl_panen'], '-', ' '); ?></td>
							<td class="text-center"><?php echo $v_det['data_do']['no_do']; ?></td>
							<td class="text-center"><?php echo $v_det['data_do']['no_sj']; ?></td>
						</tr>
						<tr class="detail">
							<td colspan="3">
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<tbody>
										<tr>
											<th class="col-xs-2 text-center">Ekor</th>
											<th class="col-xs-3 text-center">Kg</th>
											<th class="col-xs-3 text-center">Harga</th>
											<th class="col-xs-4 text-center">Total</th>
										</tr>
										<tr>
											<td class="text-right"><?php echo angkaRibuan($v_det['data_do']['ekor']); ?></td>
											<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['tonase']); ?></td>
											<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['harga']); ?></td>
											<td class="text-right total"><?php echo angkaDecimal(($v_det['data_do']['tonase'] * $v_det['data_do']['harga'])); ?></td>
										</tr>
									</tbody>
								</table>
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<?php $total = $v_det['data_do']['tonase'] * $v_det['data_do']['harga']; ?>
									<tbody>
										<tr>
											<th class="col-xs-4 text-left">Sudah Bayar</th>
											<td class="col-xs-8 sudah_bayar text-right"><?php echo ( $v_det['jumlah_bayar'] == $total ) ? 0 : angkaDecimal($total - $v_det['jumlah_bayar']); ?></td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Jumlah Bayar</th>
											<td class="col-xs-8 jml_bayar text-right" data-sudah="<?php echo ($total - $v_det['jumlah_bayar']); ?>"><?php echo angkaDecimal($v_det['jumlah_bayar']) ; ?></td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Penyesuaian</th>
											<td class="col-xs-8 penyesuaian">
												<div class="col-xs-12 no-padding">
													<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" value="<?php echo angkaDecimal($v_det['penyesuaian']); ?>" maxlength="14" onblur="bakul.cek_status_pembayaran(this)" readonly>
												</div>
												<div class="col-xs-12 no-padding"></div>
												<div class="col-xs-12 no-padding">
													<textarea class="form-control ket_penyesuaian" placeholder="Keterangan" readonly><?php echo strtoupper($v_det['ket_penyesuaian']); ?></textarea>
												</div>
											</td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Status</th>
											<td class="col-xs-8 status">
												<?php
													$ket = '';
													if ( $v_det['status'] == 'LUNAS' ) {
														$ket = '<span style="color: blue;"><b>LUNAS</b></span>';
													} else {
														$ket = '<span style="color: red;"><b>BELUM</b></span>';
													}

													echo $ket;
												?>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td colspan="3">Data tidak ditemukan.</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<form role="form" class="form-horizontal">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<button type="button" class="btn btn-primary col-xs-12" onclick="bakul.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<button type="button" class="btn btn-danger col-xs-12" onclick="bakul.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
		</div>
	</form>
</div>