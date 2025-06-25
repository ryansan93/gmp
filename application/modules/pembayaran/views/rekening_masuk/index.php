<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-body" style="padding-top: 0px;">
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-1 no-padding">
                        <label class="control-label">Tanggal Bayar</label>
                    </div>
                    <div class="col-xs-2">
                        <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                            <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-xs-1 text-center no-padding">
                        <label class="control-label">s/d</label>
                    </div>
                    <div class="col-xs-2">
                        <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                            <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-xs-1">
                        <button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="rm.getLists()">
                            <i class="fa fa-search"></i> Tampilkan
                        </button>
                    </div>
                    <div class="col-xs-5">
                        <?php if ( $akses['a_submit'] == 1 ) { ?>
                            <button id="btn-tampil" type="button" data-href="action" class="btn btn-success cursor-p pull-right" title="Tambah" onclick="rm.addForm()">
                                <i class="fa fa-plus"></i> Tambah
                            </button>
                            <button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="Import" onclick="rm.importForm()" style="margin-right: 10px;">
                                <i class="fa fa-upload"></i> Import Data
                            </button>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr class="margin-top: 10px; margin-bottom: 10px;">
                </div>
                <div class="col-xs-12 no-padding">
                    <span>Klik 2x untuk melihat detail</span>
                </div>
                <div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th class="text-center col-xs-1">Kode</th>
                                    <th class="text-center col-xs-2">No Bukti</th>
                                    <th class="text-center col-xs-1">Tanggal</th>
                                    <th class="text-center col-xs-2">Perusahaan</th>
                                    <th class="text-center col-xs-1">Bakul</th>
                                    <th class="text-center col-xs-2">Keterangan</th>
                                    <th class="text-center col-xs-1">Jml Transfer</th>
                                    <th class="text-center col-xs-1">Terpakai</th>
                                    <th class="text-center col-xs-1">Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8">Data tidak ditemukan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </small>
                </div>
			</div>
		</form>
	</div>
</div>