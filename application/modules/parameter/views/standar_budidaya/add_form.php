<div class="col-lg-1 no-padding pull-left">
	<h6>Tgl Berlaku : </h6>
</div>
<div class="col-lg-2 no-padding action">
    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
        <input type="text" class="form-control text-center" data-required="1" />
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
</div>
<div class="col-lg-6">
	<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="SAVE" onclick="sb.save(this)"> 
		<i class="fa fa-save" aria-hidden="true"></i> SAVE
	</button>
</div>
<table class="table table-bordered table-hover" id="tb_input_standar_budidaya" width="100%" cellspacing="0">
	<thead>
		<tr>
			<th class="text-center">Umur (hari)</th>
			<th class="text-center">Berat Badan (g)</th>
			<th class="text-center">FCR</th>
			<th class="text-center">Daya Hidup (%)</th>
			<th class="text-center">IP</th>
			<th class="text-center">Konsumsi Pakan Perhari (g)</th>
			<th class="text-center">Action</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="umur" value="" data-tipe="integer" isedit="1">
			</td>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="bb" value="" data-tipe="integer" onchange="" isedit="1">
			</td>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="fcr" value="" data-tipe="decimal3" isedit="1">
			</td>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="daya_hidup" value="" data-tipe="decimal" isedit="1">
			</td>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="ip" value="" data-tipe="integer" isedit="1" onchange="">
			</td>
			<td class="col-sm-1">
				<input class="form-control text-right" type="text" name="kons_pakan_harian" value="" data-tipe="integer" isedit="1" onchange="">
			</td>
			<td class="action text-center col-sm-1">
				<button type="button" class="btn btn-sm btn-danger" onclick="sb.removeRowTable(this)"><i class="fa fa-minus"></i></button>
				<button type="button" class="btn btn-sm btn-default" onclick="sb.addRowTable(this)"><i class="fa fa-plus"></i></button>
			</td>
		</tr>
	</tbody>
</table>