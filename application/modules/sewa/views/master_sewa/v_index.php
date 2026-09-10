<div class="row content-panel">
    <div class="col-lg-12">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-justified">
                <li class="nav-item active">
                    <a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Sewa</a>
                </li>
                <?php if ( $akses['a_submit'] == 1 ) { ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Tambah Data</a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="panel-body">
            <div class="tab-content">
                <div id="history" class="tab-pane fade in active" role="tabpanel">

                    <fieldset>
                        <legend>Filter Data</legend>

                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="filter_jenis_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Jenis Sewa</label>
                                <select id="filter_jenis_sewa" class="select2" style="width:100%;">
                                    <option value="">All</option>
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

                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="filter_supplier" style="display:block; font-weight:600; margin-bottom:6px;">Supplier</label>
                                <select id="filter_supplier" class="select2" style="width:100%;">
                                    <option value="">All</option>
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

                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="filter_tanggal_mulai" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Mulai</label>
                                <div class="input-group date" id="filter_tanggal_mulai_picker">
                                    <input style="caret-color: transparent; background-color:#F2F2F2;" type="text" class="form-control" id="filter_tanggal_mulai" placeholder="Pilih Tanggal" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12" style="padding-left:6px;">
                                <button type="button" class="btn btn-primary" style="width:100px;" id="btn-filter" onclick="ms.filterData(event)">Filter</button>
                                <button type="button" class="btn btn-default" style="width:100px;" id="btn-reset" onclick="ms.resetFilter(event)">Reset</button>
                            </div>
                        </div>
                    </fieldset>

                    <br>

                    <fieldset>
                        <legend>List Data</legend>

                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="search_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Cari Data</label>
                                <input type="text" class="form-control" id="search_sewa" placeholder="Cari nama, no sewa, no kontrak">
                            </div>
                            <div class="col-xs-12 col-sm-8" style="padding:0 6px; margin-top:26px;">
                                <button type="button" class="btn btn-success" id="btn-export" onclick="ms.exportData(event)">Export</button>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto; white-space:nowrap;">
                            <table class="table table-bordered" id="table-sewa" style="min-width:1100px; margin-bottom:0; white-space:nowrap;">
                                <thead>
                                    <tr>
                                        <th style="height:40px;" class="text-center" width="5%">No</th>
                                        <th style="height:40px;" class="text-center">No. Sewa</th>
                                        <th style="height:40px;" class="text-center">No. Kontrak</th>
                                        <th style="height:40px;" class="text-center">Nama Sewa</th>
                                        <th style="height:40px;" class="text-center">Jenis Sewa</th>
                                        <th style="height:40px;" class="text-center">Jumlah <br> Bulan</th>
                                        <th style="height:40px;" class="text-center">Jumlah <br> Siklus</th>
                                        <th style="height:40px;" class="text-center">Tanggal <br> Mulai</th>
                                        <th style="height:40px;" class="text-right">Nominal Sewa</th>
                                        <th style="height:40px;" class="text-center" width="70px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="text-center">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>

                <div id="action" class="tab-pane fade" role="tabpanel">
                    <div id="tab-action-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>


