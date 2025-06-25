<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php
            $color = $value['status'] == 0 ? 'red' : null;
        ?>
        <tr class="data <?php echo $color; ?>">
            <td><?php echo $value['do']; ?></td>
            <td><?php echo $value['kode_unit']; ?></td>
            <td><?php echo $value['bank'].' - '.$value['rekening']; ?></td>
            <td><?php echo $value['no_bukti']; ?></td>
            <td class="text-center"><?php echo tglIndonesia($value['tgl_bayar'], '-', ' '); ?></td>
            <td class="text-right transfer" data-val="<?php echo !empty($value['transfer']) ? $value['transfer'] : 0; ?>"><?php echo angkaDecimal($value['transfer']); ?></td>
            <td><?php echo $value['kode_plg']; ?></td>
            <td><?php echo $value['nama_plg']; ?></td>
            <td><?php echo $value['nik']; ?></td>
            <td><?php echo $value['npwp']; ?></td>
            <td><?php echo $value['no_invoice']; ?></td>
            <td class="text-right pemotongan" data-val="<?php echo !empty($value['potongan']) ? $value['potongan'] : 0; ?>"><?php echo angkaDecimal($value['potongan']); ?></td>
            <td class="text-right um" data-val="0">0</td>
            <td></td>
            <td><?php echo $value['keterangan']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="13">Data tidak ditemukan.</td>
    </tr>
<?php } ?>