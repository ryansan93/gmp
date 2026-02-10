<?php if ( !empty($data) && count($data) > 0 ): ?>
    <?php
        $unit = null;
        $no_inv = null;
    ?>
	<?php foreach ($data as $k_data => $v_data) { ?>
        <?php if ( $v_data['unit'] <> $unit ) { ?>
            <?php $unit = $v_data['unit']; ?>
            <tr>
                <td colspan="10" class="unit"><b><?php echo $v_data['nama_unit']; ?></b></td>
            </tr>
        <?php } ?>
        <tr>
            <?php if ( $v_data['no_inv'] <> $no_inv ) { ?>
                <?php $no_inv = $v_data['no_inv']; ?>
                <td><?php echo tglIndonesia($v_data['tgl_panen'], '-', ' '); ?></td>
                <td><?php echo $v_data['nama_pelanggan']; ?></td>
                <td><?php echo $v_data['nama_mitra'].' - '.$v_data['noreg']; ?></td>
                <td>
                    <a href="report/LaporanPenjualanAyam/printPreview/<?php echo exEncrypt($no_inv); ?>" title="Klik untuk mencetak invoice" target="_blank"><?php echo $no_inv; ?></a>
                </td>
            <?php } else { ?>
                <td colspan="4" class="kosong"></td>
            <?php } ?>
            <td><?php echo $v_data['no_nota']; ?></td>
            <td><?php echo $this->config->item('jenis_ayam')[$v_data['jenis_ayam']]; ?></td>
            <td class="text-right"><?php echo angkaRibuan($v_data['ekor']); ?></td>
            <td class="text-right"><?php echo angkaDecimal($v_data['tonase']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($v_data['harga']); ?></td>
            <!-- <td></td> -->
        </tr>
    <?php } ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>
