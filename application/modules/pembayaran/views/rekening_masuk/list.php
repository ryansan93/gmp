<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p data" data-kode="<?php echo $value['kode']; ?>" ondblclick="rm.viewForm(this)">
            <td>
                <a href="pembayaran/Bakul/index/<?php echo exEncrypt($value['kode']); ?>" target="_blank" title="Link pembayaran pelanggan">
                    <?php echo $value['kode']; ?>
                </a>
            </td>
            <td>
                <a href="accounting/JurnalPusat/index/<?php echo exEncrypt($value['no_bukti']); ?>" target="_blank" title="Link jurnal pusat">
                    <?php echo $value['no_bukti']; ?>
                </a>
                <?php // echo strtoupper($value['no_bukti']); ?>
            </td>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo (isset($value['nama_pelanggan']) && !empty($value['nama_pelanggan'])) ? strtoupper($value['nama_pelanggan']) : '-'; ?></td>
            <td><?php echo $value['ket']; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['jml_transfer']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['terpakai']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['sisa']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8">Data tidak ditemukan.</td>
    </tr>
<?php } ?>