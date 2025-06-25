<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $grand_total_tagihan = 0; $grand_total_bayar = 0; $grand_total_sisa_tagihan = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td colspan="4" style="background-color: #dedede;"><b><?php echo strtoupper($v_data['nama']).' | AKHIR BAYAR : '.angkaRibuan($v_data['total_pembayaran_terakhir']).', TANGGAL : '.$v_data['tgl_pembayaran_terakhir']; ?></b></td>
			<td class="text-right" style="background-color: #dedede;"><b><?php echo angkaDecimal($v_data['total_tonase']); ?></b></td>
			<td style="background-color: #dedede;"></td>
			<td class="text-right" style="background-color: #dedede;"><b><?php echo angkaDecimal($v_data['total_tagihan']); ?></b></td>
			<td class="text-right" style="background-color: #dedede;"><b><?php echo angkaDecimal($v_data['total_sisa_tagihan']); ?></b></td>
			<td class="text-center" style="background-color: #dedede;"><b><?php echo 'MAX : '.$v_data['max_umur_hutang']; ?></b></td>
		</tr>
		<?php foreach ($v_data['do'] as $k_st => $v_st): ?>
			<?php foreach ($v_st['list_do'] as $k_do => $v_do): ?>
				<tr>
					<td class="text-left"><?php echo tglIndonesia($v_do['tgl_panen'], '-', ' '); ?></td>
					<td class="text-left"><?php echo $v_do['no_nota']; ?></td>
					<td class="text-left"><?php echo $v_do['no_do']; ?></td>
					<td class="text-left"><?php echo strtoupper($v_do['nama']); ?></td>
					<td class="text-right tonase"><?php echo angkaDecimal($v_do['tonase']); ?></td>
					<td class="text-right"><?php echo angkaDecimal($v_do['harga']); ?></td>
					<td class="text-right tagihan"><?php echo angkaDecimal($v_do['total_tagihan']); ?></td>
					<td class="text-right sisa_tagihan"><?php echo angkaDecimal($v_do['sisa_tagihan']); ?></td>
					<td class="text-center"><?php echo $v_do['lama_bayar']; ?></td>
				</tr>
			<?php endforeach ?>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>