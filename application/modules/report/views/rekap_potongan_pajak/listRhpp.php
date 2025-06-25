<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
            <?php 
                $class_pdpt_peternak_belum_pajak = '';
                $pdpt_peternak_belum_pajak = null;
                if ($v_det['pdpt_peternak_belum_pajak'] > 0) {
                    $pdpt_peternak_belum_pajak = angkaDecimal($v_det['pdpt_peternak_belum_pajak']);
                } else {
                    $class_pdpt_peternak_belum_pajak = 'red';
                    $pdpt_peternak_belum_pajak = '('.angkaDecimal(abs($v_det['pdpt_peternak_belum_pajak'])).')';
                }

                $class_pdpt_peternak_sudah_pajak = '';
                $pdpt_peternak_sudah_pajak = null;
                if ($v_det['pdpt_peternak_sudah_pajak'] > 0) {
                    $pdpt_peternak_sudah_pajak = angkaDecimal($v_det['pdpt_peternak_sudah_pajak']);
                } else {
                    $class_pdpt_peternak_sudah_pajak = 'red';
                    $pdpt_peternak_sudah_pajak = '('.angkaDecimal(abs($v_det['pdpt_peternak_sudah_pajak'])).')';
                }
            ?>
            <tr class="cursor-p data">
                <td><?php echo strtoupper($value['perusahaan']); ?></td>
                <td><?php echo strtoupper($v_det['no_mitra']); ?></td>
                <td><?php echo strtoupper($v_det['mitra']); ?></td>
                <td><?php echo strtoupper($v_det['kandang']); ?></td>
                <td><?php echo strtoupper($v_det['ktp']); ?></td>
                <td><?php echo strtoupper($v_det['npwp']); ?></td>
                <td><?php echo strtoupper($v_det['alamat']); ?></td>
                <td><?php echo strtoupper($v_det['kab_kota']); ?></td>
                <td><?php echo strtoupper($v_det['prov']); ?></td>
                <td><?php echo $v_det['no_telp']; ?></td>
                <td class="text-right"><?php echo !empty($v_det['invoice']) ? $v_det['invoice'] : '-'; ?></td>
                <td class="text-right pendapatan <?php echo $class_pdpt_peternak_belum_pajak; ?>" data-val="<?php echo $v_det['pdpt_peternak_belum_pajak']; ?>"><?php echo $pdpt_peternak_belum_pajak; ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['prs_potongan_pajak']); ?></td>
                <td class="text-right pot_pajak" data-val="<?php echo $v_det['potongan_pajak']; ?>"><?php echo angkaDecimal($v_det['potongan_pajak']); ?></td>
                <td class="text-right pend_stlh_pajak <?php echo $class_pdpt_peternak_sudah_pajak; ?>" data-val="<?php echo $v_det['pdpt_peternak_sudah_pajak']; ?>"><?php echo $pdpt_peternak_sudah_pajak; ?></td>
                <td class="text-right transfer" data-val="<?php echo $v_det['transfer']; ?>"><?php echo angkaDecimal($v_det['transfer']); ?></td>
                <td><?php echo strtoupper($value['unit']); ?></td>
                <td><?php echo strtoupper(tglIndonesia($v_det['tgl_rhpp'], '-', ' ')); ?></td>
                <td><?php echo !empty($v_det['no_skb']) ? strtoupper($v_det['no_skb']) : '-'; ?></td>
                <td><?php echo !empty($v_det['tgl_habis_skb']) ? strtoupper(tglIndonesia($v_det['tgl_habis_skb'], '-', ' ')) : '-'; ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="19">Data tidak ditemukan.</td>
    </tr>
<?php } ?>