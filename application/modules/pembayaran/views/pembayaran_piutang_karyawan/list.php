<?php if ( !empty( $data ) && count( $data ) > 0 ) : ?>
    <?php foreach( $data as $key => $value ) : ?>
        <tr class="cursor-p" data-id="<?php echo $value['id']; ?>" data-href="action" data-edit="" onclick="ppk.changeTabActive(this)">
            <td class="text-center"><?php echo strtoupper( tglIndonesia($value['tanggal'], '-', '/').' | '.$value['piutang_kode'] ); ?></td>
            <td class="text-center"><?php echo strtoupper( tglIndonesia($value['tanggal'], '-', ' ') ); ?></td>
            <td class="text-left"><?php echo strtoupper( $value['nama_karyawan'] ); ?></td>
            <td class="text-left"><?php echo strtoupper( $value['nama_perusahaan'] ); ?></td>
            <td class="text-right"><?php echo strtoupper( angkaDecimal($value['nominal']) ); ?></td>
        </tr>
    <?php endforeach ?>
<?php else : ?>
    <tr>
        <td colspan="5">Data tidak ditemukan.</td>
    </tr>
<?php endif ?>