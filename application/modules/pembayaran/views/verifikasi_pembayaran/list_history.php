<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p data" data-norek="<?php echo $value['no_rek']; ?>" data-atasnama="<?php echo $value['atas_nama']; ?>" data-bank="<?php echo $value['bank']; ?>" data-coabank="<?php echo $value['coa_bank']; ?>">
            <td><?php echo strtoupper($value['jenis_transaksi']); ?></td>
            <td><?php echo strtoupper($value['nama_supl']); ?></td>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['tgl_bayar'], '-', ' ')); ?></td>
            <td class="text-center"><?php echo strtoupper($value['no_bukti']); ?></td>
            <td class="text-left"><?php echo strtoupper($value['ket_realisasi']); ?></td>
            <td class="text-right"><?php echo strtoupper(angkaDecimal($value['jml_transfer'])); ?></td>
            <td>
                <?php if ( !empty($value['filename']) ) { ?>
                    <?php $filename = explode(', ', $value['filename']); ?>
                    <?php foreach ($filename as $key => $value) { ?>
                        <a href="uploads/<?php echo $value; ?>" target="_blank">
                            <?php echo $value; ?>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    -
                <?php } ?>


                <!--
                <?php if (isset($attachment[$value['id']]) && count($attachment[$value['id']]) > 0) { ?>

                    <?php foreach ($attachment[$value['id']] as $file) { ?>
                        <a href="uploads/<?php echo $file['file_name'] ?>" target="_blank">
                            <?php echo $file['file_name'] ?>
                        </a><br>
                    <?php } ?>

                <?php } elseif (!empty($value['lampiran_realisasi'])) { ?>

                    <a href="uploads/<?php echo $value['lampiran_realisasi']; ?>" target="_blank">
                        <?php echo $value['lampiran_realisasi']; ?>
                    </a>

                <?php } else { ?>
                    -
                <?php } ?>
                -->
            </td>
            <td>
                <button type="button" class="col-xs-12 btn btn-default" data-id="<?php echo $value['id']; ?>" data-table="<?php echo $value['tbl_name']; ?>" onclick="vp.formDetail(this)"><i class="fa fa-list"></i> DETAIL</button>
            </td>
            <td>
                <?php if ( $akses['a_ack'] == 1 ) { ?>
                    <button type="button" class="col-xs-12 btn btn-primary" data-id="<?php echo $value['id']; ?>" data-table="<?php echo $value['tbl_name']; ?>" onclick="vp.formRealisasiBayarDetail(this)"><i class="fa fa-file"></i></button>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="9">Tidak ada pengajuan.</td>
    </tr>
<?php } ?>