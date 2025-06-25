<?php // cetak_r($data); ?>

<?php if (count($data) > 0): ?>
    <?php foreach ($data as $key => $val) { ?>
        <?php $resubmit = null; ?>
        <tr class="search v-center">
            <td class="tanggal"><?php echo tglIndonesia($val['tgl_trans'], '-', ' '); ?></td>
            <td class="noreg"><?php echo $val['noreg']; ?></td>
            <td class="nama_mitra"><?php echo $val['data_rdim_submit']['mitra']['d_mitra']['nama']; ?></td>
            <td class="keterangan">
                <div class="col-sm-11 no-padding">
                    <?php
                        $index_last = count($val['logs']);
                        $last_log = $val['logs'][$index_last-1];
                        $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
                        echo $keterangan;
                    ?>
                </div>
                <div class="col-sm-1 no-padding text-right">
                    <a class="cursor-p" data-href="action" onclick="kpm.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>">Lihat</a>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php else: ?>
    <tr>
        <td colspan="4" class="text-center">Data tidak ditemukan.</td>
    </tr>
<?php endif; ?>
