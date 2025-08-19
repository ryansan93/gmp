<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $no_pelanggan = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php if ( $v_data['no_pelanggan'] <> $no_pelanggan ) { ?>
			<tr class="sub_total" data-nopelanggan="<?php echo $v_data['no_pelanggan']; ?>">
				<td class="text-left" colspan="3">
					<?php $tgl_bayar = !empty($v_data['tgl_bayar_terakhir']) ? tglIndonesia($v_data['tgl_bayar_terakhir'], '-', ' ') : '-'; ?>
					<b><?php echo strtoupper($v_data['nama_pelanggan']).' | AKHIR BAYAR : '.angkaRibuan($v_data['jumlah_bayar_terakhir']).', TANGGAL : '.$tgl_bayar; ?></b>
				</td>
				<td class="text-right sub_total" target="sub_total_tonase" data-target="tonase"><b>0</b></td>
				<td class="text-right sub_total" target="sub_total_tagihan" data-target="tagihan"><b>0</b></td>
				<td class="text-right sub_total" target="sub_total_cn" data-target="cn"><b>0</b></td>
				<td class="text-right sub_total" target="sub_total_dn" data-target="dn"><b>0</b></td>
				<td class="text-right sub_total" target="sub_total_bayar" data-target="bayar"><b>0</b></td>
				<td class="text-right sub_total" target="sub_total_sisa_tagihan" data-target="sisa_tagiahn"><b>0</b></td>
				<td class="text-center" colspan="2"></td>
			</tr>
			<?php $no_pelanggan = $v_data['no_pelanggan']; ?>
		<?php } ?>
		<tr class="detail" data-nopelanggan="<?php echo $v_data['no_pelanggan']; ?>">
			<td class="text-left"><?php echo strtoupper($v_data['nama']); ?></td>
			<td class="text-left"><?php echo tglIndonesia($v_data['tgl_panen'], '-', ' '); ?></td>
			<td class="text-left"><?php echo $v_data['no_inv']; ?></td>
			<td class="text-right tonase"><?php echo angkaDecimal($v_data['tonase']); ?></td>
			<td class="text-right tagihan"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td class="text-right cn"><?php echo angkaDecimal($v_data['cn']); ?></td>
			<td class="text-right dn"><?php echo angkaDecimal($v_data['dn']); ?></td>
			<td class="text-right bayar"><?php echo angkaDecimal($v_data['bayar']); ?></td>
			<td class="text-right sisa_tagihan"><?php echo angkaDecimal($v_data['sisa_tagihan']); ?></td>
			<td class="text-center"><?php echo $v_data['umur_invoice']; ?></td>
			<td class="text-center"><?php echo $v_data['umur_invoice_by_top']; ?></td>
		</tr>
		<!-- <tr>
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
					<td class="text-left"><?php echo strtoupper($v_do['nama']); ?></td>
					<td class="text-left"><?php echo tglIndonesia($v_do['tgl_panen'], '-', ' '); ?></td>
					<td class="text-left"><?php echo $v_do['no_inv']; ?></td>
					<td class="text-right tonase"><?php echo angkaDecimal($v_do['tonase']); ?></td>
					<td class="text-right tagihan"><?php echo angkaDecimal($v_do['total']); ?></td>
					<td class="text-right cn"><?php echo angkaDecimal($v_do['cn']); ?></td>
					<td class="text-right dn"><?php echo angkaDecimal($v_do['dn']); ?></td>
					<td class="text-right bayar"><?php echo angkaDecimal($v_do['bayar']); ?></td>
					<td class="text-right sisa_tagihan"><?php echo angkaDecimal($v_do['sisa_tagihan']); ?></td>
					<td class="text-center"><?php echo $v_do['umur_invoice']; ?></td>
					<td class="text-center"><?php echo $v_do['umur_invoice_by_top']; ?></td>
				</tr>
			<?php endforeach ?>
		<?php endforeach ?> -->
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>