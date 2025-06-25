<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-4 no-padding">
				<label class="control-label">Pelanggan</label>
			</div>
			<div class="col-xs-8 no-padding">
				<select id="select_pelanggan" class="form-control selectpicker" data-live-search="true" data-required="1">
					<option value="">Pilih Pelanggan</option>
					<option value="all">ALL</option>
					<?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
						<option data-tokens="<?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?>" value="<?php echo $v_dp['nomor']; ?>"><?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>

		<div class="col-xs-12 no-padding">
			<div class="col-xs-4 no-padding">
				<label class="control-label">Tanggal Bayar</label>
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
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="bakul.get_list_pembayaran()"> 
			<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
		</button>
	</div>
	<div class="col-xs-12 no-padding">
		<button id="btn-add" type="button" data-href="transaksi" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="bakul.changeTabActive(this)"> 
			<i class="fa fa-plus" aria-hidden="true"></i> ADD
		</button>
	</div>
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
				<th class="col-xs-2">Tgl Bayar</th>
				<th class="col-xs-4">Pelanggan</th>
				<th class="col-xs-3">Jml Transfer</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>