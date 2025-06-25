<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Bank</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control bank" data-required="1">
					<?php foreach ($bank as $k_bank => $v_bank): ?>
						<option value="<?php echo $v_bank['coa']; ?>"><?php echo $v_bank['nama_coa']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Akun Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control akun_transaksi" data-required="1" multiple="multiple">
					<option value="all">ALL</option>
					<?php foreach ($akun_transaksi as $k_at => $v_at): ?>
						<option value="<?php echo implode("', '", $v_at['id']); ?>"><?php echo $v_at['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Jenis Tanggal</label></div>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="flexRadioDefault" id="bulanan" onchange="bank.cekTanggal()" checked>
				<label class="form-check-label" for="bulanan" style="margin-left: 25px;">
					Bulanan
				</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="flexRadioDefault" id="harian" onchange="bank.cekTanggal()">
				<label class="form-check-label" for="harian" style="margin-left: 25px;">
					Harian
				</label>
			</div>
		</div>
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
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
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Bulan</label></div>
				<div class="col-sm-12 no-padding">
					<select class="form-control bulan" data-required="1">
						<!-- <option value="all">ALL</option> -->
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
        </div>
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
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="bank.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="panel-heading" style="padding-top: 0px; padding-right: 0px; padding-left: 0px;">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#tipe1" data-tab="tipe1">Tipe 1</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tipe2" data-tab="tipe2">Tipe 2</a>
					</li>
				</ul>
			</div>
			<div class="panel-body no-padding">
				<div class="tab-content">
					<div id="tipe1" class="tab-pane fade show active">
						<div class="col-xs-12 no-padding">
							<small>
								<table class="table table-bordered tblTipe1" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<td colspan="4" class="text-right"><b>Total</b></td>
											<td class="tot_debit text-right"><b>0</b></td>
											<td class="tot_kredit text-right"><b>0</b></td>
										</tr>
										<tr>
											<th class="col-xs-1">Tanggal</th>
											<th class="col-xs-2">Akun Transaksi</th>
											<th class="col-xs-5">Keterangan</th>
											<th class="col-xs-2">No. Bukti</th>
											<th class="col-xs-1">Debit</th>
											<th class="col-xs-1">Kredit</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="6">Data tidak ditemukan.</td>
										</tr>
									</tbody>
								</table>
							</small>
						</div>
					</div>
					<div id="tipe2" class="tab-pane fade">
						<div class="col-xs-12 no-padding">
							<small>
								<table class="table table-bordered tblTipe2" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<td colspan="4" class="text-right"><b>Total</b></td>
											<td class="tot_debit text-right"><b>0</b></td>
											<td class="tot_kredit text-right"><b>0</b></td>
										</tr>
										<tr>
											<th class="col-xs-1">Tanggal</th>
											<th class="col-xs-2">Akun Transaksi</th>
											<th class="col-xs-5">Keterangan</th>
											<th class="col-xs-2">No. Bukti</th>
											<th class="col-xs-1">Debit</th>
											<th class="col-xs-1">Kredit</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="6">Data tidak ditemukan.</td>
										</tr>
									</tbody>
								</table>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="bank.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
	</div>
</div>