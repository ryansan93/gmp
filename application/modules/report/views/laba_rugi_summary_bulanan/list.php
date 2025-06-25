<?php if ( !empty($data) && count($data) > 0 ): ?>
	<tr>
		<td colspan="3" class="text-center"><b></b></td>
	</tr>
	<tr>
		<td class="text-center"><b>KETERANGAN</b></td>
		<td class="text-center"><b>DEBET</b></td>
		<td class="text-center"><b>KREDIT</b></td>
	</tr>
	<?php
		$total_debet = 0;
		$total_kredit = 0;

		$idx_lr_kotor = 0;
	?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php if ( stristr($v_data['nama'], 'LABA KOTOR') === FALSE ) : ?>
			<tr>
				<td colspan="3"><b><?php echo $v_data['nama']; ?></b></td>
			</tr>
			<?php
				$debet = 0;
				$kredit = 0;
			?>
			<?php foreach ($v_data['detail'] as $k_det => $v_det): ?>
				<tr>
					<td><?php echo $v_det['item_report_nama'] ?></td>
					<td class="text-right"><?php echo ($v_det['debet'] >= 0) ? angkaDecimal(abs($v_det['debet'])) : '('.angkaDecimal(abs($v_det['debet'])).')'; ?></td>
					<td class="text-right"><?php echo ($v_det['kredit'] >= 0) ? angkaDecimal(abs($v_det['kredit'])) : '('.angkaDecimal(abs($v_det['kredit'])).')'; ?></td>
				</tr>
				<?php
					$debet += $v_det['debet'];
					$kredit += $v_det['kredit'];
				?>
			<?php endforeach ?>
			<tr>
				<td class="text-right"><b>TOTAL <?php echo $v_data['nama']; ?></b></td>
				<td class="text-right"><b><?php echo ($debet >= 0) ? angkaDecimal(abs($debet)) : angkaDecimal(abs($debet)); ?></b></td>
				<td class="text-right"><b><?php echo ($kredit >= 0) ? angkaDecimal(abs($kredit)) : angkaDecimal(abs($kredit)); ?></b></td>
			</tr>
			<?php
				$total_debet += $debet;
				$total_kredit += $kredit;
			?>
		<?php endif ?>

		<?php
			if ( stristr($v_data['nama'], 'PENJUALAN') !== false || stristr($v_data['nama'], 'BEBAN POKOK PENJUALAN') !== false ) {
                $idx_lr_kotor++;
            }
		?>

		<?php if ( $idx_lr_kotor == 2 ) : ?>
			<tr>
				<td class="text-right"><b>TOTAL <?php echo $data['lr_kotor']['nama']; ?></b></td>
				<?php $laba_kotor = $data['lr_kotor']['detail'][0]['kredit'] - $data['lr_kotor']['detail'][0]['debet']; ?>
				<td colspan="2" class="text-right"><b><?php echo ($laba_kotor >= 0) ? angkaDecimal(abs($laba_kotor)) : '('.angkaDecimal(abs($laba_kotor)).')'; ?></b></td>
			</tr>
			<?php $idx_lr_kotor++; ?>
        <?php endif ?>
	<?php endforeach ?>
	<tr>
		<td class="text-right"><b>LABA BERSIH PERUSAHAAN</b></td>
		<?php $laba_bersih = $total_kredit - $total_debet; ?>
		<td colspan="2" class="text-right"><b><?php echo ($laba_bersih >= 0) ? angkaDecimal(abs($laba_bersih)) : '('.angkaDecimal(abs($laba_bersih)).')'; ?></b></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak di temukan.</td>
	</tr>
<?php endif ?>