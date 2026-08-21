<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label> Unit </label>
					</div>
					<div class="col-xs-12 no-padding">
						<select class="form-control unit" data-required="1">
							<option value="all">ALL</option>
							<?php foreach ($unit as $k_unit => $v_unit) { ?>
								<option value="<?php echo $v_unit['kode']; ?>"><?php echo $v_unit['nama']; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label> Periode Tutup Siklus </label>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-6 no-padding" style="max-width: 47.5%;">
							<div class="input-group date datetimepicker" name="startDate" id="StartDate">
								<input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
						<div class="col-xs-6 no-padding text-center" style="width: 5%; max-width: 5%;">
							<b>s/d</b>
						</div>
						<div class="col-xs-6 no-padding" style="max-width: 47.5%;">
							<div class="input-group date datetimepicker" name="endDate" id="EndDate">
								<input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 no-padding" style="padding-right: 5px;">
					<button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="rv.getLists()">Tampilkan</button>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px;">
					<button type="button" class="col-xs-12 btn btn-default pull-left" onclick="rv.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding data">
					<button type="button" class="btn-scroll-table btn-scroll-table-left" onclick="rv.scrollTable(-1)" title="Geser Kiri"><i class="fa fa-chevron-left"></i></button>
					<button type="button" class="btn-scroll-table btn-scroll-table-right" onclick="rv.scrollTable(1)" title="Geser Kanan"><i class="fa fa-chevron-right"></i></button>
					<small>
						<div class="table-responsive" id="tbl_rhpp_v2_wrapper">
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<thead>
								<tr style="border-top: 1px solid #ddd;">
									<th rowspan="2" class="text-center" style="border-top: 1px solid #ddd;">Nama Plasma</th>
									<th rowspan="2" class="text-center" style="border-top: 1px solid #ddd;">Unit</th>
									<th rowspan="2" class="text-center" style="border-top: 1px solid #ddd;">Noreg</th>
									<th colspan="2" class="text-center" style="border-top: 1px solid #ddd;">Chick In</th>
									<th colspan="8" class="text-center" style="border-top: 1px solid #ddd;">Performance</th>
									<th colspan="6" class="text-center" style="border-top: 1px solid #ddd;">Pendapatan Plasma</th>
									<th rowspan="2" class="text-center" style="border-top: 1px solid #ddd;">Total Pendapatan Plasma</th>
									<th rowspan="2" class="text-center" style="border-top: 1px solid #ddd;">Total Pendapatan Inti</th>
								</tr>
								<tr>
									<th class="text-center">Tgl</th>
									<th class="text-center">Populasi</th>
									<th class="text-center">Ekor Panen</th>
									<th class="text-center">Berat Badan</th>
									<th class="text-center">BB Rata2</th>
									<th class="text-center">FCR</th>
									<th class="text-center">Deplesi</th>
									<th class="text-center">Rata2 Umur</th>
									<th class="text-center">ADG</th>
									<th class="text-center">IP</th>
									<th class="text-center">Selisih Budidaya</th>
									<th class="text-center">Bonus Pasar</th>
									<th class="text-center">Bonus Kematian</th>
									<th class="text-center">Bonus FCR</th>
									<th class="text-center">Insentif Listrik</th>
									<th class="text-center">Insentif LPG</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="21" class="text-center">Silahkan pilih filter lalu klik Tampilkan.</td>
								</tr>
							</tbody>
						</table>
						</div>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>
