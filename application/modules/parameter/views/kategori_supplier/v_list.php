<?php if ( !empty($list) ) : ?>
    <?php foreach ( $list as $key => $row ) : ?>
        <tr>
            <td><?php echo $key + 1; ?></td>
            <td><?php echo isset($row['kode_kategori']) ? $row['kode_kategori'] : '-'; ?></td>
            <td><?php echo isset($row['nama_kategori']) ? $row['nama_kategori'] : '-'; ?></td>
            <td class="text-center" style="width:70px; white-space:nowrap;">
                <?php if ( $akses['a_edit'] == 1 ) { ?>
                    <button type="button" class="btn btn-xs btn-warning" data-id="<?php echo $row['id']; ?>" onclick="ks.edit_form(this)">
                        <i class="fa fa-pencil"></i>
                    </button>
                <?php } ?>

                <?php if ( $akses['a_delete'] == 1 ) { ?>
                    <button type="button" class="btn btn-xs btn-danger" data-id="<?php echo $row['id']; ?>" onclick="ks.delete_data(this)">
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
