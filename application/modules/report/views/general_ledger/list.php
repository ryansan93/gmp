<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p data">
            <td class="text-left"><?php echo strtoupper($value['no_coa']); ?></td>
            <td class="text-left"><?php echo strtoupper($value['nama_coa']); ?></td>
            <td class="text-left"><?php echo strtoupper($value['lap']); ?></td>
            <td class="text-left"><?php echo strtoupper($value['coa_pos']); ?></td>
            <td class="text-right"><?php echo ($value['saldo_awal_debet'] >= 0) ? angkaDecimal($value['saldo_awal_debet']) : '('.angkaDecimal(abs($value['saldo_awal_debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['saldo_awal_kredit'] >= 0) ? angkaDecimal($value['saldo_awal_kredit']) : '('.angkaDecimal(abs($value['saldo_awal_kredit'])).')'; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['debet']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['kredit']); ?></td>
            <!-- <td class="text-right"><?php echo angkaDecimal(0); ?></td>
            <td class="text-right"><?php echo angkaDecimal(0); ?></td> -->
            <td class="text-right"><?php echo ($value['saldo_akhir_debet'] >= 0) ? angkaDecimal($value['saldo_akhir_debet']) : '('.angkaDecimal(abs($value['saldo_akhir_debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['saldo_akhir_kredit'] >= 0) ? angkaDecimal($value['saldo_akhir_kredit']) : '('.angkaDecimal(abs($value['saldo_akhir_kredit'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['lr_debet'] >= 0) ? angkaDecimal($value['lr_debet']) : '('.angkaDecimal(abs($value['lr_debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['lr_kredit'] >= 0) ? angkaDecimal($value['lr_kredit']) : angkaDecimal(abs($value['lr_kredit'])); ?></td>
            <td class="text-right"><?php echo ($value['neraca_debet'] >= 0) ? angkaDecimal($value['neraca_debet']) : '('.angkaDecimal(abs($value['neraca_debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['neraca_kredit'] >= 0) ? angkaDecimal($value['neraca_kredit']) : angkaDecimal(abs($value['neraca_kredit'])); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak ditemukan.</td>
    </tr>
<?php } ?>