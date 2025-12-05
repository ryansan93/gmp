<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding contain bulanan" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Bulan</label></div>
				<div class="col-sm-12 no-padding">
					<select class="form-control bulan" data-required="1">
						<option value="all">ALL</option>
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
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
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
        </div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Kas</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control kas" data-required="1">
						<option value="all" data-unit="all">ALL</option>
						<?php foreach ($kas as $k_kas => $v_kas): ?>
							<option value="<?php echo $v_kas['no_coa']; ?>" data-unit="<?php echo $v_kas['unit']; ?>"><?php echo strtoupper($v_kas['nama_coa']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control perusahaan" data-required="1">
						<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
							<?php
								$selected = null;
								if ( !empty($kode_perusahaan) ) {
									if ( $kode_perusahaan == $v_perusahaan['kode'] ) {
										$selected = 'selected';
									}
								}
							?>
							<option value="<?php echo $v_perusahaan['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_perusahaan['nama_perusahaan']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Plasma</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control noreg" data-required="1">
					<option value="all">ALL</option>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<tbody></tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-right" onclick="kk.excryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
		<div class="col-xs-12 no-padding btn-tutup-bulan hide" data-status="0">
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding">
				<?php if ( $akses['a_submit'] == 1 ) { ?>
					<button type="button" class="col-xs-12 btn btn-success submit" data-status="0" onclick="kk.save()"><i class="fa fa-check"></i> Tutup Bulan</button>
				<?php } ?>
				<?php if ( $akses['a_ack'] == 1 ) { ?>
					<button type="button" class="col-xs-12 btn btn-primary ack" data-status="0" onclick="kk.ack()"><i class="fa fa-check"></i> ACK</button>
				<?php } ?>
			</div>
		</div>
	</div>
</div>