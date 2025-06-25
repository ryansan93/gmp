<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $val): ?>
		<tr class="search">
			<td><?php echo $val['nomor']; ?></td>
			<td><?php echo $val['d_mitra']['nama']; ?></td>
			<td class="text-center"><?php echo tglIndonesia($val['mulai'], '-', ' '); ?></td>
			<td class="text-center"><?php echo tglIndonesia($val['berakhir'], '-', ' '); ?></td>
			<td>
				<?php 
					$path = null;
					$filename = '-';
					if ( isset($val['lampiran'][ count($val['lampiran']) - 1 ]) ) {
						$last_lampiran = $val['lampiran'][ count($val['lampiran']) - 1 ];
						$path = $last_lampiran['path'];
						$filename = $last_lampiran['filename'];
					}
				?>
				<a href="uploads/<?php echo $path ?>" target="_blank"><?php echo $filename; ?></a>
			</td>
			<td>
				<div class="col-md-11 no-padding">
					<?php 
						if ( isset($val['logs'][ count($val['logs']) - 1 ]) ) {
							$last_log = $val['logs'][ count($val['logs']) - 1 ];
							$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						} else {
							$keterangan = '-';
						}

						echo $keterangan;
					?>
				</div>
				<div class="col-md-1 no-padding text-right">
					<a data-id="<?php echo $val['id']; ?>" onclick="skp.edit_form(this)">Edit</a>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>