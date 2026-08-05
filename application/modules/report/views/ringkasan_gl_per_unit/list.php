<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p data">
            <td><?php echo strtoupper($value['unit']); ?></td>
            <td><?php echo strtoupper($value['nama_unit']); ?></td>
            <td class="text-right"><?php echo (round($value['saldo_awal'], 2) >= 0) ? angkaDecimal($value['saldo_awal']) : '('.angkaDecimal(abs($value['saldo_awal'])).')'; ?></td>
            <td class="text-right"><?php echo (round($value['debet'], 2) >= 0) ? angkaDecimal($value['debet']) : '('.angkaDecimal(abs($value['debet'])).')'; ?></td>
            <td class="text-right"><?php echo (round($value['kredit'], 2) >= 0) ? angkaDecimal($value['kredit']) : '('.angkaDecimal(abs($value['kredit'])).')'; ?></td>
            <td class="text-right"><?php echo (round($value['saldo_akhir'], 2) >= 0) ? angkaDecimal($value['saldo_akhir']) : '('.angkaDecimal(abs($value['saldo_akhir'])).')'; ?></td>
        </tr>
    <?php } ?>
    <tr class="kuning">
        <td colspan="2"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo (round($total['saldo_awal'], 2) >= 0) ? angkaDecimal($total['saldo_awal']) : '('.angkaDecimal(abs($total['saldo_awal'])).')'; ?></b></td>
        <td class="text-right"><b><?php echo (round($total['debet'], 2) >= 0) ? angkaDecimal($total['debet']) : '('.angkaDecimal(abs($total['debet'])).')'; ?></b></td>
        <td class="text-right"><b><?php echo (round($total['kredit'], 2) >= 0) ? angkaDecimal($total['kredit']) : '('.angkaDecimal(abs($total['kredit'])).')'; ?></b></td>
        <td class="text-right"><b><?php echo (round($total['saldo_akhir'], 2) >= 0) ? angkaDecimal($total['saldo_akhir']) : '('.angkaDecimal(abs($total['saldo_akhir'])).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>