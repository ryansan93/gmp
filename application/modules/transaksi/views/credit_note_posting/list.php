<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p" onclick="cn.changeTabActive(this)" data-kode="<?php echo $value['id']; ?>" data-edit="" data-href="action">
            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo strtoupper($jenis_cn[$value['jenis_cn']]['nama']); ?></td>
            <td><?php echo strtoupper($value['nomor_cn']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['tot_pakai']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="4">Data tidak ditemukan.</td>
    </tr>
<?php } ?>