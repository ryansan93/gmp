<?php if ( $akses['a_submit'] == 1 ): ?>
    <div class="col-xs-12 no-padding">
        <button id="btn-add" type="button" data-href="action" class="btn btn-success cursor-p col-xs-12" title="ADD" onclick="pk.changeTabActive(this)"> 
            <i class="fa fa-plus" aria-hidden="true"></i> ADD
        </button>
    </div>
    <div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<?php endif ?>
<div class="col-xs-12 no-padding">
    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tgl Awal</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                <input type="text" class="form-control text-center uppercase" placeholder="Tgl Awal" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tgl Akhir</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                <input type="text" class="form-control text-center uppercase" placeholder="Tgl Akhir" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
    <button type="button" class="btn btn-primary col-xs-12" title="ADD" onclick="pk.getLists()"> 
        <i class="fa fa-search" aria-hidden="true"></i> Tampilkan
    </button>
</div>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<span>Klik pada baris untuk melihat detail.</span>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">Kode</th>
				<th class="col-xs-2">Tgl Hutang</th>
				<th class="col-xs-4">Nama</th>
				<th class="col-xs-3">Perusahaan</th>
				<th class="col-xs-2">Nominal (Rp.)</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="5">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>