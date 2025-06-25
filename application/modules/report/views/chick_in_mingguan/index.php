<div class="row">
	<div class="col-xs-12">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Minggu</label></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-3 no-padding">
                    <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
                <div class="col-xs-1 no-padding text-center"><label class="control-label">s/d</label></div>
                <div class="col-xs-3 no-padding">
                    <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding">
                    <select class="perusahaan" data-required="1" multiple="multiple">
                        <option value="all">ALL</option>
                        <?php if ( !empty( $perusahaan ) ) { ?>
                            <?php foreach ($perusahaan as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding">
                    <select class="unit" data-required="1" multiple="multiple">
                        <option value="all">ALL</option>
                        <?php if ( !empty( $unit ) ) { ?>
                            <?php foreach ($unit as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
		<div class="col-xs-12 no-padding">
            <button type="button" class="btn btn-primary pull-right col-xs-12" onclick="cim.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
            <small>
                <table class="table table-bordered tblRiwayat">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 15%;">Minggu</th>
                            <th rowspan="2" style="width: 15%;">Perusahaan</th>
                            <th rowspan="2" style="width: 7%;">Unit</th>
                            <th class="text-center" rowspan="2" style="width: 7%;">Jml Est. Sblm (Box)</th>
                            <th class="text-center" rowspan="2" style="width: 7%;">Jml Real. Sblm (Box)</th>
                            <th class="text-center border-right-thick" colspan="2">Selisih</th>
                            <th class="text-center" rowspan="2" style="width: 7%;">Jml Est. (Box)</th>
                            <th class="text-center" rowspan="2" style="width: 7%;">Jml Real. (Box)</th>
                            <th class="text-center border-right-thick" colspan="2">Selisih</th>
                            <th class="text-center" colspan="2">Naik Est.</th>
                            <th class="text-center" colspan="2">Naik Real</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="width: 5%;">Box</th>
                            <th class="text-center border-right-thick" style="width: 3%;">%</th>
                            <th class="text-center" style="width: 5%;">Box</th>
                            <th class="text-center border-right-thick" style="width: 3%;">%</th>
                            <th class="text-center" style="width: 5%;">Box</th>
                            <th class="text-center" style="width: 3%;">%</th>
                            <th class="text-center" style="width: 5%;">Box</th>
                            <th class="text-center" style="width: 3%;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="15">Data tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </small>
        </div>
	</div>
</div>