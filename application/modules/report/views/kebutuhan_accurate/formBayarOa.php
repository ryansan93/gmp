<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>TGL BAYAR AWAL</label>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>TGL BAYAR AKHIR</label>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                    <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>PERUSAHAAN</label>
            </div>
            <div class="col-xs-12 no-padding">
                <select class="form-control perusahaan" multiple="multiple" data-required="1">
                    <option value="all">ALL</option>
                    <?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
                        <option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px; padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>UNIT</label>
            </div>
            <div class="col-xs-12 no-padding">
                <select class="form-control unit" multiple="multiple" data-required="1">
                    <option value="all">ALL</option>
                    <?php foreach ($unit as $k_unit => $v_unit): ?>
                        <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        
        <div class="col-xs-12 no-padding">
            <button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="ka.getLists(this)" data-jenis="9"><i class="fa fa-search"></i> Tampilkan</button>
        </div>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="overflow-x: auto;">
        <small>
            <table class="table table-bordered tbl_laporan" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
                <thead>
                    <tr>
                        <td colspan="5"><b>TOTAL</b></td>
                        <td class="total text-right" data-target="transfer" data-jenis="decimal"><b>0</b></td>
                        <td colspan="3"></td>
                        <td class="total text-right" data-target="pemotongan_pajak" data-jenis="decimal"><b>0</b></td>
                        <td></td>
                        <td class="total text-right" data-target="pemotongan" data-jenis="decimal"><b>0</b></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <th>DO</th>
                        <th>Unit</th>
                        <th>Sumber Kas/Bank (Nama Kas/Bank)</th>
                        <th>No. Bukti Bank</th>
                        <th>Tgl Transfer</th>
                        <th>Nominal Transfer</th>
                        <th>Kode Ekspedisi</th>
                        <th>Nama Ekspedisi</th>
                        <th>No Invoice Yang Di Lunasi</th>
                        <th>Nominal Pemotongan Pajak</th>
                        <th>Keterangan Pemotongan Pajak</th>
                        <th>Nominal Pemotongan Lainnya</th>
                        <th>Keterangan Pemotongan Lainnya</th>
                        <th>Deskripsi/Catatan/Keterangan Bank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="14">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <button type="button" class="btn btn-default pull-right" onclick="ka.excryptParams(this)" data-tipe="excel" data-jenis="9"><i class="fa fa-file-excel-o"></i> Export Excel</button>
    <button type="button" class="btn btn-default pull-right" onclick="ka.excryptParams(this)" data-tipe="xml" data-jenis="9" style="margin-right: 10px;"><i class="fa fa-file-o"></i> Export XML</button>
</div>