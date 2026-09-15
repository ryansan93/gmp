<?php if ( !empty($list) ) : ?>
    <?php foreach ( $list as $key => $row ) : ?>
        <tr>
            <td class="text-center">
                <input type="checkbox" class="chk-row" value="<?php echo $row['id']; ?>">
            </td>
            <td class="text-center"><?php echo $key + 1; ?></td>
            <td class="text-center"><?php echo isset($row['kode_transaksi']) ? $row['kode_transaksi'] : '-'; ?></td>
            <td class="text-left"><?php echo isset($row['nama_sewa']) ? $row['nama_sewa'] : '-'; ?></td>
            <td class="text-center"><?php echo isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : '-'; ?></td>
            <td class="text-center"><?php echo isset($row['kode_amortisasi']) ? $row['kode_amortisasi'] : '-'; ?></td>
            <td class="text-right"><?php echo isset($row['nilai']) ? number_format($row['nilai'], 0, ',', '.') : '0'; ?></td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="7" class="text-center">Tidak ada data amortisasi yang perlu diproses</td>
    </tr>
<?php endif; ?>
