<?php if (!empty($data)) { ?>

<?php
$tot_saldo_awal = 0;
$tot_debet = 0;
$tot_kredit = 0;
$tot_saldo_akhir = 0;
?>

<?php foreach ($data as $value) { 

    $saldo_awal = $value['saldo_awal'];
    $debet = $value['debet'];
    $kredit = $value['kredit'];

    $saldo_akhir = $saldo_awal + $debet + $kredit;
?>

<tr class="cursor-p data"
    onclick="gl.formDetail(this)"
    data-periode="<?php echo $periode; ?>"
    data-coa="<?php echo $value['no_coa']; ?>"
    data-unit="<?php echo $value['unit']; ?>"
    title="Klik untuk melihat detail">

    <td class="text-left no_coa"><?php echo strtoupper($value['no_coa']); ?></td>
    <td class="text-left unit_tr"><?php echo strtoupper($value['unit']); ?></td>
    <td class="text-left nama_coa"><?php echo strtoupper($value['nama_coa']); ?></td>

    <td class="text-right">
        <?php echo ($saldo_awal >= 0) ? angkaDecimal($saldo_awal) : '('.angkaDecimal(abs($saldo_awal)).')'; ?>
    </td>

    <td class="text-right">
        <?php echo ($debet >= 0) ? angkaDecimal($debet) : '('.angkaDecimal(abs($debet)).')'; ?>
    </td>

    <td class="text-right">
        <?php echo ($kredit >= 0) ? angkaDecimal($kredit) : '('.angkaDecimal(abs($kredit)).')'; ?>
    </td>

    <td class="text-right">
        <?php echo ($saldo_akhir >= 0) ? angkaDecimal($saldo_akhir) : '('.angkaDecimal(abs($saldo_akhir)).')'; ?>
    </td>

</tr>

<?php
$tot_saldo_awal += $saldo_awal;
$tot_debet += $debet;
$tot_kredit += $kredit;
$tot_saldo_akhir += $saldo_akhir;
?>

<?php } ?>

<tr>
    <td class="text-right" colspan="3"><b>TOTAL</b></td>

    <td class="text-right"><b><?php echo angkaDecimal($tot_saldo_awal); ?></b></td>
    <td class="text-right"><b><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></b></td>
    <td class="text-right"><b><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></b></td>
    <td class="text-right"><b><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></b></td>
</tr>

<?php } else { ?>

<tr>
    <td colspan="7">Data tidak ditemukan.</td>
</tr>

<?php } ?>