<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $k => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['jenis_transaksi']); ?></td>
            <td><?php echo strtoupper(tglIndonesia($value['tgl_distribusi'], '-', ' ')); ?></td>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo strtoupper($value['nama_mitra']); ?></td>
            <td>-</td>
            <td><?php echo $value['kode_unit']; ?></td>
            <td><?php echo $value['nama_barang']; ?></td>
            <td class="text-right" target="sak" data-val="<?php echo $value['zak']; ?>"><?php echo ($value['zak'] >= 0) ? angkaDecimal($value['zak']) : '('.angkaDecimal(abs($value['zak'])).')'; ?></td>
            <td class="text-right" target="tonase" data-val="<?php echo $value['tonase']; ?>"><?php echo ($value['tonase'] >= 0) ? angkaDecimal($value['tonase']) : '('.angkaDecimal(abs($value['tonase'])).')'; ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['ongkos']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($value['hrg_beli']); ?></td>
            <td class="text-right" target="tot_beli" data-val="<?php echo $value['tot_beli']; ?>"><?php echo ($value['tot_beli'] >= 0) ? angkaDecimal($value['tot_beli']) : '('.angkaDecimal(abs($value['tot_beli'])).')'; ?></td>
            <td class="text-right" target="tot_oa" data-val="<?php echo ($value['tonase'] * $value['ongkos']); ?>"><?php echo (($value['tonase'] * $value['ongkos']) >= 0) ? angkaDecimal(($value['tonase'] * $value['ongkos'])) : '('.angkaDecimal(abs(($value['tonase'] * $value['ongkos']))).')'; ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="13">
            <div class="col-xs-12 no-padding">
                <button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="st.encryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
            </div>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td colspan="13">Data tidak ditemukan.</td>
    </tr>
<?php } ?>