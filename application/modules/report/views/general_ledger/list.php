<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php
        $tot_saldo_awal = 0;
        $tot_debet = 0;
        $tot_kredit = 0;
        $tot_saldo_akhir = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p data" onclick="gl.formDetail(this)" data-periode="<?php echo $periode; ?>" title="Klik untuk melihat detail" >
            <td class="text-left no_coa"><?php echo strtoupper($value['no_coa']); ?></td>
            <td class="text-left unit"><?php echo strtoupper($value['unit']); ?></td>
            <td class="text-left nama_coa"><?php echo strtoupper($value['nama_coa']); ?></td>
            <td class="text-right"><?php echo ($value['saldo_awal'] >= 0) ? angkaDecimal($value['saldo_awal']) : '('.angkaDecimal(abs($value['saldo_awal'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['debet'] >= 0) ? angkaDecimal($value['debet']) : '('.angkaDecimal(abs($value['debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['kredit'] >= 0) ? angkaDecimal($value['kredit']) : '('.angkaDecimal(abs($value['kredit'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['saldo_akhir'] >= 0) ? angkaDecimal($value['saldo_akhir']) : '('.angkaDecimal(abs($value['saldo_akhir'])).')'; ?></td>
        </tr>
        <?php
            $tot_saldo_awal += $value['saldo_awal'];
            $tot_debet += $value['debet'];
            $tot_kredit += $value['kredit'];
            $tot_saldo_akhir += $value['saldo_akhir'];
        ?>
    <?php } ?>
    <tr class="">
        <td class="text-right" colspan="3"><b><?php echo strtoupper('TOTAL'); ?></b></td>
        <td class="text-right"><b><?php echo ($tot_saldo_awal >= 0) ? angkaDecimal($tot_saldo_awal) : '('.angkaDecimal(abs($tot_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak ditemukan.</td>
    </tr>
<?php } ?>