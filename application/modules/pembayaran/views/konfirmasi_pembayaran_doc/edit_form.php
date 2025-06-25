<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Periode Order</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="start_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" data-tgl="<?php echo $first_date ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
	<div class="col-xs-5 no-padding">
		<div class="input-group date" id="end_date_order">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" data-tgl="<?php echo $last_date ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-xs-12 no-padding">
		<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
			<option value="all" <?php echo (count($kode_unit) == count($unit)) ? 'selected' : null; ?> > All </option>
			<?php foreach ($unit as $key => $v_unit): ?>
				<?php
					$selected = null;
					if ( count($kode_unit) ) {
						if ( count($kode_unit) != count($unit) ) {
							foreach ($kode_unit as $k_ku => $v_ku) {
								if ( $v_ku == $v_unit['kode'] ) {
									$selected = 'selected';
								}
							}
						}
					}
				?>
				<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_supplier" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Supplier</option>
			<?php foreach ($supplier as $k => $val): ?>
				<?php
					$selected = null;
					if ( $val['nomor'] == $data['supplier'] ) {
						$selected = 'selected';
					}
				?>
				<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_perusahaan" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Perusahaan</option>
			<?php foreach ($perusahaan as $k => $val): ?>
				<?php
					$selected = null;
					if ( $val['kode'] == $data['perusahaan'] ) {
						$selected = 'selected';
					}
				?>
				<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<button type="button" class="btn btn-primary col-xs-12" onclick="kpd.get_data_doc()"><i class="fa fa-search"></i> Tampilkan</button>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="7"></td>
					<td class="text-left"><b>Total</b></td>
					<td class="text-right total"><b><?php echo angkaDecimal($total); ?></b></td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<th class="col-xs-1">Tgl Order</th>
					<th class="col-xs-1">Kota/Kab</th>
					<th class="col-xs-2">Perusahaan</th>
					<th class="col-xs-2">No. Order</th>
					<th class="col-xs-2">Peternak</th>
					<th style="width: 5%;">Kandang</th>
					<th style="width: 5%;">Populasi</th>
					<th style="width: 5%;">Harga</th>
					<th class="col-xs-1">Sub Total</th>
					<th class="text-center" style="width: 5%;">
						<input type="checkbox" class="cursor-p check_all" data-target="list_data" checked="checked" >
					</th>
				</tr>
			</thead>
			<tbody>
				<?php echo $detail; ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="kpd.submit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-check"></i> Submit Edit</button>
</div>