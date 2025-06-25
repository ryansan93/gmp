<?php if ( !isset($data) ): ?>
<?php else : ?>
	<div class="row">
		<input type="hidden" id="id" data-idsk="<?php echo $data['id']; ?>">
		<div class="col-sm-12">
			<!-- <table class="table no-border custom_table" width="100%">
 				<tbody>
 					<tr>
 						<td class="text-left col-sm-6 no-padding">
							<label class="control-label">Berlaku : <?php echo tglIndonesia($data['mulai'], '-', ' ', true); ?></label>
 						</td>
 						<td class="text-right col-sm-6 no-padding">
 							<label class="control-label">No : <?php echo $data['nomor']; ?></label>
 						</td>
 					</tr>
 				</tbody>
 			</table> -->
 			<div class="col-sm-6 text-left no-padding">
 				<label class="control-label">Berlaku : <?php echo tglIndonesia($data['mulai'], '-', ' ', true); ?></label>
 			</div>
 			<div class="col-sm-6 text-right no-padding">
 				<label class="control-label"><label class="control-label">No : <?php echo $data['nomor']; ?></label></label>
 			</div>
 		</div>
		<div class="col-sm-12">
			<div class="col-sm-5 no-padding text-left">
				<label class="control-label">Mitra <?php echo strtoupper($data['hitung_budidaya']['pola_kerjasama']['item']) . ' ' . strtoupper($data['pola_kerjasama']['item']) . ' ' . $data['item_pola']; ?></label>
			</div>
			<div class="col-sm-7 no-padding text-right">
				<?php 
				$perwakilan = '';
				$index = 0;
				foreach ($data['hitung_budidaya']['perwakilan_maping'] as $key => $v_pm):
					$index++;
					// $no_jatim = substr($v_pm['nama_pwk'], 11, 5);
					// $perwakilan .= 'Jatim ' . $no_jatim;
					// if ( $index < count($data['hitung_budidaya']['perwakilan_maping']) ) {
					// 	$perwakilan .= ', ';
					// }
					$perwakilan = $v_pm['nama_pwk'];
				endforeach 
				?>
				<label class="control-label">Koordinator Wilayah : <?php echo $perwakilan; ?></label>
			</div>
			<div class="col-sm-12 no-padding">
				<hr>
			</div>
		</div>
		<div class="col-sm-6 text-left">
			<label>Harga Sapronak</label>
			<div class="panel panel-default">
				<div class="panel-body">
					<table class="table no-border custom_table">
						<tbody>
							<tr class="data">
								<td class="col-sm-3 text-right"><label>Biaya Operasional :</label></td>
								<td class="col-sm-3">
									<label class="pull-left">Rp. </label>
									<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['biaya_ops']); ?></label>
								</td>
								<td></td>
							</tr>
							<tr class="data line-bottom">
								<td class="col-sm-3 text-right"><label>Jaminan Keuntungan :</label></td>
								<td class="col-sm-3">
									<label class="pull-left">Rp. </label>
									<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['jam_keuntungan']); ?></label>
								<td></td>
							</tr>
							<tr>
								<td colspan="3">
									<table class="table no-border table_custom no-padding">
										<thead>
											<tr class="head">
												<td class="col-sm-2"></td>
												<th class="text-center col-sm-2"><label>Harga Supplier</label></th>
												<td class="col-sm-1"></td>
												<!-- <th class="text-center col-sm-2"><label>Ongkos Angkut</label></th>
												<td class="col-sm-1"></td> -->
												<th class="text-center col-sm-2"><label>Harga Mitra</label></th>
												<?php // if ( $show_header == 'show' ) { ?>
													<!-- <td class=""></td>
													<th class="text-center col-sm-4">Review Dirut</th> -->
												<?php // } ?>
											</tr>
										</thead>
										<tbody>
											<tr class="row-data v-center">
												<td class="text-right"><label>VOADIP</label></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['voadip']); ?></label>
												</td>
												<td class="text-center">
													<a href="uploads/<?php echo $l_voadip_sup['path']; ?>" name="dokumen" class="text-right voadip" target="_blank" style="padding-right: 10px;" title="<?php echo $l_voadip_sup['filename']; ?>"><i class="fa fa-file"></i></a>
												</td>
												<!-- <td colspan="2"></td> -->
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['voadip_mitra']); ?></label>
												</td>
												<?php if ( $show_detail == 'show' ): ?>
													<td></td>
													<td><input class="form-control harga_sapronak" name="voadip_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php // elseif ( $show_detail == 'show2' ): ?>
													<!-- <td></td>
													<td>
														<label class="pull-left">Rp.</label>
														<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['voadip_dirut']); ?></label>
													</td> -->
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right"><label>DOC</label></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['doc']); ?></label>
												</td>
												<td class="text-center">
													<a href="uploads/<?php echo $l_doc_sup['path']; ?>" name="dokumen" class="text-right doc" target="_blank" style="padding-right: 10px;" title="<?php echo $l_doc_sup['filename']; ?>"><i class="fa fa-file"></i></a>
												</td>
												<!-- <td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['oa_doc']); ?></label>
												</td>
												<td>
													<a href="uploads/<?php echo $l_oa_doc['path']; ?>" name="dokumen" class="text-right doc" target="_blank" style="padding-right: 10px;" title="<?php echo $l_oa_doc['filename']; ?>"><i class="fa fa-file"></i></a>
												</td> -->
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['doc_mitra']); ?></label>
												</td>
												<?php if ( $show_detail == 'show' ): ?>
													<td></td>
													<td><input class="form-control harga_sapronak" name="doc_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php // elseif ( $show_detail == 'show2' ): ?>
													<!-- <td></td>
													<td>
														<label class="pull-left">Rp.</label>
														<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['doc_dirut']); ?></label>
													</td> -->
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right"><label>Pakan 1</label></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan1']); ?></label>
												</td>
												<td class="text-center">
													<a href="uploads/<?php echo $l_pakan_sup['path']; ?>" name="dokumen" class="text-right pakan1" target="_blank" style="padding-right: 10px;" title="<?php echo $l_pakan_sup['filename']; ?>"><i class="fa fa-file"></i></a>
												</td>
												<!-- <td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['oa_pakan']); ?></label>
												</td>
												<td>
													<a href="uploads/<?php echo $l_oa_pakan['path']; ?>" name="dokumen" class="text-right doc" target="_blank" style="padding-right: 10px;" title="<?php echo $l_oa_pakan['filename']; ?>"><i class="fa fa-file"></i></a>
												</td> -->
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan1_mitra']); ?></label>
												</td>
												<?php if ( $show_detail == 'show' ): ?>
													<td></td>
													<td><input class="form-control harga_sapronak" name="pakan1_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php // elseif ( $show_detail == 'show2' ): ?>
													<!-- <td></td>
													<td>
														<label class="pull-left">Rp.</label>
														<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan1_dirut']); ?></label>
													</td> -->
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right"><label>Pakan 2</label></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan2']); ?></label>
												</td>
												<td></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan2_mitra']); ?></label>
												</td>
												<?php if ( $show_detail == 'show' ): ?>
													<td></td>
													<td><input class="form-control harga_sapronak" name="pakan2_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php // elseif ( $show_detail == 'show2' ): ?>
													<!-- <td></td>
													<td>
														<label class="pull-left">Rp.</label>
														<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan2_dirut']); ?></label>
													</td> -->
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right"><label>Pakan 3</label></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan3']); ?></label>
												</td>
												<td></td>
												<td>
													<label class="pull-left">Rp.</label>
													<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan3_mitra']); ?></label>
												</td>
												<?php if ( $show_detail == 'show' ): ?>
													<td></td>
													<td><input class="form-control harga_sapronak" name="pakan3_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php // elseif ( $show_detail == 'show2' ): ?>
													<!-- <td></td>
													<td>
														<label class="pull-left">Rp.</label>
														<label class="pull-right"><?php echo angkaDecimal($data['harga_sapronak'][0]['pakan3_dirut']); ?></label>
													</td> -->
												<?php endif ?>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<label>Range Standar Pemakaian Pakan</label>
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="col-sm-12 no-padding text-center">
							<table class="table no-border custom_table" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<th class="text-center" colspan="3">Range</th>
										<th class="text-center">Standar Minimum</th>
										<th class="text-center"></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($data['standar_pakan'] as $key => $v_stp): ?>		
										<?php 
											$bb_awal_pp = ($v_stp['bb_awal'] > 0) ? angkaDecimal($v_stp['bb_awal']) : '';
											$bb_akhir_pp = ($v_stp['bb_akhir'] > 0) ? angkaDecimal($v_stp['bb_akhir']) : '>=';
											$standar_min_pp = ($v_stp['standar_min'] > 0) ? angkaDecimalFormat($v_stp['standar_min'], 3) : 0;
										?>							
										<tr class="v-center">
											<td class="col-sm-3 text-center"><label><?php echo $bb_awal_pp; ?></label></td>
											<td class="col-sm-1 text-center"><label>-</label></td>
											<td class="col-sm-3 text-center"><label><?php echo $bb_akhir_pp ?></label></td>
											<td class="col-sm-5 text-right">
												<label class="pull-right">&nbsp&nbsp&nbsp&nbsp&nbspEkor/Ir</label></div>
												<label class="pull-right"><?php echo $standar_min_pp; ?></label></div>
											</td>
										</tr>
									<?php endforeach ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<label>Selisih Pemakaian Pakan</label>
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="col-sm-12 no-padding text-center">
							<table class="table no-border custom_table" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<th class="text-center" colspan="3">Range</th>
										<th class="text-center">% Selisih</th>
										<th class="text-center"></th>
										<th class="text-center">Tarif</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($data['selisih_pakan'] as $key => $v_slsp): ?>
										<?php 
											$range_awal_pp = ($v_slsp['range_awal'] > 0) ? angkaDecimalFormat($v_slsp['range_awal'], 3) : '<=';
											$range_akhir_pp = ($v_slsp['range_akhir'] > 0) ? angkaDecimalFormat($v_slsp['range_akhir'], 3) : '>=';
											$selisih_pp = ($v_slsp['selisih'] > 0) ? angkaRibuan($v_slsp['selisih']) : 0;
											$tarif_pp = ($v_slsp['tarif'] > 0) ? angkaDecimal($v_slsp['tarif']) : 0;
										?>									
										<tr>
											<td class="col-sm-2 text-center"><label><?php echo $range_awal_pp; ?></label></td>
											<td class="col-sm-1 text-center"><label>-</label></td>
											<td class="col-sm-2 text-center"><label><?php echo $range_akhir_pp; ?></label></td>
											<td class="col-sm-2 text-right">
												<label class="pull-right">%</label>
												<label class="pull-right"><?php echo $selisih_pp; ?></label>
												<!-- <div class="col-md-8 text-right no-padding"></div>
												<div class="col-md-4 text-right no-padding"></div> -->
											</td>
											<td class="col-sm-2 text-right"></td>
											<td class="col-sm-3 text-right">
												<label class="pull-left">Rp.</label>
												<label class="pull-right"><?php echo $tarif_pp; ?></label>
												<!-- <div class="col-md-3 text-right no-padding"></div>
												<div class="col-md-9 text-right no-padding"></div> -->
											</td>
										</tr>
									<?php endforeach ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12">
				<div class="col-sm-12 no-padding">
					<p>
						<b><u>Keterangan</u></b>
						<ul>
							<?php foreach ($tbl_logs as $k_logs => $v_logs): ?>
								<li class="list"><?php echo $v_logs['deskripsi'] . ' ' . $v_logs['waktu']; ?></li>
							<?php endforeach ?>
						</ul>
					</p>

					<?php if ( $data['g_status'] == getStatus('reject') ): ?>
						<p>
							<b><u>Alasan Reject</u></b>
							<ul>
								<li><?php echo $data['alasan_tolak']; ?></li>
							</ul>
						</p>
					<?php endif ?>
				</div>
			</div>
		</div>

		<div class="col-sm-6 text-left">
			<div class="col-sm-12 no-padding">
				<label>Performa</label>
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table no-border custom_table">
							<tbody>
								<tr class="v-center">
									<td class="col-sm-3 text-right"><label>DH (%)</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimal($data['harga_performa'][0]['dh']); ?></label></td>
									<td class="col-sm-1"></td>
									<td class="col-sm-4 text-right"><label>Kebutuhan Pakan</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['tot_pakan'], 3); ?></label></td>
									<td class="col-sm-1"></td>
								</tr>
								<tr class="v-center">
									<td class="col-sm-3 text-right"><label>BB (Kg)</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['bb'], 3); ?></label></td>
									<td class="col-sm-1"></td>
									<td class="col-sm-4 text-right"><label>Pakan 1</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan1'], 3); ?></label></td>
									<td class="col-sm-1"></td>
								</tr>
								<tr class="v-center">
									<td class="col-sm-3 text-right"><label>FCR</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['fcr'], 3); ?></label></td>
									<td class="col-sm-1"></td>
									<td class="col-sm-4 text-right"><label>Pakan 2</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan2'], 3); ?></label></td>
									<td class="col-sm-1"></td>
								</tr>
								<tr class="v-center">
									<td class="col-sm-3 text-right"><label>Umur (hari)</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaRibuan($data['harga_performa'][0]['umur']); ?></label></td>
									<td class="col-sm-1"></td>
									<td class="col-sm-4 text-right"><label>Pakan 3</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan3'], 3); ?></label></td>
									<td class="col-sm-1"></td>
								</tr>
								<tr class="v-center">
									<td class="col-sm-3 text-right"><label>IP</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaRibuan($data['harga_performa'][0]['ip']); ?></label></td>
									<td class="col-sm-1"></td>
									<td class="col-sm-4 text-right"><label>IE</label></td>
									<td><label>:</label></td>
									<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimal($data['harga_performa'][0]['ie']); ?></label></td>
									<td class="col-sm-1"></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<label>Harga Kesepakatan</label>
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table no-border custom_table">
							<thead>
								<tr>
									<th class="text-center" colspan="2">Range (Kg)</th>
									<th class="text-center"></th>
									<th class="text-center">Harga</th>
									<th class="text-center"></th>
								</tr>
							</thead>
							<tbody>
								<?php 
									$range_awal = $range_akhir = null; 
									$_range_awal = $_range_akhir = null; 
								?>
								<?php for ($i=0; $i < 9; $i++) { ?>
									<?php 
										if ($i == 0) {
											$_range_awal = '<=';
											$range_akhir = 1.20;
											$_range_akhir = angkaDecimal($range_akhir);
										} elseif ($i == 7) {
											$range_awal = $range_akhir + 0.01;
											$range_akhir = $range_awal + 0.28;
											$_range_awal = angkaDecimal($range_awal);
											$_range_akhir = angkaDecimal($range_akhir);
										} elseif ($i == 8) {
											$_range_awal = $range_akhir + 0.01;
											$range_akhir = '>=';
											$_range_akhir = angkaDecimal($range_akhir);
										} elseif ($i >= 3) {
											$range_awal = $range_akhir + 0.01;
											$range_akhir = $range_awal + 0.09;
											$_range_awal = angkaDecimal($range_awal);
											$_range_akhir = angkaDecimal($range_akhir);
										} else {
											$range_awal = $range_akhir + 0.01;
											$range_akhir = $range_awal + 0.14;
											$_range_awal = angkaDecimal($range_awal);
											$_range_akhir = angkaDecimal($range_akhir);
										}
									?>
									<tr class="data v-center">
										<td class="col-sm-2 text-center"><label><?php echo $_range_awal;?></label></td>
										<td class="col-sm-2 text-center"><label><?php echo $_range_akhir;?></label></td>
										<td class="col-sm-1"></td>
										<td class="col-sm-3">
											<?php 
												$hrg = 0;
												if ( !empty($data['harga_sepakat'][$i]) ) {
													$hrg = $data['harga_sepakat'][$i]['harga'];
												} 
											?>
											<label class="pull-left">Rp. </label><label class="pull-right"><?php echo angkaDecimal($hrg); ?></label>
										</td>
										<td class="col-sm-1 text-center">
											<label>
												<?php 
													if ( !empty( $data['harga_sepakat'][$i] ) ) {
														if ( $data['harga_sepakat'][$i]['hpp'] == 1 ) {
															echo '*';
														} 
													}
												?>
											</label>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<label>Bonus</label>
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="col-sm-11">
							<table class="table no-border">
								<tbody>
									<tr>
										<td class="text-right"><label>Bonus FCR</label></td>
										<td><label>:</label></td>
										<td><label>Rp.</label></td>
										<td class="text-right"><label><?php echo angkaDecimal($data['hitung_budidaya']['bonus_fcr']); ?></label></td>
										<td></td>
										<td class="text-right"><label>Bonus DH</label></td>
										<td><label>:</label></td>
										<td><label>Rp.</label></td>
										<td class="text-right"><label><?php echo angkaDecimal($data['hitung_budidaya']['bonus_dh']); ?></label></td>
									</tr>
									<tr>
										<td class="text-right"><label>Bonus CH</label></td>
										<td><label>:</label></td>
										<td><label>Rp.</label></td>
										<td class="text-right"><label><?php echo angkaDecimal($data['hitung_budidaya']['bonus_ch']); ?></label></td>
										<td></td>
										<td class="text-right"><label>Bonus BB</label></td>
										<td><label>:</label></td>
										<td><label>Rp.</label></td>
										<td class="text-right"><label><?php echo angkaDecimal($data['hitung_budidaya']['bonus_bb']); ?></label></td>
									</tr>
									<tr>
										<td class="text-right"><label>Bonus IP</label></td>
										<td><label>:</label></td>
										<td><label>Rp.</label></td>
										<td class="text-right"><label><?php echo angkaDecimal($data['hitung_budidaya']['bonus_ip']); ?></label></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<p><b><u>Note : </u></b></p>
				<p><?php echo $data['note']; ?></p>
			</div>
		</div>
		<div class="col-sm-12 bottom-align-text">
			<?php if ( $akses['a_ack'] == 1 ) { ?>
				<?php if ( $data['g_status'] == getStatus('submit') ){ ?>
					<button type="button" class="btn btn-primary pull-right" onclick="kbd.ack()"><i class="fa fa-check"></i> ACK</button>
				<?php } ?>
			<?php } ?>

			<?php if ( $akses['a_approve'] == 1 ) { ?>
				<?php if ( $data['g_status'] == getStatus('ack') ){ ?>
					<button type="button" class="btn btn-primary pull-right" onclick="kbd.approve()"><i class="fa fa-check"></i> Approve</button>
				<?php } ?>
			<?php } ?>
		</div>
 	</div>
<?php endif ?>