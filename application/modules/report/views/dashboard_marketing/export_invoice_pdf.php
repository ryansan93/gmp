<style type="text/css">
	body {
		font-size: 10pt;
		font-family: sans-serif;
	}

	h3 {
		margin: 0px 0px 4px 0px;
	}

	table {
		border-collapse: collapse;
		width: 100%;
	}

	table.border-field {
		font-size: 8pt;
	}

	table.border-field td, table.border-field th {
		border: 1px solid #000;
		padding: 3px 5px;
	}

	.text-center { text-align: center; }
	.text-right { text-align: right; }

	.header-info td {
		padding: 1px 0px;
		vertical-align: top;
	}

	tr.subtotal td {
		background: #f0f0f0;
		font-weight: bold;
	}

	@page {
		margin: 1.5em;
	}
</style>

<h3>Detail Invoice</h3>

<table class="header-info">
	<tr>
		<td style="width: 100px;">Perusahaan</td>
		<td style="width: 10px;">:</td>
		<td><?php echo strtoupper($nama_perusahaan); ?></td>
	</tr>
	<tr>
		<td>Bakul</td>
		<td>:</td>
		<td><b><?php echo strtoupper($nama_pelanggan); ?></b></td>
	</tr>
	<tr>
		<td>Tanggal Cetak</td>
		<td>:</td>
		<td><?php echo $tanggal_cetak; ?></td>
	</tr>
</table>

<br>

<table class="border-field">
	<thead>
		<tr>
			<th>No</th>
			<th>Unit</th>
			<th>Plasma</th>
			<th>No. Invoice</th>
			<th>Tgl Panen</th>
			<th>Umur</th>
			<th class="text-right">Total</th>
			<th class="text-right">Bayar</th>
			<th class="text-right">Sisa</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$total_total = 0;
			$total_bayar = 0;
			$total_sisa = 0;

			$sub_unit = null;
			$sub_total = 0;
			$sub_bayar = 0;
			$sub_sisa = 0;
			$no_urut = 0;
		?>
		<?php if ( !empty($data) ): ?>
			<?php foreach ($data as $idx => $v): ?>
				<?php if ( $sub_unit !== null && $sub_unit !== $v['unit'] ): ?>
					<tr class="subtotal">
						<td colspan="6" class="text-right">SUBTOTAL <?php echo strtoupper($sub_unit); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_total); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_bayar); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_sisa); ?></td>
					</tr>
					<?php $sub_total = 0; $sub_bayar = 0; $sub_sisa = 0; ?>
				<?php endif ?>

				<?php
					$sub_unit = $v['unit'];
					$sub_total += $v['total'];
					$sub_bayar += $v['bayar'];
					$sub_sisa += $v['sisa'];

					$total_total += $v['total'];
					$total_bayar += $v['bayar'];
					$total_sisa += $v['sisa'];

					$no_urut++;
				?>
				<tr>
					<td class="text-center"><?php echo $no_urut; ?></td>
					<td><?php echo strtoupper($v['unit']); ?></td>
					<td><?php echo strtoupper($v['nama_plasma']); ?> <small>(Kdg <?php echo strtoupper($v['no_kandang']); ?>)</small></td>
					<td><?php echo $v['no_inv']; ?></td>
					<td class="text-center"><?php echo tglIndonesia($v['tgl_panen'], '-', ' '); ?></td>
					<td class="text-center"><?php echo $v['umur_invoice']; ?> hari</td>
					<td class="text-right"><?php echo angkaRibuan($v['total']); ?></td>
					<td class="text-right"><?php echo angkaRibuan($v['bayar']); ?></td>
					<td class="text-right"><b><?php echo angkaRibuan($v['sisa']); ?></b></td>
				</tr>

				<?php if ( $idx == count($data)-1 ): ?>
					<tr class="subtotal">
						<td colspan="6" class="text-right">SUBTOTAL <?php echo strtoupper($sub_unit); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_total); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_bayar); ?></td>
						<td class="text-right"><?php echo angkaRibuan($sub_sisa); ?></td>
					</tr>
				<?php endif ?>
			<?php endforeach ?>
		<?php else: ?>
			<tr>
				<td colspan="9" class="text-center">Tidak ada invoice belum lunas.</td>
			</tr>
		<?php endif ?>
	</tbody>
	<?php if ( !empty($data) ): ?>
		<tfoot>
			<tr>
				<td colspan="6"><b>TOTAL</b></td>
				<td class="text-right"><b><?php echo angkaRibuan($total_total); ?></b></td>
				<td class="text-right"><b><?php echo angkaRibuan($total_bayar); ?></b></td>
				<td class="text-right"><b><?php echo angkaRibuan($total_sisa); ?></b></td>
			</tr>
		</tfoot>
	<?php endif ?>
</table>
