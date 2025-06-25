<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['tgl_terima'], '-', ' ')); ?></td>
            <td><?php echo $value['no_sj_asal']; ?></td>
            <td><?php echo $value['no_sj']; ?></td>
            <td><?php echo strtoupper($value['nama_asal']); ?></td>
            <td><?php echo strtoupper($value['nama_tujuan']); ?></td>
            <td><?php echo strtoupper($value['nama_barang']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['jumlah']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="7">Data tidak ditemukan.</td>
    </tr>
<?php } ?>