<?php foreach ($data as $key => $v_data) { ?>
	<div class="col-lg-1 no-padding pull-left">
		<h6>Tgl Berlaku : </h6>
	</div>
	<div class="col-lg-2 no-padding action">
	    <!-- <input class="form-control text-center" type="text" value="" data-tipe="date"> -->
	    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
	        <input type="text" class="form-control text-center" data-required="1" value="<?php echo tglIndonesia($v_data['mulai'], '-', ' '); ?>" disabled="<?php echo $disabled; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-lg-6 hide">
		<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="SAVE" onclick="pp.save(this)"> 
			<i class="fa fa-edit" aria-hidden="true"></i> EDIT
		</button>
	</div>
	<table class="table table-bordered table-hover" id="tb_input_standar_performa" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th class="text-center">Umur (hari)</th>
				<th class="text-center">Daya Hidup (%)</th>
				<th class="text-center">Mortalitas Harian (%)</th>
				<th class="text-center">Konsumsi Pakan (g)</th>
				<th class="text-center">Konsumsi Pakan Perhari (g)</th>
				<th class="text-center">Berat Badan</th>
				<th class="text-center">ADG</th>
				<th class="text-center">FCR</th>
				<th class="text-center">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($v_data['details'] as $key => $v_detail): ?>
				<?php 
					if ( $key == 1 ):
						$disabled_bb = false;
						$disabled = true;
					else:
						$disabled_bb = true;
						$disabled = false;
					endif 
				?>
				<tr>
					<td class="col-sm-1">
						<input class="form-control text-center" type="text" name="umur" value="<?php echo $v_detail['umur'] ?>" data-tipe="integer" disabled data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="daya_hidup" value="<?php echo angkaDecimal($v_detail['daya_hidup'] * 100); ?>" data-tipe="decimal" disabled data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="mortalitas" value="<?php echo angkaDecimal($v_detail['mortalitas']); ?>" data-tipe="decimal" disabled="<?php echo $disabled; ?>" isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="kons_pakan" value="<?php echo angkaRibuan($v_detail['kons_pakan']); ?>" data-tipe="integer" disabled data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="kons_pakan_harian" value="<?php echo angkaRibuan($v_detail['kons_pakan_harian']); ?>" data-tipe="integer" disabled="<?php echo $disabled; ?>" isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="bb" value="<?php echo angkaDecimal($v_detail['bb']); ?>" data-tipe="integer" disabled="<?php echo $disabled_bb; ?>" onchange="pp.calcRowValue(this)" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="adg" value="<?php echo angkaRibuan($v_detail['adg']); ?>" data-tipe="integer" disabled="<?php echo $disabled; ?>" isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="fcr" value="<?php echo angkaDecimal($v_detail['fcr'], 3); ?>" data-tipe="decimal3" disabled data-required="1">
					</td>
					<td class="action text-center col-sm-1">
						<button id="btn-add" type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="pp.addRowTable(this)"><i class="fa fa-plus"></i></button>
						<button id="btn-remove" type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="pp.removeRowTable(this)"><i class="fa fa-minus"></i></button>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
<?php } ?>