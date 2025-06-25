<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12 no-padding">
				<div class="col-lg-12 search no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<div class="col-sm-1 no-padding" style="max-width: 12%;">
						<span>Tanggal Bayar</label>
					</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="startDate" id="StartDate_PB">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
					<div class="col-sm-2">
						<div class="input-group date datetimepicker" name="endDate" id="EndDate_PB">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-4">
						<select class="form-control pelanggan">
							<option value="">Pilih Pelanggan</option>
							<?php if ( !empty($pelanggan) ): ?>
								<option value="all">ALL</option>
								<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
									<option value="<?php echo $v_plg['nomor'] ?>"><?php echo strtoupper($v_plg['nama'].' ('.$v_plg['nama_unit'].')'); ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</div>
					<div class="col-sm-2">
						<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="pb.get_lists()"><i class="fa fa-search"></i> Tampilkan</button>
					</div>
				</div>
				<div class="col-lg-12 no-padding">
					<small>
						<table class="table table-bordered tbl_ktp" width="100%" cellspacing="0">
							<thead>
								<tr class="v-center">
									<th class="text-center">Tgl Bayar</th>
									<th class="text-center">Jml Transfer</th>
									<th class="text-center">Saldo</th>
									<th class="text-center">Total Bayar</th>
									<th class="text-center">Total Hutang</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="5">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>