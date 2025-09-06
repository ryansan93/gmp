<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_gudang = null;
        $idx_gudang = 0;

        $saldo_awal = 0;
        $debet = 0;
        $kredit = 0;
        $saldo_akhir = 0;

        $jml_saldo_awal = 0;
        $jml_debet = 0;
        $jml_kredit = 0;
        $jml_saldo_akhir = 0;

        $tot_jml_saldo_awal_gdg = 0;
        $tot_saldo_awal_gdg = 0;
        $tot_jml_debet_gdg = 0;
        $tot_debet_gdg = 0;
        $tot_jml_kredit_gdg = 0;
        $tot_kredit_gdg = 0;
        $tot_jml_saldo_akhir_gdg = 0;
        $tot_saldo_akhir_gdg = 0;

        $gt_jml_saldo_awal = 0;
        $gt_saldo_awal = 0;
        $gt_jml_debet = 0;
        $gt_debet = 0;
        $gt_jml_kredit = 0;
        $gt_kredit = 0;
        $gt_jml_saldo_akhir = 0;
        $gt_saldo_akhir = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $kode_gudang <> $value['kode_gudang'] ) { ?>
            <tr class="abu">
                <td colspan="11">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Gudang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['nama_gudang']; ?></label></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Kode Barang</b></td>
                <td class="col-xs-2 text-center" rowspan="2" style="vertical-align: middle;"><b>Nama Barang</b></td>
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Harga</b></td>
                <td class="text-center" colspan="2"><b>Saldo Awal</b></td>
                <td class="text-center" colspan="2"><b>Debet</b></td>
                <td class="text-center" colspan="2"><b>Kredit</b></td>
                <td class="text-center" colspan="2"><b>Saldo Akhir</b></td>
            </tr>
            <tr>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
            </tr>
            <?php 
                $idx_gudang = 0;
                $kode_gudang = $value['kode_gudang'];
                
                $tot_jml_saldo_awal_gdg = 0;
                $tot_saldo_awal_gdg = 0;
                $tot_jml_debet_gdg = 0;
                $tot_debet_gdg = 0;
                $tot_jml_kredit_gdg = 0;
                $tot_kredit_gdg = 0;
                $tot_jml_saldo_akhir_gdg = 0;
                $tot_saldo_akhir_gdg = 0;
            ?>
        <?php } ?>

        <?php 
            $saldo_awal = $value['saldo_awal'];
            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo_akhir = $value['saldo_akhir'];

            $jml_saldo_awal = $value['jml_saldo_awal'];
            $jml_debet = $value['jml_debet'];
            $jml_kredit = $value['jml_kredit'];
            $jml_saldo_akhir = $value['jml_saldo_akhir'];

            $tot_jml_saldo_awal_gdg += $jml_saldo_awal;
            $tot_saldo_awal_gdg += $saldo_awal;
            $tot_jml_debet_gdg += $jml_debet;
            $tot_debet_gdg += $debet;
            $tot_jml_kredit_gdg += $jml_kredit;
            $tot_kredit_gdg += $kredit;
            $tot_jml_saldo_akhir_gdg += $jml_saldo_akhir;
            $tot_saldo_akhir_gdg += $saldo_akhir;

            $gt_jml_saldo_awal += $jml_saldo_awal;
            $gt_saldo_awal += $saldo_awal;
            $gt_jml_debet += $jml_debet;
            $gt_debet += $debet;
            $gt_jml_kredit += $jml_kredit;
            $gt_kredit += $kredit;
            $gt_jml_saldo_akhir += $jml_saldo_akhir;
            $gt_saldo_akhir += $saldo_akhir;
        ?>
        <tr>
            <td><?php echo strtoupper($value['kode_barang']); ?></td>
            <td><?php echo strtoupper($value['nama_barang']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['hrg_beli']); ?></td>
            <td class="text-right"><?php echo ($jml_saldo_awal >= 0) ? angkaDecimal($jml_saldo_awal) : '('.angkaDecimal(abs($jml_saldo_awal)).')'; ?></td>
            <td class="text-right"><?php echo ($saldo_awal >= 0) ? angkaDecimal($saldo_awal) : '('.angkaDecimal(abs($saldo_awal)).')'; ?></td>
            <td class="text-right"><?php echo angkaDecimal($jml_debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($jml_kredit); ?></td>
            <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
            <td class="text-right"><?php echo ($jml_saldo_akhir >= 0) ? angkaDecimal($jml_saldo_akhir) : '('.angkaDecimal(abs($jml_saldo_akhir)).')'; ?></td>
            <td class="text-right"><?php echo ($saldo_akhir >= 0) ? angkaDecimal($saldo_akhir) : '('.angkaDecimal(abs($saldo_akhir)).')'; ?></td>
        </tr>
        <?php if ( !empty($kode_gudang) && $kode_gudang <> $data[$key+1]['kode_gudang'] ) { ?>
            <tr class="biru">
                <td colspan="3"><b>Total Per Gudang</b></td>
                <td class="text-right"><b><?php echo ($tot_jml_saldo_awal_gdg >= 0) ? angkaDecimal($tot_jml_saldo_awal_gdg) : '('.angkaDecimal(abs($tot_jml_saldo_awal_gdg)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_saldo_awal_gdg >= 0) ? angkaDecimal($tot_saldo_awal_gdg) : '('.angkaDecimal(abs($tot_saldo_awal_gdg)).')'; ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_gdg); ?></b></td>
                <td class="text-right"><b><?php echo ($tot_jml_saldo_akhir_gdg >= 0) ? angkaDecimal($tot_jml_saldo_akhir_gdg) : '('.angkaDecimal(abs($tot_jml_saldo_akhir_gdg)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_saldo_akhir_gdg >= 0) ? angkaDecimal($tot_saldo_akhir_gdg) : '('.angkaDecimal(abs($tot_saldo_akhir_gdg)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="11"></td>
            </tr>
        <?php } ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="3"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo ($gt_jml_saldo_awal >= 0) ? angkaDecimal($gt_jml_saldo_awal) : '('.angkaDecimal(abs($gt_jml_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_awal >= 0) ? angkaDecimal($gt_saldo_awal) : '('.angkaDecimal(abs($gt_saldo_awal)).')'; ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_kredit); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_jml_saldo_akhir >= 0) ? angkaDecimal($gt_jml_saldo_akhir) : '('.angkaDecimal(abs($gt_jml_saldo_akhir)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo_akhir >= 0) ? angkaDecimal($gt_saldo_akhir) : '('.angkaDecimal(abs($gt_saldo_akhir)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>