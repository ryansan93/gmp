<?php if ( !empty($list) ) : ?>
    <?php foreach ( $list as $key => $row ) : ?>
        <tr>
            <td><?php echo $key + 1; ?></td>
            <td><?php echo isset($row['kode_jenis_sewa']) ? $row['kode_jenis_sewa'] : '-'; ?></td>
            <td><?php echo isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : '-'; ?></td>
            <!-- <td class="keterangan-cell" title="< ?php echo isset($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-'; ?>">
                < ?php echo isset($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-'; ?>
            </td> -->
            <td class="text-center" style="width:70px; white-space:nowrap;">
                <?php if ( $akses['a_edit'] == 1 ) { ?>
                    <button type="button" class="btn btn-sm btn-warning" data-id="<?php echo $row['id']; ?>" onclick="mjs.edit_form(this)">
                        <i class="fa fa-pencil"></i>
                    </button>
                <?php } ?>

                <?php if ( $akses['a_delete'] == 1 ) { ?>
                    <button type="button" class="btn btn-sm btn-danger" data-id="<?php echo $row['id']; ?>" onclick="mjs.delete_data(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                <?php } ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="4" class="text-center">Tidak ada data tersedia</td>
    </tr>
<?php endif; ?>
