<?php if ( $akses['a_submit'] == 1 ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="label-control">Tanggal Pakai CN</label></div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
					<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Jenis CN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control jenis_cn" data-required="1">
					<?php foreach ($jenis_cn as $key => $value) { ?>
						<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">No. CN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control cn" data-required="1">
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Nilai CN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control text-right nilai_cn" data-tipe="decimal" placeholder="Nilai CN" data-required="1" disabled>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Total Pakai CN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control text-right pakai_cn" data-tipe="decimal" placeholder="Total Pakai CN" data-required="1" disabled>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<button type="button" class="btn btn-success" onclick="cn.openModalSj()"><i class="fa fa-plus"></i> Pilih No. SJ</button>
	</div>
	<div class="col-xs-12 no-padding">
		<small>
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-4">No. SJ</th>
						<th class="col-xs-2">Nominal</th>
						<th class="col-xs-2">Sisa Tagihan</th>
						<th class="col-xs-3">Pakai CN</th>
						<th class="col-xs-1">Action</th>
					</tr>
				</thead>
				<tbody class="detail_sj">
					<tr class="empty-row">
						<td colspan="5" class="text-center">Belum ada SJ dipilih. Klik "Pilih No. SJ".</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>

	<!-- MODAL PILIH SJ -->
	<div class="bootbox modal" id="modalSj" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Pilih No. SJ (Belum Lunas)</h4>
				</div>
				<div class="modal-body">
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<div class="search left-inner-addon">
							<i class="glyphicon glyphicon-search"></i>
							<input type="text" class="form-control sj_search" placeholder="Cari No. SJ / Unit ..." onkeyup="cn.filterSjModal(this)">
						</div>
					</div>
					<div class="col-xs-12 no-padding" style="max-height: 420px; overflow: auto;">
						<small>
							<table class="table table-bordered table-hover tbl_sj col-xs-12 no-padding" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<th style="width: 34px;" class="text-center"><input type="checkbox" class="sj_check_all" onclick="cn.toggleAllSj(this)"></th>
										<th>No. SJ</th>
										<th class="text-right" style="width: 140px;">Tagihan</th>
										<th class="text-right" style="width: 140px;">Sisa</th>
									</tr>
								</thead>
								<tbody class="sj_modal_body">
									<tr><td colspan="4" class="text-center">Pilih Jenis CN &amp; No. CN terlebih dahulu.</td></tr>
								</tbody>
							</table>
						</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="cn.pilihSj()"><i class="fa fa-check"></i> Pilih SJ Terpilih</button>
				</div>
			</div>
		</div>
	</div>
<?php } else { ?>
	<h4>CREDIT NOTE</h4>
<?php } ?>
