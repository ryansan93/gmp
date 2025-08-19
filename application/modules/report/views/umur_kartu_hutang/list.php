<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $gt_saldo_awal = 0;
        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo_akhir = 0;
        $gt_current = 0;
        $gt_umur1 = 0;
        $gt_umur2 = 0;
        $gt_umur3 = 0;
        $gt_umur4 = 0;
    ?>

    <tr class="abu">
        <td style="width: 7%"><b>ID Supplier</b></td>
        <td style="width: 12%;"><b>Nama Supplier</b></td>
        <td style="width: 7%"><b>Plafon (Juta)</b></td>
        <td style="width: 7%"><b>JaTem (Hari)</b></td>
        <td style="width: 7%"><b>Saldo Awal</b></td>
        <td style="width: 7%"><b>Debet</b></td>
        <td style="width: 7%"><b>Kredit</b></td>
        <td style="width: 7%"><b>Saldo Akhir</b></td>
        <td style="width: 7%"><b>Current</b></td>
        <td style="width: 7%"><b>Umur 1-30 Hari</b></td>
        <td style="width: 7%"><b>Umur 31-60 Hari</b></td>
        <td style="width: 7%"><b>Umur 61-90 Hari</b></td>
        <td style="width: 7%"><b>Umur > 90 Hari</b></td>
    </tr>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['supplier']); ?></td>
            <td><?php echo strtoupper($value['nama_supplier']); ?></td>
            <td></td>
            <td></td>
            <td class="text-right"><?php echo ($value['saldo_awal'] >= 0) ? angkaDecimal($value['saldo_awal']) : '('.angkaDecimal(abs($value['saldo_awal'])).')'; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['debet']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['kredit']); ?></td>
            <td class="text-right"><?php echo ($value['saldo_akhir'] >= 0) ? angkaDecimal($value['saldo_akhir']) : '('.angkaDecimal(abs($value['saldo_akhir'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['_current'] >= 0) ? angkaDecimal($value['_current']) : '('.angkaDecimal(abs($value['_current'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['umur1'] >= 0) ? angkaDecimal($value['umur1']) : '('.angkaDecimal(abs($value['umur1'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['umur2'] >= 0) ? angkaDecimal($value['umur2']) : '('.angkaDecimal(abs($value['umur2'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['umur3'] >= 0) ? angkaDecimal($value['umur3']) : '('.angkaDecimal(abs($value['umur3'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['umur4'] >= 0) ? angkaDecimal($value['umur4']) : '('.angkaDecimal(abs($value['umur4'])).')'; ?></td>
        </tr>

        <?php 
            $gt_saldo_awal += $value['saldo_awal'];
            $gt_debet += $value['debet'];
            $gt_kredit += $value['kredit'];
            $gt_saldo_akhir += $value['saldo_akhir'];
            $gt_current += $value['_current'];
            $gt_umur1 += $value['umur1'];
            $gt_umur2 += $value['umur2'];
            $gt_umur3 += $value['umur3'];
            $gt_umur4 += $value['umur4'];
        ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="4"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_awal >= 0) ? angkaDecimal($gt_saldo_awal) : '('.angkaDecimal(abs($gt_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_akhir >= 0) ? angkaDecimal($gt_saldo_akhir) : '('.angkaDecimal(abs($gt_saldo_akhir)).')'; ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_current); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_umur1); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_umur2); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_umur3); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_umur4); ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td>Data tidak ditemukan.</td>
    </tr>
<?php } ?>