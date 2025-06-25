<div class="row content-panel">
	<div class="col-xs-12">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<div class="col-xs-12 no-padding">
			<div class="col-sm-12 no-padding">
				<label> Periode </label>
			</div>
			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<div class="input-group date datetimepicker" name="startDate" id="StartDate">
			        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<div class="input-group date datetimepicker" name="endDate" id="EndDate">
			        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-12 no-padding"><hr style="padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 5px;"></div>
			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<select class="form-control jenis" data-required="1">
					<option value="">-- Pilih Jenis --</option>
					<?php foreach ($jurnal_trans as $key => $value): ?>
						<option value="<?php echo $value['id']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<select class="form-control rekening" data-required="1" disabled>
					<option value="">-- Pilih Rekening --</option>
				</select>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="bbp.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
	</div>
	<div class="col-xs-12"><hr style="padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 5px;"></div>
	<div class="col-xs-12">
		<small>
			<table class="table table-bordered table-hover tbl_laporan" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="col-xs-1 text-center">Tanggal</th>
						<th class="col-xs-1 text-center">Bank</th>
						<th class="col-xs-6 text-center">Keterangan</th>
						<th class="col-xs-2 text-center">Debet</th>
						<th class="col-xs-2 text-center">Kredit</th>
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