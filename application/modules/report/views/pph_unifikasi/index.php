<div class="row content-panel detailed">
	<div class="col-lg-12 no-padding detailed">
		<form role="form" class="form-horizontal pph-unifikasi">
			<div class="panel-body" style="padding-top: 10px;">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>BULAN</label>
						</div>
						<div class="col-xs-12 no-padding">
							<select class="form-control bulan" data-required="1">
								<?php
									$bulan[1] = 'JANUARI';
									$bulan[2] = 'FEBRUARI';
									$bulan[3] = 'MARET';
									$bulan[4] = 'APRIL';
									$bulan[5] = 'MEI';
									$bulan[6] = 'JUNI';
									$bulan[7] = 'JULI';
									$bulan[8] = 'AGUSTUS';
									$bulan[9] = 'SEPTEMBER';
									$bulan[10] = 'OKTOBER';
									$bulan[11] = 'NOVEMBER';
									$bulan[12] = 'DESEMBER';
								?>
								<?php for ($i=1; $i <= 12; $i++): ?>
									<option value="<?php echo $i; ?>"><?php echo $bulan[ $i ]; ?></option>
								<?php endfor ?>
							</select>
						</div>
					</div>
					<div class="col-xs-3 no-padding" style="padding-left: 5px; padding-right: 5px; margin-bottom: 10px;">
						<div class="col-xs-12 no-padding">
							<label>TAHUN</label>
						</div>
						<div class="col-xs-12 no-padding">
							<div class="input-group date datetimepicker" name="tahun" id="Tahun">
								<input type="text" class="form-control text-center" placeholder="Tahun" data-required="1" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
					<div class="col-xs-3 no-padding" style="padding-left: 5px; padding-right: 5px; margin-bottom: 10px;">
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
					<div class="col-xs-3 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
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

					<div class="col-xs-12 no-padding">
						<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="pphUnifikasi.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 no-padding" style="overflow-x: auto;">
						<small>
							<table class="table table-bordered tbl_laporan tbl_pph" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<td colspan="9"><b>TOTAL BRUTO</b></td>
										<td class="total text-right" data-target="bruto" data-jenis="decimal"><b>0</b></td>
										<td colspan="5"></td>
									</tr>
									<tr>
										<th>No</th>
										<th>Masa Pajak</th>
										<th>Tahun Pajak</th>
										<th>Tgl Pemotongan</th>
										<th>NITKU Pemotong</th>
										<th>NPWP/NIK Penerima</th>
										<th>NITKU Penerima</th>
										<th>Nama Penerima</th>
										<th>Kode Objek Pajak</th>
										<th>Bruto</th>
										<th>Fasilitas</th>
										<th>No Fasilitas</th>
										<th>Jenis Dokumen</th>
										<th>Nomor Dokumen</th>
										<th>Tgl Dokumen</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="15">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						</small>
					</div>
					<div class="col-xs-12 no-padding pagination-pph" style="padding-top: 8px;">
						<button type="button" class="btn btn-sm btn-default btn-prev"><i class="fa fa-chevron-left"></i> Sebelumnya</button>
						<button type="button" class="btn btn-sm btn-default btn-next">Selanjutnya <i class="fa fa-chevron-right"></i></button>
						<span class="page-info" style="margin-left: 10px;">Halaman 1 dari 1 (total 0 baris)</span>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<button type="button" class="btn btn-default pull-right" onclick="pphUnifikasi.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
				</div>
			</div>
		</form>
	</div>
</div>
