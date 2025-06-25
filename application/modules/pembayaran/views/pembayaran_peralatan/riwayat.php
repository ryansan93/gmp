<div class="col-xs-12 no-padding">
	<button type="button" data-href="action" class="btn btn-success cursor-p col-xs-12" title="ADD" onclick="pp.changeTabActive(this)"> 
		<i class="fa fa-plus" aria-hidden="true"></i> ADD
	</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Start Date Bayar</label></div>
			<div class="input-group date" name="startDate" id="StartDate">
		        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">End Date Bayar</label></div>
			<div class="input-group date" name="endDate" id="EndDate">
		        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
	<div class="col-xs-12 no-padding">
		<!-- <select id="select_supplier" class="form-control selectpicker" data-live-search="true" type="text" data-required="1"> -->
		<select class="form-control supplier" multiple="multiple" data-required="1">
			<option value="all">All</option>
			<?php if ( isset($supplier) && !empty($supplier) ): ?>
				<?php foreach ($supplier as $k => $val): ?>
					<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>"><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			<?php endif ?>
		</select>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Plasma</label></div>
	<div class="col-xs-12 no-padding">
		<!-- <select id="select_perusahaan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1"> -->
		<select class="form-control mitra" multiple="multiple" data-required="1">
			<option value="all">All</option>
			<?php if ( isset($mitra) && !empty($mitra) ): ?>
				<?php foreach ($mitra as $k => $val): ?>
					<option value="<?php echo $val['nomor']; ?>"><?php echo $val['kode_unit'].' | '.strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			<?php endif ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
	<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="pp.getLists()"> 
		<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
	</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">Tgl Bayar</th>
				<th class="col-xs-2">No. Faktur</th>
				<th class="col-xs-2">Supplier</th>
				<th class="col-xs-3">Plasma</th>
				<th class="col-xs-2">Tagihan</th>
				<th class="col-xs-2">Bayar</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="6">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>