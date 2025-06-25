<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12 no-padding">
				<div class="col-lg-12 search left-inner-addon no-padding d-flex align-items-center">
					<div class="col-sm-1 no-padding">
						<label> Periode </label>
					</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="startDate" id="StartDate_RLB">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" style="padding-left: 15px;" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="endDate" id="EndDate_RLB">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" style="padding-left: 15px;" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-4 no-padding">
						<select class="form-control pelanggan" data-required="1">
							<option value="all">All</option>
							<?php if ( !empty($pelanggan) ): ?>
								<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
									<option value="<?php echo $v_plg['nomor']; ?>"><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['kab_kota']).')'; ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</div>
					<div class="col-sm-2">
						<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="rlb.get_lists(this)">Tampilkan</button>
					</div>
				</div>
			</div>
			<div class="col-lg-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-lg-12 no-padding">
				<small>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="col-lg-1">Tgl DO</th>
								<th class="col-lg-1">Tgl Bayar</th>
								<th class="col-lg-2">No. DO</th>
								<th class="col-lg-2">No. SJ</th>
								<th class="col-lg-1">Tagihan</th>
								<th class="col-lg-1">Total Tagihan</th>
								<th class="col-lg-1">Total Bayar</th>
								<th class="col-lg-1">Lebih Bayar</th>
								<th class="col-lg-1">Lama Bayar (Hari)</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="9">Data tidak ditemukan.</td>
							</tr>
						</tbody>
					</table>
				</small>
			</div>
		</form>
	</div>
</div>