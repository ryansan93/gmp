<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_gudang = null; 
        $kode_barang = null; 
        $idx_gudang = 0;
        $idx_barang = 0;
        $saldo = 0;
        $jml_saldo = 0;

        $saldo_gdg = 0;
        $jml_saldo_gdg = 0;

        $tot_debet_gdg = 0;
        $tot_kredit_gdg = 0;
        $tot_jml_debet_brg = 0;
        $tot_debet_brg = 0;
        $tot_jml_kredit_brg = 0;
        $tot_kredit_brg = 0;

        $gt_jml_debet = 0;
        $gt_debet = 0;
        $gt_jml_kredit = 0;
        $gt_kredit = 0;
        $gt_jml_saldo = 0;
        $gt_saldo = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $kode_barang <> $value['kode_barang'] ) { ?>
            <tr class="abu">
                <td colspan="10">
                    <!-- <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">ID Gudang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['kode_barang']; ?></label></div>
                    </div> -->
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Gudang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['nama_gudang']; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Kode Barang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['kode_barang']; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Barang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['nama_barang']; ?></label></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Tanggal</b></td>
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Jenis Transaksi</b></td>
                <td class="col-xs-3 text-center" rowspan="2" style="vertical-align: middle;"><b>Kode Transaksi</b></td>
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Harga</b></td>
                <td class="text-center" colspan="2"><b>Debet</b></td>
                <td class="text-center" colspan="2"><b>Kredit</b></td>
                <td class="text-center" colspan="2"><b>Saldo</b></td>
            </tr>
            <tr>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
                <td class="col-xs-1 text-center"><b>Jumlah</b></td>
                <td class="col-xs-1 text-center"><b>Nilai</b></td>
            </tr>
            <?php 
                $idx_barang = 0;
                $saldo = 0;
                $jml_saldo = 0;
                $kode_barang = $value['kode_barang'];
                
                $tot_jml_debet_brg = 0;
                $tot_debet_brg = 0;
                $tot_jml_kredit_brg = 0;
                $tot_kredit_brg = 0;
            ?>
        <?php } ?>

        <?php if ( $kode_gudang <> $value['kode_gudang'] ) { ?>
            <?php 
                $idx_gudang = 0;
                // $saldo_gudang = $value['saldo'];
                $kode_gudang = $value['kode_gudang'];

                $tot_debet_gudang = 0;
                $tot_kredit_gudang = 0;
            ?>
        <?php } ?>

        <?php 
            $tanggal = $value['tanggal'];
            $jenis_trans = $value['jenis_trans'];
            $kode_trans = $value['kode_trans'];

            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($saldo+$debet)-$kredit;

            $jml_debet = $value['jml_debet'];
            $jml_kredit = $value['jml_kredit'];
            $jml_saldo = ($jml_saldo+$jml_debet)-$jml_kredit;

            $tot_jml_debet_brg += $jml_debet;
            $tot_debet_brg += $debet;
            $tot_jml_kredit_brg += $jml_kredit;
            $tot_kredit_brg += $kredit;

            $tot_jml_debet_gdg += $jml_debet;
            $tot_debet_gdg += $debet;
            $tot_jml_kredit_gdg += $jml_kredit;
            $tot_kredit_gdg += $kredit;

            $gt_jml_debet += $jml_debet;
            $gt_debet += $debet;
            $gt_jml_kredit += $jml_kredit;
            $gt_kredit += $kredit;
        ?>
        <?php if ( $idx_barang == 0 ) { ?>
            <?php if ( $value['urut'] != 1 ) { ?>
                <tr>
                    <td><?php echo tglIndonesia(substr($tanggal, 0, 7).'-01', '-', ' '); ?></td>
                    <td><?php echo 'Saldo Awal'; ?></td>
                    <td></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <td><?php echo tglIndonesia($tanggal, '-', ' '); ?></td>
            <td><?php echo $jenis_trans; ?></td>
            <td><?php echo $kode_trans; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['hrg_beli']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($jml_debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($jml_kredit); ?></td>
            <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
            <td class="text-right"><?php echo ($jml_saldo >= 0) ? angkaDecimal($jml_saldo) : '('.angkaDecimal(abs($jml_saldo)).')'; ?></td>
            <td class="text-right"><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></td>
        </tr>
        <?php if ( !empty($kode_barang) && $kode_barang <> $data[$key+1]['kode_barang'] ) { ?>
            <?php // $gt_saldo += $saldo; ?>
            <tr>
                <td colspan="4"><b>Total Per Barang</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_brg); ?></b></td>
                <td class="text-right"><b><?php echo ($jml_saldo >= 0) ? angkaDecimal($jml_saldo) : '('.angkaDecimal(abs($jml_saldo)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>

            <?php $saldo_gdg += $saldo; $jml_saldo_gdg += $jml_saldo;?>
        <?php } ?>
        <?php if ( !empty($kode_gudang) && $kode_gudang <> $data[$key+1]['kode_gudang'] ) { ?>
            <?php $gt_saldo += $saldo_gdg; $gt_jml_saldo += $jml_saldo_gdg; ?>
            <tr class="biru">
                <td colspan="4"><b>Total Per Gudang</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_gdg); ?></b></td>
                <td class="text-right"><b><?php echo ($jml_saldo_gdg >= 0) ? angkaDecimal($jml_saldo_gdg) : '('.angkaDecimal(abs($jml_saldo_gdg)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($saldo_gdg >= 0) ? angkaDecimal($saldo_gdg) : '('.angkaDecimal(abs($saldo_gdg)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>

            <?php $saldo_gdg = 0; $jml_saldo_gdg = 0;?>
        <?php } ?>
        <?php  
            $idx_barang++;
        ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="4"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_kredit); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_jml_saldo >= 0) ? angkaDecimal($gt_jml_saldo) : '('.angkaDecimal(abs($gt_jml_saldo)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo >= 0) ? angkaDecimal($gt_saldo) : '('.angkaDecimal(abs($gt_saldo)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>