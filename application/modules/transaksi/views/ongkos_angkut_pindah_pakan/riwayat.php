<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-success cursor-p" onclick="oap.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> ADD</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-6 no-padding" style="padding-right: 5px; padding-bottom: 10px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tgl Terima Awal</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px; padding-bottom: 10px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tgl Terima Akhir</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary cursor-p" onclick="oap.getLists()">Tampilkan</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
        <div class="col-xs-12 search left-inner-addon no-padding pull-right">
            <i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <span>* Klik pada baris untuk melihat detail</span>
        <small>
            <table class="table table-bordered tbl_riwayat">
                <thead>
                    <tr>
                        <th class="col-xs-2 text-center">Tgl Terima</th>
                        <th class="col-xs-2 text-center">No. SJ</th>
                        <th class="col-xs-3 text-center">Asal</th>
                        <th class="col-xs-3 text-center">Tujuan</th>
                        <th class="col-xs-2 text-center">Ongkos Angkut (Rp.)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>