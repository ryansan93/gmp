<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat RHPP Group</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#noreg_tutup_siklus" data-tab="noreg_tutup_siklus">Pilih No. Reg Group</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#rhpp" data-tab="rhpp">RHPP Group</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-bottom: 0px;">
				<div class="tab-content">
					<div id="riwayat" class="tab-pane fade show active">
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
								<label class="control-label">Periode Submit</label>
							</div>
							<div class="col-sm-2">
								<div class="input-group date datetimepicker" name="startDateRiwayat" id="StartDateRiwayat">
							        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" style="padding-left: 15px;" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-1 text-center no-padding" ><label class="control-label">s/d</label></div>
							<div class="col-sm-2">
								<div class="input-group date datetimepicker" name="endDateRiwayat" id="EndDateRiwayat">
							        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" style="padding-left: 15px;" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-1 no-padding">
								<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="TAMPIL" onclick="rg.get_lists(this)">Tampilkan</button>
							</div>
						</div>
						<div class="col-sm-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
						<div class="col-sm-12 no-padding">
							<small>
								<table class="table table-bordered tbl_list_rhpp">
									<thead>
										<tr>
											<th class="col-sm-1">Nomor</th>
											<th class="col-sm-2">Mitra</th>
											<th class="col-sm-2">Noreg</th>
											<th class="col-sm-1">Tgl Submit</th>
											<th class="col-sm-1">Action</th>
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
					<div id="noreg_tutup_siklus" class="tab-pane fade">
						<?php echo $add_form; ?>
					</div>
					<div id="rhpp" class="tab-pane fade">
						<h4>RHPP Group</h4>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>