<?php foreach ($data as $key => $value): ?>
	<?php $performa = $value['data']['performa']; ?>
	<?php $panen_dan_rhpp_plasma = $value['data']['panen_dan_rhpp_plasma']; ?>
	<?php $laporan_inti = $value['data']['laporan_inti']; ?>

	<tr>
		<th class="col-xs-12 text-left" style="background-color: #c9c9c9;">
			BULAN : <a class="cursor-p" onclick="lr.viewForm(this)" data-unit="<?php echo $unit; ?>" data-bulan="<?php echo $key+1; ?>" data-tahun="<?php echo $tahun; ?>" data-perusahaan="<?php echo $perusahaan; ?>"><?php echo strtoupper($value['bulan']).' | TOTAL RHPP : '.$value['jumlah_rhpp'] ?></a>
		</th>
	</tr>
	<tr>
		<td>
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="text-left" colspan="8">PERFORMA</th>
					</tr>
				</thead>
				<?php if ( !empty($performa) ): ?>
					<tbody>
						<tr>
							<th class="text-center col-xs-1" style="background-color: transparent;">TOTAL PANEN (EKOR)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">JUMLAH PAKAN RHPP PANEN (KG)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">LAMA PANEN</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">UMUR</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">DEPLESI (%)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">FCR</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">BB (KG)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">IP</th>
						</tr>
						<tr>
							<td class="text-right"><?php echo isset($performa['ekor_panen']) ? angkaRibuan($performa['ekor_panen']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['total_pakai_pakan']) ? angkaDecimal($performa['total_pakai_pakan']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['lama_panen']) ? angkaDecimal($performa['lama_panen']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['umur']) ? angkaDecimal($performa['umur']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['deplesi']) ? angkaDecimal($performa['deplesi']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['fcr']) ? angkaDecimal($performa['fcr']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['bb']) ? angkaDecimal($performa['bb']) : 0; ?></td>
							<td class="text-right"><?php echo isset($performa['ip']) ? angkaDecimal($performa['ip']) : 0; ?></td>
						</tr>
					</tbody>
				<?php endif ?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="text-left" colspan="4">PANEN</th>
						<th class="text-left" colspan="4">RHPP PLASMA</th>
					</tr>
				</thead>
				<?php if ( !empty($panen_dan_rhpp_plasma) ): ?>
					<tbody>
						<tr>
							<th class="text-center col-xs-1" style="background-color: transparent;">EKOR</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">KG</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">PENJUALAN (Rp.)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">HARGA RATA-RATA</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">LABA / RUGI PLASMA</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">RATA-RATA PENDAPATAN PLASMA</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">RHPP KE PUSAT (HARI)</th>
							<th class="text-center col-xs-1" style="background-color: transparent;">DI TRANSFER (HARI)</th>
						</tr>
						<tr>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['ekor_panen']) ? angkaRibuan($panen_dan_rhpp_plasma['ekor_panen']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['kg_panen']) ? angkaDecimal($panen_dan_rhpp_plasma['kg_panen']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['total']) ? angkaDecimal($panen_dan_rhpp_plasma['total']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['rata_harga_panen']) ? angkaDecimal($panen_dan_rhpp_plasma['rata_harga_panen']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['total_pendapatan_peternak']) ? angkaDecimal($panen_dan_rhpp_plasma['total_pendapatan_peternak']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['rata_total_pendapatan_peternak']) ? angkaDecimal($panen_dan_rhpp_plasma['rata_total_pendapatan_peternak']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['rata_rhpp_ke_pusat']) ? angkaDecimal($panen_dan_rhpp_plasma['rata_rhpp_ke_pusat']) : 0; ?></td>
							<td class="text-right"><?php echo isset($panen_dan_rhpp_plasma['rata_transfer']) ? angkaDecimal($panen_dan_rhpp_plasma['rata_transfer']) : 0; ?></td>
						</tr>
					</tbody>
				<?php endif ?>
			</table>
		</td>
	</tr>
	<tr>
		<td style="overflow-x: scroll;">
			<table class="table table-bordered" style="margin-bottom: 0px; width: 150%; max-width: 150%;">
				<thead>
					<tr>
						<th class="text-left" colspan="11">LAPORAN INTI</th>
					</tr>
				</thead>
				<?php if ( !empty($laporan_inti) ): ?>
					<tbody>
						<tr>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">MODAL INTI</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">MODAL INTI SEBENARNYA</th>
							<th class="text-center col-xs-2" style="background-color: transparent; width: 20%;">ESTIMASI OPERASIONAL (300)</th>
							<th class="text-center col-xs-2" style="background-color: transparent; width: 20%;">L/R INTI TANPA OP RILL</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">PENDAPATAN LAIN-LAIN</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">OPERASIONAL PAJAK</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">OPERASIONAL KAS DLL</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">GAJI PER BULAN</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">RATA-RATA OPS / KG PANEN</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">TOT L/R INTI RILL</th>
							<th class="text-center col-xs-1" style="background-color: transparent; width: 10%;">RATA-RATA L/R INTI POPULASI TERPANEN</th>
						</tr>
						<tr>
							<td class="text-right"><?php echo isset($laporan_inti['modal_inti']) ? angkaDecimal($laporan_inti['modal_inti']) : 0; ?></td>
							<td class="text-right"><?php echo isset($laporan_inti['modal_inti_sebenarnya']) ? angkaDecimal($laporan_inti['modal_inti_sebenarnya']) : 0; ?></td>
							<td class="text-right">
								<?php
									$val = null;
									if ( $laporan_inti['lr_inti'] < 0 ) {
										$val = '('.angkaDecimal(abs($laporan_inti['lr_inti'])).')';
									} else {
										$val = angkaDecimal($laporan_inti['lr_inti']);
									}
								?>
								<?php echo isset($laporan_inti['lr_inti']) ? $val : 0; ?>
							</td>
							<td class="text-right">
								<?php
									$val = null;
									if ( $laporan_inti['lr_inti'] < 0 ) {
										$val = '('.angkaDecimal(abs($laporan_inti['lr_inti'])-$laporan_inti['total_biaya_ops_300']).')';
									} else {
										$val = angkaDecimal($laporan_inti['lr_inti']-$laporan_inti['total_biaya_ops_300']);
									}
								?>
								<?php echo isset($laporan_inti['lr_inti_tanpa_ops_300']) ? $val : 0; ?>
							</td>
							<td class="text-right"></td>
							<td class="text-right"></td>
							<td class="text-right"></td>
							<td class="text-right"></td>
							<td class="text-right"></td>
							<td class="text-right"></td>
							<td class="text-right"></td>
						</tr>
					</tbody>
				<?php endif ?>
			</table>
		</td>
	</tr>
<?php endforeach ?>