<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php
            $ket = $value['ket'];
            $tgl_order = $value['tgl_order'];
            $pakan = $value['pakan'];
            $supplier = $value['supplier'];
            $harga = $value['harga'];
        ?>
        <tr>
            <td><?php echo strtoupper($ket); ?></td>
            <td class="text-center"><?php echo strtoupper(tglIndonesia($tgl_order, '-', ' ')); ?></td>
            <td><?php echo strtoupper($supplier); ?></td>
            <td><?php echo strtoupper($pakan); ?></td>
            <td class="text-right"><?php echo strtoupper(angkaRibuan($harga)); ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="4">Data tidak ditemukan.</td>
    </tr>
<?php } ?>