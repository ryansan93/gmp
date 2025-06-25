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
				<label>PERUSAHAAN</label>
			</div>
			<div class="col-sm-12 no-padding">
				<select class="col-sm-12 form-control perusahaan" data-required="1">
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
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="gl.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding" style="overflow-x: auto;">
			<small>
				<table class="table table-bordered" style="margin-bottom: 0px; max-width: 130%; width: 150%;">
					<thead>
						<tr>
							<th class="text-center" rowspan="2" style="width: 5%;">No. COA</th>
							<th class="text-center" rowspan="2" style="width: 10%;">Nama</th>
							<th class="text-center" rowspan="2" style="width: 2.5%;">N/L</th>
							<th class="text-center" rowspan="2" style="width: 2.5%;">D/K</th>
							<th class="text-center" colspan="2">Saldo Awal</th>
							<th class="text-center" colspan="2">Mutasi</th>
							<!-- <th class="text-center" colspan="2">Penyesuaian</th> -->
							<th class="text-center" colspan="2">Saldo Akhir</th>
							<th class="text-center" colspan="2">Laba-Rugi</th>
							<th class="text-center" colspan="2">Neraca</th>
						</tr>
                        <tr>
                            <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th>
                            <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th>
                            <!-- <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th> -->
                            <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th>
                            <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th>
                            <th class="text-center" style="width: 6.66%;">Debit</th>
                            <th class="text-center" style="width: 6.66%;">Kredit</th>
                        </tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="14">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
        <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-default" onclick="gl.excryptParams()"><i class="fa fa-file-excel-o"></i> Export Excel</button>
			</div>
		</div>
	</div>
</div>