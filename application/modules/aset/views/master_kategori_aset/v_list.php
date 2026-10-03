<style>
    #table-kategori-aset th,
    #table-kategori-aset td {
        white-space: nowrap;
    }

    .group-header {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 14px;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
    }
</style>

<table class="table table-bordered" id="table-kategori-aset">
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th class="text-center">Nama Kategori</th>
            <th class="text-center">Masa Manfaat Komersial</th>
            <th class="text-center">Masa Manfaat Fiskal</th>
            <th class="text-center">Tarif Garis Lurus (%)</th>
            <th class="text-center">Tarif Saldo Menurun (%)</th>
            <th class="text-center" style="width: 120px;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($list)) : ?>
            <?php 
            $prev_kategori_kode = null; 
            $no = 1; 
            ?>
            
            <?php foreach ($list as $row) : 
                $is_used = isset($row['is_used']) ? (int)$row['is_used'] : 0;
                
                // --- 1. LOGIKA MASA MANFAAT KOMERSIAL ---
                $masa_komersial_bulan = isset($row['masa_manfaat_komersial']) ? (int)$row['masa_manfaat_komersial'] : 0;
                $masa_komersial_display = '-';
                if ($masa_komersial_bulan > 0) {
                    $tahun_k = floor($masa_komersial_bulan / 12);
                    $sisa_k  = $masa_komersial_bulan % 12;
                    
                    // Detail bulan selalu ditampilkan
                    $detail_k = '<br><small class="text-muted">(' . $masa_komersial_bulan . ' bln)</small>';
                    
                    if ($sisa_k == 0) {
                        $masa_komersial_display = $tahun_k . ' Tahun' . $detail_k;
                    } elseif ($tahun_k > 0) {
                        $masa_komersial_display = $tahun_k . ' Tahun ' . $sisa_k . ' Bulan' . $detail_k;
                    } else {
                        $masa_komersial_display = $masa_komersial_bulan . ' Bulan' . $detail_k;
                    }
                }

                // --- 2. LOGIKA MASA MANFAAT FISKAL ---
                $masa_fiskal_bulan = isset($row['masa_manfaat_fiskal']) ? (int)$row['masa_manfaat_fiskal'] : 0;
                $masa_fiskal_display = '-';
                if ($masa_fiskal_bulan > 0) {
                    $tahun_f = floor($masa_fiskal_bulan / 12);
                    $sisa_f  = $masa_fiskal_bulan % 12;
                    
                    // Detail bulan selalu ditampilkan
                    $detail_f = '<br><small class="text-muted">(' . $masa_fiskal_bulan . ' bln)</small>';
                    
                    if ($sisa_f == 0) {
                        $masa_fiskal_display = $tahun_f . ' Tahun' . $detail_f;
                    } elseif ($tahun_f > 0) {
                        $masa_fiskal_display = $tahun_f . ' Tahun ' . $sisa_f . ' Bulan' . $detail_f;
                    } else {
                        $masa_fiskal_display = $masa_fiskal_bulan . ' Bulan' . $detail_f;
                    }
                }
                
                $trf_garis_lurus   = isset($row['trf_garis_lurus']) ? (float)$row['trf_garis_lurus'] : 0;
                $trf_saldo_menurun = isset($row['trf_saldo_menurun']) ? (float)$row['trf_saldo_menurun'] : 0;
                
                $current_kode = isset($row['kategori_kode']) ? $row['kategori_kode'] : '-';
            ?>
    
                <!-- HEADER GROUPING PER KODE KATEGORI -->
                <?php if ($prev_kategori_kode !== $current_kode): ?>
                    <tr>
                        <td colspan="7" class="group-header text-left">
                            <i class="fa fa-folder-open"></i> Kode Kategori: <?php echo htmlspecialchars($current_kode); ?>
                        </td>
                    </tr>
                    <?php 
                    $prev_kategori_kode = $current_kode; 
                    $no = 1; 
                    ?>
                <?php endif; ?>

                <!-- BARIS DATA -->
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td>
                        <?php echo isset($row['kategori_name']) ? htmlspecialchars($row['kategori_name']) : '-'; ?>
                    </td>
                    
                    <td class="text-center">
                        <?php echo $masa_komersial_display; ?>
                    </td>
                    
                    <td class="text-center">
                        <?php echo $masa_fiskal_display; ?>
                    </td>
                    
                    <td class="text-center">
                        <?php echo $trf_garis_lurus > 0 ? number_format($trf_garis_lurus, 2) . ' %' : '-'; ?>
                    </td>
                    
                    <td class="text-center">
                        <?php echo $trf_saldo_menurun > 0 ? number_format($trf_saldo_menurun, 2) . ' %' : '-'; ?>
                    </td>
                    
                    <td class="text-center" style="width: 120px;">
                        <?php if ($is_used == 1): ?>
                            <button type="button" class="btn btn-sm btn-warning" disabled title="Data sudah digunakan">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" disabled title="Data sudah digunakan">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <?php if (isset($akses['a_edit']) && $akses['a_edit'] == 1): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-id="<?php echo $row['id']; ?>" onclick="mka.edit_form(this)" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            <?php endif; ?>

                            <?php if (isset($akses['a_delete']) && $akses['a_delete'] == 1): ?>
                                <button type="button" class="btn btn-sm btn-danger" data-id="<?php echo $row['id']; ?>" onclick="mka.delete_data(this)" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding: 20px;">
                    <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                    Tidak ada data tersedia
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>