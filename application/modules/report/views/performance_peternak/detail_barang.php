<div class="sk-detail-wrap">
	<?php if ( !empty($data) && count($data) > 0 ): ?>
		<div class="sk-detail-table-wrap">
			<table class="table table-condensed table-bordered sk-detail-table">
				<thead>
					<tr>
						<th>Tanggal</th>
						<th>Jenis</th>
						<th class="text-right">Masuk</th>
						<th class="text-right">Keluar</th>
						<th class="text-right">Saldo</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data as $value): ?>
						<tr>
							<td>
								<?php echo tglIndonesia($value['tanggal'], '-', ' '); ?>
								<br><small class="text-muted"><?php echo $value['kode_trans']; ?></small>
							</td>
							<td><?php echo strtoupper($value['jenis_trans']); ?></td>
							<td class="text-right"><?php echo ($value['masuk'] > 0) ? angkaDecimal($value['masuk']) : '-'; ?></td>
							<td class="text-right"><?php echo ($value['keluar'] > 0) ? angkaDecimal($value['keluar']) : '-'; ?></td>
							<td class="text-right"><b><?php echo angkaDecimal($value['saldo']); ?></b></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div class="sk-empty-state">
			<i class="fa fa-inbox"></i>
			<div>Belum ada transaksi untuk barang ini.</div>
		</div>
	<?php endif ?>
</div>
