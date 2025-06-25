<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data header" data-id="<?php echo $v_data['id']; ?>">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_panen'], '-', ' '); ?></td>
			<td class="text-center"><?php echo $v_data['no_do']; ?></td>
			<td class="text-center"><?php echo $v_data['no_sj']; ?></td>
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
							<td class="text-right"><?php echo angkaRibuan($v_data['ekor']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_data['kg']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_data['harga']); ?></td>
							<td class="text-right total"><?php echo angkaDecimal($v_data['total']); ?></td>
						</tr>
					</tbody>
				</table>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<tbody>
						<tr>
							<th class="col-xs-4 text-left">Sudah Bayar</th>
							<td class="col-xs-8 sudah_bayar text-right"><?php echo angkaDecimal($v_data['sudah_bayar']); ?></td>
						</tr>
						<tr>
							<th class="col-xs-4 text-left">Jumlah Bayar</th>
							<td class="col-xs-8 jml_bayar text-right" data-sudah="<?php echo $v_data['sudah_bayar']; ?>"><?php echo angkaDecimal($v_data['total'] - $v_data['sudah_bayar']); ?></td>
						</tr>
						<tr>
							<th class="col-xs-4 text-left">Penyesuaian</th>
							<td class="col-xs-8 penyesuaian">
								<div class="col-xs-12 no-padding">
									<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" value="0" maxlength="14" onblur="bakul.cek_status_pembayaran(this)">
								</div>
								<div class="col-xs-12 no-padding"></div>
								<div class="col-xs-12 no-padding">
									<textarea class="form-control ket_penyesuaian" placeholder="Keterangan"></textarea>
								</div>
							</td>
						</tr>
						<tr>
							<th class="col-xs-4 text-left">Status</th>
							<td class="col-xs-8 status">-</td>
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