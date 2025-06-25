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
				<div class="col-sm-12 no-padding hide" style="margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<label>PERUSAHAAN</label>
					</div>
					<div class="col-sm-12 no-padding">
						<select class="col-sm-12 form-control perusahaan">
							<option value="">Pilih Perusahaan</option>
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
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="tb.getData()">PROSES DATA TUTUP BULAN</button>
				</div>
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding data">
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding hide btn-tutup">
				<button type="button" class="col-xs-12 btn btn-success pull-right" onclick="tb.tutupBulan()"><i class="fa fa-check"></i> TUTUP BULAN</button>
			</div>
			<div class="col-xs-12 no-padding hide btn-hapus">
				<button type="button" class="col-xs-12 btn btn-danger pull-right" onclick="tb.hapusTutupBulan()"><i class="fa fa-check"></i> HAPUS TUTUP BULAN</button>
			</div>
		</div>
	</div>
</div>