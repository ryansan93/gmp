<?php if ( !empty($invoices) && count($invoices) > 0 ) { ?>
    <?php foreach ($invoices as $inv) { ?>
        <?php
            $header = $inv['header'];
            $total_dpp = 0;
            $total_ppn = 0;
            foreach ($inv['details'] as $det) {
                $dpp = (float)$det['harga_per_satuan_kuantitas'] * (float)$det['kuantitas'];
                $dpp_nilai_lain = $dpp * 11 / 12;
                $total_dpp += $dpp;
                $total_ppn += round($dpp_nilai_lain * 0.12);
            }
        ?>
        <tr class="data">
            <td class="text-center"><?php echo $inv['baris']; ?></td>
            <td><?php echo $inv['no_nota']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($header['tanggal_panen'], '-', ' '); ?></td>
            <td><?php echo $inv['identitas']['jenis_id'] == 'TIN' ? $inv['identitas']['npwp_nik'].' (NPWP)' : $inv['identitas']['nomor_dokumen'].' (NIK)'; ?></td>
            <td><?php echo $header['nama_bakul']; ?></td>
            <td class="text-center"><?php echo count($inv['details']); ?></td>
            <td class="text-right number_format dpp" data-val="<?php echo $total_dpp; ?>"><?php echo angkaRibuan($total_dpp); ?></td>
            <td class="text-right number_format ppn" data-val="<?php echo $total_ppn; ?>"><?php echo angkaRibuan($total_ppn); ?></td>
            <td><?php echo $header['alamat_jalan']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="9">Data tidak ditemukan.</td>
    </tr>
<?php } ?>
