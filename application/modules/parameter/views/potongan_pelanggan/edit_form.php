<div class="col-md-12">
	<form class="form form-horizontal" role="form">
		<div name="data-mitra">
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Pelanggan</label>
				<div class="col-sm-5">
					<select class="form-control selectpicker pelanggan" data-live-search="true" data-required="1">
						<option value="">Pilih Pelanggan</option>
						<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
							<?php
								$selected = null;
								if ( $v_plg['nomor'] == $data['no_pelanggan'] ) {
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v_plg['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_plg['nama'].' ('.$v_plg['kab_kota'].')'); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Potongan (%)</label>
				<div class="col-sm-2">
					<input type="text" class="form-control text-right potongan_persen" data-tipe="decimal" placeholder="Potongan" data-required="1" maxlength="6" value="<?php echo angkaDecimal($data['potongan_persen']); ?>">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Mulai</label>
				<div class="col-sm-2">
					<div class="input-group date" name="startDate" id="StartDate_PP">
				        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" data-tgl="<?php echo $data['start_date']; ?>" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Berakhir</label>
				<div class="col-sm-2">
					<div class="input-group date" name="endDate" id="EndDate_PP">
				        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" data-tgl="<?php echo $data['end_date']; ?>" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Aktif</label>
				<div class="col-sm-2">
					<select class="form-control aktif" data-required="1">
						<option value="1" <?php echo ($data['aktif'] == 1) ? 'selected' : null; ?> >Aktif</option>
						<option value="0" <?php echo ($data['aktif'] == 0) ? 'selected' : null; ?> >Non Aktif</option>
					</select>
				</div>
			</div>
		</div>
	</form>
	<hr>
	<div class="col-md-12 no-padding">
		<button type="button" class="btn btn-large btn-primary pull-right" onclick="pp.edit(this)" data-id="<?php echo $data['id']; ?>"> <i class="fa fa-save"></i> Simpan Perubahan</button>
	</div>
</div>