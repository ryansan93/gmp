<style>
    #table-pending.table-bordered,
    #table-processed.table-bordered {
        border-top: 1px solid #ddd;
    }

    #table-pending.table-bordered thead tr:first-child th,
    #table-processed.table-bordered thead tr:first-child th {
        border-top: 1px solid #ddd;
    }

    .pa-toolbar {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 20px;
        background: #f8f9fc;
        border: 1px solid #e6e9f0;
        border-radius: 10px;
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    .pa-field-periode label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }

    .pa-date-wrap {
        width: 230px;
    }

    .pa-date-wrap .pa-date-input {
        height: 38px;
        border: 1px solid #d7dbe3;
        border-right: none;
        border-radius: 8px 0 0 8px;
        padding: 0 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        cursor: pointer;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .pa-date-wrap .pa-date-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
    }

    .pa-date-wrap .pa-date-icon {
        border: 1px solid #d7dbe3;
        border-left: none;
        border-radius: 0 8px 8px 0;
        background: #fff;
        color: #8a94a6;
        font-size: 13px;
        cursor: pointer;
    }

    .pa-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<div class="row content-panel">
    <div class="col-lg-12">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-justified">
                <li class="nav-item active">
                    <a class="nav-link active" data-toggle="tab" href="#pending" data-tab="pending">Belum Diproses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#processed" data-tab="processed">Sudah Diproses</a>
                </li>
            </ul>
        </div>

        <div class="panel-body">
            <div class="tab-content">
                <div id="pending" class="tab-pane fade in active" role="tabpanel">
                    <fieldset>
                        <legend>Daftar Amortisasi Belum Diproses</legend>

                        <div class="pa-toolbar">
                            <div class="pa-field-periode">
                                <label for="periode_proses">Periode Proses</label>
                                <div class="input-group date pa-date-wrap" id="periode_proses_picker">
                                    <input type="text" class="form-control pa-date-input" placeholder="Pilih Tanggal" id="periode_proses" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                                    <span class="input-group-addon pa-date-icon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="pa-toolbar-actions">
                                <?php if ( $akses['a_submit'] == 1 ) { ?>
                                    <button type="button" class="btn btn-primary" id="btn-proses" onclick="pa.proses(event)">
                                        <i class="fa fa-check"></i> Proses Amortisasi Terpilih
                                    </button>
                                <?php } ?>
                                <span id="pa-selected-count-pending" class="text-muted"></span>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto;">
                            <table class="table table-bordered table-hover" id="table-pending" style="min-width:900px; margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="4%">
                                            <input type="checkbox" id="chk-all-pending">
                                        </th>
                                        <th class="text-center">No</th>
                                        <th class="text-center">No. Sewa</th>
                                        <th class="text-center">Nama Sewa</th>
                                        <th class="text-center">Jenis Sewa</th>
                                        <th class="text-center">Kode Amortisasi</th>
                                        <th class="text-right">Nilai</th>
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

                <div id="processed" class="tab-pane fade" role="tabpanel">
                    <fieldset>
                        <legend>Daftar Amortisasi Sudah Diproses</legend>

                        <div class="row" style="padding:0 10px; margin-bottom:15px;">
                            <div class="col-xs-12" style="padding-left:6px;">
                                <?php if ( $akses['a_submit'] == 1 ) { ?>
                                    <button type="button" class="btn btn-danger" id="btn-batal" onclick="pa.batal(event)">
                                        <i class="fa fa-undo"></i> Batalkan Amortisasi Terpilih
                                    </button>
                                <?php } ?>
                                <span id="pa-selected-count-processed" class="text-muted" style="margin-left:10px;"></span>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x:auto;">
                            <table class="table table-bordered table-hover" id="table-processed" style="min-width:1000px; margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="4%">
                                            <input type="checkbox" id="chk-all-processed">
                                        </th>
                                        <th class="text-center">No</th>
                                        <th class="text-center">No. Sewa</th>
                                        <th class="text-center">Nama Sewa</th>
                                        <th class="text-center">Jenis Sewa</th>
                                        <th class="text-center">Kode Amortisasi</th>
                                        <th class="text-center">Tanggal Diproses</th>
                                        <th class="text-right">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>
