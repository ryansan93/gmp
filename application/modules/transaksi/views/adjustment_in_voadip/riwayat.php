<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-success" onclick="aiv.changeTabActive(this)" data-id="" data-edit="" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">Start Date</label>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" name="startDate" id="StartDate">
		            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		            <span class="input-group-addon">
		                <span class="glyphicon glyphicon-calendar"></span>
		            </span>
		        </div>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">End Date</label>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" name="endDate" id="EndDate">
		            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		            <span class="input-group-addon">
		                <span class="glyphicon glyphicon-calendar"></span>
		            </span>
		        </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Gudang</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control param_getsj gudang" data-required="1">
					<option value="all">ALL</option>
					<?php foreach ($gudang as $key => $value): ?>
						<option value="<?php echo $value['id']; ?>"><?php echo $value['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Barang</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control param_getsj barang" data-required="1">
					<option value="all">ALL</option>
					<?php foreach ($barang as $key => $value): ?>
						<option value="<?php echo $value['kode']; ?>"><?php echo $value['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="aiv.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 action no-padding">
		<div class="col-lg-12 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
		</div>
	</div>
	<div class="col-xs-12 action no-padding">
		<small>
			<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-1">Tgl. Adjout</th>
						<th class="col-xs-2">No. Adjout</th>
						<th class="col-xs-3">Gudang</th>
						<th class="col-xs-3">Barang</th>
						<th class="col-xs-2">Jumlah</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="5">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>