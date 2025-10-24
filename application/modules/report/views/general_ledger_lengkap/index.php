<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Start Date</label></div>
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
				<div class="col-xs-12 no-padding"><label class="control-label">End Date</label></div>
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
			<div class="col-xs-12 no-padding">
				<label>UNIT</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="col-xs-12 form-control unit" data-required="1">
					<option value="all">ALL</option>
					<?php if ( count($unit) > 0 ): ?>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label>Coa Start</label></div>
				<div class="col-xs-12 no-padding">
					<select class="col-xs-12 form-control coa_start" data-required="1">
						<?php if ( count($coa_start) > 0 ): ?>
							<?php foreach ($coa_start as $k_coa => $v_coa): ?>
								<option value="<?php echo $v_coa['no_coa']; ?>"><?php echo strtoupper($v_coa['no_coa'].' | '.$v_coa['nama_coa']); ?></option>
							<?php endforeach ?>
						<?php endif ?>
					</select>
				</div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label>Coa End</label></div>
				<div class="col-xs-12 no-padding">
					<select class="col-xs-12 form-control coa_end" data-required="1">
						<?php if ( count($coa_end) > 0 ): ?>
							<?php foreach ($coa_end as $k_coa => $v_coa): ?>
								<option value="<?php echo $v_coa['no_coa']; ?>"><?php echo strtoupper($v_coa['no_coa'].' | '.$v_coa['nama_coa']); ?></option>
							<?php endforeach ?>
						<?php endif ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding hide" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding">
				<label>PERUSAHAAN</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="col-xs-12 form-control perusahaan" data-required="1">
					<!-- <option value="">Pilih Perusahaan</option> -->
					<?php if ( count($perusahaan) > 0 ): ?>
						<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
							<?php 
								$text_perusahaan = '';

								$perusahaan_old = null;
								foreach ($v_prs['detail'] as $k_det => $v_det) {
									if ( !empty($perusahaan_old) ) {
										$text_perusahaan .= ', ';
									}
									$text_perusahaan .= $v_det['nama'];

									$perusahaan_old = $v_det['nama'];
								} 
							?>
							<option value="<?php echo $v_prs['kode_gabung_perusahaan']; ?>"><?php echo strtoupper($text_perusahaan); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="gl.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding" style="overflow-x: auto;">
			<small>
				<table class="table table-bordered table-hover" style="margin-bottom: 0px; max-width: 100%; width: 150%;">
					<tbody>
						<tr>
							<td>Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
        <!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-default" onclick="gl.encryptParams()"><i class="fa fa-file-excel-o"></i> Export Excel</button>
			</div>
		</div> -->
	</div>
</div>