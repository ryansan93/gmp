<div class="row content-panel detailed">
	<div class="col-lg-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-body" style="padding-top: 0px;">
				<div class="col-xs-12 filter no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-12 no-padding">
						<label class="control-label">Nama Mitra</label>
					</div>
					<div class="col-xs-12 no-padding">
						<select id="select_mitra" data-placeholder="Pilih Mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
							<option value="">Pilih Mitra</option>
							<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
								<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
							<?php endforeach ?>
						</select>
					</div>

					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding" style="padding-right: 5px;">
							<label class="control-label">Tanggal Jual</label>
						</div>
						<div class="col-xs-2 no-padding" style="padding-right: 5px; padding-left: 5px;">&nbsp;</div>
						<div class="col-xs-2 no-padding" style="padding-left: 50px;">
							<label class="control-label">Filter</label>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-2 no-padding" style="padding-right: 5px;">
							<div class="input-group date datetimepicker" id="StartDateJual">
						        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-xs-2 no-padding" style="padding-right: 5px; padding-left: 5px;">
							<div class="input-group date datetimepicker" id="EndDateJual">
						        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-xs-2 no-padding" style="padding-left: 50px;">
							<select class="form-control filter" data-required="1">
								<option value="ALL">ALL</option>
								<option value="LUNAS">LUNAS</option>
								<option value="BELUM">BELUM</option>
							</select>
						</div>
						<div class="col-xs-6 no-padding">
							<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="TAMPIL" onclick="bpp.get_lists(this)">Tampilkan</button>
						</div>
					</div>
				</div>

				<div class="col-xs-12 no-padding"><hr style="margin-bottom: 10px; margin-top: 5px;"></div>

				<div class="col-xs-12 no-padding">
					<small>
						<span>Klik pada baris untuk melihat detail</span>
						<table class="table table-bordered tbl_penjualan">
							<thead>
								<tr>
									<th class="col-xs-2">Tanggal Jual</th>
									<th class="col-xs-4">Total</th>
									<th class="col-xs-4">Sisa</th>
									<th class="col-xs-2">Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="2">Data tidak ditemukan.</th>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>