<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-sm-6 no-padding" style="padding-right: 5px;">
				<div class="col-sm-12 no-padding">
					<label>TGL AWAL</label>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="input-group date datetimepicker" name="startDate" id="StartDate">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
				</div>
			</div>
			<div class="col-sm-6 no-padding" style="padding-left: 5px;">
				<div class="col-sm-12 no-padding">
					<label>TGL AKHIR</label>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="input-group date datetimepicker" name="endDate" id="EndDate">
				        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
			</div>

			<!-- <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Bulan</label></div>
				<div class="col-sm-12 no-padding">
					<select class="form-control bulan" data-required="1">
						<option value="all">ALL</option>
						<?php for ($i=1; $i <= 12; $i++) { ?>
							<?php
								$bulan[1] = 'JANUARI';
								$bulan[2] = 'FEBRUARI';
								$bulan[3] = 'MARET';
								$bulan[4] = 'APRIL';
								$bulan[5] = 'MEI';
								$bulan[6] = 'JUNI';
								$bulan[7] = 'JULI';
								$bulan[8] = 'AGUSTUS';
								$bulan[9] = 'SEPTEMBER';
								$bulan[10] = 'OKTOBER';
								$bulan[11] = 'NOVEMBER';
								$bulan[12] = 'DESEMBER';
							?>
							<option value="<?php echo $i; ?>"><?php echo $bulan[ $i ]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Tahun</label></div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tahun" id="Tahun">
						<input type="text" class="form-control text-center" placeholder="Tahun" data-required="1" />
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div> -->
        </div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control unit" data-required="1">
						<option value="all">ALL</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="kkh.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<div class="col-sm-12">
					<div class="row">
						<a class="tu-float-btn tu-table-prev" style="margin-top:-30px;">
							<i class="fa fa-arrow-left my-float"></i>
						</a>

						<a class="tu-float-btn tu-float-btn-right tu-table-next" style="margin-top:-30px;">
							<i class="fa fa-arrow-right my-float"></i>
						</a>
					</div>
				</div>
				<table class="table table-bordered table-hover" style="margin-bottom: 0px;" id="tbl_kkh">
					<thead>
						<tr>
							<th rowspan="2" class="page0 text-center col-xs-1">Unit</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Noreg</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Nama</th>
							<th colspan="2" class="page0 text-center">Chick In</th>
							<th colspan="7" class="page1 text-center">Pakan</th>
							<th colspan="6" class="page2 text-center">OVK</th>
							<th colspan="6" class="page3 text-center">DOC</th>
							<th colspan="6" class="page4 text-center">OA</th>
							<th rowspan="2" class="page0 text-center  col-xs-1">RHPP</th>
							<th rowspan="2" class="page0 text-center  col-xs-1">Total</th>
						</tr>
						<tr>
							<th class="page0 text-center col-xs-1">Tgl</th>
							<th class="page0 text-center col-xs-1">Populasi</th>
							<th class="page1 text-center col-xs-1">Saldo Awal</th>
							<th class="page1 text-center col-xs-1">Beli</th>
							<th class="page1 text-center col-xs-1">Mutasi (+)</th>
							<th class="page1 text-center col-xs-1">Mutasi (-)</th>
							<th class="page1 text-center col-xs-1">Koreksi (+/-)</th>
							<th class="page1 text-center col-xs-1">Pemakaian</th>
							<th class="page1 text-center col-xs-1">Sisa</th>
							<th class="page2 text-center col-xs-1">Saldo Awal</th>
							<th class="page2 text-center col-xs-1">Beli</th>
							<th class="page2 text-center col-xs-1">Mutasi (+)</th>
							<th class="page2 text-center col-xs-1">Mutasi (-)</th>
							<th class="page2 text-center col-xs-1">Koreksi (+/-)</th>
							<th class="page2 text-center col-xs-1">Pemakaian</th>
							<th class="page3 text-center col-xs-1">Saldo Awal</th>
							<th class="page3 text-center col-xs-1">Beli</th>
							<th class="page3 text-center col-xs-1">Mutasi (+)</th>
							<th class="page3 text-center col-xs-1">Mutasi (-)</th>
							<th class="page3 text-center col-xs-1">Koreksi (+/-)</th>
							<th class="page3 text-center col-xs-1">Pemakaian</th>
							<th class="page4 text-center col-xs-1">Saldo Awal</th>
							<th class="page4 text-center col-xs-1">Beli</th>
							<th class="page4 text-center col-xs-1">Mutasi (+)</th>
							<th class="page4 text-center col-xs-1">Mutasi (-)</th>
							<th class="page4 text-center col-xs-1">Koreksi (+/-)</th>
							<th class="page4 text-center col-xs-1">Net OA</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="28">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="kkh.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
	</div>
</div>