<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $kode_unit = null; 
        $kode_noreg = null; 
        $kode_barang = null; 
        $idx_unit = 0;
        $idx_noreg = 0;
        $idx_barang = 0;

        $tot_jml_sa_per_barang = 0;
        $tot_sa_per_barang = 0;
        $tot_jml_debet_per_barang = 0;
        $tot_debet_per_barang = 0;
        $tot_jml_kredit_per_barang = 0;
        $tot_kredit_per_barang = 0;
        $tot_jml_saldo_akhir_per_barang = 0;
        $tot_saldo_akhir_per_barang = 0;

        $tot_jml_sa_per_unit = 0;
        $tot_sa_per_unit = 0;
        $tot_jml_debet_per_unit = 0;
        $tot_debet_per_unit = 0;
        $tot_jml_kredit_per_unit = 0;
        $tot_kredit_per_unit = 0;
        $tot_jml_saldo_akhir_per_unit = 0;
        $tot_saldo_akhir_per_unit = 0;

        $gt_saldo_awal = 0;
        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo_akhir = 0;
    ?>
    <?php $key_unit = null; $key_barang = null; ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $key_unit <> $value['unit'] ) { ?>
            <?php $key_unit = $value['unit']; ?>
            <tr class="abu">
                <td colspan="11">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Unit</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['unit']; ?></label></div>
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
                <td class="col-xs-1 text-center" rowspan="2" style="vertical-align: middle;"><b>Noreg</b></td>
                <td class="col-xs-2 text-center" rowspan="2" style="vertical-align: middle;"><b>Plasma</b></td>
                <td class="col-xs-2 text-center" rowspan="2" style="vertical-align: middle;"><b>Status RHPP</b></td>
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
        <?php } ?>

        <?php 
            $noreg = $value['noreg'];
            $nama_plasma = $value['nama_plasma'];
            $status_rhpp = $value['status'];

            $sa = $value['sa'];
            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($sa+$debet)-$kredit;

            $jml_sa = $value['jml_sa'];
            $jml_debet = $value['jml_debet'];
            $jml_kredit = $value['jml_kredit'];
            $jml_saldo = ($jml_sa+$jml_debet)-$jml_kredit;

            $tot_jml_sa_per_barang += $jml_sa;
            $tot_sa_per_barang += $sa;
            $tot_jml_debet_per_barang += $jml_debet;
            $tot_debet_per_barang += $debet;
            $tot_jml_kredit_per_barang += $jml_kredit;
            $tot_kredit_per_barang += $kredit;
            $tot_jml_saldo_akhir_per_barang += $jml_saldo;
            $tot_saldo_akhir_per_barang += $saldo;

            $tot_sa_per_unit += $sa;
            $tot_debet_per_unit += $debet;
            $tot_kredit_per_unit += $kredit;
            $tot_saldo_akhir_per_unit += $saldo;

            $gt_saldo_awal += $sa;
            $gt_debet += $debet;
            $gt_kredit += $kredit;
            $gt_saldo_akhir += $saldo;
        ?>
        
        <?php if ( $key_barang <> $value['kode_barang'] ) { ?>
            <?php $key_barang = $value['kode_barang']; ?>
            <tr>
                <td colspan="11"><b><?php echo $value['kode_barang'].' | '.$value['nama_barang']; ?></b></td>
            </tr>
        <?php } ?>

        <tr>
            <td><?php echo $noreg; ?></td>
            <td><?php echo $nama_plasma; ?></td>
            <td><?php echo $status_rhpp; ?></td>
            <td class="text-right"><?php echo ($jml_sa >= 0) ? angkaDecimal($jml_sa) : '('.angkaDecimal(abs($jml_sa)).')'; ?></td>
            <td class="text-right"><?php echo ($sa >= 0) ? angkaDecimal($sa) : '('.angkaDecimal(abs($sa)).')'; ?></td>
            <td class="text-right"><?php echo ($jml_debet >= 0) ? angkaDecimal($jml_debet) : '('.angkaDecimal(abs($jml_debet)).')'; ?></td>
            <td class="text-right"><?php echo ($debet >= 0) ? angkaDecimal($debet) : '('.angkaDecimal(abs($debet)).')'; ?></td>
            <td class="text-right"><?php echo ($jml_kredit >= 0) ? angkaDecimal($jml_kredit) : '('.angkaDecimal(abs($jml_kredit)).')'; ?></td>
            <td class="text-right"><?php echo ($kredit >= 0) ? angkaDecimal($kredit) : '('.angkaDecimal(abs($kredit)).')'; ?></td>
            <td class="text-right"><?php echo ($jml_saldo >= 0) ? angkaDecimal($jml_saldo) : '('.angkaDecimal(abs($jml_saldo)).')'; ?></td>
            <td class="text-right"><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></td>
        </tr>
        <?php if ( $key_barang != $data[$key+1]['kode_barang'] ) { ?>
            <tr>
                <td colspan="3"><b>Total Per Barang <?php echo $value['kode_barang']; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_jml_sa_per_barang >= 0) ? angkaDecimal($tot_jml_sa_per_barang) : '('.angkaDecimal(abs($tot_jml_sa_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_sa_per_barang >= 0) ? angkaDecimal($tot_sa_per_barang) : '('.angkaDecimal(abs($tot_sa_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_jml_debet_per_barang >= 0) ? angkaDecimal($tot_jml_debet_per_barang) : '('.angkaDecimal(abs($tot_jml_debet_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_debet_per_barang >= 0) ? angkaDecimal($tot_debet_per_barang) : '('.angkaDecimal(abs($tot_debet_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_jml_kredit_per_barang >= 0) ? angkaDecimal($tot_jml_kredit_per_barang) : '('.angkaDecimal(abs($tot_jml_kredit_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_kredit_per_barang >= 0) ? angkaDecimal($tot_kredit_per_barang) : '('.angkaDecimal(abs($tot_kredit_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_jml_saldo_akhir_per_barang >= 0) ? angkaDecimal($tot_jml_saldo_akhir_per_barang) : '('.angkaDecimal(abs($tot_jml_saldo_akhir_per_barang)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($tot_saldo_akhir_per_barang >= 0) ? angkaDecimal($tot_saldo_akhir_per_barang) : '('.angkaDecimal(abs($tot_saldo_akhir_per_barang)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="11"></td>
            </tr>
            <?php 
                $tot_jml_sa_per_barang = 0;
                $tot_sa_per_barang = 0;
                $tot_jml_debet_per_barang = 0;
                $tot_debet_per_barang = 0;
                $tot_jml_kredit_per_barang = 0;
                $tot_kredit_per_barang = 0;
                $tot_jml_saldo_akhir_per_barang = 0;
                $tot_saldo_akhir_per_barang = 0;
            ?>
        <?php } ?>

        <?php if ( $key_unit != $data[$key+1]['unit'] ) { ?>
            <tr class="biru">
                <td colspan="3"><b>Total Per Unit <?php echo $value['unit']; ?></b></td>
                <td class="text-right" colspan="2"><b><?php echo ($tot_sa_per_unit >= 0) ? angkaDecimal($tot_sa_per_unit) : '('.angkaDecimal(abs($tot_sa_per_unit)).')'; ?></b></td>
                <td class="text-right" colspan="2"><b><?php echo ($tot_debet_per_unit >= 0) ? angkaDecimal($tot_debet_per_unit) : '('.angkaDecimal(abs($tot_debet_per_unit)).')'; ?></b></td>
                <td class="text-right" colspan="2"><b><?php echo ($tot_kredit_per_unit >= 0) ? angkaDecimal($tot_kredit_per_unit) : '('.angkaDecimal(abs($tot_kredit_per_unit)).')'; ?></b></td>
                <td class="text-right" colspan="2"><b><?php echo ($tot_saldo_akhir_per_unit >= 0) ? angkaDecimal($tot_saldo_akhir_per_unit) : '('.angkaDecimal(abs($tot_saldo_akhir_per_unit)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="11"></td>
            </tr>
            <?php 
                $tot_sa_per_unit += $sa;
                $tot_debet_per_unit += $debet;
                $tot_kredit_per_unit += $kredit;
                $tot_saldo_akhir_per_unit += $saldo;
            ?>
        <?php } ?>
    <?php } ?>

    <tr class="kuning">
        <td colspan="3"><b>Total Keseluruhan</b></td>
        <td class="text-right" colspan="2"><b><?php echo ($gt_saldo_awal >= 0) ? angkaDecimal($gt_saldo_awal) : '('.angkaDecimal(abs($gt_saldo_awal)).')'; ?></b></td>
        <td class="text-right" colspan="2"><b><?php echo ($gt_debet >= 0) ? angkaDecimal($gt_debet) : '('.angkaDecimal(abs($gt_debet)).')'; ?></b></td>
        <td class="text-right" colspan="2"><b><?php echo ($gt_kredit >= 0) ? angkaDecimal($gt_kredit) : '('.angkaDecimal(abs($gt_kredit)).')'; ?></b></td>
        <td class="text-right" colspan="2"><b><?php echo ($gt_saldo_akhir >= 0) ? angkaDecimal($gt_saldo_akhir) : '('.angkaDecimal(abs($gt_saldo_akhir)).')'; ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>