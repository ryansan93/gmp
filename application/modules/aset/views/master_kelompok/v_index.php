<div class="row content-panel">
    <div class="col-lg-12">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-justified">
                <li class="nav-item active">
                    <a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Kelompok Aset</a>
                </li>

                <!-- < ?php if ( $akses['a_submit'] == 1 ) { ?> -->
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Tambah Data</a>
                    </li>
                <!-- < ?php } ?> -->
            </ul>
        </div>

        <div class="panel-body">
            <div class="tab-content">
                <div id="history" class="tab-pane fade in active" role="tabpanel">
                    <fieldset>
                        <legend>Filter Data</legend>
                        <div class="row" style="padding:0 10px;">
                            <div class="col-xs-12" style="padding:0 6px; margin-bottom:15px;">
                                <label for="filter_keyword" style="display:block; font-weight:600; margin-bottom:6px;">Nama Kelompok</label>
                                <input type="text" class="form-control" id="filter_keyword" placeholder="Cari nama kelompok aset">
                            </div>
                        </div>
                    </fieldset>
                    <br>
                    <fieldset>
                        <legend>List Data</legend>
                        <div class="table-area">
                            <table class="table table-bordered table-hover" id="table-kelompok-aset">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:50px;">No</th>
                                        <th class="text-center">Nama Kelompok</th>
                                        <th class="text-center">Umur Ekonomis<br>(Tahun)</th>
                                        <th class="text-center">Deskripsi</th>
                                        <th class="text-center">Tarif Garis Lurus<br>(%)</th>
                                        <th class="text-center">Tarif Saldo Menurun<br>(%)</th>
                                        <th class="text-center" style="width:100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center">Memuat data...</td>
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