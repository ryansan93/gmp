<?php if ( !empty( $data ) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper(tglIndonesia($value['start_date'], '-', ' ').' - '.tglIndonesia($value['end_date'], '-', ' ')); ?></td>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo $value['kode_unit']; ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['prev_jumlah_box_est']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['prev_jumlah_box_real']); ?></td>
            <td class="text-right <?php echo ($value['selisih_box_prev'] < 0) ? 'red' : ''; ?>"><?php echo angkaRibuan($value['selisih_box_prev']); ?></td>
            <td class="text-right border-right-thick <?php echo ($value['persentase_prev'] < 0) ? 'red' : ''; ?>"><?php echo angkaDecimal($value['persentase_prev']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['jumlah_box_est']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['jumlah_box_real']); ?></td>
            <td class="text-right <?php echo ($value['selisih_box'] < 0) ? 'red' : ''; ?>"><?php echo angkaRibuan($value['selisih_box']); ?></td>
            <td class="text-right border-right-thick <?php echo ($value['persentase'] < 0) ? 'red' : ''; ?>"><?php echo angkaDecimal($value['persentase']); ?></td>
            <td class="text-right <?php echo ($value['box_est_prev_with_now'] < 0) ? 'red' : ''; ?>"><?php echo angkaRibuan($value['box_est_prev_with_now']); ?></td>
            <td class="text-right <?php echo ($value['persentase_est_prev_with_now'] < 0) ? 'red' : ''; ?>"><?php echo angkaDecimal($value['persentase_est_prev_with_now']); ?></td>
            <td class="text-right <?php echo ($value['box_real_prev_with_now'] < 0) ? 'red' : ''; ?>"><?php echo angkaRibuan($value['box_real_prev_with_now']); ?></td>
            <td class="text-right <?php echo ($value['persentase_real_prev_with_now'] < 0) ? 'red' : ''; ?>"><?php echo angkaDecimal($value['persentase_real_prev_with_now']); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="15">Data tidak ditemukan.</td>
    </tr>
<?php } ?>