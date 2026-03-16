<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p" onclick="rdim.viewFormPembatalan(this)" data-noreg="<?php echo $value['noreg']; ?>">
            <td><?php echo tglIndonesia($value['mulai'], '-', ' ').' - '.tglIndonesia($value['selesai'], '-', ' '); ?></td>
            <td><?php echo strtoupper($value['nama_mitra']); ?></td>
            <td><?php echo $value['noreg_view']; ?></td>
            <td>-</td>
            <td><?php echo $value['alasan_batal']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="5">pilih periode untuk menampilkan data</td>
    </tr>
<?php } ?>