<div class="row content-panel">
    <div class="col-lg-12">
        <div class="panel-body rs-page">

            <div class="rs-stats">
                <div class="rs-stat-card rs-stat-card--total">
                    <div class="rs-stat-tile"><i class="fa fa-file-text-o"></i></div>
                    <div>
                        <div class="rs-stat-value" id="stat-total">0</div>
                        <div class="rs-stat-label">Total Kontrak</div>
                    </div>
                </div>
                <div class="rs-stat-card rs-stat-card--aktif">
                    <div class="rs-stat-tile"><i class="fa fa-bolt"></i></div>
                    <div>
                        <div class="rs-stat-value" id="stat-aktif">0</div>
                        <div class="rs-stat-label">Aktif</div>
                    </div>
                </div>
                <div class="rs-stat-card rs-stat-card--selesai">
                    <div class="rs-stat-tile"><i class="fa fa-check"></i></div>
                    <div>
                        <div class="rs-stat-value" id="stat-selesai">0</div>
                        <div class="rs-stat-label">Selesai</div>
                    </div>
                </div>
            </div>

            <div class="rs-panel">
                <div class="rs-panel-head">
                    <div class="rs-panel-icon"><i class="fa fa-sliders"></i></div>
                    <div class="rs-panel-title">Filter Data</div>
                </div>
                <div class="rs-panel-body">
                    <div class="rs-filter-grid">
                        <div class="rs-field">
                            <label for="filter_jenis_sewa">Jenis Sewa</label>
                            <select id="filter_jenis_sewa" style="width:100%;">
                                <option value="">Semua Jenis</option>
                                <?php if ( !empty($jenis_sewa) ) : ?>
                                    <?php foreach ( $jenis_sewa as $row ) : ?>
                                        <?php $kode = isset($row['kode_jenis_sewa']) ? trim($row['kode_jenis_sewa']) : ''; ?>
                                        <?php $nama = isset($row['nama_jenis_sewa']) ? trim($row['nama_jenis_sewa']) : ''; ?>
                                        <option value="<?php echo htmlspecialchars($kode); ?>">
                                            <?php echo htmlspecialchars($kode . ' - ' . $nama); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="rs-field">
                            <label for="filter_supplier">Supplier</label>
                            <select id="filter_supplier" style="width:100%;">
                                <option value="">Semua Supplier</option>
                                <?php if ( !empty($supplier) ) : ?>
                                    <?php foreach ( $supplier as $row ) : ?>
                                        <?php $nomor = isset($row['nomor']) ? trim($row['nomor']) : ''; ?>
                                        <?php $nama = isset($row['nama']) ? trim($row['nama']) : ''; ?>
                                        <option value="<?php echo htmlspecialchars($nomor); ?>">
                                            <?php echo strtoupper($nama); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="rs-field">
                            <label for="filter_status">Status Kontrak</label>
                            <select id="filter_status" style="width:100%;">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="rs-filter-actions">
                        <button type="button" class="rs-btn rs-btn-primary" id="btn-filter" onclick="rs.filterData(event)"><i class="fa fa-filter"></i> Filter</button>
                        <button type="button" class="rs-btn rs-btn-ghost" id="btn-reset" onclick="rs.resetFilter(event)"><i class="fa fa-rotate-left"></i> Reset</button>
                        <button type="button" class="rs-btn rs-btn-outline-success" id="btn-export" onclick="rs.exportData(event)"><i class="fa fa-download"></i> Export Excel</button>
                    </div>
                </div>
            </div>

            <div class="rs-panel">
                <div class="rs-panel-head">
                    <div class="rs-panel-icon"><i class="fa fa-table"></i></div>
                    <div class="rs-panel-title">Rekap Kontrak Sewa</div>
                    <div class="rs-panel-sub" id="rs-row-count"></div>
                </div>
                <div class="rs-panel-body">
                    <div class="rs-table-toolbar">
                        <div class="rs-input-icon-wrap rs-table-search">
                            <i class="fa fa-search"></i>
                            <input type="text" class="rs-input rs-input-icon" id="search_sewa" placeholder="Cari nama, no. sewa, no. kontrak">
                        </div>
                    </div>
                    <div class="rs-table-scroll">
                        <table class="rs-table" id="table-rekap-sewa">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th>No. Sewa</th>
                                    <th>No. Kontrak</th>
                                    <th>Nama Sewa</th>
                                    <th>Jenis Sewa</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Durasi (Bln)</th>
                                    <th>Bulan Berjalan</th>
                                    <th width="140px">Progress Waktu</th>
                                    <th width="140px">Progress Amortisasi</th>
                                    <th>Status</th>
                                    <th class="text-right">Nominal Sewa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="14" class="rs-empty"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
