<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['jenis_transaksi']); ?></td>
            <td><?php echo strtoupper($value['nama_supl']); ?></td>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($value['tgl_pengajuan'], '-', ' ')); ?></td>
            <td class="text-right"><?php echo strtoupper(angkaDecimal($value['jml_transfer'])); ?></td>
            <td><a href="uploads/<?php echo $value['lampiran']; ?>" target="_blank"><?php echo $value['lampiran']; ?></a></td>
            <td><?php echo strtoupper($value['deskripsi'].' '.$value['waktu']); ?></td>
            <td>
                <button type="button" class="col-xs-12 btn btn-primary" data-id="<?php echo $value['id']; ?>" onclick="vp.formRealisasiBayar(this)"><i class="fa fa-check"></i> BAYAR</button>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="6">Tidak ada pengajuan.</td>
    </tr>
<?php } ?>