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
            <td><?php echo $value['kode_eks']; ?></td>
            <td><?php echo $value['nama_eks']; ?></td>
            <td><?php echo $value['no_invoice']; ?></td>
            <td class="text-right pemotongan_pajak" data-val="<?php echo !empty($value['nominal_pajak']) ? $value['nominal_pajak'] : 0; ?>"><?php echo angkaDecimal($value['nominal_pajak']); ?></td>
            <td></td>
            <td class="text-right pemotongan" data-val="<?php echo !empty($value['potongan']) ? $value['potongan'] : 0; ?>"><?php echo angkaDecimal($value['potongan']); ?></td>
            <td>
                <?php 
                    $ket = '';
                    if ( !empty($value['ket_cn']) ) {
                        $ket .= !empty($ket) ? ', '.$value['ket_cn'] : $value['ket_cn'];
                    }

                    if ( !empty($value['ket_potongan']) ) {
                        $ket .= !empty($ket) ? ', '.$value['ket_potongan'] : $value['ket_potongan'];
                    }

                    if ( !empty($value['ket_materai']) ) {
                        $ket .= !empty($ket) ? ', '.$value['ket_materai'] : $value['ket_materai'];
                    }

                    echo strtoupper( $ket );
                ?>
            </td>
            <td><?php echo $value['keterangan']; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="13">Data tidak ditemukan.</td>
    </tr>
<?php } ?>