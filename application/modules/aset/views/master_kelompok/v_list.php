<style>
    #table-kelompok-aset th,
    #table-kelompok-aset td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .btn-disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<table class="table table-bordered table-hover" id="table-kelompok-aset">
    <thead>
        <tr>
            <th class="text-center" style="width: 50px;">No</th>
            <th class="text-center">Nama Kelompok</th>
            <th class="text-center" style="width: 120px;">Umur Ekonomis<br>(Tahun)</th>
            <th>Deskripsi</th>
            <th class="text-center" style="width: 120px;">Tarif Garis Lurus<br>(%)</th>
            <th class="text-center" style="width: 130px;">Tarif Saldo Menurun<br>(%)</th>
            <th class="text-center" style="width: 100px;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($list)) : ?>
            <?php foreach ($list as $key => $row) : 
                $nama_kelompok     = isset($row['nama_kelompok']) ? $row['nama_kelompok'] : (isset($row->nama_kelompok) ? $row->nama_kelompok : '');
                $umur_ekonomis     = isset($row['umur_ekonomis']) ? (int)$row['umur_ekonomis'] : (isset($row->umur_ekonomis) ? (int)$row->umur_ekonomis : 0);
                $deskripsi         = isset($row['deskripsi']) ? $row['deskripsi'] : (isset($row->deskripsi) ? $row->deskripsi : '');
                $trf_garis_lurus   = isset($row['trf_garis_lurus']) ? (float)$row['trf_garis_lurus'] : (isset($row->trf_garis_lurus) ? (float)$row->trf_garis_lurus : 0);
                $trf_saldo_menurun = isset($row['trf_saldo_menurun']) ? (float)$row['trf_saldo_menurun'] : (isset($row->trf_saldo_menurun) ? (float)$row->trf_saldo_menurun : 0);
                $id                = isset($row['id']) ? $row['id'] : (isset($row->id) ? $row->id : '');
                $is_used           = isset($row['is_used']) ? $row['is_used'] : false;
            ?>
                <tr>
                    <td class="text-center"><?php echo $key + 1; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($nama_kelompok); ?></strong>
                        <?php if ($is_used) : ?>
                            <span class="label label-warning" style="margin-left:5px;" title="Sudah dipakai di Kategori Aset">
                                <i class="fa fa-lock"></i> Terpakai
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $umur_ekonomis > 0 ? $umur_ekonomis . ' Tahun' : '-'; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($deskripsi) ?: '<span class="text-muted">-</span>'; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $trf_garis_lurus > 0 ? number_format($trf_garis_lurus, 2) . ' %' : '-'; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $trf_saldo_menurun > 0 ? number_format($trf_saldo_menurun, 2) . ' %' : '-'; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($is_used) : ?>
                            <button type="button" class="btn btn-sm btn-warning btn-disabled" 
                                    title="Tidak bisa diedit karena sudah dipakai di Kategori Aset">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-disabled" 
                                    title="Tidak bisa dihapus karena sudah dipakai di Kategori Aset">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-id="<?php echo $id; ?>" 
                                    onclick="mk.edit_form(this)" 
                                    title="Edit">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-id="<?php echo $id; ?>" 
                                    onclick="mk.delete_data(this)" 
                                    title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada data tersedia</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>