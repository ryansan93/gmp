<table border="1">
	<thead>
		<tr>
			<th>Pelanggan</th>
			<th>Tanggal</th>
			<th>No. Nota</th>
			<th>No. DO</th>
			<th>Plasma</th>
			<th>Tonase</th>
			<th>Harga</th>
			<th>Total</th>
			<th>Sisa Tagihan</th>
			<th>Lama Belum Bayar (Hari)</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $k_data => $v_data): ?>
			<?php
				$idx_pelanggan = 0;
				$rowspan_pelanggan = 0;
				foreach ($v_data['do'] as $k_st => $v_st) {
					$rowspan_pelanggan += count($v_st['list_do']);
				}
			?>
			<?php foreach ($v_data['do'] as $k_st => $v_st): ?>
				<?php foreach ($v_st['list_do'] as $k_do => $v_do): ?>
					<tr>
						<?php if ( $idx_pelanggan == 0 ) : ?>
							<td valign="top" align="left" rowspan="<?php echo $rowspan_pelanggan; ?>">
								<b>
									<span><?php echo strtoupper($v_data['nama']); ?></span>
									<br style="mso-data-placement:same-cell;" />
									<span><?php echo 'AKHIR BAYAR : '.angkaRibuan($v_data['total_pembayaran_terakhir']); ?></span>
									<br style="mso-data-placement:same-cell;" />
									<span><?php echo 'TANGGAL : '.$v_data['tgl_pembayaran_terakhir']; ?></span>
									<br style="mso-data-placement:same-cell;" />
									<span><?php echo 'MAX : '.$v_data['max_umur_hutang']; ?></span>
									<!-- <?php echo strtoupper($v_data['nama']).'\nAKHIR BAYAR : '.angkaRibuan($v_data['total_pembayaran_terakhir']).'\nTANGGAL : '.$v_data['tgl_pembayaran_terakhir'].'\nMAX : '.$v_data['max_umur_hutang']; ?> -->
								</b>
							</td>
						<?php endif ?>
						<td valign="top" align="left"><?php echo $v_do['tgl_panen']; ?></td>
						<td valign="top" align="left"><?php echo $v_do['no_nota']; ?></td>
						<td valign="top" align="left"><?php echo $v_do['no_do']; ?></td>
						<td valign="top" align="left"><?php echo strtoupper($v_do['nama']); ?></td>
						<td valign="top" align="right"><?php echo angkaDecimal($v_do['tonase']); ?></td>
						<td valign="top" align="right"><?php echo angkaDecimal($v_do['harga']); ?></td>
						<td valign="top" align="right"><?php echo angkaDecimal($v_do['total_tagihan']); ?></td>
						<td valign="top" align="right"><?php echo angkaDecimal($v_do['sisa_tagihan']); ?></td>
						<td valign="top" class="text-center"><?php echo $v_do['lama_bayar']; ?></td>
					</tr>
					<?php $idx_pelanggan++; ?>
				<?php endforeach ?>
			<?php endforeach ?>
		<?php endforeach ?>
	</tbody>
</table>