<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p" onclick="jp.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id_header']; ?>">
			<td><?php echo strtoupper($value['kode_jurnal']); ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td class="detail_jurnal"><?php echo (isset($value['jurnal_trans_detail_nama']) && !empty($value['jurnal_trans_detail_nama'])) ? strtoupper($value['jurnal_trans_detail_nama']) : '-'; ?></td>
			<td class="perusahaan"><?php echo strtoupper($value['nama_perusahaan']); ?></td>
			<td><?php echo (isset($value['asal']) && !empty($value['asal'])) ? strtoupper($value['asal']) : '-'; ?></td>
			<td><?php echo (isset($value['tujuan']) && !empty($value['tujuan'])) ? strtoupper($value['tujuan']) : '-'; ?></td>
			<td><?php echo strtoupper($value['nama_unit']); ?></td>
			<td><?php echo strtoupper($value['keterangan']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['nominal']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>