<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $k_unit => $v_unit) { ?>
        <tr>
            <td colspan="14" style="background-color: #bbb3ff;"><b><?php echo strtoupper($v_unit['nama_unit']); ?></b></td>
        </tr>
        <?php foreach ($v_unit['mitra'] as $k_mtr => $v_mtr) { ?>
            <?php $no = 1; ?>
            <?php foreach ($v_mtr['detail'] as $k_det => $v_det) { ?>
                <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo !empty($v_det['kandang']) ? $v_det['kandang'] : '-'; ?></td>
                    <td><?php echo strtoupper($v_mtr['nama']); ?></td>
                    <td>-</td>
                    <!-- <td><?php echo strtoupper(tglIndonesia($v_det['tgl_chick_in'], '-', ' ')); ?></td> -->
                    <td><?php echo substr($v_det['tgl_chick_in'], -2).'/'.substr($v_det['tgl_chick_in'], 5, 2).'/'.substr($v_det['tgl_chick_in'], 0, 4); ?></td>
                    <td><?php echo strtoupper($v_det['barang']); ?></td>
                    <td class="text-right"><?php echo angkaRibuan($v_det['ekor']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['umur']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['deplesi']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['fcr']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['bb']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['ip']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['pdpt_plasma']); ?></td>
                    <td class="text-right"><?php echo angkaDecimal($v_det['pdpt_plasma_per_ekor']); ?></td>
                </tr>
                <?php $no++; ?>
            <?php } ?>
            <tr>
                <td colspan="14"></td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak ditemukan.</td>
    </tr>
<?php } ?>