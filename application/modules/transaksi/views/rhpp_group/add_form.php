<div class="col-lg-12 no-padding filter_noreg">
	<div class="col-sm-1 no-padding">
		<label class="control-label">Peternak</label>
	</div>
	<div class="col-sm-3">
		<select class="form-control selectpicker mitra" data-live-search="true" data-required="1">
			<option value="">Pilih Peternak</option>
			<?php foreach ($mitra as $k_mitra => $v_mitra): ?>
				<option data-tokens="<?php echo $v_mitra['nama']; ?>" value="<?php echo $v_mitra['nomor']; ?>"><?php echo strtoupper($v_mitra['unit'].' | '.$v_mitra['nama']); ?></option>
			<?php endforeach ?>
			<!-- <option data-tokens="ryan santoso" value="">Ryan Santoso</option> -->
		</select>
	</div>
	<!-- <div class="col-sm-1">&nbsp;</div> -->
	<div class="col-sm-2 no-padding text-right">
		<label class="control-label">Periode Chick In</label>
	</div>
	<div class="col-sm-2">
		<div class="input-group date datetimepicker" name="startDate" id="StartDate">
	        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" style="padding-left: 15px;" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-sm-1 text-center no-padding" ><label class="control-label">s/d</label></div>
	<div class="col-sm-2">
		<div class="input-group date datetimepicker" name="endDate" id="EndDate">
	        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" style="padding-left: 15px;" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-sm-1 no-padding">
		<button id="btn-tampil-noreg" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="TAMPIL" onclick="rg.get_noreg(this)">Tampilkan</button>
	</div>
</div>
<div class="col-lg-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-12 no-padding"><label>List No. Reg sudah tutup siklus</label></div>
	<div class="col-lg-12 no-padding">
		<small>
			<table class="table table-bordered tbl_tutup_siklus" id="dataTable" width="100%" cellspacing="0" style="padding-bottom: 0px; margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-md-1">Noreg</th>
						<th class="col-md-1">Kandang</th>
						<th class="col-md-1">Chick In</th>
						<th class="text-center" style="width: 2%;">
							<input class="check_all cursor-p" data-target="check" type="checkbox">
						</th>
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
	<div class="col-lg-12 no-padding" style="margin-top: 5px;">
		<button type="button" class="btn btn-primary pull-right" onclick="rg.proses_hit_rhpp_group(this)" data-href="rhpp">Proses</button>
	</div>
</div>