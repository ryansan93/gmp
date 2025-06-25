<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" data-id="<?php echo $v_data['id']; ?>" onclick="rp.changeTabActive(this)" data-href="transaksi">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td><?php echo $v_data['nomor']; ?></td>
			<td><?php echo $v_data['jenis_transaksi']; ?></td>
			<td><?php echo strtoupper($v_data['nama_perusahaan']); ?></td>
			<td>
				<?php 
					$ket = '-';
					if ( !empty($v_data['supplier']) ) {
						$ket = $v_data['nama_supplier'];
					}

					if ( !empty($v_data['peternak']) ) {
						$ket = $v_data['nama_mitra'];
					}

					if ( !empty($v_data['ekspedisi']) ) {
						$ket = $v_data['nama_ekspedisi'];
					}
					echo strtoupper($ket); 
				?>
			</td>
			<td class="text-right"><?php echo angkaDecimal($v_data['cn']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['jml_transfer']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['uang_muka']); ?></td>
			<td class="text-center"><a href="uploads/<?php echo $v_data['lampiran']; ?>" target="_blank"><?php echo $v_data['no_bukti']; ?></a></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>