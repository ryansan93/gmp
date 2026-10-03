<?php
if (isset($data[0]) && is_array($data[0])) {
    $data = $data[0];
}
?>

<style>
    .detail-wrap {
        max-width: 960px;
        margin: 0 auto;
        font-family: Arial, sans-serif;
    }

    .detail-wrap .detail-data {
        font-size: 13px;
    }

    .detail-wrap .detail-grid {
        display: flex;
        flex-wrap: wrap;
    }

    .detail-wrap .detail-col {
        width: 50%;
        box-sizing: border-box;
        padding-right: 15px;
    }

    .detail-wrap .detail-col:nth-child(even) {
        padding-right: 0;
        padding-left: 15px;
    }

    .detail-wrap .detail-row {
        display: flex;
        align-items: center;
        min-height: 38px;
        padding: 6px 0;
    }

    .detail-wrap .detail-label {
        width: 180px; 
        font-weight: bold;
        color: #555;
        flex-shrink: 0;
    }

    .detail-wrap .detail-value {
        color: #141414;
        flex-grow: 1;
    }

    .text-muted {
        color: #999;
    }

    .doc-preview-box {
        padding: 15px;
        background: #f9f9f9;
        border: 1px dashed #ccc;
        border-radius: 4px;
        text-align: center;
    }
</style>

<?php if(isset($data) && !empty($data)): ?>
<div class="detail-wrap">
  
    <fieldset>
        <legend>Detail Penerimaan Aset</legend>
    
        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Kode Penerimaan</div>
                    <div class="detail-value">: <strong><?= isset($data['kode_penerimaan']) ? $data['kode_penerimaan'] : '-' ?></strong></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Kode Aset</div>
                    <div class="detail-value">: <?= isset($data['kode_aset']) ? $data['kode_aset'] : '-' ?></div>
                </div>
            </div>
        </div>
        
        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">No. Bukti Pembelian</div>
                    <div class="detail-value">: <?= isset($data['bukti_pembelian']) ? $data['bukti_pembelian'] : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Nilai Perolehan</div>
                    <div class="detail-value">: Rp. <?= isset($data['nilai_perolehan']) ? number_format($data['nilai_perolehan'], 0, ',', '.') : '-' ?></div>
                </div>
            </div>
            
        </div>

        <div class="detail-data detail-grid">            
           
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Tgl. Penerimaan</div>
                    <div class="detail-value">: <?= isset($data['tgl_penerimaan']) ? tglIndonesia($data['tgl_penerimaan'], '-', ' ') : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Tgl. Perolehan</div>
                    <div class="detail-value">: <?= isset($data['tgl_perolehan']) ? tglIndonesia($data['tgl_perolehan'], '-', ' ') : '-' ?></div>
                </div>
            </div>
        </div>

        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Lokasi Digunakan</div>
                    <div class="detail-value">: <?= isset($data['nama_wilayah']) ? $data['nama_wilayah'] : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">PIC Penerima</div>
                    <div class="detail-value">: <?= isset($data['nama_karyawan']) ? $data['nama_karyawan'] : '-' ?></div>
                </div>
            </div>
        </div>

        <hr style="margin: 20px 0; border-top: 1px dashed #ccc;">

        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">No. Bukti Terima</div>
                    <div class="detail-value">: <?= isset($data['no_bukti_terima']) ? $data['nama_wilayah'] : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-value">: <?= isset($data['keterangan_terima']) ? $data['keterangan_terima'] : '-' ?></div>
                </div>
            </div>
        </div>

        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row" style="align-items: flex-start; flex-direction: column;">
                    <div class="detail-label" style="width: 100%; margin-bottom: 8px;">Dokumen Pembelian</div>
                    <div class="detail-value" style="width: 100%;">
                        <?php if (!empty($data['file_pembelian'])): ?>
                            <img src="<?php echo base_url('uploads/aset/' . $data['file_pembelian']); ?>" 
                                 alt="Dokumen Pembelian" 
                                 style="max-width: 100%; max-height: 250px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: block; margin: 0 auto;"
                                 onclick="window.open(this.src, '_blank')">
                            <div style="margin-top: 8px; font-size: 12px; color: #666; text-align: center;">
                                <i class="fa fa-calendar"></i> Tgl. Perolehan: 
                                <strong><?= isset($data['tgl_perolehan']) ? tglIndonesia($data['tgl_perolehan'], '-', ' ') : '-' ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="doc-preview-box">
                                <i class="fa fa-image" style="font-size: 32px; color: #ccc;"></i>
                                <div style="margin-top: 8px; color: #999; font-size: 12px;">Tidak ada dokumen</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row" style="align-items: flex-start; flex-direction: column;">
                    <div class="detail-label" style="width: 100%; margin-bottom: 8px;">Dokumen Penerimaan (BAST)</div>
                    <div class="detail-value" style="width: 100%;">
                        <?php if (!empty($data['file_penerimaan'])): ?>
                            <img src="<?php echo base_url('uploads/penerimaan_aset/' . $data['file_penerimaan']); ?>" 
                                 alt="Dokumen Penerimaan" 
                                 style="max-width: 100%; max-height: 250px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: block; margin: 0 auto;"
                                 onclick="window.open(this.src, '_blank')">
                            <div style="margin-top: 8px; font-size: 12px; color: #666; text-align: center;">
                                <i class="fa fa-calendar"></i> Tgl. Penerimaan: 
                                <strong><?= isset($data['tgl_penerimaan']) ? tglIndonesia($data['tgl_penerimaan'], '-', ' ') : '-' ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="doc-preview-box">
                                <i class="fa fa-image" style="font-size: 32px; color: #ccc;"></i>
                                <div style="margin-top: 8px; color: #999; font-size: 12px;">Tidak ada dokumen</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </fieldset>

</div>
<?php else: ?>
    <p class="text-center text-muted" style="padding: 20px;">Data tidak tersedia.</p>
<?php endif; ?>