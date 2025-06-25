<?php if ( !empty($data) && count($data) > 0 ): ?>
	<tr>
		<td colspan="3" class="text-center"><b></b></td>
	</tr>
	<tr>
		<td class="text-center"><b>NAMA AKUN</b></td>
		<td class="text-center"><b>DEBET</b></td>
		<td class="text-center"><b>KREDIT</b></td>
	</tr>
	<?php
		$total_debet = 0;
		$total_kredit = 0;

		$idx_lr_kotor = 0;
	?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td colspan="3"><b><?php echo $v_data['nama']; ?></b></td>
		</tr>
		<?php
			$debet = 0;
			$kredit = 0;
		?>
		<?php foreach ($v_data['detail'] as $k_det => $v_det): ?>
			<tr>
				<td style="padding-left: 30px;"><?php echo $v_det['item_report_nama'] ?></td>
				<td class="text-right"><?php echo ($v_det['debet'] >= 0) ? angkaDecimal($v_det['debet']) : '('.angkaDecimal(abs($v_det['debet'])).')'; ?></td>
				<td class="text-right"><?php echo ($v_det['kredit'] >= 0) ? angkaDecimal($v_det['kredit']) : '('.angkaDecimal(abs($v_det['kredit'])).')'; ?></td>
			</tr>
			<?php
				$debet += $v_det['debet'];
				$kredit += $v_det['kredit'];
			?>
		<?php endforeach ?>
		<tr>
			<td class="text-right">&nbsp;</td>
			<td class="text-right" style="border-top: 1px solid;"><b><?php echo ($debet >= 0) ? angkaDecimal($debet) : angkaDecimal(abs($debet)); ?></b></td>
			<td class="text-right" style="border-top: 1px solid;"><b><?php echo ($kredit >= 0) ? angkaDecimal($kredit) : angkaDecimal(abs($kredit)); ?></b></td>
		</tr>
		<?php
			$total_debet += $debet;
			$total_kredit += $kredit;
		?>
	<?php endforeach ?>
	<tr>
		<td class="text-right"><b>TOTAL</b></td>
		<td class="text-right" style="border-top: 1px solid;"><b><?php echo ($total_debet >= 0) ? angkaDecimal($total_debet) : angkaDecimal(abs($total_debet)); ?></b></td>
		<td class="text-right" style="border-top: 1px solid;"><b><?php echo ($total_kredit >= 0) ? angkaDecimal($total_kredit) : angkaDecimal(abs($total_kredit)); ?></b></td>
	</tr>
	<tr>
		<td class="text-right"><b>SELISIH AKTIVA - PASSIVA</b></td>
		<?php $selisih = $total_kredit - $total_debet; ?>
		<td colspan="2" class="text-right"><b><?php echo ($selisih >= 0) ? angkaDecimal($selisih) : '('.angkaDecimal(abs($selisih)).')'; ?></b></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak di temukan.</td>
	</tr>
<?php endif ?>