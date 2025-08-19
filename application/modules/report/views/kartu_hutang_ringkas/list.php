<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $gt_saldo_awal = 0;
        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo_akhir = 0;
    ?>

    <tr class="abu">
        <td class="col-xs-1"><b>ID Supplier</b></td>
        <td class="col-xs-3"><b>Nama Supplier</b></td>
        <td class="col-xs-2"><b>Saldo Awal</b></td>
        <td class="col-xs-2"><b>Debet</b></td>
        <td class="col-xs-2"><b>Kredit</b></td>
        <td class="col-xs-2"><b>Saldo Akhir</b></td>
    </tr>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['supplier']); ?></td>
            <td><?php echo strtoupper($value['nama_supplier']); ?></td>
            <td class="text-right"><?php echo ($value['saldo_awal'] >= 0) ? angkaDecimal($value['saldo_awal']) : '('.angkaDecimal(abs($value['saldo_awal'])).')'; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['debet']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['kredit']); ?></td>
            <td class="text-right"><?php echo ($value['saldo_akhir'] >= 0) ? angkaDecimal($value['saldo_akhir']) : '('.angkaDecimal(abs($value['saldo_akhir'])).')'; ?></td>
        </tr>

        <?php 
            $gt_saldo_awal += $value['saldo_awal'];
            $gt_debet += $value['debet'];
            $gt_kredit += $value['kredit'];
            $gt_saldo_akhir += $value['saldo_akhir'];
        ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="2"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_awal >= 0) ? angkaDecimal($gt_saldo_awal) : '('.angkaDecimal(abs($gt_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_akhir >= 0) ? angkaDecimal($gt_saldo_akhir) : '('.angkaDecimal(abs($gt_saldo_akhir)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td>Data tidak ditemukan.</td>
    </tr>
<?php } ?>