<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['nama_jurnal_trans']; ?></td>
            <td><?php echo trim($value['keterangan']); ?></td>
            <td class="text-right debit"><?php echo angkaDecimal($value['debit']); ?></td>
            <td class="text-right kredit"><?php echo angkaDecimal($value['kredit']); ?></td>
        </tr>
    <?php } ?>
<?php } else {?>
    <tr>
        <td colspan="5">Data tidak ditemukan.</td>
    </tr>
<?php } ?>