<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p" onclick="dn.changeTabActive(this)" data-kode="<?php echo $value['id']; ?>" data-edit="" data-href="action">
            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo strtoupper($value['nomor']); ?></td>
            <td class="text-center"><?php echo (isset($value['tipe_dn']) && $value['tipe_dn'] == 'LANGSUNG') ? 'LANGSUNG' : 'INVOICE'; ?></td>
            <td class="text-center"><?php echo !empty($value['unit']) ? strtoupper($value['unit']) : '-'; ?></td>
            <td><?php echo strtoupper($value['nama_supplier']); ?></td>
            <td><?php echo strtoupper($value['ket_dn']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['tot_dn']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="7">Data tidak ditemukan.</td>
    </tr>
<?php } ?>