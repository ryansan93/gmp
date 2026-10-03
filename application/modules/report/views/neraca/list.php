<?php if ( !empty($data) && count($data) > 0 ): ?>

	<!-- Header kolom -->
	<tr>
		<td class="text-center" style="background-color:#fff; font-weight:bold; padding:8px 6px; border-bottom:2px solid #6b83a0;">KETERANGAN</td>
		<td class="text-center" style="background-color:#fff; font-weight:bold; padding:8px 6px; border-bottom:2px solid #6b83a0; width:28%;">SALDO</td>
	</tr>

	<?php $grand_debet = 0; $grand_kredit = 0; ?>

	<?php foreach ($data as $v_data): ?>
		<?php if ( empty($v_data['detail']) ) continue; ?>
		<?php $tipe_group = $v_data['tipe'] ?? 'data'; ?>

		<?php if ( $tipe_group === 'subtotal' ): ?>
			<?php $s = (float)($v_data['detail'][0]['saldo'] ?? 0); ?>
			<tr style="background-color:#fdf3d7; border-top:2px solid #e6c44f;">
				<td class="text-right" style="font-weight:bold; color:#7a5c00; padding:7px 8px;">
					<?php echo strtoupper($v_data['nama']); ?>
				</td>
				<td class="text-right" style="font-weight:bold; color:#7a5c00; padding:7px 8px;">
					<?php echo ($s >= 0) ? angkaDecimal(abs($s)) : '('.angkaDecimal(abs($s)).')'; ?>
				</td>
			</tr>

		<?php else: ?>

			<!-- Group Header -->
			<tr style="background-color:#fff; border-top:2px solid #6b83a0;">
				<td colspan="2" style="font-weight:bold; color:#1e3a5f; letter-spacing:0.5px; padding:7px 10px;">
					<?php echo strtoupper($v_data['nama']); ?>
				</td>
			</tr>

			<?php $sec_d = 0; $sec_k = 0; $sec_s = 0; ?>

			<?php foreach ($v_data['detail'] as $v_det): ?>
				<?php if ( ($v_det['item_tipe'] ?? 'item') === 'spacer' ): ?>
					<tr><td colspan="2" style="padding:3px 0;"></td></tr>
				<?php else: ?>
					<?php $s = (float)($v_det['saldo'] ?? 0); ?>
					<tr class="neraca-item-row" style="background-color:#fff; cursor:pointer;" onclick="nrc.formDetail(this)" data-id-header="<?php echo $v_data['id']; ?>" data-item-report-id="<?php echo $v_det['item_report_id']; ?>" data-item-report-nama="<?php echo htmlspecialchars($v_det['item_report_nama']); ?>" title="Klik untuk lihat detail per COA">
						<td style="padding-left:28px; padding-top:5px; padding-bottom:5px; color:#333;">
							<?php echo $v_det['item_report_nama']; ?>
							<button type="button" class="btn btn-xs btn-default" style="margin-left:8px; padding:0 6px; font-size:11px;" onclick="event.stopPropagation(); nrc.formDetail(this);" title="Lihat detail per COA">
								<i class="fa fa-search"></i> Detail
							</button>
						</td>
						<td class="text-right" style="padding-top:5px; padding-bottom:5px; color:#333;">
							<?php echo ($s >= 0) ? angkaDecimal(abs($s)) : '('.angkaDecimal(abs($s)).')'; ?>
						</td>
					</tr>
					<?php
						$sec_d += (float)($v_det['debet']  ?? 0);
						$sec_k += (float)($v_det['kredit'] ?? 0);
						$sec_s += $s;
					?>
				<?php endif ?>
			<?php endforeach ?>

			<!-- Total per group -->
			<tr style="background-color:#fff; border-top:1px solid #a8c8e8;">
				<td class="text-right" style="font-weight:bold; color:#1e3a5f; padding:7px 8px;">
					TOTAL <?php echo strtoupper($v_data['nama']); ?>
				</td>
				<td class="text-right" style="font-weight:bold; color:#1e3a5f; padding:7px 8px;">
					<?php echo ($sec_s >= 0) ? angkaDecimal(abs($sec_s)) : '('.angkaDecimal(abs($sec_s)).')'; ?>
				</td>
			</tr>
			<!-- Spacer antar group -->
			<tr><td colspan="2" style="padding:4px 0; background-color:#fff; border:none;"></td></tr>

			<?php $grand_debet += $sec_d; $grand_kredit += $sec_k; ?>
		<?php endif ?>
	<?php endforeach ?>

	<!-- Selisih Aktiva - Passiva (cek keseimbangan neraca, berdasarkan raw debet=kredit di seluruh COA) -->
	<?php $selisih = $grand_kredit - $grand_debet; ?>
	<?php if ( abs($selisih) < 1 ): ?>
		<tr style="background-color:#e3f3ec; border-top:3px solid #5aab8a;">
			<td class="text-right" style="font-weight:bold; color:#1a5c42; font-size:13px; letter-spacing:0.5px; padding:9px 8px;">
				SELISIH AKTIVA - PASSIVA (BALANCE)
			</td>
			<td class="text-right" style="font-weight:bold; color:#1a5c42; font-size:13px; padding:9px 8px;">
				<?php echo angkaDecimal(abs($selisih)); ?>
			</td>
		</tr>
	<?php else: ?>
		<tr style="background-color:#fbe4e7; border-top:3px solid #d4788a;">
			<td class="text-right" style="font-weight:bold; color:#7a2030; font-size:13px; letter-spacing:0.5px; padding:9px 8px;">
				SELISIH AKTIVA - PASSIVA (TIDAK BALANCE)
			</td>
			<td class="text-right" style="font-weight:bold; color:#7a2030; font-size:13px; padding:9px 8px;">
				<?php echo ($selisih >= 0) ? angkaDecimal(abs($selisih)) : '('.angkaDecimal(abs($selisih)).')'; ?>
			</td>
		</tr>
	<?php endif ?>

<?php else: ?>
	<tr>
		<td colspan="2" class="text-center" style="padding:20px; color:#999;">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>
