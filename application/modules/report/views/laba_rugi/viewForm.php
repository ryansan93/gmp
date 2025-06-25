<div class="modal-body body no-padding">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<div class="col-lg-8">
				<span style="font-weight: bold;">DETAIL</span>
			</div>
			<div class="col-md-4 text-right">
				<button type="button" class="close pull-right" data-dismiss="modal" style="color: #000000;">&times;</button>
			</div>
			<div class="col-md-12 text-left">
				<hr style="margin-top: 5px; margin-bottom: 10px;">
			</div>
		</div>
		<div class="col-lg-12">
			<small>
				<table class="table table-bordered" width="100%" cellspacing="0" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-12 text-left" style="background-color: #c9c9c9;">
								<div class="col-xs-6 no-padding text-left">LABA RUGI PLASMA INTI (PER RHPP) BULAN : <?php echo strtoupper($nama_bulan).' '.$tahun; ?></div>
								<div class="col-xs-6 no-padding text-left">
									<button type="button" class="btn btn-default pull-right" data-unit="<?php echo $unit; ?>" data-bulan="<?php echo $bulan; ?>" data-tahun="<?php echo $tahun; ?>" data-perusahaan="<?php echo $perusahaan; ?>" onclick="lr.encryptParams(this)"><i class="fa fa-file-excel-o"></i> Export Excel</button>
								</div>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data as $key => $value): ?>
							<tr>
								<td>
									<table class="table table-bordered" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th class="text-left" colspan="7" style="background-color: #b7c9e8;">PERFORMA <?php echo $value['nama_mitra'].' '.$value['no_kandang'].'-'.$value['no_siklus'].' | '.'NO. REG : '.$value['noreg']; ?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<th class="text-center col-xs-1" style="background-color: transparent;">CHICK IN</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">PAKAN (KG)</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">LAMA PANEN</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">PERFORMA</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">IP</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">PANEN</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">PENJUALAN</th>
											</tr>
											<tr>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">TANGGAL</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-2 no-padding">&nbsp;</div>
														<div class="col-xs-10 no-padding"><?php echo strtoupper(tglIndonesia($value['tgl_docin'], '-', ' ')) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">POPULASI</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-2 no-padding">&nbsp;</div>
														<div class="col-xs-10 no-padding"><?php echo angkaRibuan($value['populasi_panen']) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">JENIS</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-2 no-padding">&nbsp;</div>
														<div class="col-xs-10 no-padding"><?php echo $value['nama_doc'] ?></div>
													</div>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">TOTAL PAKAN</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-4 no-padding"><b><?php echo angkaRibuan($value['detail_pakan']['total']) ?></b></div>
													</div>
													<?php foreach ($value['detail_pakan']['detail'] as $k_det => $v_det): ?>
														<div class="col-xs-12 no-padding">
															<div class="col-xs-7 no-padding"><?php echo $v_det['nama_barang'] ?></div>
															<div class="col-xs-1 no-padding">:</div>
															<div class="col-xs-4 no-padding"><b><?php echo angkaRibuan($v_det['total_pakan']) ?></b></div>
														</div>
													<?php endforeach ?>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">AWAL</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-7 no-padding"><?php echo strtoupper(tglIndonesia($value['tgl_panen_awal'], '-', ' ')) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">AKHIR</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-7 no-padding"><?php echo strtoupper(tglIndonesia($value['tgl_panen_akhir'], '-', ' ')) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">LAMA</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-7 no-padding"><?php echo $value['lama_panen'] ?></div>
													</div>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">UMUR (DAY)</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-4 no-padding"><?php echo $value['umur'] ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">DEPLESI (%)</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-4 no-padding"><?php echo angkaDecimal($value['deplesi']) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">FCR</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-4 no-padding"><?php echo angkaDecimal($value['fcr']) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">BB (KG)</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-4 no-padding"><?php echo angkaDecimal($value['bb']) ?></div>
													</div>
												</td>
												<td class="text-center" style="vertical-align: middle;">
													<b><?php echo angkaDecimal($value['ip']) ?></b>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">EKOR</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-7 no-padding"><?php echo angkaRibuan($value['ekor_panen']) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">KG</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-7 no-padding"><?php echo angkaDecimal($value['kg_panen']) ?></div>
													</div>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-11 no-padding">TOTAL</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-2 no-padding">&nbsp;</div>
														<div class="col-xs-10 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['total']) ?></b></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-11 no-padding">HARGA RATA-RATA</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-2 no-padding">&nbsp;</div>
														<div class="col-xs-10 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['rata_harga_panen']) ?></b></div>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table class="table table-bordered" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th class="text-left" colspan="2">RHPP PLASMA</th>
												<th class="text-left" colspan="3">LAPORAN INTI</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<th class="text-center col-xs-1" style="background-color: transparent;">TANGGAL (DARI PANEN TERAKHIR)</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">L/R DAN PENDAPATAN PLASMA</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">MODAL INTI</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">ESTIMASI OPR (300)</th>
												<th class="text-center col-xs-1" style="background-color: transparent;">L/R INTI TANPA OPR RILL</th>
											</tr>
											<tr>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">RHPP KE PUSAT</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-5 no-padding"><?php echo strtoupper(tglIndonesia($value['tgl_tutup'], '-', ' ')) ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">JML HARI</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-5 no-padding"><?php echo ($value['transfer'] < 0) ? '('.abs($value['transfer']).')' : $value['transfer'] ?></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding">TRFS PLASMA</div>
														<div class="col-xs-1 no-padding">:</div>
														<div class="col-xs-5 no-padding"><?php echo strtoupper(tglIndonesia($value['tgl_bayar'], '-', ' ')) ?></div>
													</div>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-8 no-padding">L/R</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">&nbsp;</div>
														<div class="col-xs-8 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['pdpt_peternak_belum_pajak']) ?></b></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-8 no-padding">PEND PER POP PANEN</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">&nbsp;</div>
														<div class="col-xs-8 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['pdpt_peternak_belum_pajak']/$value['ekor_panen']) ?></b></div>
													</div>
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">INTI</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">&nbsp;</div>
														<div class="col-xs-8 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['modal_inti']) ?></b></div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-7 no-padding">INTI SEBENARNYA</div>
														<div class="col-xs-1 no-padding">:</div>
													</div>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-4 no-padding">&nbsp;</div>
														<div class="col-xs-8 no-padding"><b><?php echo 'Rp. '.angkaDecimal($value['modal_inti_sebenarnya']) ?></b></div>
													</div>
												</td>
												<td class="text-center" style="vertical-align: middle;">
													<?php
														$val = (($value['lr_inti']) < 0) ? '('.angkaDecimal(abs($value['lr_inti'])).')' : angkaDecimal($value['lr_inti']);
													?>
													<b><?php echo 'Rp. '.angkaDecimal($val) ?></b>
												</td>
												<td class="text-center" style="vertical-align: middle;">
													<?php
														$val = (($value['lr_inti']-$value['biaya_operasional']) < 0) ? '('.angkaDecimal(abs(abs($value['lr_inti'])-$value['biaya_operasional'])).')' : angkaDecimal($value['lr_inti']-$value['biaya_operasional']);
													?>
													<b><?php echo 'Rp. '.$val ?></b>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</small>
		</div>
	</div>
</div>