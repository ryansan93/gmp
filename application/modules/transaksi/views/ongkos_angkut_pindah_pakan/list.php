<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p" onclick="oap.changeTabActive(this)" data-href="action" data-id="<?php echo $v_data['id']; ?>">
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['tgl_terima'], '-', ' ')); ?></td>
			<td class="text-center"><?php echo $v_data['no_sj']; ?></td>
			<td>
				<?php
					$ket_asal = $v_data['nama_asal'].' ( KDG : '.(int)substr($v_data['noreg_asal'], -2).' )';
					if ( $v_data['jenis_asal'] != 'mitra' ) {
						$ket_asal = $v_data['nama_asal'];
					}
				?>
				<?php echo $ket_asal; ?>
			</td>
			<td>
				<?php
					$ket_tujuan = $v_data['nama_tujuan'].' ( KDG : '.(int)substr($v_data['noreg_tujuan'], -2).' )';
					if ( $v_data['jenis_tujuan'] != 'mitra' ) {
						$ket_tujuan = $v_data['nama_tujuan'];
					}
				?>
				<?php echo $ket_tujuan; ?>
			</td>
			<td class="text-right"><?php echo angkaDecimal($v_data['ongkos_angkut']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>