<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo $v_data['umur']; ?></td>
			<td class="text-center">
				<?php
					$tahun = substr($v_data['tgl_lhk'], 0, 4);
					$bulan = substr($v_data['tgl_lhk'], 5, 2);
					$tanggal = substr($v_data['tgl_lhk'], -2);
					$tgl_lhk = $tanggal.'/'.$bulan.'/'.$tahun;

					echo $tgl_lhk;
				?>
			</td>
			<td class="text-right"><?php echo angkaRibuan($v_data['kons']); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['adg'], 3); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['deplesi'], 3); ?></td>
			<td class="text-right">
				<b><a class="cursor-p solusi" onclick="lhk.sekat(this)" data-id="<?php echo $v_data['id']; ?>" title="DATA SEKAT"><?php echo angkaDecimalFormat($v_data['bb'], 3); ?></a></b>
			</td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['fcr'], 3); ?></td>
			<td class="text-right">
				<?php
					$_url = array();
					foreach ($v_data['foto_ekor_mati'] as $k_fn => $v_fn) {
						array_push($_url, $v_fn['path']);
					}

					$json_url = json_encode($_url, JSON_FORCE_OBJECT);
				?>
				<b><a class="cursor-p nekropsi" onclick="lhk.preview_file_attachment(this)" data-title="Preview Foto Mati" data-url='<?php echo $json_url; ?>' title="EKOR MATI"><?php echo angkaRibuan($v_data['mati']); ?></a></b>
			</td>
			<td>
				<b><a class="cursor-p nekropsi" onclick="lhk.nekropsi(this)" data-id="<?php echo $v_data['id']; ?>">NEKROPSI</a></b>
			</td>
			<td>
				<b><a class="cursor-p solusi" onclick="lhk.solusi(this)" data-id="<?php echo $v_data['id']; ?>">SOLUSI</a></b>
			</td>
			<td class="text-right"><?php echo angkaRibuan($v_data['kirim_pakan']); ?></td>
			<td class="text-right">
				<?php
					$_url = array();
					foreach ($v_data['foto_sisa_pakan'] as $k_fn => $v_fn) {
						array_push($_url, $v_fn['path']);
					}

					$json_url = json_encode($_url, JSON_FORCE_OBJECT);
				?>
				<b><a class="cursor-p nekropsi" onclick="lhk.preview_file_attachment(this)" data-title="Preview Sisa Pakan" data-url='<?php echo $json_url; ?>' title="SISA PAKAN"><?php echo angkaRibuan($v_data['sisa_pakan']); ?></a></b>
			</td>
			<td class="text-right"><?php echo angkaRibuan($v_data['pakai_pakan']); ?></td>
			<td><?php echo strtoupper($v_data['keterangan']); ?></td>
			<td>
				<?php
					$url = 'https://www.google.com/maps/dir/'.preg_replace('/\s+/', '', $v_data['posisi']).'/'.preg_replace('/\s+/', '', $v_data['lat_long_mitra']);
				?>
				<button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="DATA POSISI" onclick="window.open('<?php echo $url; ?>', 'blank')"><i class="fa fa-map-marker"></i></button>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="14">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>