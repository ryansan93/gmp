<?php
function getBulanTahun($value)
{
    if (empty($value)) {
        return '';
    }
    $date = DateTime::createFromFormat('Y-m', $value);
    if (!$date) {
        return '';
    }
    return $date->format('F Y');
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
        width: 160px; 
        font-weight: bold;
        color: #555;
        flex-shrink: 0;
    }

    .detail-wrap .detail-value {
        color: #141414;
        flex-grow: 1;
    }


    /* Badge Status */
    .badge-status {
        display: inline-block;
        padding: 4px 6px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-done {
        background-color: #d4edda;
        color: #155724;
    }

    .text-muted {
        color: #999;
    }
</style>

<?php if(isset($data) && !empty($data)): ?>
<div class="detail-wrap">
  
    <fieldset>
        <legend>Detail Data Aset</legend>
    
        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Kode Aset</div>
                    <div class="detail-value">: <?= isset($data['kode_aset']) ? $data['kode_aset'] : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">No. Faktur / Bukti</div>
                    <div class="detail-value">: <?= isset($data['document_no']) ? $data['document_no'] : '-' ?></div>
                </div>
            </div>
        </div>
        
        <div class="detail-data">
            <div class="detail-row">
                <div class="detail-label">Jenis Aset</div>
                <div class="detail-value">: <?= isset($data['kategori_name']) ? $data['kategori_name'] : '-' ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Deskripsi Asset</div>
                <div class="detail-value">: <?= isset($data['deskripsi_aset']) ? $data['deskripsi_aset'] : '-' ?></div>
            </div>
        </div>

        <div class="detail-data detail-grid">
            <div class="detail-col">

                <div class="detail-row">
                    <div class="detail-label">Tgl. Perolehan</div>
                    <div class="detail-value">: Rp. <?= isset($data['tgl_perolehan']) ? tglIndonesia($data['tgl_perolehan'], '-' , ' ') : '-' ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Nominal Perolehan</div>
                    <div class="detail-value">: Rp. <?= isset($data['nilai_perolehan']) ? angkaRibuan($data['nilai_perolehan']) : '-' ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Dokumen Attachment</div>
                    <div class="detail-value">: 
                        <?php if (!empty($data['attachment'])): ?>
                            <a href="<?php echo base_url('uploads/aset/' . $data['attachment']); ?>" target="_blank" style="color: #337ab7; text-decoration: none; font-weight: 600;">
                                <i class="fa fa-file-pdf-o"></i> Lihat / Download Dokumen
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">Tidak ada dokumen</span>
                        <?php endif; ?>
                    </div>
                </div>
              
            </div>
        </div>
        
        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Unit Pengguna</div>
                    <div class="detail-value">: <?= isset($data['unit_pengguna']) ? $data['unit_pengguna'] : '-' ?> </div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Pengelompokan</div>
                    <div class="detail-value">: <?= isset($data['nama_kelompok']) ? $data['nama_kelompok'] : '-' ?> </div>
                </div>
            </div>
        </div>

        <?php
            $lastRowKomersial   = !empty($komersial) ? end($komersial) : [];
            $last_date_komersial= !empty($lastRowKomersial['tanggal_jatuh_tempo']) ? tglIndonesia($lastRowKomersial['tanggal_jatuh_tempo'], '-', ' ') : '-';

            $lastRowFiskal      = !empty($fiskal) ? end($fiskal) : [];
            $last_date_fiskal   = !empty($lastRowFiskal['tanggal_jatuh_tempo']) ? tglIndonesia($lastRowFiskal['tanggal_jatuh_tempo'], '-', ' ') : '-';
        ?>

        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Masa Manfaat Komersial </div>
                    <div class="detail-value">: <?= isset($data['masa_manfaat_komersial']) ? $data['masa_manfaat_komersial'] . ' (Bulan) / ' . $last_date_komersial : '-' ?></div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Masa Manfaat Fiskal</div>
                    <div class="detail-value">: <?= isset($data['masa_manfaat_fiskal']) ? $data['masa_manfaat_fiskal'] . ' (Bulan) / ' .  $last_date_fiskal : '-' ?></div>
                </div>
            </div>
        </div>

        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-value">: <?= isset($data['keterangan']) ? $data['keterangan'] : '-' ?></div>
                </div>
            </div>
        </div>

        <br>
       
        <fieldset>
            <legend>Penyusutan Komersial</legend>

            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd;">
                <table class="table table-bordered" style="font-size: 12px; width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="5%">No</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Komersial</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Tgl. Jatuh Tempo</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Beban Penyusutan</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Akumulasi Penyusutan</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nilai Buku Akhir</th>
                            <th style="height: 40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($komersial)): ?>
                            <?php foreach ($komersial as $index => $row): ?>
                                <tr>
                                   
                                    <td class="text-center" style="white-space: nowrap;">
                                        <?= $index + 1 ?>
                                    </td>

                                    <td class="text-center" style="white-space: nowrap;">
                                        <?= !empty($row['kode_komersial']) ? $row['kode_komersial'] : '-' ?>
                                    </td>

                                    <td class="text-center" style="white-space: nowrap;">
                                        <?= !empty($row['tanggal_jatuh_tempo']) 
                                            ? tglIndonesia($row['tanggal_jatuh_tempo'], '-', ' ') 
                                            : '-' ?>
                                    </td>

                                    <td class="text-right" style="white-space: nowrap;">
                                        <?= !empty($row['beban_penyusutan']) 
                                            ? 'Rp ' . number_format($row['beban_penyusutan'], 2, ',', '.') 
                                            : '-' ?>
                                    </td>

                                    <td class="text-right" style="white-space: nowrap;">
                                        <?= !empty($row['akumulasi_penyusutan']) 
                                            ? 'Rp ' . number_format($row['akumulasi_penyusutan'], 2, ',', '.') 
                                            : '-' ?>
                                    </td>

                                    <td class="text-right" style="white-space: nowrap;">
                                        <?php 
                                            if ($lastRowKomersial['id'] == $row['id']){
                                                echo 'Rp 0,00';
                                            } else {
                                                echo !empty($row['nilai_buku_akhir']) ? 'Rp ' . number_format($row['nilai_buku_akhir'], 2, ',', '.') : '-';
                                            } 
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if (isset($row['status'])): ?>
                                            <?php if ($row['status'] == 0): ?>
                                                <span class="badge-status badge-pending">Pending</span>
                                            <?php else: ?>
                                                <span class="badge-status badge-done">Done</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data komersial tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <br>
        <fieldset>
            <legend>Penyusutan Fiskal</legend>

            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd;"> 
                <table class="table table-bordered" style="font-size:12px; width:100%; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="5%">No</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Fiskal</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Tgl. Jatuh Tempo</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Beban Penyusutan</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Akumulasi Penyusutan</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nilai Buku Akhir</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fiskal)): ?>
                            
                            <?php foreach ($fiskal as $index => $row): ?>
                                <tr>
                                    <td class="text-center" style="white-space: nowrap;"><?= $index + 1 ?></td>
                                    <td class="text-center" style="white-space: nowrap;"><?= $row['kode_fiskal'] ?></td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <?= !empty($row['tanggal_jatuh_tempo']) ? tglIndonesia($row['tanggal_jatuh_tempo'], '-', ' ') : '-' ?>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <?= !empty($row['beban_penyusutan']) ? 'Rp ' . number_format($row['beban_penyusutan'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <?= !empty($row['akumulasi_penyusutan']) ? 'Rp ' . number_format($row['akumulasi_penyusutan'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <?= !empty($row['nilai_buku_akhir']) ? 'Rp ' . number_format($row['nilai_buku_akhir'], 2, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (isset($row['status'])): ?>
                                            <?php if ($row['status'] == 0): ?>
                                                <span class="badge-status badge-pending">Pending</span>
                                            <?php else: ?>
                                                <span class="badge-status badge-done">Done</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Colspan 8 disesuaikan dengan jumlah kolom -->
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 20px;">Tidak ada data penyusutan fiskal tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

    </fieldset>




</div>
<?php else: ?>
    <p>Data tidak tersedia.</p>
<?php endif; ?>