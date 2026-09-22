<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Arah</th>
			<th>Partner</th>
			<th>Barang</th>
			<th>Qty</th>
			<th>Harga</th>
			<th>Dok. Asal</th>
			<th>Status</th>
			<th>Waktu Kirim</th>
			<th>Waktu Terima</th>
			<th>Pesan Error</th>
			<th>Aksi</th>
		</tr>
	</thead>
	<tbody>
		<?php if (empty($list)) : ?>
			<tr><td colspan="11" class="text-center">Tidak ada data.</td></tr>
		<?php else : ?>
			<?php foreach ($list as $row) : ?>
				<tr>
					<td><?php echo $row['arah']; ?></td>
					<td><?php echo $row['kode_partner']; ?><?php echo !empty($row['d_partner']['nama_partner']) ? ' - '.$row['d_partner']['nama_partner'] : ''; ?></td>
					<td><?php echo $row['kode_barang']; ?><?php echo !empty($row['d_barang']['nama']) ? ' - '.$row['d_barang']['nama'] : ''; ?></td>
					<td class="text-right"><?php echo number_format($row['jml_qty'], 2); ?></td>
					<td class="text-right"><?php echo !empty($row['harga']) ? number_format($row['harga'], 2) : '-'; ?></td>
					<td><?php echo $row['tbl_name_asal']; ?> #<?php echo $row['tbl_id_asal']; ?></td>
					<td>
						<?php if ($row['status'] == 'DITERIMA') : ?>
							<span class="label label-success">Diterima</span>
						<?php elseif ($row['status'] == 'GAGAL') : ?>
							<span class="label label-danger">Gagal</span>
						<?php else : ?>
							<span class="label label-warning">Terkirim</span>
						<?php endif; ?>
					</td>
					<td><?php echo $row['waktu_kirim']; ?></td>
					<td><?php echo $row['waktu_terima']; ?></td>
					<td class="text-danger"><?php echo $row['pesan_error']; ?></td>
					<td>
						<?php if ($row['status'] == 'GAGAL' && $akses['a_submit'] == 1) : ?>
							<button type="button" class="btn btn-xs btn-primary" onclick="intercompanyPakan.kirimUlang(<?php echo $row['id']; ?>)">Kirim Ulang</button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
