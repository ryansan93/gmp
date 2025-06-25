<?php 
	$readonly_add = 'readonly';
	$readonly_edit = null;
	if ( !empty($resubmit) ) {
		$readonly_add = null;
		$readonly_edit = 'readonly';

	}
?>

<?php if ( count($data) > 0 ): ?>
	<?php $bagi = (ceil(count($data)/2)) - 1; ?>
	<?php for ($i=0; $i <= $bagi; $i++) { ?>
		<?php
			$std_kirim_pakan = ($data[$i]['std_kirim_pakan'] == 0) ? null : $data[$i]['std_kirim_pakan'];
			$rcn_kirim = ($data[$i]['rcn_kirim'] == 0) ? null : $data[$i]['rcn_kirim'];

			if ( $i == -1 ) {
				$idx_bagi = $bagi;
			} else if ( $i == 0 ) {
				$idx_bagi = $bagi + 1;
			} else {
				$idx_bagi = $bagi + $i + 1;
			}

			$exist = ($data[$i]['exist'] == true) ? 'disabled' : null;
			if ( !empty($readonly_edit) ) {
				$exist = null;
			}

			$id_bagi = isset($data[$idx_bagi]['id']) ? $data[$idx_bagi]['id'] : null;
			$umur_bagi = isset($data[$idx_bagi]['umur']) ? $data[$idx_bagi]['umur'] : '-';
			$tanggal_bagi = isset($data[$idx_bagi]['tanggal']) ? $data[$idx_bagi]['tanggal'] : '-';
			$setting_bagi = !empty($data[$idx_bagi]['setting']) ? angkaRibuan($data[$idx_bagi]['setting']) : null;
			$rcn_kirim_bagi = !empty($data[$idx_bagi]['rcn_kirim']) ? angkaDecimal($data[$idx_bagi]['rcn_kirim']) : null;
			$tgl_kirim_bagi = !empty($data[$idx_bagi]['tgl_kirim']) ? $data[$idx_bagi]['tgl_kirim'] : null;

			if ( isset($data[$idx_bagi]['exist']) ) {
				$exist_bagi = ($data[$idx_bagi]['exist'] == true) ? 'disabled' : null;
			} else {
				$exist_bagi = null;
			}

			if ( !empty($readonly_edit) ) {
				$exist_bagi = null;
			}

			if ( isset($data[$idx_bagi]) ) {
				$std_kirim_pakan2 = ($data[$idx_bagi]['std_kirim_pakan'] == 0) ? null : $data[$idx_bagi]['std_kirim_pakan'];
				$rcn_kirim_bagi = ($data[$idx_bagi]['rcn_kirim'] == 0) ? null : $data[$idx_bagi]['rcn_kirim'];

			} else {
				$std_kirim_pakan2 = '-';
			}
		?>
		<tr class="data v-center">
			<td class="text-center tanggal" data-href="rcn_kirim" data-tanggal="<?php echo $data[$i]['tanggal']; ?>"><?php echo tglIndonesia($data[$i]['tanggal'], '-', ' '); ?></td>
			<td class="text-right umur1" data-id="<?php echo $data[$i]['id']; ?>" data-exist="<?php echo $exist; ?>" data-href="rcn_kirim" data-umur="<?php echo $data[$i]['umur']; ?>"><?php echo $data[$i]['umur']; ?></td>
			<td class="text-right pakan1" style="width: 50px;"><?php echo $std_kirim_pakan; ?></td>
			<td class="text-center setting" data-href="rcn_kirim" data-ipt="rcn_kirim" style="width: 85px;">
				<?php 
					if ( $std_kirim_pakan == '-' ):
						echo "-";
					else:
						$setting = ( !empty($data[$i]['setting']) ) ? angkaRibuan($data[$i]['setting']) : null;
						if ( empty($readonly_edit) ) {
						echo "<input type='text' class='form-control text-right' data-tipe='integer' maxlength='7' data-td='rcn_kirim' data-umur='" . $data[$i]['umur'] . "' onblur='kpm.hit_rcn_kirim(this)' value='" . $setting . "' " . $exist . ">";
						} else {
							$readonly = (!empty($setting))?:$readonly_edit;
							echo "<input type='text' class='form-control text-right' data-tipe='integer' maxlength='7' data-td='rcn_kirim' data-umur='" . $data[$i]['umur'] . "' onblur='kpm.hit_rcn_kirim(this)' value='" . $setting . "' " . $readonly . ">";
						}
					endif
				?>
			</td>
			<td class="text-center rcn_kirim" data-href="rcn_kirim" style="width: 85px;">
				<?php 
					if ( $std_kirim_pakan == '-' ):
						echo "-";
					else:
						$rcn_kirim = ( !empty($data[$i]['rcn_kirim']) ) ? angkaDecimal($data[$i]['rcn_kirim']) : null;
						echo "<input type='text' class='form-control text-right' data-tipe='decimal' maxlength='7' value='" . $rcn_kirim . "' readonly>";
					endif
				?>
			</td>
			<td class="col-sm-1">
				<?php 
					$tgl_kirim = null;
					if ( !empty($data[$i]['tgl_kirim']) ) {
						$tgl_kirim = $data[$i]['tgl_kirim'];
					}
				?>

				<?php if ( $exist == 'disabled' ): ?>
					<input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" value="<?php echo tglIndonesia($tgl_kirim, '-', ' '); ?>" <?php echo $exist; ?> />
				<?php else: ?>
					<?php if ( empty($readonly_edit) ): ?>
			        	<input type="text" class="form-control text-center date datetimepicker tgl-terima" data-href="rcn_kirim" data-tanggal="<?php echo $tgl_kirim; ?>" data-required="1" placeholder="Tanggal" />
			        <?php else: ?>
						<?php $readonly = (!empty($tgl_kirim))?:$readonly_edit; ?>
			        	<input type="text" class="form-control text-center date datetimepicker tgl-terima" data-required="1" placeholder="Tanggal" data-href="rcn_kirim" data-tanggal="<?php echo $tgl_kirim; ?>" value="<?php echo tglIndonesia($tgl_kirim, '-', ' '); ?>" <?php echo $readonly; ?> />
					<?php endif ?>
				<?php endif ?>
			</td>
			<td class="col-sm-1"></td>
			<td class="col-sm-1 jns_pakan">
				<?php $readonly = null; ?>
				<?php if ( !empty($readonly_edit) ): ?>
					<?php $readonly = (!empty($data[$i]['jns_pakan'])) ? null : 'disabled'; ?>
					<?php // $exist = (!empty($exist)) ? null : 'disabled'; ?>
				<?php endif ?>
				<select class="form-control" data-href="rcn_kirim" data-required="1" <?php echo $exist; ?> <?php echo $readonly; ?> >
					<option value="">Pakan</option>
					<?php foreach ($jenis_pakan as $key => $v_jp): ?>
						<?php
							$selected = '';
							if ( trim($v_jp['kode']) == $data[$i]['jns_pakan'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_jp['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_jp['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td class="text-center tanggal" data-href="rcn_kirim2" data-tanggal="<?php echo ($tanggal_bagi != '-') ? $tanggal_bagi : null; ?>"><?php echo ($tanggal_bagi != '-') ? tglIndonesia($tanggal_bagi, '-', ' ') : '-'; ?></td>
			<td class="text-right umur2" data-id="<?php echo $id_bagi; ?>" data-exist="<?php echo $exist_bagi; ?>" data-href="rcn_kirim2" data-umur="<?php echo $umur_bagi; ?>"><?php echo $umur_bagi; ?></td>
			<td class="text-right pakan1" data-href="rcn_kirim2" style="width: 50px;"><?php echo $std_kirim_pakan2; ?></td>
			<td class="text-center setting" data-href="rcn_kirim2" data-ipt="rcn_kirim2" style="width: 85px;">
				<?php 
					if ( $std_kirim_pakan2 == '-' ):
						echo "-";
					else:
						echo "<input type='text' class='form-control text-right' data-tipe='integer' maxlength='7' data-td='rcn_kirim2' data-umur='" . $umur_bagi . "' onblur='kpm.hit_rcn_kirim(this)' value='" . $setting_bagi . "' " . $exist_bagi . " >";
					endif
				?>
			</td>
			<td class="text-center rcn_kirim2" data-href="rcn_kirim2" style="width: 85px;">
				<?php 
					if ( $std_kirim_pakan2 == '-' ):
						echo "-";
					else:
						echo "<input type='text' class='form-control text-right' data-tipe='decimal' maxlength='7' value='" . $rcn_kirim_bagi . "' readonly>";
					endif
				?>
			</td>
			<td class="col-sm-1 text-center">
				<?php if ( $tanggal_bagi != '-' ): ?>
					<?php if ( $exist_bagi == 'disabled' ): ?>
						<input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" value="<?php echo tglIndonesia($tgl_kirim_bagi, '-', ' '); ?>" <?php echo $exist_bagi; ?> />
					<?php else: ?>
						<?php if ( empty($readonly_edit) ): ?>
				        	<input type="text" class="form-control text-center date datetimepicker tgl-terima" data-href="rcn_kirim2" data-required="1" placeholder="Tanggal" data-tanggal="<?php echo $tgl_kirim_bagi; ?>" />
				        <?php else: ?>
							<?php $readonly = (!empty($tgl_kirim_bagi))?:'disabled'; ?>
				        	<input type="text" class="form-control text-center date datetimepicker tgl-terima" data-href="rcn_kirim2" data-required="1" placeholder="Tanggal" data-tanggal="<?php echo $tgl_kirim_bagi; ?>" value="<?php echo tglIndonesia($tgl_kirim_bagi, '-', ' '); ?>" <?php echo $readonly; ?> />
						<?php endif ?>
					<?php endif ?>
				<?php else: ?>
					<?php echo '-'; ?>
				<?php endif ?>
			</td>
			<td class="col-sm-1"></td>
			<td class="col-sm-1 jns_pakan2">
				<?php if ( $tanggal_bagi != '-' ): ?>
					<?php $readonly = null; ?>
					<?php if ( !empty($readonly_edit) ): ?>
						<?php $readonly = (!empty($data[$idx_bagi]['jns_pakan'])) ? null : 'disabled'; ?>
						<?php // $exist_bagi = (!empty($exist_bagi)) ? null : 'disabled'; ?>
					<?php endif ?>
					<select class="form-control" data-href="rcn_kirim2" data-required="1" <?php echo $exist_bagi; ?> <?php echo $readonly; ?> >
						<option value="">Pakan</option>
						<?php foreach ($jenis_pakan as $key => $v_jp): ?>
							<?php
								$selected = '';
								if ( trim($v_jp['kode']) == $data[$idx_bagi]['jns_pakan'] ) {
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v_jp['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_jp['nama']; ?></option>
						<?php endforeach ?>
					</select>
				<?php else: ?>
					<?php echo '-'; ?>
				<?php endif ?>
			</td>
		</tr>
	<?php } ?>
<?php else : ?>
	<tr>
		<td class="text-center" colspan="16">Data Kosong.</td>
	</tr>
<?php endif ?>