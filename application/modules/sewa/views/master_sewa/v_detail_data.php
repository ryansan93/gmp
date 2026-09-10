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
                    <div class="detail-label">Tanggal Mulai</div>
                    <div class="detail-value">: <?= isset($data['tanggal_mulai']) ? date('d F Y', strtotime($data['tanggal_mulai'])) : '-' ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Nominal Sewa</div>
                    <div class="detail-value">: Rp <?= isset($data['nominal_sewa']) ? number_format($data['nominal_sewa'], 0, ',', '.') : '-' ?></div>
                </div>

                <div class="detail-row" style="visibility: hidden;"></div> 
            </div>
        </div>
        <br>
       
        <fieldset>
            <legend>Amortisasi</legend>

            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd;"> 
                <table class="table table-bordered" style="width:100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center" width="5%">No</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Amortisasi</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Kode Transaksi</th>
                            <th style="height:40px; position: sticky; top: 0; z-index: 2; border-bottom: 2px solid #dee2e6 !important;" class="text-center">Nilai</th>
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

    </fieldset>




</div>
<?php else: ?>
    <p>Data tidak tersedia.</p>
<?php endif; ?>