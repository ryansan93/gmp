<?php if ( !empty($data) && count($data) > 0 ) : ?>
    <?php foreach ( $data as $key => $value ) : ?>
        <tr>
            <td class="text-left">
                <?php echo strtoupper($value['nama_perusahaan']); ?>
            </td>
            <td class="text-left">
                <?php echo strtoupper($value['nama_unit']); ?>
            </td>
            <td class="text-right">
                <?php echo angkaRibuan($value['nominal']); ?>
            </td>
            <td class="text-center">
                <?php echo $value['jangka_waktu']; ?>
            </td>
            <td class="text-left">
                <?php echo strtoupper(tglIndonesia($value['mulai'], '-', ' ')); ?>
            </td>
            <td class="text-left">
                <?php echo strtoupper(tglIndonesia($value['akhir'], '-', ' ')); ?>
            </td>
            <td class="text-left">
                <?php echo strtoupper($value['deskripsi'].' '.substr($value['waktu'], 0, 16)); ?>
            </td>
        </tr>
    <?php endforeach ?>
<?php else : ?>
    <tr>
        <td colspan="7">Data tidak ditemukan.</td>
    </tr>
<?php endif ?>