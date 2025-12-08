<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Per Tanggal</label></div>
				<div class="col-sm-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
						<input type="text" class="form-control text-center" placeholder="Per Tanggal" data-required="1" />
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
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
				<button type="button" class="col-xs-12 btn btn-primary" onclick="ssa.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
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
				<table class="table table-bordered" id="tbl_data" style="margin-bottom: 0px;">
					<tbody></tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="ssa.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
	</div>
</div>