<?php if ( !empty($data) && count($data) > 0 ): ?>
    <?php $no = 1; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $no; ?></td>
			<td><?php echo $v_data['nomor']; ?></td>
			<td><?php echo strtoupper($v_data['nama']); ?></td>
			<td><?php echo strtoupper(tglIndonesia($v_data['max_tgl_ambil'], '-', ' ')); ?></td>
			<td>
				<button type="button" class="btn btn-primary col-xs-12" onclick="plg.detailMobile(this)" data-id="<?php echo $v_data['nomor']; ?>" data-edit="" data-href="action">
					<i class="fa fa-file"></i>
				</button>
			</td>
		</tr>
        <?php $no++; ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>