<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <td colspan="5"><b>TOTAL</b></td>
    <td class="text-right"><b><?php echo angkaRibuan($data['populasi']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['rata_umur']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['deplesi']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['fcr']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['bb']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['ip']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['lr_plasma']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['lr_plasma_per_ekor']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['lr_inti']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['lr_inti_per_ekor']); ?></b></td>
    <td class="text-right"><b><?php echo angkaDecimal($data['modal_inti_per_kg']); ?></b></td>
<?php } else { ?>
    <td colspan="5"><b>TOTAL</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
    <td class="text-right"><b>0</b></td>
<?php } ?>