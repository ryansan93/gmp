<?php if (!empty($data)) { ?>

<?php
$tot_saldo_awal = 0;
$tot_debet = 0;
$tot_kredit = 0;
$tot_saldo_akhir = 0;
?>

<?php foreach ($data as $coa => $noregs) : ?>
    <?php foreach ($noregs as $noreg => $rows) : ?>
        <?php foreach ($rows as $value) : ?>
            <?php if (!is_array($value)) continue; ?>

            <?php
            $saldo_awal = isset($value['saldo_awal']) ? (float)$value['saldo_awal'] : 0;
            $debet = isset($value['debet']) ? (float)$value['debet'] : 0;
            $kredit = isset($value['kredit']) ? (float)$value['kredit'] : 0;
            $saldo_akhir = $saldo_awal + $debet + $kredit;

            $tot_saldo_awal += $saldo_awal;
            $tot_debet += $debet;
            $tot_kredit += $kredit;
            $tot_saldo_akhir += $saldo_akhir;
            ?>
            
            <tr>
                <td class="text-left"><?php echo $value['no_coa'] ?? ''; ?></td>
                <td class="text-left"><?php echo $value['unit'] ?? ''; ?></td>
                <td class="text-left"><?php echo $value['nama_coa'] ?? ''; ?></td>
                <td class="text-right"><?php echo $value['noreg'] ?? ''; ?></td>
                <td class="text-center"><?php echo $value['nama_mitra'] ?? ''; ?></td>
                <td class="text-right"><?php echo angkaDecimal($saldo_awal); ?></td>
                <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
                <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
                <td class="text-right"><?php echo angkaDecimal($saldo_akhir); ?></td>
            </tr>

        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endforeach; ?>

<tr>
    <td class="text-right" colspan="5"><b>TOTAL</b></td>
    <td class="text-right"><b><?php echo ($tot_saldo_awal >= 0) ? angkaDecimal($tot_saldo_awal) : '('.angkaDecimal(abs($tot_saldo_awal)).')'; ?></b></td>
    <td class="text-right"><b><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></b></td>
    <td class="text-right"><b><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></b></td>
    <td class="text-right"><b><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></b></td>
</tr>

<?php } else { ?>

<tr>
    <td colspan="9">Data tidak ditemukan.</td>
</tr>

<?php } ?>