<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label> Jenis RHPP </label>
					</div>
					<div class="col-xs-12 no-padding">
						<select class="form-control jenis_rhpp" data-required="1">
							<option value="all">ALL</option>
							<option value="0">MERAH</option>
							<option value="1">HIJAU</option>
						</select>
					</div>
				</div>
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
				<div class="col-xs-12 no-padding">
					<button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="lr.getLists()">Tampilkan</button>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding data">
					<small>
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th style="width: 2%;">No.</th>
									<th style="width: 5%;">Kandang</th>
									<th style="width: 20%;">Nama Plasma</th>
									<th style="width: 10%;">Tgl Chick In</th>
									<th style="width: 5%;">Populasi</th>
									<th style="width: 5%;">Umur</th>
									<th style="width: 5%;">Deplesi</th>
									<th style="width: 5%;">FCR</th>
									<th style="width: 5%;">BW</th>
									<th style="width: 5%;">IP</th>
									<th style="width: 10%;">L/R Plasma</th>
									<th style="width: 7%;">L/R Plasma Per Ekor</th>
									<th style="width: 10%;">L/R Inti</th>
									<th style="width: 7%;">L/R Plasma Per Ekor</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="14">Data tidak di temukan.</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>
