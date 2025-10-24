<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo ($value['tanggal'] < '2025-01-01') ? '' : tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['kode_trans']; ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td><?php echo strtoupper($value['keterangan']); ?></td>
            <td class="text-right"><?php echo ($value['debet'] >= 0) ? angkaDecimal($value['debet']) : '('.angkaDecimal(abs($value['debet'])).')'; ?></td>
            <td class="text-right"><?php echo ($value['kredit'] >= 0) ? angkaDecimal($value['kredit']) : '('.angkaDecimal(abs($value['kredit'])).')'; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="6">Data tidak ditemukan.</td>
    </tr>
<?php } ?>