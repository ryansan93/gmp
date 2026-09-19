<style>
    .detail-wrap {
        max-width: 960px;
        margin: 0 auto;
        font-family: Arial, sans-serif;
    }

    .detail-wrap .detail-data {
        font-size: 13px;
    }

    /* --- STYLE UNTUK GRID 2 KOLOM --- */
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

    /* --- STYLE DASAR ROW & LABEL --- */
    .detail-wrap .detail-row {
        display: flex;
        align-items: center;
        min-height: 38px;
        padding: 6px 0;
    }

    .detail-wrap .detail-label {
        width: 140px; /* Diperkecil agar muat di grid 2 kolom */
        font-weight: bold;
        color: #555;
        flex-shrink: 0;
    }

    .detail-wrap .detail-value {
        color: #141414;
        flex-grow: 1;
    }
</style>

<?php if(isset($data) && !empty($data)): ?>
<div class="detail-wrap">

    <?php if(!empty($supplier)): ?>
        <fieldset>
            <legend>Detail Data Supplier</legend>
            
            <?php if(!empty($supplier)): ?>
                <?php foreach($supplier as $row): ?>
                    <div class="detail-data">
                        
                        <div class="detail-row" style="align-items: flex-start; ">
                            <div class="detail-label">Nama Supplier</div>
                            <div class="detail-value" style="word-break: break-word;">
                                : <?= isset($row['nama']) ? $row['nama'] : '-' ?>
                                <?php if(isset($row['cp']) && !empty($row['cp'])): ?>
                                    <span style="color:#666; font-size: 0.9em; margin-left: 10px;">(CP: <?= $row['cp'] ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-col">
                                <div class="detail-row">
                                    <div class="detail-label">NPWP</div>
                                    <div class="detail-value">: <?= isset($row['npwp']) ? $row['npwp'] : '-' ?></div>
                                </div>
                            </div>
                            <div class="detail-col"></div>
                            <div class="detail-col" style="width: 100%; padding-left: 0; padding-right: 0;">
                                <div class="detail-row" style="align-items: flex-start;">
                                    <div class="detail-label">Alamat</div>
                                    <div class="detail-value" style="word-break: break-word;">: 
                                        <?= (isset($row['usaha_jalan']) ? $row['usaha_jalan'] : '') . ', RT ' . (isset($row['usaha_rt']) ? $row['usaha_rt'] : '') . '/RW ' . (isset($row['usaha_rw']) ? $row['usaha_rw'] : '') . ', ' . (isset($row['usaha_kelurahan']) ? $row['usaha_kelurahan'] : '') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="detail-row"><div class="detail-value">Data supplier tidak tersedia.</div></div>
            <?php endif; ?>
        </fieldset>
        <br>
    <?php endif; ?>

  
    <fieldset>
        <legend>Detail Data Sewa</legend>
    
        <div class="detail-data detail-grid">
            <div class="detail-col">
                <div class="detail-row">
                    <div class="detail-label">No. Sewa</div>
                    <div class="detail-value">: <?= isset($data['no_sewa']) ? $data['no_sewa'] : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Nama Sewa</div>
                    <div class="detail-value">: <?= isset($data['nama_sewa']) ? $data['nama_sewa'] : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">No. Kontrak</div>
                    <div class="detail-value">: <?= isset($data['no_kontrak']) ? $data['no_kontrak'] : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Jenis Sewa</div>
                    <div class="detail-value">: <?= isset($data['jenis_sewa']) ? $data['jenis_sewa'] : '-' ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Tgl. Mulai</div>
                    <div class="detail-value">: <?= isset($data['tanggal_mulai']) ? tglIndonesia($data['tanggal_mulai']) : '-' ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Unit</div>
                    <div class="detail-value">: <?= isset($nama_unit[0]['nama_unit']) ? $nama_unit[0]['nama_unit'] : '-' ?></div>
                </div>


                <div class="detail-row">
                    <div class="detail-label">Dokumen Attachment</div>
                    <div class="detail-value">: 
                        <?php if (!empty($data['attachment'])): ?>
                            <a href="<?php echo base_url('uploads/sewa/' . $data['attachment']); ?>" target="_blank" style="color: #337ab7; text-decoration: none; font-weight: 600;">
                                <i class="fa fa-file-pdf-o"></i> Lihat / Download Dokumen
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">Tidak ada dokumen</span>
                        <?php endif; ?>
                    </div>
                </div>
              
            </div>

            <div class="detail-col">

                <?php if(isset($data['jumlah_siklus']) && (int)$data['jumlah_siklus'] > 0): ?>
                    <div class="detail-row">
                        <div class="detail-label">Siklus</div>
                        <div class="detail-value">: <?= isset($data['jumlah_siklus']) ? $data['jumlah_siklus'] . ' siklus' : '-' ?></div>
                    </div>
                <?php endif; ?>

                <?php if(isset($data['jumlah_bulan']) && (int)$data['jumlah_bulan'] > 0): ?>
                    <div class="detail-row">
                        <div class="detail-label">Jumlah Bulan</div>
                        <div class="detail-value">: <?= isset($data['jumlah_bulan']) ? $data['jumlah_bulan'] . ' bulan' : '-' ?></div>
                    </div>
                <?php endif; ?>
               
                <div class="detail-row">
                    <div class="detail-label">Nominal Sewa</div>
                    <div class="detail-value">: Rp <?= isset($data['nominal_sewa']) ? number_format($data['nominal_sewa'], 0, ',', '.') : '-' ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Nominal Cicilan</div>
                    <div class="detail-value">: Rp <?= isset($data['nominal_cicilan']) ? number_format($data['nominal_cicilan'], 0, ',', '.') : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Durasi Cicilan</div>
                    <div class="detail-value">: <?= isset($data['durasi_cicilan']) ? $data['durasi_cicilan'] . '  Bulan' : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tgl. Jatuh Tempo</div>
                    <div class="detail-value">: <?= isset($data['tgl_jatuh_tempo']) ? $data['tgl_jatuh_tempo'] : '-' ?></div>
                </div>

                
                <div class="detail-row">
                    <div class="detail-label">DP</div>
                    <div class="detail-value">: Rp <?= isset($data['dp']) ? number_format($data['dp'], 0, ',', '.') : '-' ?></div>
                </div>

                <div class="detail-row" style="visibility: hidden;"></div> 
            </div>
        </div>
        <br>
       
        <fieldset>
            <legend>Amortisasi</legend>

            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd;"> 
                <table class="table table-bordered" style="font-size:12px; width:100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="5%">No</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Amortisasi</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Transaksi</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nilai (Rp)</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($amortisasi)): ?>
                            <?php foreach($amortisasi as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td class="text-center"><?= isset($row['kode_amortisasi']) ? $row['kode_amortisasi'] : '-' ?></td>
                                    <td class="text-center"><?= isset($row['kode_transaksi']) ? $row['kode_transaksi'] : '-' ?></td>
                                    <td class="text-right"><?= isset($row['nilai']) ? number_format($row['nilai'], 0, ',', '.') : '0' ?></td>
                                    <td class="text-center">
                                        <?php if(isset($row['status'])): ?>
                                            <?php if($row['status'] == 0): ?>
                                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#fff3cd; color:#856404; font-size:12px; font-weight:600;">Pending</span>
                                            <?php else: ?>
                                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#d4edda; color:#155724; font-size:12px; font-weight:600;">Done</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data amortisasi tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <br>
        <fieldset>
            <legend>Termin</legend>

            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd;"> 
                <table class="table table-bordered " style=" font-size:12px; width:100%; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="5%">No</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">No. Sewa</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">No. Termin</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Jenis Termin</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Jatuh Tempo</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nominal (Rp)</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nominal Terbayar (Rp)</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($termin)): ?>
                            <?php foreach($termin as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td class="text-center"><?= isset($row['no_sewa']) ? $row['no_sewa'] : '-' ?></td>
                                    <td class="text-center"><?= isset($row['no_termin']) ? $row['no_termin'] : '-' ?></td>
                                    
                                    <!-- Jenis Termin -->
                                    <td class="text-center">
                                        <?php if ( isset($row['jenis_termin']) && $row['jenis_termin'] !== '' ): ?>
                                            <?php 
                                                $jenis = strtolower(trim($row['jenis_termin']));
                                                $label = '';
                                                $style = '';

                                                // Tentukan Label dan Warna berdasarkan Jenis Termin
                                                if ( $jenis == 'dp' ) {
                                                    $label = 'DP';
                                                    $style = 'background-color:#d1ecf1; color:#0c5460;'; // Biru (Info)
                                                } 
                                                elseif ( $jenis == 'cicilan' ) {
                                                    $label = 'Cicilan';
                                                    $style = 'background-color:#e2e3e5; color:#383d41;'; // Abu-abu (Secondary)
                                                } 
                                                elseif ( $jenis == 'pelunasan' ) {
                                                    $label = 'Pelunasan';
                                                    $style = 'background-color:#fff3cd; color:#856404;'; // Kuning (Warning)
                                                } 
                                                elseif ( $jenis == 'full_payment' || $jenis == 'lunas' ) {
                                                    $label = 'Lunas';
                                                    $style = 'background-color:#d4edda; color:#155724;'; // Hijau (Success)
                                                } 
                                                else {
                                                    $label = ucfirst($jenis);
                                                    $style = 'background-color:#f8f9fa; color:#6c757d;'; // Default
                                                }
                                            ?>
                                            
                                            <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; <?php echo $style; ?>">
                                                <?php echo $label; ?>
                                            </span>

                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Jatuh Tempo -->
                                    <td class="text-center" style="white-space:nowrap">
                                        <?= isset($row['tgl_jatuh_tempo']) ? tglIndonesia($row['tgl_jatuh_tempo'], '-', ' ') : '-' ?>
                                    </td>

                                    <!-- Nominal (Rata Kanan agar rapi) -->
                                    <td class="text-right">
                                        <?= isset($row['nominal']) ? number_format($row['nominal'], 0, ',', '.') : '0' ?>
                                    </td>

                                    <!-- Nominal Terbayar (Rata Kanan) -->
                                    <td class="text-right">
                                        <?= isset($row['nominal_terbayar']) ? number_format($row['nominal_terbayar'], 0, ',', '.') : '0' ?>
                                    </td>

                                    <!-- Status -->
                                    <td class="text-center">
                                        <?php if(isset($row['status'])): ?>
                                            <?php if($row['status'] == 0): ?>
                                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#fff3cd; color:#856404; font-size:12px; font-weight:600;">Belum</span>
                                            <?php else: ?>
                                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#d4edda; color:#155724; font-size:12px; font-weight:600;">Lunas</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Perbaikan colspan dari 4 menjadi 8 -->
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data termin tersedia</td>
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