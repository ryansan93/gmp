<div class="row content-panel detailed">
	<div class="col-lg-12 no-padding detailed">
		<form role="form" class="form-horizontal fp-ortax">
			<div class="panel-body" style="padding-top: 10px;">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>TGL PANEN AWAL</label>
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
					<div class="col-xs-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>TGL PANEN AKHIR</label>
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
					<div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>PERUSAHAAN</label>
						</div>
						<div class="col-xs-12 no-padding">
							<select class="form-control perusahaan" multiple="multiple" data-required="1">
								<option value="all">ALL</option>
								<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
									<option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-xs-4 no-padding" style="padding-left: 5px; padding-right: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>UNIT</label>
						</div>
						<div class="col-xs-12 no-padding">
							<select class="form-control unit" multiple="multiple" data-required="1">
								<option value="all">ALL</option>
								<?php foreach ($unit as $k_unit => $v_unit): ?>
									<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-xs-2 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>TUTUP SIKLUS</label>
						</div>
						<div class="col-xs-12 no-padding">
							<select class="form-control tutup_siklus" data-required="1">
								<option value="all">ALL</option>
								<option value="1">SUDAH TUTUP SIKLUS</option>
								<option value="0">BELUM TUTUP SIKLUS</option>
							</select>
						</div>
					</div>

					<div class="col-xs-12 no-padding">
						<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="fpOrtax.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 no-padding" style="overflow-x: auto;">
						<small>
							<table class="table table-bordered tbl_laporan" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<td colspan="6"><b>TOTAL</b></td>
										<td class="total text-right" data-target="dpp" data-jenis="decimal"><b>0</b></td>
										<td class="total text-right" data-target="ppn" data-jenis="decimal"><b>0</b></td>
										<td></td>
									</tr>
									<tr>
										<th>Baris</th>
										<th>No. Faktur (Referensi)</th>
										<th>Tanggal Faktur</th>
										<th>Jenis ID / NPWP-NIK Pembeli</th>
										<th>Nama Pembeli</th>
										<th>Jumlah Item</th>
										<th>Total DPP</th>
										<th>Total PPN</th>
										<th>Alamat Pembeli</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						</small>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<button type="button" class="btn btn-default pull-right" onclick="fpOrtax.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
				</div>
			</div>
		</form>
	</div>
</div>
