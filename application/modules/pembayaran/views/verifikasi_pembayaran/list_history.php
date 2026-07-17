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
                <?php if ( !empty($value['path']) ) { ?>
                    <?php 
                    // $path = str_replace('C:\xampp_php7\htdocs\gmp_erp\uploads/', '', $value['path']); 
                    $path = explode(', ', $value['path']); 
                    ?>
                    <?php foreach ($path as $k_fm => $fm) { ?>
                        <a href="uploads/<?php echo $fm; ?>" target="_blank">
                            <?php echo $fm; ?>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    -
                <?php } ?>
            </td>
            <td>
                <?php echo strtoupper($value['bank']); ?>
                <br>
                <?php echo strtoupper($value['no_rek']); ?>
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