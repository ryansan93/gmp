<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-success" onclick="chb.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 0px"></div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Bank</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control bank" data-required="1">
					<?php foreach ($bank as $k_bank => $v_bank): ?>
						<option value="<?php echo $v_bank['coa']; ?>"><?php echo $v_bank['nama_coa']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Tgl Awal</label></div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                    <input type="text" class="form-control text-center" placeholder="Tgl Awal" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding"><label class="control-label">Tgl Akhir</label></div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                    <input type="text" class="form-control text-center" placeholder="Tgl Akhir" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="chb.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px"></div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-xs-1">Tanggal</th>
                        <th class="col-xs-5">Bank</th>
                        <th class="col-xs-3">Saldo Awal</th>
                        <th class="col-xs-3">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>