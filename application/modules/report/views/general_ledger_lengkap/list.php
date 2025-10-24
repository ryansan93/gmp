<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php
        $urut = 1;
        $idx_coa = 0;
        $key_coa = null;

        $tot_saldo_awal = 0;
        $tot_debet = 0;
        $tot_kredit = 0;
        $tot_saldo_akhir = 0;

        $gt_saldo_awal = 0;
        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo_akhir = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php $key_coa = $value['no_coa'].'-'.$value['unit']; ?>

        <?php
            $tot_saldo_awal = 0;
            $tot_debet = 0;
            $tot_kredit = 0;
            $tot_saldo_akhir = 0;
        ?>

        <tr class="abu get_data" data-urut="<?php echo $urut; ?>" data-coa="<?php echo $value['no_coa']; ?>" data-unit="<?php echo $value['unit']; ?>">
            <td colspan="10">
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-1 no-padding"><label class="label-control">NO. COA</label></div>
                    <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                    <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['no_coa']; ?></label></div>
                </div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-1 no-padding"><label class="label-control">NAMA COA</label></div>
                    <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                    <div class="col-xs-10 no-padding"><label class="label-control"><?php echo strtoupper($value['nama_coa']); ?></label></div>
                </div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-1 no-padding"><label class="label-control">UNIT</label></div>
                    <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                    <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['unit']; ?></label></div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="col-xs-1"><b>Tgl</b></td>
            <td class="col-xs-2"><b>No. Dokumen</b></td>
            <td class="col-xs-1"><b>Unit</b></td>
            <td class="col-xs-4"><b>Keterangan</b></td>
            <td class="col-xs-2"><b>Debet</b></td>
            <td class="col-xs-2"><b>Kredit</b></td>
        </tr>
        <tr class="detail" data-urut="<?php echo $urut; ?>"></tr>
        
        <!--
        <?php if ( $idx_coa == 0 ) { ?>
            <?php if ( stristr($value['keterangan'], 'Saldo Awal') === false ) { ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?php echo $value['unit']; ?></td>
                    <td>Saldo Awal</td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <td><?php echo ($value['tanggal'] < '2025-01-01') ? '' : tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['kode_trans']; ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td><?php echo strtoupper($value['keterangan']); ?></td>
            <td class="text-right"><?php echo ($value['debet'] >= 0) ? angkaDecimal($value['debet']) : '('.angkaDecimal(abs($value['debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['kredit'] >= 0) ? angkaDecimal($value['kredit']) : '('.angkaDecimal(abs($value['kredit'])).')'; ?></td>
        </tr>
        -->
       
        <?php 
            $idx_coa++;

            $tot_saldo_awal += $value['saldo_awal'];
            $tot_debet += $value['debet'];
            $tot_kredit += $value['kredit'];
            $tot_saldo_akhir += $value['saldo_akhir'];

            $gt_saldo_awal += $value['saldo_awal'];
            $gt_debet += $value['debet'];
            $gt_kredit += $value['kredit'];
            $gt_saldo_akhir += $value['saldo_akhir'];
        ?>

        <tr>
            <td colspan="6" style="padding: 10px 5px 5px 5px;">
                <table class="table table-bordered" style="margin-bottom: 0px;">
                    <thead>
                        <tr>
                            <th class="col-xs-3">Saldo Awal</th>
                            <th class="col-xs-3">Debet</th>
                            <th class="col-xs-3">Kredit</th>
                            <th class="col-xs-3">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-right"><?php echo ($tot_saldo_awal >= 0) ? angkaDecimal($tot_saldo_awal) : '('.angkaDecimal(abs($tot_saldo_awal)).')'; ?></td>
                            <td class="text-right"><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></td>
                            <td class="text-right"><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></td>
                            <td class="text-right"><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <?php $urut++; ?>
    <?php } ?>
    <tr>
        <td colspan="6" style="padding: 10px 5px 5px 5px;">
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-xs-3">Total Saldo Awal</th>
                        <th class="col-xs-3">Total Debet</th>
                        <th class="col-xs-3">Total Kredit</th>
                        <th class="col-xs-3">Total Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-right"><?php echo ($gt_saldo_awal >= 0) ? angkaDecimal($gt_saldo_awal) : '('.angkaDecimal(abs($gt_saldo_awal)).')'; ?></td>
                        <td class="text-right"><?php echo ($gt_debet >= 0) ? angkaDecimal($gt_debet) : '('.angkaDecimal(abs($gt_debet)).')'; ?></td>
                        <td class="text-right"><?php echo ($gt_kredit >= 0) ? angkaDecimal($gt_kredit) : '('.angkaDecimal(abs($gt_kredit)).')'; ?></td>
                        <td class="text-right"><?php echo ($gt_saldo_akhir >= 0) ? angkaDecimal($gt_saldo_akhir) : '('.angkaDecimal(abs($gt_saldo_akhir)).')'; ?></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>