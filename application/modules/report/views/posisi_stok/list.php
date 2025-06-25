<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_detail => $v_detail): ?>
		<tbody>
			<tr>
				<td colspan="12" style="background-color: #ededed;"><b><?php echo $v_detail['nama']; ?></b></td>
			</tr>
		</tbody>
		<?php $urut_item = 0; ?>
		<?php foreach ($v_detail['detail'] as $k_item => $v_item): ?>
			<?php $saldo = 0; ?>
			<?php $nilai_saldo = 0; ?>
			<?php $idx_item = 0; ?>
			<?php 
				$rowspan_item = 0;
				foreach ($v_item['detail'] as $k_tgl => $v_tgl) {
					$rowspan_item += count($v_tgl['masuk']) + count($v_tgl['keluar']);
				} 
			?>
			<tbody class="row-wrapper">
				<?php foreach ($v_item['detail'] as $k_tgl => $v_tgl): ?>
					<?php $idx_tgl = 0; ?>
					<?php $rowspan_tanggal = count($v_tgl['masuk']) + count($v_tgl['keluar']); ?>
					<?php if ( !empty($v_tgl['masuk']) ): ?>
						<?php foreach ($v_tgl['masuk'] as $k_masuk => $v_masuk): ?>
							<?php $saldo += $v_masuk['masuk']; ?>
							<?php $nilai_saldo += ($v_masuk['masuk'] * $v_masuk['harga_beli']); ?>
							<tr class="data">
								<?php if ( $idx_item == 0 ): ?>
									<td rowspan="<?php echo $rowspan_item; ?>"><?php echo $v_item['kode']; ?></td>
									<td rowspan="<?php echo $rowspan_item; ?>"><?php echo $v_item['nama']; ?></td>

									<?php $idx_item++; ?>
								<?php endif ?>
								<?php if ( $idx_tgl == 0 ): ?>
									<td class="text-center" rowspan="<?php echo $rowspan_tanggal; ?>"><?php echo strtoupper(tglIndonesia($v_masuk['tgl_trans'], '-', ' ')); ?></td>
									<?php $idx_tgl++; ?>
								<?php endif ?>
								<td>
									<?php if ( stristr($v_masuk['jenis_trans'], 'order') === false ): ?>
										<b><?php echo $v_masuk['jenis_trans']; ?></b>
										<br>										
									<?php endif ?>
									<?php echo $v_masuk['kode']; ?>
									<hr style="margin-top: 5px; margin-bottom: 5px;">
									<?php echo $v_masuk['dari']; ?>
								</td>
								<td class="text-right"><?php echo angkaDecimal($v_masuk['masuk']); ?></td>
								<td class="text-right"><?php echo angkaDecimalFormat($v_masuk['harga_beli'], $v_masuk['decimal']); ?></td>
								<td class="text-right"><?php echo angkaDecimal($v_masuk['nilai_beli']); ?></td>
								<td class="text-right"><?php echo angkaDecimalFormat($v_masuk['harga_jual'], $v_masuk['decimal']); ?></td>
								<td class="text-right"><?php echo angkaDecimal($v_masuk['nilai_jual']); ?></td>
								<td class="text-right"><?php echo angkaDecimal($saldo); ?></td>
								<td class="text-right"><?php echo angkaDecimal($nilai_saldo); ?></td>
							</tr>
						<?php endforeach ?>
					<?php endif ?>
				<?php endforeach ?>
			</tbody>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tbody class="row-wrapper">
		<tr>
			<td colspan="11">Data tidak ditemukan.</td>
		</tr>
	</tbody>
<?php endif ?>