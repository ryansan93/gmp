<div class="row">
	<div class="col-xs-12">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Berdasarkan</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control jenis">
                    <option value="rencana">Rencana Chick In</option>
                    <option value="realisasi">Realisasi Chick In</option>
                </select>
            </div>
        </div>
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Periode</label></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
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
            <button type="button" class="btn btn-primary pull-right col-xs-12" onclick="rci.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
            <small>
                <table class="table table-bordered tblRiwayat">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center col-xs-1">Unit</th>
                            <th rowspan="2" class="text-center col-xs-2">Nama</th>
                            <!-- <th rowspan="2" class="text-center col-xs-1">Kandang</th> -->
                            <th rowspan="2" class="text-center col-xs-1">Noreg</th>
                            <th colspan="3" class="text-center">Rencana</th>
                            <th colspan="3" class="text-center">Realisasi</th>
                            <th rowspan="2" class="text-center col-xs-1">Tgl Panen</th>
                            <th rowspan="2" class="text-center col-xs-1">Tgl Tutup Siklus</th>
                        </tr>
                        <tr>
                            <th class="text-center col-xs-1">Tanggal</th>
                            <th class="text-center col-xs-1">Box</th>
                            <th class="text-center col-xs-1">Ekor</th>
                            <th class="text-center col-xs-1">Tanggal</th>
                            <th class="text-center col-xs-1">Box</th>
                            <th class="text-center col-xs-1">Ekor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="11">Data tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </small>
        </div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
            <button type="button" class="btn btn-default pull-right" onclick="rci.encryptParams()"><i class="fa fa-file-excel-o"></i> Export Excel</button>
        </div>
	</div>
</div>