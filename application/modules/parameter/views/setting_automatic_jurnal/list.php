<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data cursor-p" onclick="saj.changeTabActive(this)" data-id="<?php echo $value['id']; ?>" data-edit="" data-href="action">
            <td><?php echo strtoupper(tglIndonesia($value['tgl_berlaku'], '-', ' ')); ?></td>
            <td><?php echo $value['nama_fitur'].' | '.$value['nama']; ?></td>
            <td><?php echo $value['tbl_name']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="3">Data tidak ditemukan.</td>
    </tr>
<?php } ?>