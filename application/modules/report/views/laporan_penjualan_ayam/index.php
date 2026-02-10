<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain harian" style="margin-bottom: 10px;">
            <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Tgl Awal</label></div>
                <div class="col-xs-12 no-padding">
                    <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
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
                        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control unit" data-required="1" multiple="multiple">
						<option value="all">ALL</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>" ><?php echo $v_unit['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding"><label class="control-label">Pelanggan</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control pelanggan" data-required="1" multiple="multiple">
						<option value="all">ALL</option>
						<?php foreach ($pelanggan as $k_pelanggan => $v_pelanggan): ?>
							<option value="<?php echo $v_pelanggan['nomor']; ?>" ><?php echo strtoupper($v_pelanggan['nama'].' ('.str_replace('Kota ', '', str_replace('Kab ', '', $v_pelanggan['kab_kota'])).')'); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-1">Tanggal</th>
							<th class="col-xs-2">Customer</th>
							<th class="col-xs-2">Kandang</th>
							<th class="col-xs-1">No. Invoice</th>
							<th class="col-xs-1">Nota Timbang</th>
							<th class="col-xs-1">Jenis Ayam</th>
							<th class="col-xs-1">Ekor</th>
							<th class="col-xs-1">KG</th>
							<th class="col-xs-1">Harga</th>
							<!-- <th class="col-xs-1"></th> -->
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="kk.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
	</div>
</div>