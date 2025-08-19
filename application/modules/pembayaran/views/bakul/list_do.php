<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data">
			<td class="text-center" style="vertical-align: top;"><?php echo tglIndonesia($v_data['tgl_panen'], '-', ' '); ?></td>
			<td class="text-left" style="vertical-align: top;"><?php echo strtoupper($v_data['nama']).'<br>'.'KDG : '.$v_data['kandang']; ?></td> 
			<td class="text-center no_sj" style="vertical-align: top;"><?php echo $v_data['no_sj']; ?></td>
			<td class="text-center no_inv" style="vertical-align: top;"><?php echo $v_data['no_inv']; ?></td>
			<td class="text-right" style="vertical-align: top;"><?php echo angkaRibuan($v_data['ekor']); ?></td>
			<td class="text-right" style="vertical-align: top;"><?php echo angkaDecimal($v_data['kg']); ?></td>
			<td class="text-right cn" style="vertical-align: top;"><?php echo (isset($v_data['cn']) && !empty($v_data['cn'])) ? angkaDecimal($v_data['cn']) : 0; ?></td>
			<td class="text-right dn" style="vertical-align: top;"><?php echo (isset($v_data['dn']) && !empty($v_data['dn'])) ? angkaDecimal($v_data['dn']) : 0; ?></td>
			<td class="text-right nilai" style="vertical-align: top;"><?php echo (isset($v_data['nilai']) && !empty($v_data['nilai'])) ? angkaDecimal($v_data['nilai']) : 0; ?></td>
			<td class="text-right tagihan" style="vertical-align: top;"><?php echo (isset($v_data['tagihan']) && !empty($v_data['tagihan'])) ? angkaDecimal($v_data['tagihan']) : 0; ?></td>
			<td class="text-right jml_bayar" style="vertical-align: top;"><?php echo (isset($v_data['jml_bayar']) && !empty($v_data['jml_bayar'])) ? angkaDecimal($v_data['jml_bayar']) : 0; ?></td>
			<td class="text-right penyesuaian">
				<div class="col-lg-12 no-padding">
					<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" value="0" maxlength="14" onblur="bakul.hit_total_uang()">
				</div>
				<div class="col-lg-12 no-padding"></div>
				<div class="col-lg-12 no-padding">
					<textarea class="form-control ket_penyesuaian" placeholder="Keterangan"></textarea>
				</div>
			</td>
			<td class="text-right sisa_tagihan" style="vertical-align: top;"><?php echo (isset($v_data['sisa_tagihan']) && !empty($v_data['sisa_tagihan'])) ? angkaDecimal($v_data['sisa_tagihan']) : 0; ?></td>
			<td class="text-center status" style="vertical-align: top;"></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="14">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>