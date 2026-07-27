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
					<ul class="nav nav-tabs nav-justified">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#tabFaktur" data-tab="tabFaktur">Sheet: Faktur</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#tabDetail" data-tab="tabDetail">Sheet: DetailFaktur</a>
						</li>
					</ul>
					<div class="tab-content">
						<div id="tabFaktur" class="tab-pane show active" style="padding-top: 10px;">
							<div class="col-xs-12 no-padding" style="overflow-x: auto;">
								<small>
									<table class="table table-bordered tbl_laporan tbl_faktur" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th>Baris</th>
												<th>Tanggal Faktur</th>
												<th>Jenis Faktur</th>
												<th>Kode Transaksi</th>
												<th>Keterangan Tambahan</th>
												<th>Dokumen Pendukung</th>
												<th>Referensi</th>
												<th>Cap Fasilitas</th>
												<th>ID TKU Penjual</th>
												<th>NPWP/NIK Pembeli</th>
												<th>Jenis ID Pembeli</th>
												<th>Negara Pembeli</th>
												<th>Nomor Dokumen Pembeli</th>
												<th>Nama Pembeli</th>
												<th>Alamat Pembeli</th>
												<th>Email Pembeli</th>
												<th>ID TKU Pembeli</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="17">Data tidak ditemukan.</td>
											</tr>
										</tbody>
									</table>
								</small>
							</div>
							<div class="col-xs-12 no-padding pagination-faktur" style="padding-top: 8px;">
								<button type="button" class="btn btn-sm btn-default btn-prev"><i class="fa fa-chevron-left"></i> Sebelumnya</button>
								<button type="button" class="btn btn-sm btn-default btn-next">Selanjutnya <i class="fa fa-chevron-right"></i></button>
								<span class="page-info" style="margin-left: 10px;">Halaman 1 dari 1 (total 0 baris)</span>
							</div>
						</div>
						<div id="tabDetail" class="tab-pane" style="padding-top: 10px;">
							<div class="col-xs-12 no-padding" style="overflow-x: auto;">
								<small>
									<table class="table table-bordered tbl_laporan tbl_detail" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<td colspan="6"><b>TOTAL</b></td>
												<td class="total text-right" data-target="jumlah" data-jenis="decimal"><b>0</b></td>
												<td></td>
												<td class="total text-right" data-target="dpp" data-jenis="decimal"><b>0</b></td>
												<td class="total text-right" data-target="dppnl" data-jenis="decimal"><b>0</b></td>
												<td></td>
												<td class="total text-right" data-target="ppn" data-jenis="decimal"><b>0</b></td>
												<td colspan="2"></td>
											</tr>
											<tr>
												<th>Baris</th>
												<th>Barang/Jasa</th>
												<th>Kode Barang Jasa</th>
												<th>Nama Barang/Jasa</th>
												<th>Nama Satuan Ukur</th>
												<th>Harga Satuan</th>
												<th>Jumlah Barang Jasa</th>
												<th>Total Diskon</th>
												<th>DPP</th>
												<th>DPP Nilai Lain</th>
												<th>Tarif PPN</th>
												<th>PPN</th>
												<th>Tarif PPnBM</th>
												<th>PPnBM</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="14">Data tidak ditemukan.</td>
											</tr>
										</tbody>
									</table>
								</small>
							</div>
							<div class="col-xs-12 no-padding pagination-detail" style="padding-top: 8px;">
								<button type="button" class="btn btn-sm btn-default btn-prev"><i class="fa fa-chevron-left"></i> Sebelumnya</button>
								<button type="button" class="btn btn-sm btn-default btn-next">Selanjutnya <i class="fa fa-chevron-right"></i></button>
								<span class="page-info" style="margin-left: 10px;">Halaman 1 dari 1 (total 0 baris)</span>
							</div>
						</div>
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
