<?php if ( !empty($data) && count($data) > 0): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $v_data['nomor']; ?></td>
			<td><?php echo $v_data['mitra']; ?></td>
			<td class="noreg">
				<?php
					$list_noreg = '';
					$idx = 0;
					foreach ($v_data['list_noreg'] as $k_ln => $v_ln) {
						$list_noreg .= '<span>'.$v_ln['noreg'].'</span>';
						if ( $idx != (count($v_data['list_noreg'])-1) ) {
							$list_noreg .= '<br>';
						}
						$idx++;
					}

					echo $list_noreg;
				?>
			</td>
			<td><?php echo tglIndonesia($v_data['tgl_submit'], '-', ' '); ?></td>
			<td class="text-center">
				<a class="cursor-p lihat" onclick="rg.proses_hit_rhpp_group(this)" data-href="rhpp" data-id="<?php echo $v_data['id']; ?>">Lihat</a>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>