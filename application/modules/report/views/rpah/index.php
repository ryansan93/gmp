<?php // cetak_r($supplier); ?>
<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="form-group">
				<div class="col-sm-1 no-padding text-right">
					<label class="control-label">Tgl Panen</label>
				</div>
				<div class="col-sm-2">
					<div class="input-group date datetimepicker" name="startDate" id="StartDate">
				        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
				<div class="col-sm-1 text-center no-padding" style="max-width: 4%; margin-top: 7px;">s/d</div>
				<div class="col-sm-2">
					<div class="input-group date datetimepicker" name="endDate" id="EndDate">
				        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
				<div class="col-sm-2">
					<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="rpah.get_lists()">Tampilkan</button>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<hr style="margin-top: 0px; margin-bottom: 0px;">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<table class="table table-bordered tbl_rpah">
						<tbody>
							<tr class="unit">
								<th colspan="8">
									<div class="col-md-12 no-padding">
										<div class="col-md-6 no-padding">
											Unit
										</div>
										<div class="col-md-6 no-padding text-right">
											Bottom Price : -
										</div>
									</div>
								</th>
							</tr>
							<tr class="head">
								<td class="col-sm-3">Peternak</td>
								<td class="col-sm-2">Noreg</td>
								<td class="col-sm-1 text-right">Tonase</td>
								<td class="col-sm-1 text-right">Ekor</td>
								<td class="col-sm-1"></td>
								<td class="col-sm-1">Penjualan</td>
								<td class="col-sm-1 text-right">Tonase</td>
								<td class="col-sm-1 text-right">Ekor</td>
							</tr>
							<tr class="detail">
								<td colspan="8">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th class="col-md-2">Nama Pelanggan</th>
												<th class="col-md-2">Outstanding</th>
												<th class="col-md-1">Tonase</th>
												<th class="col-md-1">Ekor</th>
												<th class="col-md-1">BB</th>
												<th class="col-md-1">Harga</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="6">Data tidak ditemukan.</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>