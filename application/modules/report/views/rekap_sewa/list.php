<?php if ( !empty($data) ) : ?>
    <?php foreach ( $data as $key => $row ) : ?>
        <?php
            $status = isset($row['status_kontrak']) ? $row['status_kontrak'] : '-';

            $badgeClass = 'rs-badge-default';
            $rowStatusClass = '';
            if ( $status === 'Aktif' ) {
                $badgeClass = 'rs-badge-aktif';
                $rowStatusClass = 'rs-row-aktif';
            } else if ( $status === 'Selesai' ) {
                $badgeClass = 'rs-badge-selesai';
                $rowStatusClass = 'rs-row-selesai';
            }

            $progressWaktu = isset($row['progress_waktu']) ? (float) $row['progress_waktu'] : 0;
            $progressAmortisasi = isset($row['progress_amortisasi']) ? (float) $row['progress_amortisasi'] : 0;

            $fillWaktuClass = $progressWaktu >= 100 ? 'is-done' : ($progressWaktu >= 60 ? 'is-mid' : '');
            $fillAmortClass = $progressAmortisasi >= 100 ? 'is-done' : ($progressAmortisasi >= 60 ? 'is-mid' : '');
        ?>
        <tr class="tr_loop <?php echo $rowStatusClass; ?>" data-status="<?php echo htmlspecialchars($status); ?>" data-nominal="<?php echo isset($row['nominal_sewa']) ? (float) $row['nominal_sewa'] : 0; ?>">
            <td class="text-center rs-cell-muted"><?php echo $key + 1; ?></td>
            <td class="text-center"><strong><?php echo isset($row['no_sewa']) ? $row['no_sewa'] : '-'; ?></strong></td>
            <td class="text-center rs-cell-muted"><?php echo isset($row['no_kontrak']) ? $row['no_kontrak'] : '-'; ?></td>
            <td class="text-left"><?php echo isset($row['nama_sewa']) ? $row['nama_sewa'] : '-'; ?></td>
            <td class="text-center"><?php echo isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : (isset($row['jenis_sewa']) ? $row['jenis_sewa'] : '-'); ?></td>
            <td class="text-left"><?php echo isset($row['nama_supplier']) ? $row['nama_supplier'] : '-'; ?></td>
            <td class="text-center rs-cell-muted"><?php echo isset($row['tanggal_mulai']) ? tglIndonesia($row['tanggal_mulai'], '-', ' ') : '-'; ?></td>
            <td class="text-center rs-cell-muted"><?php echo !empty($row['tanggal_selesai']) ? tglIndonesia($row['tanggal_selesai'], '-', ' ') : '-'; ?></td>
            <td class="text-center"><?php echo isset($row['durasi']) ? $row['durasi'] : '-'; ?></td>
            <td class="text-center"><?php echo isset($row['bulan_berjalan']) ? $row['bulan_berjalan'] : '-'; ?></td>
            <td class="text-center">
                <div class="rs-progress">
                    <div class="rs-progress-track">
                        <div class="rs-progress-fill <?php echo $fillWaktuClass; ?>" style="width:<?php echo min($progressWaktu, 100); ?>%;"></div>
                    </div>
                    <span class="rs-progress-pct"><?php echo number_format($progressWaktu, 1, ',', '.'); ?>%</span>
                </div>
            </td>
            <td class="text-center">
                <div class="rs-progress">
                    <div class="rs-progress-track">
                        <div class="rs-progress-fill <?php echo $fillAmortClass; ?>" style="width:<?php echo min($progressAmortisasi, 100); ?>%;"></div>
                    </div>
                    <span class="rs-progress-pct"><?php echo number_format($progressAmortisasi, 1, ',', '.'); ?>%</span>
                </div>
            </td>
            <td class="text-center"><span class="rs-badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span></td>
            <td class="text-right rs-num"><?php echo isset($row['nominal_sewa']) ? number_format($row['nominal_sewa'], 0, ',', '.') : '0'; ?></td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="14">
            <div class="rs-empty">
                <i class="fa fa-inbox"></i>
                Tidak ada data tersedia
            </div>
        </td>
    </tr>
<?php endif; ?>
