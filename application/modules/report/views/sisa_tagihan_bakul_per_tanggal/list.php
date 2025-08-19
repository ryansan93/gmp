<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo strtoupper($value['nama_pelanggan']); ?></td>
            <td><?php echo strtoupper($value['nama_mitra']); ?></td>
            <!-- <td><?php echo strtoupper(tglIndonesia($value['tgl_tutup_siklus'], '-', ' ')); ?></td> -->
            <td><?php echo strtoupper(tglIndonesia($value['tgl_invoice'], '-', ' ')); ?></td>
            <td><?php echo !empty($value['no_invoice']) ? $value['no_invoice'] : ''; ?></td>
            <td class="text-right" target="total"><?php echo angkaDecimal($value['total']); ?></td>
            <td class="text-right" target="dn"><?php echo angkaDecimal($value['dn']); ?></td>
            <td class="text-right" target="cn"><?php echo angkaDecimal($value['cn']); ?></td>
            <td class="text-right" target="bayar"><?php echo angkaDecimal($value['bayar']); ?></td>
            <td class="text-right" target="sisa"><?php echo angkaDecimal($value['sisa_tagihan']); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="10">
            <div class="col-xs-12 no-padding">
                <button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="st.encryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
            </div>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="10">Data tidak ditemukan.</td>
    </tr>
<?php } ?>