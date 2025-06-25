<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $v_data['nama_vaksin']; ?></td>
			<td><?php echo angkaRibuan($v_data['harga']); ?></td>
			<td>
				<div class="col-md-11">
					<?php 
						if ( isset($v_data['logs'][ count($v_data['logs']) - 1 ]) ) {
							$last_log = $v_data['logs'][ count($v_data['logs']) - 1 ];
							$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						} else {
							$keterangan = '-';
						}

						echo $keterangan;
					?>
				</div>
				<div class="col-md-1">
					<button type="button" class="btn btn-primary" title="EDIT" data-id="<?php echo $v_data['id']; ?>" onclick="vaksin.edit_form(this)"><i class="fa fa-edit"></i></button>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>