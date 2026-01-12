<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php $no = 1; ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo $no; ?></td>
            <td><?php echo $value['kandang']; ?></td>
            <td><?php echo strtoupper($value['mitra']); ?></td>
            <td><?php echo strtoupper(tglIndonesia($value['tgl_docin'], '-', ' ')); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['populasi']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['rata_umur']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['deplesi']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['fcr']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['bb']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['ip']); ?></td>
            <td class="text-right"><?php echo ($value['lr_plasma'] >= 0) ? angkaDecimal($value['lr_plasma']) : '('.angkaDecimal(abs($value['lr_plasma'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['lr_plasma_per_ekor'] >= 0) ? angkaDecimal($value['lr_plasma_per_ekor']) : '('.angkaDecimal(abs($value['lr_plasma_per_ekor'])).')'; ?></td>
            <?php
                $color = 'green';
                if ( $value['jenis_rhpp'] == 0 ) {
                    $color = 'red';
                }
            ?>
            <td class="text-right" style="color: <?php echo $color; ?>"><b><?php echo ($value['lr_inti'] >= 0 ) ? angkaDecimal($value['lr_inti']) : '('.angkaDecimal(abs($value['lr_inti'])).')'; ?></b></td>
            <td class="text-right" style="color: <?php echo $color; ?>"><b><?php echo ($value['lr_inti_per_ekor'] >= 0 ) ? angkaDecimal($value['lr_inti_per_ekor']) : '('.angkaDecimal(abs($value['lr_inti_per_ekor'])).')'; ?></b></td>
        </tr>
        <?php $no++; ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak di temukan.</td>
    </tr>
<?php } ?>