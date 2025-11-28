<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="data" data-norek="<?php echo $value['no_rek']; ?>" data-atasnama="<?php echo $value['atas_nama']; ?>" data-bank="<?php echo $value['bank']; ?>" data-coabank="<?php echo $value['coa_bank']; ?>">
            <td><?php echo strtoupper($value['jenis_transaksi']); ?></td>
            <td><?php echo strtoupper($value['nama_supl']); ?></td>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['tgl_pengajuan'], '-', ' ')); ?></td>
            <td class="text-right"><?php echo strtoupper(angkaDecimal($value['jml_transfer'])); ?></td>
            <td>
                <?php if ( !empty($value['lampiran']) ) { ?>
                    <a href="uploads/<?php echo $value['lampiran']; ?>" target="_blank"><?php echo $value['lampiran']; ?></a>
                <?php } else { ?>
                    -
                <?php } ?>
            </td>
            <td><?php echo strtoupper($value['deskripsi'].' '.$value['waktu']); ?></td>
            <td><?php echo strtoupper($value['nama_bank']); ?></td>
            <td>
                <button type="button" class="col-xs-12 btn btn-default" data-id="<?php echo $value['id']; ?>" data-table="<?php echo $value['tbl_name']; ?>" onclick="vp.formDetail(this)"><i class="fa fa-list"></i> DETAIL</button>
            </td>
            <td>
                <?php if ( $akses['a_ack'] == 1 && $value['verifikasi'] == 1 ) { ?>
                    <button type="button" class="col-xs-12 btn btn-primary" data-id="<?php echo $value['id']; ?>" data-table="<?php echo $value['tbl_name']; ?>" onclick="vp.formRealisasiBayar(this)"><i class="fa fa-check"></i> BAYAR</button>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8">Tidak ada pengajuan.</td>
    </tr>
<?php } ?>