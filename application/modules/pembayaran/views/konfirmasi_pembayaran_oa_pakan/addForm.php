<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Periode Terima</label></div>
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
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control unit" multiple="multiple" width="100%" data-required="1">
				<option value="all" > All </option>
				<?php foreach ($unit as $key => $v_unit): ?>
					<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Filter</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control filter" multiple="multiple" width="100%" data-required="1">
				<option value="mutasi">Mutasi</option>
				<option value="not_mutasi">Not Mutasi</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		&nbsp;
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Kirim</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_kirim" multiple="multiple" width="100%" data-required="1">
				<option value="">-- Pilih Jenis --</option>
				<option value="opks">Order Pabrik (OPKS)</option>
				<option value="opkp">Dari Peternak (OPKP)</option>
				<option value="opkg">Dari Gudang (OPKG)</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
		<div class="col-xs-12 no-padding">
			<select id="select_perusahaan" class="form-control" type="text" data-required="1">
				<option value="">Pilih Perusahaan</option>
				<?php foreach ($perusahaan as $k => $val): ?>
					<option value="<?php echo $val['kode']; ?>"><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Ekspedisi</label></div>
		<div class="col-xs-12 no-padding">
			<select id="select_ekspedisi" class="form-control" type="text" data-required="1">
				<option value="">Pilih Ekspedisi</option>
				<?php foreach ($ekspedisi as $k => $val): ?>
					<option value="<?php echo $val['nomor']; ?>"><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<button type="button" class="btn btn-primary col-xs-12" onclick="kpoap.getDataOa()"><i class="fa fa-search"></i> Tampilkan</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 10px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_list" placeholder="Search" onkeyup="filter_all(this)">
</div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="5"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b>0</b></td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl Terima</th>
					<th class="col-xs-2">Ekspedisi</th>
					<th class="col-xs-1">No. Polisi</th>
					<th class="col-xs-1">No. SJ</th>
					<th class="col-xs-2">Asal</th>
					<th class="col-xs-2">Tujuan</th>
					<th class="col-xs-1">Sub Total</th>
					<th class="col-xs-1 text-center">
						<input type="checkbox" class="cursor-p checkAll" data-target="sj" >
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="8">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kpoap.submit(this)"><i class="fa fa-check"></i> Submit</button>
</div>