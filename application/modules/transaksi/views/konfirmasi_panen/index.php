<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12" id="penerimaan-pakan">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Unit</div>
					<div class="col-lg-2">
						<select class="form-control" name="unit" data-required="1">
							<option value="">-- Pilih Unit --</option>
							<?php foreach ($unit as $k_unit => $v_unit): ?>
								<option value="<?php echo $v_unit['id']; ?>"><?php echo $v_unit['nama']; ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Periode DOC In</div>
					<div class="col-sm-2">
						<div class="input-group date" id="start_date" name="startPeriode">
					        <input type="text" class="form-control text-center" placeholder="Start Date" name="startPeriode" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-1 text-center" style="max-width: 4%;">s/d</div>
					<div class="col-sm-2">
						<div class="input-group date" id="end_date" name="endPeriode">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-2">
						<button type="button" class="btn btn-primary" onclick="kp.get_lists()"><i class="fa fa-search"></i> Cari</button>
					</div>
				</div>
				<hr>
				<div class="form-group">
					<div class="col-lg-12">
						<small>
							<table class="table table-bordered tbl_list_konfirmasi_panen">
								<thead>
									<tr>
										<th class="text-center">Tanggal DOC In</th>
										<th class="text-center">Tanggal Panen</th>
										<th class="text-center">Noreg</th>
										<th class="text-center">Nama Mitra</th>
										<th class="text-center">Populasi</th>
										<th class="text-center">Kandang</th>
										<th class="text-center">Jumlah (Kg)</th>
										<th class="text-center">BB Rata2</th>
									</tr>
								</thead>
								<tbody>
									<tr onclick="kp.load_form(this);">
										<td colspan="8">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						</small>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>