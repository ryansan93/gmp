<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data">
            <td class="nomor"><?php echo $value['nomor']; ?></td>
            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['nominal']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['pakai']); ?></td>
            <td class="text-right sisa"><?php echo angkaDecimal($value['sisa']); ?></td>
            <td>
                <input type="text" class="form-control pakai text-right" data-tipe="decimal" placeholder="Mau Pakai" onblur="bakul.cekNominal(this)" disabled>
            </td>
            <td class="text-center">
                <input type="checkbox" class="cursor-p check" data-id="<?php echo $value['nomor']; ?>" onchange="bakul.cekMauPakai(this)">
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8">Data tidak ditemukan.</td>
    </tr>
<?php } ?>