<div class="row">
    <div class="col-xs-12">
        <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
            <div class="col-xs-12 no-padding"><label class="control-label">JENIS LAPORAN</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control jenis" data-required="1">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="pakan">PAKAN</option>
                    <option value="voadip">OVK</option>
                </select>
            </div>
        </div>
        <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
            <div class="col-xs-12 no-padding"><label class="control-label">JENIS FILTER</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control jenis_filter" data-required="1">
                    <option value="tanggal">TANGGAL</option>
                    <option value="no_sj_asal">NO. SJ ASAL</option>
                </select>
            </div>
        </div>
        <div class="col-xs-12 no-padding jenis_filter tanggal" style="margin-bottom: 10px;">
            <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">TGL TERIMA AWAL</label></div>
                <div class="col-xs-12 no-padding">
                    <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center uppercase" placeholder="Start Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">TGL TERIMA AKHIR</label></div>
                <div class="col-xs-12 no-padding">
                    <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                        <input type="text" class="form-control text-center uppercase" placeholder="End Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 no-padding jenis_filter no_sj_asal hide" style="margin-bottom: 10px;">
            <div class="col-xs-12 no-padding"><label class="control-label">NO. SJ ASAL</label></div>
            <div class="col-xs-12 no-padding">
                <input type="text" class="form-control no_sj_asal uppercase" placeholder="No. SJ Asal" maxlength="20">
            </div>
        </div>
        <div class="col-xs-12 no-padding">
            <button type="button" class="col-xs-12 btn btn-primary" onclick="pb.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
        </div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
            <small>
                <table class="table table-bordered" style="margin-bottom: 0px;">
                    <thead>
                        <tr>
                            <th class="col-xs-1">Tgl Terima</th>
                            <th class="col-xs-1">No. SJ Asal</th>
                            <th class="col-xs-1">No. SJ Kirim</th>
                            <th class="col-xs-2">Asal</th>
                            <th class="col-xs-2">Tujuan</th>
                            <th class="col-xs-2">Nama Barang</th>
                            <th class="col-xs-1">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7">Data tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </small>
        </div>
    </div>
</div>