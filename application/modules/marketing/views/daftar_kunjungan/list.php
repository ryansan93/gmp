<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $v_data['tanggal']; ?></td>
			<td>
				<?php if ( $isMobile ): ?>
					<a class="cursor-p" href="geo:0, 0?z=15&q=<?php echo $v_data['lat_long']; ?>" target="_blank"><?php echo $v_data['lat_long']; ?></a>
				<?php else: ?>
					<a class="cursor-p" href="https://www.google.com/maps/?q=<?php echo $v_data['lat_long']; ?>" target="_blank"><?php echo $v_data['lat_long']; ?></a>
				<?php endif ?>
			</td>
			<td>
				<a class="cursor-p" href="uploads/<?php echo $v_data['foto_kunjungan']; ?>" target="_blank">
					<!-- <?php echo $v_data['foto_kunjungan']; ?> -->
					Foto Kunjungan
				</a>
			</td>
			<td>
				<button type="button" class="btn btn-primary col-xs-12" onclick="dk.changeTab(this)" data-id="<?php echo $v_data['id']; ?>" data-edit="" data-href="action">
					<i class="fa fa-file"></i>
				</button>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>