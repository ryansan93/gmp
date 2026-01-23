<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td class="transaksi" data-val="<?php echo $v_data['transaksi']; ?>"><?php echo $v_data['transaksi']; ?></td>
			<td class="no_bayar" data-val="<?php echo $v_data['no_bayar']; ?>">
				<?php if ( isset($v_data['lampiran']) && !empty($v_data['lampiran']) ) { ?>
					<a href="upload/<?php echo $v_data['lampiran']; ?>" target="_blank">
						<?php echo (isset($v_data['no_invoice']) && !empty($v_data['no_invoice'])) ? $v_data['no_invoice'] : $v_data['no_bayar']; ?>
					</a>
				<?php } else { ?>
					<?php echo (isset($v_data['no_invoice']) && !empty($v_data['no_invoice'])) ? $v_data['no_invoice'] : $v_data['no_bayar']; ?>
				<?php } ?>
			</td>
			<td><?php echo (isset($v_data['kode_unit']) && !empty($v_data['kode_unit'])) ? $v_data['kode_unit'] : '-'; ?></td>
			<td><?php echo $v_data['periode']; ?></td>
			<td><?php echo $v_data['nama_penerima']; ?></td>
			<td class="text-right _tagihan" data-val="<?php echo $v_data['tagihan']; ?>"><?php echo angkaDecimal($v_data['tagihan']); ?></td>
			<td class="text-right _dn" data-val="<?php echo $v_data['dn']; ?>"><?php echo angkaDecimal($v_data['dn']); ?></td>
			<td class="text-right _cn" data-val="<?php echo $v_data['cn']; ?>"><?php echo angkaDecimal($v_data['cn']); ?></td>
			<?php $pph = isset($v_data['pph']) ? $v_data['pph'] : 0; ?>
			<td class="text-right _potongan_pph" data-val="<?php echo $pph; ?>"><?php echo angkaDecimal($pph); ?></td>
			<?php $netto = isset($v_data['netto']) ? $v_data['netto'] : $v_data['tagihan']; ?>
			<td class="text-right _netto" data-val="<?php echo $netto; ?>"><?php echo angkaDecimal($netto); ?></td>
			<td class="text-right _transfer" data-val="<?php echo $v_data['transfer']; ?>"><?php echo angkaDecimal($v_data['transfer']); ?></td>
			<td class="text-right _bayar" data-val="<?php echo $v_data['bayar']; ?>"><?php echo isset($v_data['bayar']) ? angkaDecimal($v_data['bayar']) : 0; ?></td>
			<td class="text-right _sisa tagihan" data-val="<?php echo $v_data['jumlah']; ?>"><?php echo angkaDecimal($v_data['jumlah']); ?></td>
			<td class="text-center">
				<?php if ( $v_data['jumlah'] > 0 ): ?>
					<input type="checkbox" class="cursor-p check" target="check" <?php echo ($v_data['checked'] == true) ? 'checked' : null; ?> >
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>