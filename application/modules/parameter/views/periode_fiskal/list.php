<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $no = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $no++; ?>
		<tr onclick="pf.edit_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo angkaRibuan($no); ?></td>
			<td><?php echo explode('-', $v_data['periode'])[1].'/'.explode('-', $v_data['periode'])[0]; ?></td>
			<td><?php echo tglIndonesia($v_data['start_date'], '-', ' '); ?></td>
			<td><?php echo tglIndonesia($v_data['end_date'], '-', ' '); ?></td>
			<td><?php echo ($v_data['status'] == 1) ? 'AKTIF' : 'TUTUP'; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>