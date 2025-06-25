<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data" data-id="<?php echo $v_data['id']; ?>">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_panen'], '-', ' '); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['nama']).'<br>'.'KDG : '.$v_data['kandang']; ?></td> 
			<td class="text-center"><?php echo $v_data['no_do']; ?></td>
			<td class="text-center"><?php echo $v_data['no_sj']; ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['ekor']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['kg']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['harga']); ?></td>
			<td class="text-right total"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td class="text-right sudah_bayar"><?php echo angkaDecimal($v_data['jumlah_bayar']); ?></td>
			<td class="text-right jml_bayar" data-sudah="<?php echo $v_data['jumlah_bayar']; ?>">0</td>
			<td class="text-right penyesuaian">
				<div class="col-lg-12 no-padding">
					<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" value="0" maxlength="14" onblur="bakul.cek_status_pembayaran(this)">
				</div>
				<div class="col-lg-12 no-padding"></div>
				<div class="col-lg-12 no-padding">
					<textarea class="form-control ket_penyesuaian" placeholder="Keterangan"></textarea>
				</div>
			</td>
			<td class="text-center status"></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="9">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>