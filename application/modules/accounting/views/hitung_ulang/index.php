<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<div class="col-xs-12">
			<div class="col-xs-12 no-padding">
				<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-sm-6 no-padding" style="padding-right: 5px;">
						<div class="col-sm-12 no-padding">
							<label>BULAN</label>
						</div>
						<div class="col-sm-12 no-padding">
							<select class="form-control bulan" data-required="1">
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
					<div class="col-sm-6 no-padding" style="padding-left: 5px;">
						<div class="col-sm-12 no-padding">
							<label>TAHUN</label>
						</div>
						<div class="col-sm-12 no-padding">
							<div class="input-group date datetimepicker" name="tahun" id="tahun">
								<input type="text" class="form-control text-center" placeholder="TAHUN" data-required="1" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
				</div>
                <div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<div class="col-sm-12 no-padding">
							<label>JENIS</label>
						</div>
						<div class="col-sm-12 no-padding">
							<select class="form-control jenis" data-required="1">
								<option value="all">ALL</option>
								<option value="terima_doc">TERIMA DOC</option>
								<option value="terima_pakan">TERIMA PAKAN</option>
								<option value="terima_voadip">TERIMA OVK</option>
								<option value="lhk">LHK</option>
								<option value="retur_pakan">RETUR PAKAN</option>
								<option value="retur_voadip">RETUR VOADIP</option>
							</select>
						</div>
					</div>
                </div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="hu.postingUlang()">POSTING ULANG</button>
				</div>
			</div>
		</div>
	</div>
</div>