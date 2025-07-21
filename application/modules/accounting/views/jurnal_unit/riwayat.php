<div class="col-xs-12 no-padding">
	<div class="col-xs-8 search left-inner-addon no-padding d-flex align-items-center">
		<div class="col-sm-1 no-padding">
			<label class="control-label" style="padding-top: 0px;">Tanggal</label>
		</div>
		<div class="col-sm-3">
			<div class="input-group date datetimepicker" name="startDate" id="StartDate">
		        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" style="padding-left: 15px;" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
		<div class="col-sm-3">
			<div class="input-group date datetimepicker" name="endDate" id="EndDate">
		        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" style="padding-left: 15px;" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-sm-3 no-padding">
			<select class="form-control unit" data-required="1">
				<option value="">-- Pilih --</option>
				<option value="all">ALL</option>
				<!-- <option value="pusat">PUSAT GEMUK</option>
				<option value="pusat_gml">PUSAT GEMILANG</option>
				<option value="pusat_ma">PUSAT MA</option>
				<option value="pusat_mv">PUSAT MV</option> -->
				<?php foreach ($unit as $k_unit => $v_unit): ?>
					<option value="<?php echo $v_unit['kode']; ?>"><?php echo $v_unit['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
		<div class="col-sm-2">
			<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="ju.getLists()">Tampilkan</button>
		</div>
	</div>
	<div class="col-xs-2 no-padding">
		&nbsp;
	</div>
	<div class="col-xs-2 no-padding">
		<div class="col-sm-12 no-padding">
			<button type="button" class="btn btn-success pull-right" onclick="ju.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
	<input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="ju.filter_all(this)">
</div>
<div class="col-xs-12 no-padding">
	<span>* Klik pada baris untuk melihat detail</span>
	<small>
		<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;">
						<select class="form-control filter jurnal_trans_detail" data-target="detail_jurnal">
							<option value="">-- Detail Transaksi --</option>
							<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
								<?php foreach ($v_jt['detail'] as $k_det => $v_det): ?>
									<option value="<?php echo $v_det['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_det['nama']); ?> </option>
								<?php endforeach ?>
							<?php endforeach ?>
						</select>
					</td>
					<td class="col-xs-1 text-center" style="border: transparent;">
						<!-- <select class="form-control filter perusahaan" data-target="perusahaan">
							<option value="">-- Perusahaan --</option>
							<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
								<option value="<?php echo $v_prs['kode']; ?>"> <?php echo strtoupper($v_prs['nama']); ?> </option>
							<?php endforeach ?>
						</select> -->
					</td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-3 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
				</tr>
				<tr>
					<th class="col-xs-1 text-center">Kode Jurnal</th>
					<th class="col-xs-1 text-center">Tanggal</th>
					<th class="col-xs-1 text-center">Detail Transaksi</th>
					<th class="col-xs-2 text-center">Perusahaan</th>
					<th class="col-xs-1 text-center">Asal</th>
					<th class="col-xs-1 text-center">Tujuan</th>
					<th class="col-xs-1 text-center">Unit</th>
					<th class="col-xs-3 text-center">Keterangan</th>
					<th class="col-xs-1 text-center">Nominal</th>
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