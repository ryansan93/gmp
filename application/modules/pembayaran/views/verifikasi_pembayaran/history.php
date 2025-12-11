<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">TGL BAYAR</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="startDate">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="endDate">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 0px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">JENIS TRANSAKSI</label></div>
		<div class="col-xs-12 no-padding">
			<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="1">
				<option value="all">All</option>
				<option value="doc">DOC</option>
				<option value="voadip">OVK</option>
				<option value="pakan">PAKAN</option>
				<option value="plasma">PLASMA</option>
				<option value="oa pakan">OA PAKAN</option>
				<option value="piutang plasma">PIUTANG PLASMA</option>
				<option value="peralatan">PERALATAN</option>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">BANK</label></div>
        <div class="col-xs-12 no-padding">
            <select class="form-control bank">
                <option value="all">ALL</option>
                <?php foreach ($bank as $key => $value) { ?>
                    <option value="<?php echo $value['no_coa']; ?>"><?php echo $value['no_coa'].' | '.$value['nama_coa']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
	<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="vp.getLists()"> 
			<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
		</button>
	</div>
</form>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<table class="table table-bordered table-hover tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">TRANSAKSI</th>
				<th class="col-xs-2">SUPPLIER</th>
				<th class="col-xs-1">TGL BAYAR</th>
				<th class="col-xs-1">NO. BUKTI</th>
				<th class="col-xs-2">KET BAYAR</th>
				<th class="col-xs-1">TRANSFER</th>
				<th class="col-xs-2">LAMPIRAN</th>
				<th class="col-xs-1">DETAIL</th>
				<th class="col-xs-1">ACTION</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="9">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>