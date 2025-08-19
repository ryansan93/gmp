<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<button id="btn-add" type="button" data-href="transaksi" class="btn btn-success cursor-p col-xs-12" title="ADD" onclick="rp.changeTabActive(this)"> 
			<i class="fa fa-plus" aria-hidden="true"></i> ADD
		</button>
	</div>
	<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Bayar</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="start_date_bayar">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="end_date_bayar">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 0px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
		<div class="col-xs-12 no-padding">
			<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="1">
				<option value="all">All</option>
				<option value="doc">DOC</option>
				<option value="voadip">OVK</option>
				<option value="pakan">PAKAN</option>
				<option value="plasma">PLASMA</option>
				<option value="oa pakan">OA PAKAN</option>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 0px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
		<div class="col-xs-12 no-padding">
			<select class="perusahaan" multiple="multiple" width="100%" data-required="1">
				<option value="all">All</option>
				<?php foreach ($perusahaan as $k => $val): ?>
					<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>"><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rp.get_lists()"> 
			<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
		</button>
	</div>
</form>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_transaksi" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">Tgl Bayar</th>
				<th class="col-xs-1">No. Bayar</th>
				<th class="col-xs-1">Transaksi</th>
				<th class="col-xs-2">Perusahaan</th>
				<th class="col-xs-2">Nama Penerima</th>
				<th class="col-xs-1">DN</th>
				<th class="col-xs-1">CN</th>
				<th class="col-xs-1">Transfer</th>
				<th class="col-xs-1">Uang Muka</th>
				<th class="col-xs-1">No. Bukti</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="10">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>