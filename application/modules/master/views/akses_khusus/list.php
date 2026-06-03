<?php if ( !empty($list) && count($list) > 0 ): ?>
	<?php $no = 1; foreach ( $list as $row ): ?>
		<tr>
			<td class="text-center"><?php echo $no++; ?></td>
			<td>
				<span class="label label-default"><?php echo $row['id_group']; ?></span>
				<?php echo strtoupper($row['nama_group']); ?>
			</td>
			<td class="nama_detfitur"><?php echo $row['nama_detfitur'] ?: '<em class="text-muted">—</em>'; ?></td>
			<td class="text-muted" style="font-size:11px;"><?php echo $row['path_detfitur'] ?: '—'; ?></td>
			<td>
				<span class="label label-info"><?php echo htmlspecialchars($row['akses_khusus']); ?></span>
			</td>
			<?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1 ): ?>
				<td class="text-center">
					<?php if ( $akses['a_edit'] == 1 ): ?>
						<button type="button" class="btn btn-xs btn-primary"
							title="Edit" onclick="aksesKhusus.edit_form(<?php echo $row['id']; ?>)">
							<i class="fa fa-pencil"></i>
						</button>
					<?php endif ?>
					<?php if ( $akses['a_delete'] == 1 ): ?>
						<button type="button" class="btn btn-xs btn-danger"
							title="Hapus" onclick="aksesKhusus.delete_data(<?php echo $row['id']; ?>)">
							<i class="fa fa-trash"></i>
						</button>
					<?php endif ?>
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
<?php endif ?>
