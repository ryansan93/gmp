<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php
            $bg_color = null;
            if ( empty($value['tanggal']) ) {
                $bg_color = 'red';
            }
        ?>

        <tr class="data <?php echo $bg_color; ?>">
            <td><?php echo 'OVK'; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
            <td><?php echo $value['perusahaan']; ?></td>
            <td><?php echo $value['kode_supplier']; ?></td>
            <td><?php echo $value['nama_supplier']; ?></td>
            <td><?php echo $value['nik']; ?></td>
            <td><?php echo $value['npwp']; ?></td>
            <td><?php echo $value['no_form']; ?></td>
            <td><?php echo !empty($value['no_faktur_pembelian']) ? $value['no_faktur_pembelian'] : '-'; ?></td>
            <td class="text-center"><?php echo !empty($value['tanggal_faktur']) ? tglIndonesia($value['tanggal_faktur'], '-', ' ') : '-'; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tanggal_pengiriman'], '-', ' '); ?></td>
            <td><?php echo $value['kode_barang']; ?></td>
            <td><?php echo $value['nama_barang']; ?></td>
            <td class="text-right kuantitas" data-val="<?php echo $value['kuantitas']; ?>"><?php echo angkaDecimal($value['kuantitas']); ?></td>
            <td><?php echo $value['satuan_kuantitas']; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['harga_per_satuan_kuantitas']); ?></td>
            <td class="text-right total" data-val="<?php echo $value['total']; ?>"><?php echo angkaDecimal($value['total']); ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tgl_terima'], '-', ' '); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="19">Data tidak ditemukan.</td>
    </tr>
<?php } ?>