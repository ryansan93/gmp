<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_unit = null; 
        $kode_noreg = null; 
        $kode_barang = null; 
        $idx_unit = 0;
        $idx_noreg = 0;
        $idx_barang = 0;
        
        $saldo_unit = 0;
        $jml_saldo_unit = 0;
        $saldo_noreg = 0;
        $jml_saldo_noreg = 0;
        $saldo_barang = 0;
        $jml_saldo_barang = 0;

        $tot_jml_debet_unit = 0;
        $tot_debet_unit = 0;
        $tot_jml_kredit_unit = 0;
        $tot_kredit_unit = 0;
        $tot_jml_debet_noreg = 0;
        $tot_debet_noreg = 0;
        $tot_jml_kredit_noreg = 0;
        $tot_kredit_noreg = 0;
        $tot_jml_debet_barang = 0;
        $tot_debet_barang = 0;
        $tot_jml_kredit_barang = 0;
        $tot_kredit_barang = 0;

        $gt_jml_debet = 0;
        $gt_debet = 0;
        $gt_jml_kredit = 0;
        $gt_kredit = 0;
        $gt_jml_saldo = 0;
        $gt_saldo = 0;
    ?>
    <?php $key_plasma = null; $key_barang = null; ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $key_plasma <> $value['unit'].'-'.$value['noreg'] ) { ?>
            <?php $key_plasma = $value['unit'].'-'.$value['noreg']; ?>
            <tr class="abu">
                <td colspan="10">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Unit</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['unit']; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Plasma</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['noreg'].' | '.$value['nama_plasma'].' (KDG:'.$value['kandang'].')'; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Status RHPP</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['status']; ?></label></div>
                    </div>
                    <!-- <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Kode Barang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['kode_barang']; ?></label></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Nama Barang</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['nama_barang']; ?></label></div>
                    </div> -->
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
        <?php } ?>

        <?php 
            $tanggal = ($value['tanggal'] < '2025-01-01') ? null : $value['tanggal'];
            $jenis_trans = $value['jenis_trans'];
            $kode_trans = $value['kode_trans'];

            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($saldo+$debet)-$kredit;

            $jml_debet = $value['jml_debet'];
            $jml_kredit = $value['jml_kredit'];
            $jml_saldo = ($jml_saldo+$jml_debet)-$jml_kredit;

            $gt_jml_debet += $jml_debet;
            $gt_debet += $debet;
            $gt_jml_kredit += $jml_kredit;
            $gt_kredit += $kredit;
        ?>
        
        <?php if ( $key_barang <> $value['noreg'].'-'.$value['kode_barang'] ) { ?>
            <?php $key_barang = $value['noreg'].'-'.$value['kode_barang']; ?>
            <tr>
                <td colspan="10"><b><?php echo $value['kode_barang'].' | '.$value['nama_barang']; ?></b></td>
            </tr>
        <?php } ?>

        <?php
            $saldo_unit = ($saldo_unit+$debet)-$kredit;
            $jml_saldo_unit = ($jml_saldo_unit+$jml_debet)-$jml_kredit;
            $tot_jml_debet_unit += $jml_debet;
            $tot_debet_unit += $debet;
            $tot_jml_kredit_unit += $jml_kredit;
            $tot_kredit_unit += $kredit;

            $saldo_noreg = ($saldo_noreg+$debet)-$kredit;
            $jml_saldo_noreg = ($jml_saldo_noreg+$jml_debet)-$jml_kredit;
            $tot_jml_debet_noreg += $jml_debet;
            $tot_debet_noreg += $debet;
            $tot_jml_kredit_noreg += $jml_kredit;
            $tot_kredit_noreg += $kredit;
            
            $saldo_barang = ($saldo_barang+$debet)-$kredit;
            $jml_saldo_barang = ($jml_saldo_barang+$jml_debet)-$jml_kredit;
            $tot_jml_debet_brg += $jml_debet;
            $tot_debet_brg += $debet;
            $tot_jml_kredit_brg += $jml_kredit;
            $tot_kredit_brg += $kredit;
        ?>

        <?php if ( $idx_barang == 0 ) { ?>
            <?php if ( $value['urut'] != 1 ) { ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?php echo 'Saldo Awal'; ?></td>
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
            <td class="text-right"><?php echo ($jml_saldo_barang >= 0) ? angkaDecimal($jml_saldo_barang) : '('.angkaDecimal(abs($jml_saldo_barang)).')'; ?></td>
            <td class="text-right"><?php echo ($saldo_barang >= 0) ? angkaDecimal($saldo_barang) : '('.angkaDecimal(abs($saldo_barang)).')'; ?></td>
        </tr>
        <?php if ( $key_barang != $data[$key+1]['noreg'].'-'.$data[$key+1]['kode_barang'] ) { ?>
            <tr>
                <td colspan="4"><b>Total Per Barang <?php echo $value['kode_barang']; ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_debet_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_brg); ?></b></td>
                <td class="text-right"><b><?php echo angkaDecimal($tot_kredit_brg); ?></b></td>
                <td class="text-right"><b><?php echo ($jml_saldo_barang >= 0) ? angkaDecimal($jml_saldo_barang) : '('.angkaDecimal(abs($jml_saldo_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($saldo_barang >= 0) ? angkaDecimal($saldo_barang) : '('.angkaDecimal(abs($saldo_barang)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>
            <?php 
                $idx_barang = 0;

                $saldo_barang = 0;
                $jml_saldo_barang = 0;

                $tot_jml_debet_brg = 0;
                $tot_debet_brg = 0;
                $tot_jml_kredit_brg = 0;
                $tot_kredit_brg = 0;
            ?>
        <?php } ?>
        <?php if ( $key_plasma <> $data[$key+1]['unit'].'-'.$data[$key+1]['noreg'] ) { ?>
            <tr class="biru">
                <td colspan="4"><b>Total Per Noreg | <?php echo $value['noreg']; ?></b></td>
                <!-- <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_noreg); ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo angkaDecimal($tot_debet_noreg); ?></b></td>
                <!-- <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_noreg); ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo angkaDecimal($tot_kredit_noreg); ?></b></td>
                <!-- <td class="text-right"><b><?php echo ($jml_saldo_noreg >= 0) ? angkaDecimal($jml_saldo_noreg) : '('.angkaDecimal(abs($jml_saldo_noreg)).')'; ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo ($saldo_noreg >= 0) ? angkaDecimal($saldo_noreg) : '('.angkaDecimal(abs($saldo_noreg)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>
            <?php 
                $saldo_noreg = 0;
                $jml_saldo_noreg = 0;
                
                $tot_jml_debet_noreg = 0;
                $tot_debet_noreg = 0;
                $tot_jml_kredit_noreg = 0;
                $tot_kredit_noreg = 0;
            ?>
        <?php } ?>
        <?php if ( $value['unit'] <> $data[$key+1]['unit'] ) { ?>
            <tr class="kuning">
                <td colspan="4"><b>Total Per Unit | <?php echo $value['unit']; ?></b></td>
                <!-- <td class="text-right"><b><?php echo angkaDecimal($tot_jml_debet_unit); ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo angkaDecimal($tot_debet_unit); ?></b></td>
                <!-- <td class="text-right"><b><?php echo angkaDecimal($tot_jml_kredit_unit); ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo angkaDecimal($tot_kredit_unit); ?></b></td>
                <!-- <td class="text-right"><b><?php echo ($jml_saldo_unit >= 0) ? angkaDecimal($jml_saldo_unit) : '('.angkaDecimal(abs($jml_saldo_unit)).')'; ?></b></td> -->
                <td class="text-right" colspan="2"><b><?php echo ($saldo_unit >= 0) ? angkaDecimal($saldo_unit) : '('.angkaDecimal(abs($saldo_unit)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>
            <?php
                $saldo_unit = 0;
                $jml_saldo_unit = 0;

                $tot_jml_debet_unit = 0;
                $tot_debet_unit = 0;
                $tot_jml_kredit_unit = 0;
                $tot_kredit_unit = 0;
            ?>
        <?php } ?>
        <?php  
            $idx_barang++;
        ?>
    <?php } ?>
    <!-- <tr class="kuning">
        <td colspan="4"><b>Total Keseluruhan</b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_debet); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_jml_kredit); ?></b></td>
        <td class="text-right"><b><?php echo angkaDecimal($gt_kredit); ?></b></td>
        <td class="text-right"><b><?php echo ($gt_jml_saldo >= 0) ? angkaDecimal($gt_jml_saldo) : '('.angkaDecimal(abs($gt_jml_saldo)).')'; ?></b></td>
        <td class="text-right"><b><?php echo ($gt_saldo >= 0) ? angkaDecimal($gt_saldo) : '('.angkaDecimal(abs($gt_saldo)).')'; ?></b></td>
    </tr> -->
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>