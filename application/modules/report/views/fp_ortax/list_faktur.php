<?php if ( !empty($invoices) && count($invoices) > 0 ) { ?>
    <?php foreach ($invoices as $inv) { ?>
        <?php
            $header = $inv['header'];
            $identitas = $inv['identitas'];
        ?>
        <tr class="data">
            <td class="text-center"><?php echo $inv['baris']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($header['tanggal_panen'], '-', ' '); ?></td>
            <td>Normal</td>
            <td class="text-center">08</td>
            <td class="text-center">10</td>
            <td>(Ternak dan Unggas Hidup, Karkas, Nonkarkas, Jeroan)</td>
            <td><?php echo $inv['no_nota']; ?></td>
            <td class="text-center">10</td>
            <td><?php echo $id_tku_penjual; ?></td>
            <td><?php echo $identitas['npwp_nik']; ?></td>
            <td class="text-center"><?php echo $identitas['jenis_id']; ?></td>
            <td class="text-center">IDN</td>
            <td><?php echo $identitas['nomor_dokumen']; ?></td>
            <td><?php echo $inv['nama_pembeli_final']; ?></td>
            <td><?php echo $inv['alamat_final']; ?></td>
            <td></td>
            <td><?php echo $identitas['id_tku']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="17">Data tidak ditemukan.</td>
    </tr>
<?php } ?>
