<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data">
            <td><?php echo $value['kode_bakul']; ?></td>
            <td><?php echo $value['nik_bakul']; ?></td>
            <td><?php echo $value['nama_bakul']; ?></td>
            <td><?php echo $value['alamat_bakul']; ?></td>
            <td><?php echo $value['no_faktur']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tanggal_panen'], '-', ' '); ?></td>
            <td class="text-center"><?php echo !empty($value['tanggal_rhpp']) ? tglIndonesia($value['tanggal_rhpp'], '-', ' ') : '-'; ?></td>
            <td><?php echo !empty($value['no_nota']) ? $value['no_nota'] : '-'; ?></td>
            <td class="text-center"><?php echo $value['kode_barang']; ?></td>
            <td class="text-center"><?php echo $value['deskripsi_barang']; ?></td>
            <td class="text-right decimal_number_format kuantitas" data-val="<?php echo $value['kuantitas']; ?>"><?php echo angkaDecimal($value['kuantitas']); ?></td>
            <td class="text-right number_format"><?php echo angkaRibuan($value['harga_per_satuan_kuantitas']); ?></td>
            <td class="text-right number_format jml_ekor" data-val="<?php echo $value['jumlah_ekor']; ?>"><?php echo angkaRibuan($value['jumlah_ekor']); ?></td>
            <td class="text-right number_format total" data-val="<?php echo $value['total']; ?>"><?php echo angkaDecimal($value['total']); ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['periode'], '-', ' '); ?></td>
            <td><?php echo $value['unit']; ?></td>
            <td><?php echo $value['nim']; ?></td>
            <td><?php echo $value['nik']; ?></td>
            <td><?php echo $value['nama_plasma']; ?></td>
            <td class="text-center"><?php echo $value['kandang_plasma']; ?></td>
            <td><?php echo $value['npwp_plasma']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="21">Data tidak ditemukan.</td>
    </tr>
<?php } ?>