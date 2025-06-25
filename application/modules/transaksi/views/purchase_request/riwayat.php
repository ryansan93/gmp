<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-success" onclick="pr.change_tab(this)" data-id="" data-edit="" data-href="transaksi"><i class="fa fa-plus"></i> Tambah</button>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 0px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Unit</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select class="form-control unit" data-required="1">
						<option value="all">ALL</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <div class="col-xs-12 no-padding">
                        <label class="control-label">Tgl Awal PR</label>
                    </div>
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
                    <div class="col-xs-12 no-padding">
                        <label class="control-label">Tgl Akhir PR</label>
                    </div>
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
		</div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="pr.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<span>* Klik pada baris untuk melihat detail.</span>
				<table class="table table-bordered tbl_riwayat" style="margin-bottom: 10px;">
					<thead>
						<tr>
							<th class="col-xs-2">Tgl PR</th>
							<th class="col-xs-1">Jenis</th>
							<th class="col-xs-1">Unit</th>
							<th class="col-xs-4">Gudang</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="4">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
	</div>
</div>