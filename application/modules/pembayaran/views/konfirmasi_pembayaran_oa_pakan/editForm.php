<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Periode Terima</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="start_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" data-tgl="<?php echo $data['first_date']; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="end_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" data-tgl="<?php echo $data['last_date']; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
		<div class="col-xs-12 no-padding">
			<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
				<option value="all" > All </option>
				<?php foreach ($unit as $key => $v_unit): ?>
					<?php
						$selected = null;
						foreach ($data['unit'] as $key => $value) {
							if ( $value == $v_unit['kode'] ) {
								$selected = 'selected';
							}
						}
					?>
					<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Filter</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control filter" multiple="multiple" width="100%">
				<option value="mutasi">Mutasi</option>
				<option value="not_mutasi">Not Mutasi</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		&nbsp;
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Kirim</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_kirim" multiple="multiple" width="100%">
				<option value="">-- Pilih Jenis --</option>
				<option value="opks">Order Pabrik (OPKS)</option>
				<option value="opkp">Dari Peternak (OPKP)</option>
				<option value="opkg">Dari Gudang (OPKG)</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
		<div class="col-xs-12 no-padding">
			<select id="select_perusahaan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
				<option value="">Pilih Perusahaan</option>
				<?php foreach ($perusahaan as $k => $val): ?>
					<?php
						$selected = null;
						if ( $data['perusahaan'] == $val['kode'] ) {
							$selected = 'selected';
						}
					?>
					<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Ekspedisi</label></div>
		<div class="col-xs-12 no-padding">
			<select id="select_ekspedisi" class="form-control" type="text" data-required="1">
				<option value="">Pilih Ekspedisi</option>
				<?php foreach ($ekspedisi as $k => $val): ?>
					<?php
						$selected = null;
						if ( $data['ekspedisi_id'] == $val['nomor'] ) {
							$selected = 'selected';
						}
					?>
					<option value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<button type="button" class="btn btn-primary col-xs-12" onclick="kpoap.getDataOa()"><i class="fa fa-search"></i> Tampilkan</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 10px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_list" placeholder="Search" onkeyup="filter_all(this)">
</div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="5"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b><?php echo angkaDecimal($data['sub_total']); ?></b></td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl Terima</th>
					<th class="col-xs-2">Ekspedisi</th>
					<th class="col-xs-1">No. Polisi</th>
					<th class="col-xs-1">No. SJ</th>
					<th class="col-xs-2">Asal</th>
					<th class="col-xs-2">Tujuan</th>
					<th class="col-xs-1">Sub Total</th>
					<th class="col-xs-1 text-center">
						<input type="checkbox" class="cursor-p checkAll" data-target="sj" checked>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty($data['detail']) && count($data) > 0 ): ?>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr class="search">
							<td class="tgl_mutasi" data-val="<?php echo $v_det['tgl_mutasi']; ?>"><?php echo tglIndonesia($v_det['tgl_mutasi'], '-', ' '); ?></td>
							<td class="ekspedisi" data-val="<?php echo $v_det['ekspedisi']; ?>"><?php echo $v_det['ekspedisi']; ?></td>
							<td class="no_polisi" data-val="<?php echo $v_det['no_polisi']; ?>"><?php echo $v_det['no_polisi']; ?></td>
							<td class="no_sj" data-val="<?php echo $v_det['no_sj']; ?>"><?php echo $v_det['no_sj']; ?></td>
							<td><?php echo $v_det['asal']; ?></td>
							<td><?php echo $v_det['tujuan']; ?></td>
							<td class="text-right sub_total" data-val="<?php echo $v_det['sub_total']; ?>"><?php echo angkaDecimal($v_det['sub_total']); ?></td>
							<td class="text-center check">
								<?php $checked = 'checked="checked"'?>
								<input type="checkbox" class="cursor-p checkSelf" target="sj" <?php echo $checked; ?> >
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td colspan="8">Data tidak ditemukan.</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kpoap.submit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-check"></i> Submit</button>
</div>