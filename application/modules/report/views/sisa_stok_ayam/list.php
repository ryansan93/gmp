<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php 
        $idx = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $idx == 0 ) { ?>
            <tr>
				<th class="col-xs-1 text-center">NOREG</th>
				<th class="col-xs-1 text-center">UNIT</th>
				<th class="col-xs-1 text-center">KDG</th>
				<th class="col-xs-1 text-center">TGL CHICK IN</th>
				<th class="col-xs-1 text-center">TGL DATANG PPL</th>
				<th class="col-xs-1 text-center">UMUR</th>
				<th class="col-xs-1 text-center">POPULASI</th>
				<th class="col-xs-1 text-center">EKOR MATI REAL</th>
				<th class="col-xs-1 text-center">SISA EKOR LHK</th>
				<th class="col-xs-1 text-center">BW RATA LHK</th>
				<th class="col-xs-1 text-center">TONASE LHK</th>
				<th class="col-xs-1 text-center">TGL PANEN TERAKHIR</th>
				<th class="col-xs-1 text-center">EKOR JUAL</th>
				<th class="col-xs-1 text-center">TONASE JUAL</th>
				<th class="col-xs-1 text-center">SISA EKOR</th>
				<th class="col-xs-1 text-center">SISA TONASE</th>
				<th class="col-xs-1 text-center">TGL TUTUP SIKLUS</th>
			</tr>
        <?php } ?>

        <tr>
            <td><?php echo $value['noreg']; ?></td>
            <td><?php echo $value['kode']; ?></td>
            <td><?php echo $value['noreg']; ?></td>
            <td class="text-center"><?php echo tglIndonesia(substr($value['tgl_chick_in'], 0, 10), '-', ' ').' '.substr($value['tgl_chick_in'], 11, 5); ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tgl_datang_ppl'], '-', ' '); ?></td>
            <td><?php echo $value['umur']; ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['populasi']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['ekor_mati_real']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['sisa_ekor_lhk']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['bw_rata_lhk']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['tonase_lhk']); ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tgl_panen_terakhir'], '-', ' '); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['ekor_jual']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['tonase_jual']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['sisa_ekor']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['sisa_tonase']); ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tgl_tutup_siklus'], '-', ' '); ?></td>
        </tr>

        <?php $idx++; ?>
    <?php } ?>
<?php else: ?>
	<tr>
		<td colspan="16">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>