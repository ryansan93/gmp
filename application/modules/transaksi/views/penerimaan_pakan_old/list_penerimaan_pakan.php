<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php foreach ($v_data['detail'] as $k_detail => $v_detail): ?>
			<tr class="data" data-iddetspm="<?php echo $v_detail['id_detspm']; ?>">
				<td><?php echo $v_detail['pakan']; ?></td>
				<td class="text-right"><?php echo angkaRibuan($v_detail['jml_pakan']); ?></td>
				<td class="text-right"><?php echo angkaDecimal($v_detail['tonase']); ?></td>
				<td>
					<select class="form-control" name="pakan">
						<option value="">-- Pilih Pakan --</option>
						<?php foreach ($pakan as $k_pakan => $v_pakan): ?>
							<?php
								$selected = '';
								if ( $v_pakan['kode'] == $v_detail['id_pakan'] ):
									$selected = 'selected';
								endif 
							?>
							<option value="<?php echo $v_pakan['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_pakan['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</td>
				<td>
					<input type="text" class="form-control text-right" name="zak" data-tipe="integer" maxlength="10">
				</td>
				<td>
					<input type="text" class="form-control text-right" name="tonase" data-tipe="decimal" maxlength="12">
				</td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>