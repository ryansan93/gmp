<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p" onclick="chb.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id']; ?>">
            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['nama_coa']; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['saldo_awal']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['saldo_akhir']); ?></td>
        </tr>
    <?php } ?>
<?php } else {?>
    <tr>
        <td colspan="4">Data tidak ditemukan.</td>
    </tr>
<?php } ?>