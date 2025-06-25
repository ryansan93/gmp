<?php if ( !empty($data) && count($data) > 0 || !empty($data_saldo) ): ?>
	<?php
		$saldo_awal = $data_saldo['debit'];
		$saldo = $saldo_awal;
		$dropping_kas_kecil = 0;
		$total_operasional = 0;
	?>

	<tr>
		<td><?php echo strtoupper(tglIndonesia($data_saldo['tanggal'], '-', ' ')); ?></td>
		<td><?php echo strtoupper($data_saldo['nama_akun_transaksi']); ?></td>
		<td><?php echo strtoupper($data_saldo['pic']); ?></td>
		<td><?php echo strtoupper(trim($data_saldo['keterangan'])); ?></td>
		<td class="text-right"><?php echo angkaDecimal($data_saldo['debit']); ?></td>
		<td class="text-right"><?php echo angkaDecimal($data_saldo['kredit']); ?></td>
		<td class="text-right"><?php echo angkaDecimal($saldo); ?></td>
	</tr>

	<?php foreach ($data as $key => $value): ?>
		<?php $saldo = ($saldo + $value['debit']) - $value['kredit']; ?>
		<tr class="cursor-p det_jurnal" data-id="<?php echo $value['det_jurnal_id']; ?>" data-gstatus="<?php echo $g_status; ?>" onclick="kk.detailForm(this)">
			<td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($value['nama_akun_transaksi'].' ('.$value['no_akun_transaksi'].')'); ?></td>
			<td><?php echo strtoupper($value['pic']); ?></td>
			<td><?php echo strtoupper(trim($value['keterangan'])); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['debit']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['kredit']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($saldo); ?></td>
		</tr>

		<?php $dropping_kas_kecil += $value['debit']; ?>
		<?php $total_operasional += $value['kredit']; ?>
	<?php endforeach ?>
	<tr>
		<td colspan="4" class="text-right"><b>SALDO AWAL</b></td>
		<td colspan="3" class="text-right"><b><?php echo angkaDecimal($saldo_awal); ?></b></td>
	</tr>
	<tr>
		<td colspan="4" class="text-right"><b>DROPPING KAS KECIL</b></td>
		<td colspan="3" class="text-right"><b><?php echo angkaDecimal($dropping_kas_kecil); ?></b></td>
	</tr>
	<tr>
		<td colspan="4" class="text-right"><b>TOTAL OPERASIONAL</b></td>
		<td colspan="3" class="text-right"><b><?php echo angkaDecimal($total_operasional); ?></b></td>
	</tr>
		<td colspan="4" class="text-right"><b>SALDO AKHIR</b></td>
		<?php
			$saldo_akhir = ($saldo_awal + $dropping_kas_kecil) - $total_operasional;
		?>
		<td colspan="3" class="text-right saldo_akhir"><b><?php echo angkaDecimal($saldo_akhir); ?></b></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>