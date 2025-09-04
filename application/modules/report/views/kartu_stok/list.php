<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_gudang = null; 
        $kode_barang = null; 
        $idx_gudang = 0;
        $idx_barang = 0;
        $saldo = 0;

        $tot_debet_gdg = 0;
        $tot_kredit_gdg = 0;
        $tot_debet_brg = 0;
        $tot_kredit_brg = 0;

        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $kode_barang <> $value['kode_barang'] ) { ?>
            <tr class="abu">
                <td colspan="6">
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
                <td class="col-xs-1"><b>Tanggal</b></td>
                <td class="col-xs-1"><b>Jenis Transaksi</b></td>
                <td class="col-xs-4"><b>Kode Transaksi</b></td>
                <td class="col-xs-2"><b>Debet</b></td>
                <td class="col-xs-2"><b>Kredit</b></td>
                <td class="col-xs-2"><b>Saldo</b></td>
            </tr>
            <?php 
                $idx_barang = 0;
                $saldo = $value['saldo'];
                $kode_barang = $value['kode_barang'];
                
                $tot_debet_brg = 0;
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
            $kode_trans = $value['kode_trans'];
            $jenis_trans = $value['jenis_trans'];
            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($saldo+$debet)-$kredit;

            $tot_debet_brg += $debet;
            $tot_kredit_brg += $kredit;

            $tot_debet_gdg += $debet;
            $tot_kredit_gdg += $kredit;

            $gt_debet += $debet;
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
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <td><?php echo tglIndonesia($tanggal, '-', ' '); ?></td>
            <td><?php echo $kode_trans; ?></td>
            <td><?php echo $jenis_trans; ?></td>
            <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
            <td class="text-right"><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></td>
        </tr>
        <?php if ( !empty($kode_barang) && $kode_barang <> $data[$key+1]['kode_barang'] ) { ?>
            <?php // $gt_saldo += $saldo; ?>
            <tr>
                <td colspan="3"><b>Total Per Barang</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_brg); ?></b></td>
                <td class="text-right"><b><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="6"></td>
            </tr>

            <?php $saldo_cust += $saldo; ?>
        <?php } ?>
        <?php if ( !empty($kode_gudang) && $kode_gudang <> $data[$key+1]['kode_gudang'] ) { ?>
            <?php $gt_saldo += $saldo; ?>
            <tr class="biru">
                <td colspan="3"><b>Total Per Gudang</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_gdg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_gdg); ?></b></td>
                <td class="text-right"><b><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="6"></td>
            </tr>
        <?php } ?>
        <?php  
            $idx_barang++;
        ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="3"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo >= 0) ? angkaDecimal($gt_saldo) : '('.angkaDecimal(abs($gt_saldo)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>