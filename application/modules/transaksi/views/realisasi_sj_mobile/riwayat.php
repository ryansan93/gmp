<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-4 no-padding">
				<label class="control-label">Unit</label>
			</div>
			<div class="col-xs-8 no-padding">
				<select id="select_unit" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
					<option value="">Pilih Unit</option>
					<?php foreach ($unit as $k_unit => $v_unit): ?>
						<option data-tokens="<?php echo $v_unit['nama']; ?>" value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>

		<div class="col-xs-12 no-padding">
			<div class="col-xs-4 no-padding">
				<label class="control-label">Tanggal Panen</label>
			</div>
			<div class="col-xs-8 no-padding">
				<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
	                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" />
	                <span class="input-group-addon">
	                    <span class="glyphicon glyphicon-calendar"></span>
	                </span>
	            </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rsm.get_lists()"> 
			<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
		</button>
	</div>
	<?php if ( $akses['a_submit'] == 1 && empty($akses['a_khusus']) ): ?>
		<div class="col-xs-12 no-padding">
			<button id="btn-add" type="button" data-href="transaksi" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rsm.changeTabActive(this)"> 
				<i class="fa fa-plus" aria-hidden="true"></i> ADD
			</button>
		</div>
	<?php endif ?>
</form>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rpah" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<span>Klik pada baris untuk melihat detail.</span>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-6">Mitra</th>
				<th class="col-xs-2">Ekor</th>
				<th class="col-xs-2">Kg</th>
				<th class="col-xs-1">BB</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="4">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>