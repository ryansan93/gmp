<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-xs-4 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Tahun</label></div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tahun" id="Tahun">
						<input type="text" class="form-control text-center" placeholder="Tahun" data-required="1" />
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div>
			<div class="col-xs-8 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Bulan</label></div>
				<div class="col-sm-12 no-padding">
					<select class="form-control bulan" data-required="1">
						<!-- <option value="all">ALL</option> -->
						<?php for ($i=1; $i <= 12; $i++) { ?>
							<?php
								$bulan[1] = 'JANUARI';
								$bulan[2] = 'FEBRUARI';
								$bulan[3] = 'MARET';
								$bulan[4] = 'APRIL';
								$bulan[5] = 'MEI';
								$bulan[6] = 'JUNI';
								$bulan[7] = 'JULI';
								$bulan[8] = 'AGUSTUS';
								$bulan[9] = 'SEPTEMBER';
								$bulan[10] = 'OKTOBER';
								$bulan[11] = 'NOVEMBER';
								$bulan[12] = 'DESEMBER';
							?>
							<option value="<?php echo $i; ?>"><?php echo $bulan[ $i ]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
        </div>
		<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-sm-12 no-padding">
				<label>UNIT</label>
			</div>
			<div class="col-sm-12 no-padding">
				<select class="col-sm-12 form-control unit" data-required="1">
					<option value="all">ALL</option>
					<?php if ( count($unit) > 0 ): ?>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>
		<div class="col-sm-12 no-padding hide" style="margin-bottom: 10px;">
			<div class="col-sm-12 no-padding">
				<label>PERUSAHAAN</label>
			</div>
			<div class="col-sm-12 no-padding">
				<select class="col-sm-12 form-control perusahaan" data-required="1">
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
					<thead>
						<tr>
							<th class="text-center col-xs-1">No. COA</th>
							<th class="text-center col-xs-1">Unit</th>
							<th class="text-center col-xs-2">Nama COA</th>
							<th class="text-center col-xs-1">Saldo Awal</th>
							<th class="text-center col-xs-1">Debet</th>
							<th class="text-center col-xs-1">Kredit</th>
							<th class="text-center col-xs-1">Saldo Akhir</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="7">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-default" onclick="gl.encryptParams()"><i class="fa fa-file-excel-o"></i> Export Excel</button>
			</div>
		</div>
	</div>
</div>