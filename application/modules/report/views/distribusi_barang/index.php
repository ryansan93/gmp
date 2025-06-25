<div class="row content-panel">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>JENIS</label>
				</div>
				<div class="col-sm-12 no-padding">
					<select class="form-control jenis" data-required="1">
						<option value="">-- Pilih Jenis --</option>
						<option value="voadip">OVK</option>
						<option value="pakan">PAKAN</option>
					</select>
				</div>
			</div>

			<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>BARANG</label>
				</div>
				<div class="col-sm-12 no-padding">
					<select class="form-control barang" multiple="multiple" data-required="1">
						<option class="all" value="all">ALL</option>
						<?php foreach ($barang as $k_brg => $v_brg): ?>
							<option value="<?php echo $v_brg['kode']; ?>" data-tipe="<?php echo $v_brg['tipe']; ?>"><?php echo strtoupper($v_brg['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-sm-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>TGL AWAL</label>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="input-group date datetimepicker" name="startDate" id="StartDate">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
				</div>
			</div>
			<div class="col-sm-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>TGL AKHIR</label>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="input-group date datetimepicker" name="endDate" id="EndDate">
				        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
			</div>

			<div class="col-sm-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>UNIT</label>
				</div>
				<div class="col-sm-12 no-padding">
					<select class="form-control unit" multiple="multiple" data-required="1">
						<option value="all">ALL</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-sm-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
				<div class="col-sm-12 no-padding">
					<label>PERUSAHAAN</label>
				</div>
				<div class="col-sm-12 no-padding">
					<select class="form-control perusahaan" multiple="multiple" data-required="1">
						<option value="all">ALL</option>
						<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
							<option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="db.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
	</div>
	<div class="col-xs-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12">
		<small>
			<table class="table table-bordered tbl_laporan" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="text-center" style="width: 5%; vertical-align: middle;">Transaksi</th>
						<th class="text-center" style="width: 6%; vertical-align: middle;">Tanggal</th>
						<th class="text-center" style="width: 5%; vertical-align: middle;">Unit</th>
						<th class="text-center" style="width: 18%; vertical-align: middle;">Peternak</th>
						<th class="text-center" style="width: 10%; vertical-align: middle;">Barang</th>
						<th class="text-center" style="width: 7%; vertical-align: middle;">No. SJ</th>
						<th class="text-center" style="width: 5%; vertical-align: middle;">Jumlah</th>
						<th class="text-center" style="width: 5%; vertical-align: middle;">OA</th>
						<th class="text-center" style="width: 5%; vertical-align: middle;">OA Mutasi</th>
						<th class="text-center" style="width: 7%; vertical-align: middle;">Hrg Beli</th>
						<th class="text-center" style="width: 10%; vertical-align: middle;">Total Beli</th>
						<th class="text-center" style="width: 7%; vertical-align: middle;">Hrg Jual</th>
						<th class="text-center" style="width: 10%; vertical-align: middle;">Total Jual</th>
						
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="12">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
	<div class="col-xs-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12">
		<button type="button" class="btn btn-default pull-right" onclick="db.excryptParams()"><i class="fa fa-file-excel-o"></i> Export Excel</button>
	</div>
</div>