<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td colspan="8" style="background-color: #dedede;"><b><?php echo strtoupper( $value['nama_unit'].' - '.$value['nama_gudang'] ); ?></b></td>
            <td class="text-right" style="background-color: #dedede;"><b><?php echo angkaDecimal( $value['total'] ); ?></b></td>
        </tr>
        <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
            <tr>
                <td><?php echo tglIndonesia($v_det['tgl_terima'], '-', ' '); ?></td>
                <td><?php echo $v_det['no_order']; ?></td>
                <td><?php echo $v_det['no_sj']; ?></td>
                <td><?php echo $v_det['kode_barang']; ?></td>
                <td><?php echo $v_det['nama_barang']; ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['jml_stok']); ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['hrg_beli']); ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['hrg_beli'] * $v_det['jml_stok']); ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="9">Data tidak ditemukan.</td>
    </tr>
<?php } ?>