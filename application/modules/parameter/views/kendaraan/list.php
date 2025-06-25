<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p" onclick="kend.changeTabActive(this)" data-id="<?php echo $value['id']; ?>" data-edit="" data-href="action">
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo strtoupper($value['jenis']); ?></td>
            <td><?php echo strtoupper($value['nopol']); ?></td>
            <td><?php echo strtoupper($value['nama_unit']); ?></td>
            <td><?php echo strtoupper($value['nama_karyawan']); ?></td>
            <td><?php echo strtoupper($value['merk']); ?></td>
            <td><?php echo strtoupper($value['tipe']); ?></td>
            <td><?php echo strtoupper($value['warna']); ?></td>
            <td><?php echo strtoupper($value['tahun']); ?></td>
            <td></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="10">Data tidak ditemukan.</td>
    </tr>
<?php } ?>