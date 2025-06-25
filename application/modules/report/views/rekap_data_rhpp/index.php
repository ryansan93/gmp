<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
				<div class="col-xs-12 no-padding">
					<label>TGL TUTUP AWAL</label>
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
					<label>TGL TUTUP AKHIR</label>
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
					<label>JENIS</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select class="form-control jenis" data-required="1">
						<option value="all">ALL</option>
						<option value="doc">DOC</option>
						<option value="pakan">PAKAN</option>
						<option value="ovk">OVK</option>
					</select>
				</div>
			</div>
			
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="rdr.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<td class="total text-right" colspan="10">Total</td>
							<td class="total total_hit text-right tot_box_sak" data-target="box_sak">0</td>
							<td class="total total_hit text-right tot_jumlah" data-target="jumlah">0</td>
							<td></td>
							<td class="total total_hit text-right tot_total" data-target="total">0</td>
							<td class="total total_hit text-right tot_mutasi_barang" data-target="mutasi_barang">0</td>
							<td class="total total_hit text-right tot_nominal" data-target="nominal">0</td>
							<td class="total total_hit text-right tot_mutasi_box_sak" data-target="mutasi_box_sak">0</td>
							<td colspan="4"></td>
						</tr>
						<tr>
							<th>Urutan</th>
							<th>Peternak</th>
							<th>Kandang</th>
							<th>Periode</th>
							<th>Tgl Chick-in</th>
							<th>Jenis</th>
							<th>Kategori</th>
							<th>Tgl Distribusi</th>
							<th>Nota</th>
							<th>Barang</th>
							<th>Box/Sak</th>
							<th>Jumlah</th>
							<th>Harga</th>
							<th>Total</th>
							<th>Mutasi Barang</th>
							<th>Nominal</th>
							<th>Mutasi Box/Sak</th>
							<th>D.O</th>
							<th>Unit</th>
							<th>Tgl. Panen Awal</th>
							<th>Tgl. Panen Akhir</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="21">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="rdr.excryptParams(this)" data-tipe="excel" data-jenis="1"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
	</div>
</div>