<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-sm-6 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Bulan</label></div>
				<div class="col-sm-12 no-padding">
					<select class="form-control bulan" data-required="1">
						<option value="">Pilih Bulan</option>
						<?php for ($i=1; $i <= 12; $i++) { ?>
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
							<option value="<?php echo $i; ?>"><?php echo $bulan[ $i ]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="col-sm-6 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Tahun</label></div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tahun" id="Tahun">
						<input type="text" class="form-control text-center" placeholder="Tahun" data-required="1" />
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
					<select class="form-control unit" data-required="1">
						<option value="all">ALL</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding"><label class="control-label">Tutup Siklus</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control tutup_siklus" data-required="1">
						<option value="all">ALL</option>
						<option value="sudah">SUDAH TUTUP SIKLUS</option>
						<option value="belum">BELUM TUTUP SIKLUS</option>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="lkh.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<div class="col-sm-12">
					<div class="row">
						<a class="tu-float-btn tu-table-prev" style="margin-top:-30px;">
							<i class="fa fa-arrow-left my-float"></i>
						</a>

						<a class="tu-float-btn tu-float-btn-right tu-table-next" style="margin-top:-30px;">
							<i class="fa fa-arrow-right my-float"></i>
						</a>
					</div>
				</div>
				<table class="table table-bordered table-hover" style="margin-bottom: 0px;" id="tbl_lkh">
					<thead>
						<tr>
							<th rowspan="2" class="page0 text-center col-xs-1">Unit</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Noreg</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Plasma</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Jenis Mitra</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Tgl CI</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Tgl Tutup Siklus</th>
							<th rowspan="2" class="page0 text-center col-xs-1">Populasi</th>
							<th colspan="8" class="page1 text-center">Saldo Awal</th>
							<th colspan="8" class="page2 text-center">Produksi</th>
							<th colspan="8" class="page3 text-center">Tersedia</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Buku Balik Cad RHPP</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Stock Tersedia</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Terjual</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Persentase (%)</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Start Date Proporsi</th>
							<th rowspan="2" class="page4 text-center col-xs-1">End Date Proporsi</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Hari</th>
							<th rowspan="2" class="page4 text-center col-xs-1">Proporsi</th>
							<th colspan="8" class="page5 text-center">Dijual</th>
							<th colspan="8" class="page6 text-center">Saldo Akhir</th>
						</tr>
						<tr>
							<th class="page1 text-center col-xs-1">DOC</th>
							<th class="page1 text-center col-xs-1">Pakan</th>
							<th class="page1 text-center col-xs-1">OVK</th>
							<th class="page1 text-center col-xs-1">OA</th>
							<th class="page1 text-center col-xs-1">BL</th>
							<th class="page1 text-center col-xs-1">BTL</th>
							<th class="page1 text-center col-xs-1">RHPP</th>
							<th class="page1 text-center col-xs-1">Total</th>
							<th class="page2 text-center col-xs-1">DOC</th>
							<th class="page2 text-center col-xs-1">Pakan</th>
							<th class="page2 text-center col-xs-1">OVK</th>
							<th class="page2 text-center col-xs-1">OA</th>
							<th class="page2 text-center col-xs-1">BL</th>
							<th class="page2 text-center col-xs-1">BTL</th>
							<th class="page2 text-center col-xs-1">RHPP</th>
							<th class="page2 text-center col-xs-1">Total</th>
							<th class="page3 text-center col-xs-1">DOC</th>
							<th class="page3 text-center col-xs-1">Pakan</th>
							<th class="page3 text-center col-xs-1">OVK</th>
							<th class="page3 text-center col-xs-1">OA</th>
							<th class="page3 text-center col-xs-1">BL</th>
							<th class="page3 text-center col-xs-1">BTL</th>
							<th class="page3 text-center col-xs-1">RHPP</th>
							<th class="page3 text-center col-xs-1">Total</th>
							<th class="page5 text-center col-xs-1">DOC</th>
							<th class="page5 text-center col-xs-1">Pakan</th>
							<th class="page5 text-center col-xs-1">OVK</th>
							<th class="page5 text-center col-xs-1">OA</th>
							<th class="page5 text-center col-xs-1">BL</th>
							<th class="page5 text-center col-xs-1">BTL</th>
							<th class="page5 text-center col-xs-1">RHPP</th>
							<th class="page5 text-center col-xs-1">Total</th>
							<th class="page6 text-center col-xs-1">DOC</th>
							<th class="page6 text-center col-xs-1">Pakan</th>
							<th class="page6 text-center col-xs-1">OVK</th>
							<th class="page6 text-center col-xs-1">OA</th>
							<th class="page6 text-center col-xs-1">BL</th>
							<th class="page6 text-center col-xs-1">BTL</th>
							<th class="page6 text-center col-xs-1">RHPP</th>
							<th class="page6 text-center col-xs-1">Total</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="55">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="lkh.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
			<button type="button" class="btn btn-success pull-right" style="margin-right: 5px;" onclick="lkh.prosesHpp()"><i class="fa fa-save"></i> Proses HPP</button>
		</div>
	</div>
</div>