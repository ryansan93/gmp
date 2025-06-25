<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-success pull-right" onclick="jp.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Tanggal Awal</label></div>
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
			<div class="col-xs-12 no-padding"><label class="control-label">Tanggal Akhir</label></div>
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
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Detail Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control filter jurnal_trans_detail" data-target="detail_jurnal">
					<option value="all">All</option>
					<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
						<?php foreach ($v_jt['detail'] as $k_det => $v_det): ?>
							<option value="<?php echo $v_det['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_det['nama']); ?> </option>
						<?php endforeach ?>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control filter perusahaan" data-target="perusahaan">
					<option value="all">All</option>
					<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
						<option value="<?php echo $v_prs['kode']; ?>"> <?php echo strtoupper($v_prs['nama']); ?> </option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="jp.getLists()">Tampilkan</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
	<input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="jp.filter_all(this)">
</div>
<div class="col-xs-12 no-padding">
	<span>* Klik pada baris untuk melihat detail</span>
	<small>
		<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
			<thead>
				<!-- <tr>
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
						<select class="form-control filter perusahaan" data-target="perusahaan">
							<option value="">-- Perusahaan --</option>
							<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
								<option value="<?php echo $v_prs['kode']; ?>"> <?php echo strtoupper($v_prs['nama']); ?> </option>
							<?php endforeach ?>
						</select>
					</td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
					<td class="col-xs-3 text-center" style="border: transparent;"></td>
					<td class="col-xs-1 text-center" style="border: transparent;"></td>
				</tr> -->
				<tr>
					<th class="col-xs-1 text-center">Tanggal</th>
					<th class="col-xs-1 text-center">Detail Transaksi</th>
					<th class="col-xs-1 text-center">Perusahaan</th>
					<th class="col-xs-1 text-center">Asal</th>
					<th class="col-xs-1 text-center">Tujuan</th>
					<th class="col-xs-1 text-center">Unit</th>
					<th class="col-xs-3 text-center">Keterangan</th>
					<th class="col-xs-1 text-center">Nominal</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="8">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>