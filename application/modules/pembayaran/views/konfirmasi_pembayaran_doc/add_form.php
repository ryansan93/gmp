<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Periode Order</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="start_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="end_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-xs-12 no-padding">
		<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
			<option value="all" > All </option>
			<?php foreach ($unit as $key => $v_unit): ?>
				<option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_supplier" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Supplier</option>
			<?php foreach ($supplier as $k => $val): ?>
				<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>"><?php echo strtoupper($val['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_perusahaan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Perusahaan</option>
			<?php foreach ($perusahaan as $k => $val): ?>
				<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>"><?php echo strtoupper($val['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<button type="button" class="btn btn-primary col-xs-12" onclick="kpd.get_data_doc()"><i class="fa fa-search"></i> Tampilkan</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="7"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b>0</b></td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl Order</th>
					<th class="col-xs-1">Kota/Kab</th>
					<th class="col-xs-2">Perusahaan</th>
					<th class="col-xs-2">No. Order</th>
					<th class="col-xs-2">Peternak</th>
					<th style="width: 5%;">Kandang</th>
					<th style="width: 5%;">Populasi</th>
					<th style="width: 5%;">Harga</th>
					<th class="col-xs-1">Sub Total</th>
					<th class="text-center" style="width: 5%;">
						<input type="checkbox" class="cursor-p check_all" data-target="list_data" >
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="10">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kpd.submit(this)"><i class="fa fa-check"></i> Submit</button>
</div>