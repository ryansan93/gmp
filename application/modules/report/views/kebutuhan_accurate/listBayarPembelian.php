<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php 
        $rowspan = 1; 
        $id_old = null;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php
            $color = $value['status'] == 0 ? 'red' : null;

            if ( $value['id'] != $id_old ) {
                $rowspan = array_count_values(array_column($data, 'id'))[$value['id']];
            }

        ?>
        <tr class="data <?php echo $color; ?>" data-id="<?php echo $value['id'] ?>" data-row="<?php echo array_count_values(array_column($data, 'id'))[$value['id']] ?>">
            <td><?php echo $value['do']; ?></td>
            <td><?php echo $value['kode_unit']; ?></td>
            <?php if ( $value['id'] != $id_old ) { ?>
                <td rowspan="<?php echo $rowspan; ?>"><?php echo $value['bank'].' - '.$value['rekening']; ?></td>
                <td rowspan="<?php echo $rowspan; ?>"><?php echo $value['no_bukti']; ?></td>
                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo tglIndonesia($value['tgl_bayar'], '-', ' '); ?></td>
                <td rowspan="<?php echo $rowspan; ?>" class="text-right transfer" data-val="<?php echo !empty($value['transfer']) ? $value['transfer'] : 0; ?>"><?php echo angkaDecimal($value['transfer']); ?></td>
                <td rowspan="<?php echo $rowspan; ?>"><?php echo $value['kode_supl']; ?></td>
                <td rowspan="<?php echo $rowspan; ?>"><?php echo $value['nama_supl']; ?></td>
                <td rowspan="<?php echo $rowspan; ?>"><?php echo $value['jenis']; ?></td>
            <?php } ?>
            <td><?php echo $value['no_invoice']; ?></td>
            <td class="text-right tagihan" data-val="<?php echo !empty($value['sisa_tagihan']) ? $value['sisa_tagihan'] : 0; ?>"><?php echo angkaDecimal($value['sisa_tagihan']); ?></td>
            <td class="text-right bayar" data-val="<?php echo !empty($value['bayar']) ? $value['bayar'] : 0; ?>"><?php echo angkaDecimal($value['bayar']); ?></td>
            <?php if ( $value['id'] != $id_old ) { ?>
                <td rowspan="<?php echo $rowspan; ?>" class="text-right pemotongan" data-val="<?php echo !empty($value['potongan']) ? $value['potongan'] : 0; ?>"><?php echo angkaDecimal($value['potongan']); ?></td>
                <td rowspan="<?php echo $rowspan; ?>" class="text-right um" data-val="0">0</td>
                <td rowspan="<?php echo $rowspan; ?>"></td>
                <td rowspan="<?php echo $rowspan; ?>"></td>
            <?php } ?>
        </tr>
        <?php $id_old = $value['id']; ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="16">Data tidak ditemukan.</td>
    </tr>
<?php } ?>