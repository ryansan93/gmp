<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_supplier = null; 
        $no_inv = null; 
        $idx_supplier = 0;
        $idx_inv = 0;
        $saldo = 0;
        $saldo_supl = 0;

        $tot_debet_inv = 0;
        $tot_kredit_inv = 0;
        $tot_debet_supl = 0;
        $tot_kredit_supl = 0;

        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $no_inv <> $value['no_inv'] ) { ?>
            <tr class="abu">
                <td colspan="5">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">ID Supplier</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['supplier']; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Supplier</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo strtoupper($value['nama_supplier']); ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">No. Invoice</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['no_inv']; ?></label></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="col-xs-1"><b>Tanggal</b></td>
                <td class="col-xs-5"><b>Jenis Transaksi</b></td>
                <td class="col-xs-2"><b>Debet</b></td>
                <td class="col-xs-2"><b>Kredit</b></td>
                <td class="col-xs-2"><b>Saldo</b></td>
            </tr>
            <?php 
                $idx_inv = 0;
                $saldo = $value['saldo'];
                $no_inv = $value['no_inv'];

                $tot_debet_inv = 0;
                $tot_kredit_inv = 0;
            ?>
        <?php } ?>

        <?php if ( $kode_supplier <> $value['supplier'] ) { ?>
            <?php 
                $idx_supplier = 0;
                // $saldo_supl = $value['saldo'];
                $kode_supplier = $value['supplier'];

                $tot_debet_supl = 0;
                $tot_kredit_supl = 0;
            ?>
        <?php } ?>
        <?php 
            $tanggal = $value['tanggal'];
            $ket = $value['jenis_trans'];
            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($saldo+$debet)-$kredit;
            // $saldo_supl = ($saldo_supl+$debet)-$kredit;

            $tot_debet_inv += $debet;
            $tot_kredit_inv += $kredit;

            $tot_debet_supl += $debet;
            $tot_kredit_supl += $kredit;

            $gt_debet += $debet;
            $gt_kredit += $kredit;
        ?>
        <?php if ( $idx_inv == 0 ) { ?>
            <?php if ( $value['urut'] != 1 ) { ?>
                <tr>
                    <td><?php echo tglIndonesia(substr($tanggal, 0, 7).'-01', '-', ' '); ?></td>
                    <td><?php echo 'Saldo Awal'; ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <td><?php echo tglIndonesia($tanggal, '-', ' '); ?></td>
            <td><?php echo $ket; ?></td>
            <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
            <td class="text-right"><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></td>
        </tr>
        <?php if ( !empty($no_inv) && $no_inv <> $data[$key+1]['no_inv'] ) { ?>
            <?php // $gt_saldo += $saldo; ?>
            <tr>
                <td colspan="2"><b>Total Per Invoice</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_inv); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_inv); ?></b></td>
                <td class="text-right"><b><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="5"></td>
            </tr>

            <?php $saldo_supl += $saldo; ?>
        <?php } ?>
        <?php if ( !empty($kode_supplier) && $kode_supplier <> $data[$key+1]['supplier'] ) { ?>
            <?php $gt_saldo += $saldo_supl; ?>
            <tr class="biru">
                <td colspan="2"><b>Total Per Supplier</b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_supl); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_supl); ?></b></td>
                <td class="text-right"><b><?php echo ($saldo_supl >= 0) ? angkaDecimal($saldo_supl) : '('.angkaDecimal(abs($saldo_supl)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="5"></td>
            </tr>

            <?php $saldo_supl = 0; ?>
        <?php } ?>
        <?php  
            $idx_inv++;
        ?>
    <?php } ?>
    <tr class="kuning">
        <td colspan="2"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo >= 0) ? angkaDecimal($gt_saldo) : '('.angkaDecimal(abs($gt_saldo)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="5">Data tidak ditemukan.</td>
    </tr>
<?php } ?>