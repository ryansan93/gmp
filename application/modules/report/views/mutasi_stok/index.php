<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12 no-padding">
				<div class="col-lg-12 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<div class="col-sm-1 no-padding">
						<label> Periode </label>
					</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="startDate" id="StartDate_Mutasi">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="endDate" id="EndDate_Mutasi">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-2">
						<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="ms.get_data()">Tampilkan</button>
					</div>
				</div>
				<div class="col-lg-12 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<div class="col-sm-1 no-padding">
						<label> Jenis Barang </label>
					</div>
					<div class="col-sm-2">
						<select class="form-control jns_barang" onchange="ms.get_gudang_dan_barang(this)">
							<option value="">Pilih Jenis</option>
							<option value="obat">Obat</option>
							<option value="pakan">Pakan</option>
						</select>
					</div>
				</div>
				<div class="col-lg-12 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<div class="col-sm-1 no-padding">
						<label> Gudang </label>
					</div>
					<div class="col-sm-2">
						<select class="form-control gudang">
							<option value="">Pilih Gudang</option>
						</select>
					</div>
					<div class="col-sm-1">&nbsp;</div>
					<div class="col-sm-1 no-padding">
						<label> Barang </label>
					</div>
					<div class="col-sm-2">
						<select class="form-control barang">
							<option value="">Pilih Barang</option>
						</select>
					</div>
				</div>
				<div class="col-lg-12 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<small class="col-lg-12 no-padding">
						<table class="table table-bordered tbl_list" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="text-center col-lg-1">Kode Brg</th>
									<th class="text-center col-lg-1">Nama Brg</th>
									<th class="text-center col-lg-1">Tanggal</th>
									<th class="text-center col-lg-1">Tgl Stok</th>
									<th class="text-center col-lg-1">Transaksi</th>
									<th class="text-center col-lg-1">Kode Transaksi</th>
									<th class="text-center col-lg-1" style="width: 5%;">Jumlah</th>
									<th class="text-center col-lg-1">Hrg Beli</th>
									<th class="text-center col-lg-1">Total Beli</th>
									<th class="text-center col-lg-1">Hrg Jual</th>
									<th class="text-center col-lg-1">Total Jual</th>
									<th class="text-center col-lg-1" style="width: 5%;">Saldo</th>
									<th class="text-center col-lg-1">Nilai Saldo</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="13" style="background-color: #dedede;"><b>Gudang : -</b></td>
								</tr>
								<tr>
									<td colspan="13">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>
