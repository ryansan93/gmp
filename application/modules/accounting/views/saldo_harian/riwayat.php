<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-success cursor-p" onclick="sld.changeTabActive(this)" data-href="action" data-edit="" data-id=""><i class="fa fa-plus"></i> Tambah</button>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label" style="padding-top: 0px;">Tanggal</label>
			</div>
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<div class="input-group date datetimepicker" name="startDate" id="StartDate">
			        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" style="padding-left: 15px;" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<div class="input-group date datetimepicker" name="endDate" id="EndDate">
			        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" style="padding-left: 15px;" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label" style="padding-top: 0px;">Perusahaan</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control perusahaan" data-required="1" multiple="multiple">
					<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
						<option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-primary cursor-p" title="TAMPIL" onclick="sld.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
	<input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<div class="col-xs-12 no-padding">
	<span>* Klik pada baris untuk melihat detail</span>
	<small>
		<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1 text-center">No</th>
					<th class="col-xs-3 text-center">Tanggal</th>
					<th class="col-xs-8 text-center">Perusahaan</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="3">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>