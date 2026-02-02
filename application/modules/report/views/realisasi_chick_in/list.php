<?php if ( !empty( $data ) ) { ?>
    <?php
        $kode_unit = null;
        $tot_rcn_ekor_unit = 0;
        $tot_rcn_box_unit = 0;
        $tot_real_ekor_unit = 0;
        $tot_real_box_unit = 0;
        $gt_rcn_ekor_unit = 0;
        $gt_rcn_box_unit = 0;
        $gt_real_ekor_unit = 0;
        $gt_real_box_unit = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $kode_unit <> $value['kode_unit'] ) { ?>
            <?php $kode_unit = $value['kode_unit']; ?>
            <tr class="abu">
                <td colspan="10">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Unit</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo $value['kode_unit']; ?></label></div>
                    </div>
                </td>
            </tr>
        <?php } ?>
        <?php
            $row_span = 1;
            $idx = $key+1;
            while ( $value['noreg'] == $data[ $idx ]['noreg'] ) {
                $row_span++;

                $idx++;
            }
        ?>
        <tr>
            <?php if ( !isset($data[ $key-1 ]) || $value['noreg'] <> $data[ $key-1 ]['noreg'] ) { ?>
                <td rowspan="<?php echo $row_span; ?>"><?php echo strtoupper($value['kode_unit']); ?></td>
                <td rowspan="<?php echo $row_span; ?>">
                    <div class="col-xs-12 no-padding" style="border-bottom: 1px solid #dedede;">
                        <?php echo strtoupper($value['nama_plasma']); ?>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <?php echo strtoupper($value['alamat']); ?>
                    </div>
                </td>
                <td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo strtoupper($value['kandang']); ?></td>
                <td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo strtoupper($value['noreg']); ?></td>
                <td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo strtoupper(tglIndonesia($value['rcn_tgl_docin'], '-', ' ')); ?></td>
                <td rowspan="<?php echo $row_span; ?>" class="text-right"><?php echo angkaRibuan($value['rcn_jml_box']); ?></td>
                <td rowspan="<?php echo $row_span; ?>" class="text-right"><?php echo angkaRibuan($value['rcn_jml_ekor']); ?></td>
            <?php } ?>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['real_tgl_docin'], '-', ' ')).' '.substr($value['real_tgl_docin'], 11, 5); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['real_jml_box']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['real_jml_ekor']); ?></td>
        </tr>
        <?php
            $tot_rcn_ekor_unit += $value['rcn_jml_box'];
            $tot_rcn_box_unit += $value['rcn_jml_ekor'];
            $tot_real_ekor_unit += $value['real_jml_box'];
            $tot_real_box_unit += $value['real_jml_ekor'];

            $gt_rcn_ekor_unit += $value['rcn_jml_box'];
            $gt_rcn_box_unit += $value['rcn_jml_ekor'];
            $gt_real_ekor_unit += $value['real_jml_box'];
            $gt_real_box_unit += $value['real_jml_ekor'];
        ?>
        <?php if ( $kode_unit <> $data[ $key+1 ]['kode_unit'] ) { ?>
            <tr class="biru">
                <td class="text-right" colspan="5"><b>TOTAL</b></td>
                <td class="text-right"><b><?php echo angkaRibuan( $tot_rcn_ekor_unit ); ?></b></td>
                <td class="text-right"><b><?php echo angkaRibuan( $tot_rcn_box_unit ); ?></b></td>
                <td></td>
                <td class="text-right"><b><?php echo angkaRibuan( $tot_real_ekor_unit ); ?></b></td>
                <td class="text-right"><b><?php echo angkaRibuan( $tot_real_box_unit ); ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>

            <?php
                $tot_rcn_ekor_unit = 0;
                $tot_rcn_box_unit = 0;
                $tot_real_ekor_unit = 0;
                $tot_real_box_unit = 0;
            ?>
        <?php } ?>
    <?php } ?>
    <tr class="kuning">
        <td class="text-right" colspan="5"><b>GRAND TOTAL</b></td>
        <td class="text-right"><b><?php echo angkaRibuan( $gt_rcn_ekor_unit ); ?></b></td>
        <td class="text-right"><b><?php echo angkaRibuan( $gt_rcn_box_unit ); ?></b></td>
        <td></td>
        <td class="text-right"><b><?php echo angkaRibuan( $gt_real_ekor_unit ); ?></b></td>
        <td class="text-right"><b><?php echo angkaRibuan( $gt_real_box_unit ); ?></b></td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="10">Data tidak ditemukan.</td>
    </tr>
<?php } ?>