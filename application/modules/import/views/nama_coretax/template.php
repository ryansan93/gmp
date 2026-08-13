
<table border="1">
	<thead>
		<tr>
			<th>NOMOR</th>
			<th>NIK</th>
			<th>NPWP</th>
			<th>NAMA CORETAX</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $k => $val): ?>
			<tr>
				<td><?php echo $val['nomor']; ?></td>
				<td align="left" style="mso-number-format:\@;"><?php echo $val['nik']; ?></td>
				<td align="left" style="mso-number-format:\@;"><?php echo $val['npwp']; ?></td>
				<td align="left"><?php echo $val['nama_coretax']; ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
