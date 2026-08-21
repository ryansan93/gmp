<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php
		$sum_fields = ['populasi', 'jml_panen_ekor', 'jml_panen_kg', 'selisih_budidaya', 'bonus_pasar', 'bonus_kematian', 'bonus_insentif_fcr', 'total_bonus_insentif_listrik', 'insentif_lpg', 'total_pendapatan_plasma', 'total_pendapatan_inti'];
		$avg_fields = ['bb', 'fcr', 'deplesi', 'rata_umur', 'adg', 'ip'];
		$all_fields = array_merge($sum_fields, $avg_fields);

		// Sub total per unit (data sudah terurut "order by unit asc" dari query) + Grand Total seluruh baris
		$unit_totals = [];
		$unit_counts = [];
		$total = array_fill_keys($all_fields, 0);
		$total_count = 0;

		foreach ($data as $v) {
			$u = $v['unit'];
			if ( !isset($unit_totals[$u]) ) {
				$unit_totals[$u] = array_fill_keys($all_fields, 0);
				$unit_counts[$u] = 0;
			}
			foreach ($all_fields as $f) {
				$val = isset($v[$f]) ? $v[$f] : 0;
				$unit_totals[$u][$f] += $val;
				$total[$f] += $val;
			}
			$unit_counts[$u]++;
			$total_count++;
		}

		$fmt = function($v) {
			return ($v >= 0) ? angkaDecimal($v) : '('.angkaDecimal(abs($v)).')';
		};

		// bb/fcr/deplesi/rata_umur/adg/ip adalah rata2 per siklus, jadi di baris total ditampilkan rata2-nya (bukan dijumlah)
		$render_total_row = function($label, $t, $count) use ($fmt) {
			?>
			<tr style="font-weight: bold; background-color: #f5f5f5;">
				<td colspan="4"><?php echo $label; ?></td>
				<td class="text-right"><?php echo $fmt($t['populasi']); ?></td>
				<td class="text-right"><?php echo $fmt($t['jml_panen_ekor']); ?></td>
				<td class="text-right"><?php echo $fmt($t['jml_panen_kg']); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['bb'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['fcr'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['deplesi'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['rata_umur'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['adg'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($count > 0 ? $t['ip'] / $count : 0); ?></td>
				<td class="text-right"><?php echo $fmt($t['selisih_budidaya']); ?></td>
				<td class="text-right"><?php echo $fmt($t['bonus_pasar']); ?></td>
				<td class="text-right"><?php echo $fmt($t['bonus_kematian']); ?></td>
				<td class="text-right"><?php echo $fmt($t['bonus_insentif_fcr']); ?></td>
				<td class="text-right"><?php echo $fmt($t['total_bonus_insentif_listrik']); ?></td>
				<td class="text-right"><?php echo $fmt($t['insentif_lpg']); ?></td>
				<td class="text-right"><?php echo $fmt($t['total_pendapatan_plasma']); ?></td>
				<td class="text-right"><?php echo $fmt($t['total_pendapatan_inti']); ?></td>
			</tr>
			<?php
		};

		$data_count = count($data);
	?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="row-clickable">
			<td title="NAMA PLASMA"><?php echo strtoupper($value['mitra']); ?></td>
			<td class="text-center" title="UNIT"><?php echo strtoupper($value['unit']); ?></td>
			<td title="NOREG"><?php echo strtoupper($value['noreg']); ?></td>
			<td class="text-center" title="TGL CHICK IN"><?php echo !empty($value['tgl_docin']) ? strtoupper(tglIndonesia($value['tgl_docin'], '-', ' ')) : '-'; ?></td>
			<td class="text-right" title="POPULASI"><?php echo angkaDecimal($value['populasi']); ?></td>
			<td class="text-right" title="EKOR PANEN"><?php echo angkaDecimal($value['jml_panen_ekor']); ?></td>
			<td class="text-right" title="BERAT BADAN"><?php echo angkaDecimal($value['jml_panen_kg']); ?></td>
			<td class="text-right" title="BB RATA2"><?php echo angkaDecimal($value['bb']); ?></td>
			<td class="text-right" title="FCR"><?php echo angkaDecimal($value['fcr']); ?></td>
			<td class="text-right" title="DEPLESI"><?php echo angkaDecimal($value['deplesi']); ?></td>
			<td class="text-right" title="RATA2 UMUR"><?php echo angkaDecimal($value['rata_umur']); ?></td>
			<td class="text-right" title="ADG"><?php echo angkaDecimal($value['adg']); ?></td>
			<td class="text-right" title="IP"><?php echo angkaDecimal($value['ip']); ?></td>
			<td class="text-right" title="SELISIH BUDIDAYA"><?php echo $fmt($value['selisih_budidaya']); ?></td>
			<td class="text-right" title="BONUS PASAR"><?php echo $fmt($value['bonus_pasar']); ?></td>
			<td class="text-right" title="BONUS KEMATIAN"><?php echo $fmt($value['bonus_kematian']); ?></td>
			<td class="text-right" title="BONUS FCR"><?php echo $fmt($value['bonus_insentif_fcr']); ?></td>
			<td class="text-right" title="INSENTIF LISTRIK"><?php echo $fmt($value['total_bonus_insentif_listrik']); ?></td>
			<td class="text-right" title="INSENTIF LPG"><?php echo $fmt($value['insentif_lpg']); ?></td>
			<td class="text-right" title="TOTAL PENDAPATAN PLASMA"><?php echo $fmt($value['total_pendapatan_plasma']); ?></td>
			<td class="text-right" title="TOTAL PENDAPATAN INTI"><?php echo $fmt($value['total_pendapatan_inti']); ?></td>
		</tr>
		<?php
			// Baris SUB TOTAL per unit - tampil begitu baris terakhir milik unit ini selesai (data terurut per unit)
			$is_last_row_of_unit = ($key == $data_count - 1) || ($data[$key + 1]['unit'] !== $value['unit']);
			if ( $is_last_row_of_unit ) {
				$render_total_row('SUB TOTAL '.$value['unit'], $unit_totals[$value['unit']], $unit_counts[$value['unit']]);
			}
		?>
	<?php endforeach ?>
	<?php $render_total_row('GRAND TOTAL', $total, $total_count); ?>
<?php else: ?>
	<tr>
		<td colspan="21" class="text-center">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>
