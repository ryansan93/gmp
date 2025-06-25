<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data">
            <td><?php echo !empty($value['no_batch']) ? $value['no_batch'] : '-'; ?></td>
            <td class="text-center"><?php echo !empty($value['tanggal_panen']) ? tglIndonesia($value['tanggal_panen'], '-', ' ') : '-'; ?></td>
            <td><?php echo $value['jenis_pengiriman']; ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td><?php echo $value['nama_plasma']; ?></td>
            <td class="text-center"><?php echo $value['kandang']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['periode'], '-', ' '); ?></td>
            <td><?php echo $value['no_sj']; ?></td>
            <td><?php echo $value['kode_barang']; ?></td>
            <td class="text-right kuantitas" data-val="<?php echo $value['kuantitas']; ?>"><?php echo angkaDecimal($value['kuantitas']); ?></td>
            <td><?php echo $value['satuan_kuantitas']; ?></td>
            <td><?php echo $value['gudang']; ?></td>
            <td><?php echo !empty($value['departemen']) ? $value['departemen'] : '-'; ?></td>
            <td><?php echo !empty($value['kode_proyek']) ? $value['kode_proyek'] : '-'; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak ditemukan.</td>
    </tr>
<?php } ?>