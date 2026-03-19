<?php if (!empty($data)) { ?>
    <?php
        $tot_saldo_awal = 0;
        $tot_debet = 0;
        $tot_kredit = 0;
        $tot_saldo_akhir = 0;
    ?>

    <?php foreach ($data as $k => $val) : ?>
        <tr>
            <td class="text-left"><?php echo strtoupper( $val['unit'] ); ?></td>
            <td class="text-left"><?php echo strtoupper( $val['noreg'] ); ?></td>
            <td class="text-left"><?php echo strtoupper( $val['nama_mitra'] ); ?></td>
            <td class="text-center"><?php echo tglIndonesia( $val['tanggal'], '-', ' ' ); ?></td>
            <td class="text-right"><?php echo $val['umur']; ?></td>
            <td class="text-right col-xs-1"><?php echo angkaRibuan($val['jml_lhk'] / 50); ?></td>
            <td class="text-right col-xs-1"><?php echo angkaRibuan($val['jml_stok'] / 50); ?></td>
            <td class="text-right col-xs-1"><?php echo angkaRibuan($val['selisih'] / 50); ?></td>
            <td class="text-right col-xs-1"><?php echo angkaDecimal($val['total_stok']); ?></td>
            <td class="text-right col-xs-1"><?php echo angkaDecimal($val['nominal_jurnal']); ?></td>
            <td class="text-right col-xs-1"><?php echo angkaDecimal($val['selisih_jurnal']); ?></td>
        </tr>
    <?php endforeach; ?>

    <!-- <tr>
        <td class="text-right" colspan="5"><b>TOTAL</b></td>
        <td class="text-right"><b><?php echo ($tot_saldo_awal >= 0) ? angkaDecimal($tot_saldo_awal) : '('.angkaDecimal(abs($tot_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></b></td>
    </tr> -->
<?php } else { ?>
    <tr>
        <td colspan="11">Data tidak ditemukan.</td>
    </tr>
<?php } ?>