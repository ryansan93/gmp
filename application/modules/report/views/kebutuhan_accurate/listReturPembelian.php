<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data">
            <td><?php echo $value['kode_supplier']; ?></td>
            <td><?php echo $value['nama_supplier']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tanggal_retur'], '-', ' '); ?></td>
            <td><?php echo $value['kode_barang']; ?></td>
            <td><?php echo $value['nama_barang']; ?></td>
            <td class="text-right kuantitas" data-val="<?php echo $value['kuantitas']; ?>"><?php echo angkaDecimal($value['kuantitas']); ?></td>
            <td><?php echo $value['satuan_kuantitas']; ?></td>
            <td><?php echo $value['deskripsi_penyebab_retur']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8">Data tidak ditemukan.</td>
    </tr>
<?php } ?>