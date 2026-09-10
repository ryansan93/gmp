<?php if ( !empty($list) ) : ?>
    <?php
        $grouped = [];
        foreach ( $list as $row ) {
            $supplier = isset($row['nama_supplier']) && trim($row['nama_supplier']) !== '' ? trim($row['nama_supplier']) : '-';
            $grouped[$supplier][] = $row;
        }
    ?>

    <?php foreach ( $grouped as $supplier => $rows ) : ?>
        <tr>
            <td colspan="11" class="text-left" style="font-weight:bold; background:#f5f5f5;">
                 <?php echo $supplier; ?>
            </td>
        </tr>

        <?php foreach ( $rows as $key => $row ) : ?>
            <tr class="tr_loop">
                <td class="text-center"><?php echo $key + 1; ?></td>
                <td class="text-center"> <a href="javascript:void(0);" onclick="ms.show_detail(this)" data-id="<?php echo $row['id']; ?>"><?php echo isset($row['no_sewa']) ? $row['no_sewa'] : '-'; ?></a></td>
                <td class="text-center"><?php echo isset($row['no_kontrak']) ? $row['no_kontrak'] : '-'; ?></td>
                <td class="text-center"><?php echo isset($row['nama_sewa']) ? $row['nama_sewa'] : '-'; ?></td>
                <td class="text-center"><?php echo isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : $row['nama_sewa'] ; ?></td>
                <td class="text-center"><?php echo isset($row['jumlah_bulan']) ? $row['jumlah_bulan'] : '-'; ?></td>
                <td class="text-center"><?php echo isset($row['jumlah_siklus']) ? $row['jumlah_siklus'] : '-'; ?></td>
                <td class="text-center"><?php echo isset($row['tanggal_mulai']) ? tglIndonesia($row['tanggal_mulai'], '-', ' ') : '-'; ?></td>
                <td class="text-right"><?php echo isset($row['nominal_sewa']) ? number_format($row['nominal_sewa'], 0, ',', '.') : '0'; ?></td>
                <td class="text-center" style="width:70px; white-space:nowrap;">
                    <button type="button" class="btn btn-sm btn-warning" data-id="<?php echo $row['id']; ?>" onclick="ms.edit_form(this)">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <?php if ( $akses['a_delete'] == 1 ) { ?>
                        <button type="button" class="btn btn-sm btn-danger" data-id="<?php echo $row['id']; ?>" onclick="ms.delete_data(this)">
                            <i class="fa fa-trash"></i>
                        </button>
                    <?php } ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="11" class="text-center">Tidak ada data tersedia</td>
    </tr>
<?php endif; ?>

