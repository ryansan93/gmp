<?php if ( !empty( $data ) && count( $data ) > 0 ) : ?>
    <?php foreach( $data as $key => $value ) : ?>
        <tr class="cursor-p" data-periode="<?php echo $value['periode']; ?>" data-perusahaan="<?php echo $value['perusahaan']; ?>" data-href="action" data-edit="" onclick="gk.changeTabActive(this)">
            <td class="text-left"><?php echo strtoupper( $value['bulan'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['tot_gaji'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['bpjs_karyawan'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['pot_hutang'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['pph21'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['jml_transfer'] ); ?></td>
            <td class="text-right"><?php echo angkaDecimal( $value['bpjs_perusahaan'] ); ?></td>
            <td class="text-left"><?php echo strtoupper( tglIndonesia($value['tgl_transfer'], '-', ' ') ); ?></td>
        </tr>
    <?php endforeach ?>
<?php else : ?>
    <tr>
        <td colspan="8">Data tidak ditemukan.</td>
    </tr>
<?php endif ?>