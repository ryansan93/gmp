<?php if ( !empty($data) ): ?>
	<?php $jml_detail = 0; ?>
	<?php foreach ($data as $k => $v_data): ?>
		<tr>
			<td>
				<select class="form-control barang" disabled="disabled">
					<?php foreach ($pakan as $k_pakan => $v_pakan): ?>
						<?php
							$selected = null;
							if ( $v_pakan['kode'] == $v_data['barang'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_pakan['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_pakan['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($v_data['jumlah']); ?>">
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
					<!-- <span onclick="pp.removeRowChild(this)" class="btn_del_row_2x <?php echo (count($data) == 1) ? 'hide' : null; ?>"></span>
					<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span> -->
				</div>

				<!-- <div class="btn-ctrl">
					<span onclick="pp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
					<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span>
				</div> -->
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td>
			<select class="form-control barang">
				<option value="">Pilih Pakan</option>
				<?php foreach ($pakan as $k_pakan => $v_pakan): ?>
					<option value="<?php echo $v_pakan['kode']; ?>" ><?php echo $v_pakan['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</td>
		<td>
			<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" value="0" onblur="pp.cek_stok_gudang(this)">
		</td>
		<td>
			<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1">
			<?php
				$css = 'display: block;';
			?>
			<div class="btn-ctrl" style="<?php echo $css; ?>">
				<span onclick="pp.removeRowChild(this)" class="btn_del_row_2x <?php echo (!empty($data) && count($data) == 1) ? 'hide' : null; ?>"></span>
				<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span>
			</div>

			<!-- <div class="btn-ctrl">
				<span onclick="pp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
				<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span>
			</div> -->
		</td>
	</tr>
<?php endif ?>