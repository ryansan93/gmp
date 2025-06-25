<table border="1">
	<thead>
		<tr>
			<th>NOMOR</th>
			<th>MITRA</th>
			<th>NO TELP</th>
			<th>UNIT</th>
			<th>KANDANG</th>
			<th>KAPASITAS</th>
			<th>NIK</th>
			<th>NPWP</th>
			<th>ALAMAT KANDANG</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $k => $val): ?>
			<tr>
				<td style="vertical-align: top;"><?php echo $val['nomor']; ?></td>
				<td style="vertical-align: top;"><?php echo $val['nama']; ?></td>
				<td style="vertical-align: top;" align="left" style="mso-number-format:\@;"><?php echo $val['no_telp']; ?></td>
				<td style="vertical-align: top;"><?php echo $val['unit']; ?></td>
				<td style="vertical-align: top;" align="left"><?php echo $val['kdg']; ?></td>
				<td style="vertical-align: top;" align="right" style="mso-number-format:0;"><?php echo $val['kapasitas']; ?></td>
				<td style="vertical-align: top;" align="left" style="mso-number-format:\@;"><?php echo !empty($val['ktp']) ? $val['ktp'] : '-'; ?></td>
				<td style="vertical-align: top;" align="left" style="mso-number-format:\@;"><?php echo !empty($val['npwp']) ? $val['npwp'] : '-'; ?></td>
				<td style="vertical-align: top;" align="left"><?php echo $val['alamat']; ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>