<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p search" title="Klik 2x untuk edit data" ondblclick="pegawai.edit_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo $v_data['level']; ?></td>
			<td><?php echo $v_data['nik']; ?></td>
			<td><?php echo $v_data['nama']; ?></td>
			<td><?php echo getJabatan($v_data['jabatan']); ?></td>
			<td><?php echo ($v_data['atasan'] != "") ? $v_data['atasan'] : '-'; ?></td>
			<td><?php echo ucfirst($v_data['marketing']); ?></td>
			<td><?php echo ucfirst($v_data['kordinator']); ?></td>
			<td>
				<?php if ( count($v_data['wilayah']) > 0 ): ?>
					<?php
						$wilayah = "";
						$index = 0;
						foreach ($v_data['wilayah'] as $k_wilayah => $v_wilayah) {
							$wilayah .= ucfirst($v_wilayah['nama']);

							$index++;
							if ( $index != count($v_data['wilayah']) ) {
								$wilayah .= ', ';
							}
						}

						echo $wilayah;
					?>
				<?php else: ?>
					-
				<?php endif ?>
			</td>
			<td>
				<?php if ( count($v_data['unit']) > 0 ): ?>
					<?php
						$unit = "";
						$index = 0;
						foreach ($v_data['unit'] as $k_unit => $v_unit) {
							$unit .= ucfirst($v_unit['nama']);

							$index++;
							if ( $index != count($v_data['unit']) ) {
								$unit .= ', ';
							}
						}

						echo $unit;
					?>
				<?php else: ?>
					-
				<?php endif ?>
			</td>
			<td>
				<?php echo ( $v_data['status'] == 1 ) ? 'AKTIF' : 'NON AKTIF'; ?>
			</td>
			<td>
				<button type="button" class="col-xs-12 btn btn-primary" onclick="pegawai.modalGaji(this)" data-nik="<?php echo $v_data['nik']; ?>"><i class="fa fa-usd"></i></button>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="9">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>