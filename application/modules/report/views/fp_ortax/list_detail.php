<?php if ( !empty($invoices) && count($invoices) > 0 ) { ?>
    <?php foreach ($invoices as $inv) { ?>
        <?php foreach ($inv['details'] as $det) { ?>
            <?php
                $harga = (float)$det['harga_per_satuan_kuantitas'];
                $kuantitas = (float)$det['kuantitas'];
                $dpp = $harga * $kuantitas;
                $dpp_nilai_lain = $dpp * 11 / 12;
                $ppn = round($dpp_nilai_lain * 0.12);
            ?>
            <tr class="data">
                <td class="text-center"><?php echo $inv['baris']; ?></td>
                <td class="text-center">A</td>
                <td class="text-center">000000</td>
                <td>AYAM <?php echo $det['deskripsi_barang']; ?></td>
                <td class="text-center">UM.0033</td>
                <td class="text-right number_format"><?php echo angkaRibuan($harga); ?></td>
                <td class="text-right number_format jumlah" data-val="<?php echo $kuantitas; ?>"><?php echo angkaDecimal($kuantitas); ?></td>
                <td class="text-right number_format">0</td>
                <td class="text-right number_format dpp" data-val="<?php echo $dpp; ?>"><?php echo angkaRibuan($dpp); ?></td>
                <td class="text-right number_format dppnl" data-val="<?php echo $dpp_nilai_lain; ?>"><?php echo angkaRibuan($dpp_nilai_lain); ?></td>
                <td class="text-center">12</td>
                <td class="text-right number_format ppn" data-val="<?php echo $ppn; ?>"><?php echo angkaRibuan($ppn); ?></td>
                <td class="text-center">0</td>
                <td class="text-right number_format">0</td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="14">Data tidak ditemukan.</td>
    </tr>
<?php } ?>
