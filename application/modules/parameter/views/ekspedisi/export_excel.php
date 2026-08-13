
<table border="1">
	<thead>
		<tr>
			<th>NOMOR</th>
			<th>EKSPEDISI</th>
			<th>NIK</th>
			<th>NPWP</th>
			<th>NAMA CORETAX</th>
			<th>ALAMAT</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $k => $val): ?>
			<tr>
				<td><?php echo $val['nomor']; ?></td>
				<td><?php echo $val['nama']; ?></td>
				<td align="left" style="mso-number-format:\@;"><?php echo !empty($val['ktp']) ? $val['ktp'] : '-'; ?></td>
				<td align="left" style="mso-number-format:\@;"><?php echo !empty($val['npwp']) ? $val['npwp'] : '-'; ?></td>
				<td align="left"><?php echo !empty($val['nama_coretax']) ? $val['nama_coretax'] : '-'; ?></td>
				<td align="left"><?php echo $val['alamat']; ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
