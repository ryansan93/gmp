<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo strtoupper($value['nama_pelanggan']); ?></td>
            <td><?php echo strtoupper(tglIndonesia($value['tgl_bayar'], '-', ' ')); ?></td>
            <td class="text-right" target="saldo_awal"><?php echo angkaDecimal($value['saldo']); ?></td>
            <td class="text-right" target="jml_transfer"><?php echo angkaDecimal($value['jml_transfer']); ?></td>
            <td class="text-right" target="pajak"><?php echo angkaDecimal($value['nil_pajak']); ?></td>
            <td class="text-right" target="lebih_bayar_non_saldo"><?php echo angkaDecimal($value['non_saldo']); ?></td>
            <td class="text-right" target="total_bayar"><?php echo angkaDecimal($value['total_bayar']); ?></td>
            <td class="text-right" target="saldo_akhir"><?php echo angkaDecimal($value['lebih_kurang']); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="10">
            <div class="col-xs-12 no-padding">
                <button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="ss.encryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
            </div>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="10">Data tidak ditemukan.</td>
    </tr>
<?php } ?>