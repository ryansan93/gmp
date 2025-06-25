<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rt.addFormRm()"><i class="fa fa-plus"></i> ADD</button>
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 5px;"></div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;"><label class="control-label text-left">Tanggal</label></div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="input-group date" id="start_date_rm">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="input-group date" id="end_date_rm">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rt.getListsRm()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding"><span>Klik pada baris untuk melihat detail.</span></div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="col-xs-1">Tanggal</th>
								<th class="col-xs-2">Perusahaan</th>
								<th class="col-xs-2">Nominal</th>
								<th class="col-xs-3">Lampiran</th>
								<th class="col-xs-3">Keterangan</th>
								<th class="col-xs-1"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="6">Data tidak ditemukan.</td>
							</tr>
						</tbody>
					</table>
				</small>
			</div>
		</div>
	</div>
</form>