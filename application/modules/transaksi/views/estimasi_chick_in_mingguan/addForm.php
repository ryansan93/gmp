<div class="modal-header header">
	<span class="modal-title">Add Estimasi Chick In Mingguan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
    <div class="col-xs-12 no-padding">
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
                <div class="col-xs-6 no-padding">
                    <select class="perusahaan" data-required="1">
                        <option value="">-- Pilih Perusahaan --</option>
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
                <div class="col-xs-4 no-padding">
                    <select class="unit" data-required="1">
                        <option value="">-- Pilih Unit --</option>
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
            <div class="col-xs-12 no-padding"><label class="control-label">Jumlah (Ekor)</label></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-3 no-padding">
                    <input type="text" class="form-control text-right jumlah" data-tipe="integer" data-required="1" placeholder="Jumlah" maxlength="10">
                </div>
            </div>
        </div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
        <div class="col-xs-12 no-padding">
            <button type="button" class="btn btn-primary col-xs-12" onclick="est.save()">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</div>