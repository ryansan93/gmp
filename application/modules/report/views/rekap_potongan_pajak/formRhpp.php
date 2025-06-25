<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding">
                <label>TGL TUTUP SIKLUS AWAL</label>
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
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding">
                <label>TGL TUTUP SIKLUS AKHIR</label>
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
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-12 no-padding">
            <label>PERUSAHAAN</label>
        </div>
        <div class="col-xs-12 no-padding">
            <select class="form-control perusahaan" data-required="1" multiple="multiple">
                <option value="all">ALL</option>
                <?php foreach ($perusahaan as $key => $value) { ?>
                    <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['perusahaan']); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-12 no-padding">
            <label>UNIT</label>
        </div>
        <div class="col-xs-12 no-padding">
            <select class="form-control unit" data-required="1" multiple="multiple">
                <option value="all">ALL</option>
                <?php foreach ($unit as $key => $value) { ?>
                    <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="rpp.getLists(this)" data-jenis="1"><i class="fa fa-search"></i> Tampilkan</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="overflow-x: scroll;">
            <small>
                <table class="table table-bordered tbl_laporan" style="max-width: 150%; width: 150%; margin-bottom: 0px;">
                    <thead>
                        <tr>
                            <td class="text-right" colspan="11"><b>TOTAL</b></td>
                            <td class="total text-right" data-target="pendapatan" data-jenis="decimal"><b>0</b></td>
                            <td></td>
                            <td class="total text-right" data-target="pot_pajak" data-jenis="decimal"><b>0</b></td>
                            <td class="total text-right" data-target="pend_stlh_pajak" data-jenis="decimal"><b>0</b></td>
                            <td class="total text-right" data-target="transfer" data-jenis="decimal"><b>0</b></td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <th style="width: 9%;">PERUSAHAAN</th>
                            <th style="width: 5%;">NO. PLASMA</th>
                            <th style="width: 9%;">NAMA PLASMA</th>
                            <th style="width: 3%;">KDG</th>
                            <th style="width: 7%;">NO. KTP</th>
                            <th style="width: 7%;">NO. NPWP</th>
                            <th style="width: 15%;">ALAMAT</th>
                            <th style="width: 3%;">KAB / KOTA</th>
                            <th style="width: 3%;">PROVINSI</th>
                            <th style="width: 5%;">NO. HP</th>
                            <th style="width: 5%;">NO. INVOICE</th>
                            <th style="width: 5%;">PENDAPATAN</th>
                            <th style="width: 3%;">POT PAJAK (%)</th>
                            <th style="width: 5%;">POT PAJAK (Rp.)</th>
                            <th style="width: 5%;">PEND STLH PAJAK</th>
                            <th style="width: 5%;">TRANSFER</th>
                            <th style="width: 3%;">UNIT</th>
                            <th style="width: 5%;">TGL RHPP</th>
                            <th style="width: 7%;">NO. SKB</th>
                            <th style="width: 5%;">TGL HABIS BERLAKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="20">Data tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </small>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="btn btn-default pull-right" onclick="rpp.excryptParams(this)" data-jenis="1"><i class="fa fa-file-excel-o"></i> Export Excel</button>
    </div>
</div>