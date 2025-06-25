<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $jml_detail = 0; ?>
	<?php foreach ($data as $k => $v_data): ?>
		<tr>
			<td>
				<select class="form-control barang" disabled="disabled">
					<?php foreach ($obat as $k_obat => $v_obat): ?>
						<?php
							$selected = null;
							if ( $v_obat['kode'] == $v_data['kode_barang'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_obat['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_obat['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($v_data['jumlah']); ?>" onblur="pv.cek_stok_gudang(this)" disabled="disabled">
			</td>
			<td>
				<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1">
				<?php
					$jml_detail++;
					$css = 'display: none;';
					if ( $jml_detail == count($data) ) {
						$css = 'display: block;';
					}
				?>
				<div class="btn-ctrl" style="<?php echo $css; ?>">
					<!-- <span onclick="pv.removeRowChild(this)" class="btn_del_row_2x <?php echo (count($data) == 1) ? 'hide' : null; ?>"></span>
					<span onclick="pv.addRowChild(this)" class="btn_add_row_2x"></span> -->
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td>
			<select class="form-control barang">
				<option value="">Pilih Obat</option>
				<?php foreach ($obat as $k_obat => $v_obat): ?>
					<option value="<?php echo $v_obat['kode']; ?>" ><?php echo $v_obat['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</td>
		<td>
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1" value="0" onblur="pv.cek_stok_gudang(this)">
		</td>
		<td>
			<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1">
			<?php
				$css = 'display: block;';
			?>
			<div class="btn-ctrl" style="<?php echo $css; ?>">
				<span onclick="pv.removeRowChild(this)" class="btn_del_row_2x <?php echo (!empty($data) && count($data) == 1) ? 'hide' : null; ?>"></span>
				<span onclick="pv.addRowChild(this)" class="btn_add_row_2x"></span>
			</div>
		</td>
	</tr>
<?php endif ?>