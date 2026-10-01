

<div class="row content-panel">
    <div class="col-lg-12">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-justified">
                <li class="nav-item active">
                    <a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Penerimaan</a>
                </li>
                <?php if ( $akses['a_submit'] == 1 ) { ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Tambah Penerimaan</a>
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
                                <label for="filter_kategori_aset" style="display:block; font-weight:600; margin-bottom:6px;">Kategori Aset</label>
                                <select id="filter_kategori_aset" class="select2" style="width:100%;">
                                    <option value="">Semua Kategori</option>
                                    <?php if ( !empty($kategori_aset) ) : ?>
                                        <?php foreach ( $kategori_aset as $row ) : ?>
                                            <?php $kode = isset($row['kategori_kode']) ? trim($row['kategori_kode']) : ''; ?>
                                            <?php $nama = isset($row['kategori_name']) ? trim($row['kategori_name']) : ''; ?>
                                            <option value="<?php echo htmlspecialchars($nama); ?>">
                                                <?php echo htmlspecialchars($kode . ' - ' . $nama); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="filter_tanggal_mulai" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Penerimaan</label>
                                <div class="input-group date" id="filter_tanggal_mulai_picker">
                                    <input style="caret-color: transparent; background-color:#fff;" type="text" class="form-control" id="filter_tanggal_mulai" placeholder="Pilih Tanggal" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12" style="padding-left:6px;">
                                <button type="button" class="btn btn-primary" style="width:100px;" id="btn-filter" onclick="pa.filterData(event)">Filter</button>
                                <button type="button" class="btn btn-default" style="width:100px;" id="btn-reset" onclick="pa.resetFilter(event)">Reset</button>
                            </div>
                        </div>
                    </fieldset>

                    <br>

                    <fieldset>
                        <legend>List Data Penerimaan</legend>

                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                                <label for="search_penerimaan" style="display:block; font-weight:600; margin-bottom:6px;">Cari Data</label>
                                <input type="text" class="form-control" id="search_penerimaan" placeholder="Cari kode penerimaan, kode aset, deskripsi, atau PIC">
                            </div>
                            <div class="col-xs-12 col-sm-8" style="padding:0 6px; margin-top:26px;">
                                <button type="button" class="btn btn-success" id="btn-export" onclick="pa.exportData(event)">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto; white-space:nowrap;">
        
                            <table class="table table-bordered" id="table-penerimaan" style="min-width:1100px; margin-bottom:0; white-space:nowrap; font-size:12px;">
                                <thead>
                                    <tr>
                                        <th style="height:40px;" class="text-center" width="5%">No</th>
                                        <th style="height:40px;" class="text-center">Kode Penerimaan</th>
                                        <th style="height:40px;" class="text-center">Kode Aset</th>
                                        <th style="height:40px;" class="text-center">Kategori</th>
                                        <th style="height:40px;" class="text-center">Deskripsi</th>
                                        <th style="height:40px;" class="text-center">Tgl. Perolehan</th>
                                        <th style="height:40px;" class="text-center">Tgl. Penerimaan</th>
                                        <th style="height:40px;" class="text-center">PIC</th>
                                        <th style="height:40px;" class="text-center">Lokasi</th>
                                        <th style="height:40px;" class="text-center" width="80px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center">Memuat data...</td>
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