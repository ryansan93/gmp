<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 search no-padding">
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<select class="pelanggan" name="pelanggan[]" multiple="multiple" width="100%" data-required="1">
							<option value="all">All</option>
							<?php if ( !empty($pelanggan) ): ?>
								<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
									<option value="<?php echo $v_plg['nomor'] ?>"><?php echo strtoupper($v_plg['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_plg['nama_unit'])).')'; ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</div>
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
							<option value="all" > All </option>
							<?php foreach ($unit as $key => $v_unit): ?>
								<option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
							<?php endforeach ?>
						</select>
					</div>
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<select class="perusahaan" name="perusahaan[]" multiple="multiple" width="100%" data-required="1">
							<?php foreach ($perusahaan as $key => $v_perusahaan): ?>
								<option value="<?php echo $v_perusahaan['kode']; ?>" > <?php echo strtoupper($v_perusahaan['nama']); ?> </option>
							<?php endforeach ?>
						</select>
					</div>
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<div class="col-xs-12 no-padding"><label class="label-control">Minimal Lama Bayar (Hari)</label></div>
						<div class="col-xs-12 no-padding">
							<input type="text" class="form-control minimal_lama_bayar" placeholder="MINIMAL LAMA BAYAR (HARI)" data-required="1" data-tipe="angka">
						</div>
					</div>
					<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
						<div class="col-xs-12 no-padding"><label class="label-control">Tanggal Maksimal DO</label></div>
						<div class="col-xs-12 no-padding">
							<div class="input-group date datetimepicker" id="tanggal">
								<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo date('Y-m-d'); ?>" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left col-xs-12" title="TAMPIL" onclick="stpp.get_lists(this)"><i class="fa fa-search"></i> Tampilkan</button>
					</div>
				</div>
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
				<button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="stpp.cekExportExcel(this)"><i class="fa fa-file-excel-o"></i> Export</button>
			</div>
			<div class="col-xs-12 no-padding" style="overflow-x: auto;">
				<small>
					<table class="table table-bordered" style="table-layout: auto;">
						<thead>
							<tr>
								<td class="text-right" colspan="4"><b>Total</b></td>
								<td class="text-right total_tonase"><b>0</b></td>
								<td class="text-right"><b></b></td>
								<td class="text-right total_tagihan"><b>0</b></td>
								<td class="text-right total_sisa_tagiahn"><b>0</b></td>
								<td class="text-center"></td>
							</tr>
							<tr>
								<th class="col-xs-1" style="vertical-align: middle;">Tanggal</th>
								<th class="col-xs-1" style="vertical-align: middle;">No. Nota</th>
								<th class="col-xs-1" style="vertical-align: middle;">No. DO</th>
								<th class="col-xs-2" style="vertical-align: middle;">Plasma</th>
								<th class="col-xs-1" style="vertical-align: middle;">Tonase</th>
								<th class="col-xs-1" style="vertical-align: middle;">Harga</th>
								<th class="col-xs-1" style="vertical-align: middle;">Total</th>
								<th class="col-xs-1" style="vertical-align: middle;">Sisa Tagihan</th>
								<th class="col-xs-1" style="vertical-align: middle;">Lama Belum Bayar (Hari)</th>
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
		</form>
	</div>
</div>