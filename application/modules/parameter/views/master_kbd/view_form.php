<?php if ( !isset($data) ): ?>
<?php else : ?>
	<div class="row">
		<input type="hidden" id="id" data-idsk="<?php echo $data['id']; ?>">
		<div class="col-sm-12">
 			<div class="col-sm-6 text-left no-padding">
 				<label class="control-label">Perusahaan : <?php echo strtoupper($data['data_perusahaan']['perusahaan']); ?></label>
 			</div>
		</div>
		<div class="col-sm-12">
 			<div class="col-sm-6 text-left no-padding">
 				<label class="control-label">Berlaku : <?php echo tglIndonesia($data['mulai'], '-', ' ', true); ?></label>
 			</div>
 			<div class="col-sm-6 text-right no-padding">
 				<label class="control-label">No : <?php echo $data['nomor']; ?></label>
 			</div>
 		</div>
		<!-- <div class="col-sm-12">
			<div class="col-sm-5 no-padding text-left">
				<?php $hb_len = count($data['hitung_budidaya']) - 1; ?>
				<label class="control-label">Mitra <?php echo strtoupper($data['hitung_budidaya'][$hb_len]['pola_kerjasama']['item']) . ' ' . strtoupper($data['pola_kerjasama']['item']) . ' ' . $data['item_pola']; ?></label>
			</div>
		</div> -->
		<div class="col-sm-12">
			<div class="col-sm-10 no-padding">
				<?php 
				$perwakilan = '';
				$index = 0;
				foreach ($data['hitung_budidaya'][$hb_len]['perwakilan_maping'] as $key => $v_pm):
					$index++;
					if ( $perwakilan != '' ) {
						$perwakilan .= ', '.$v_pm['nama_pwk'];
					} else {
						$perwakilan .= $v_pm['nama_pwk'];
					}
				endforeach 
				?>
				<label class="control-label">Koordinator Wilayah : <?php echo $perwakilan; ?></label>
			</div>
			<div class="col-sm-2 no-padding text-right">
				<?php $hb_len = count($data['hitung_budidaya']) - 1; ?>
				<label class="control-label">Mitra <?php echo strtoupper($data['hitung_budidaya'][$hb_len]['pola_kerjasama']['item']) . ' ' . strtoupper($data['pola_kerjasama']['item']) . ' ' . $data['item_pola']; ?></label>
			</div>
			<div class="col-sm-12 no-padding">
				<hr>
			</div>
		</div>
		<div class="col-sm-6 text-left">
			<?php foreach ($data['harga_sapronak'] as $k_hs => $v_hs): ?>
				<div class="panel-body no-padding">
					<fieldset>
						<legend><div class="col-sm-8 no-padding">Harga Sapronak</div></legend>
						<div class="col-sm-12 no-padding">
							<div class="col-sm-2"><label class="control-label">Supplier</label></div>
							<div class="col-sm-1 no-padding" style="width: 3%;"><label class="control-label">:</label></div>
							<div class="col-sm-6"><label class="control-label"><?php echo strtoupper($v_hs['d_supplier']['nama']) ?></label></div>
						</div>
						<div class="col-sm-12"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
						<div class="col-sm-12 no-padding">
							<div class="col-sm-12 no-padding hrg_sapronak_doc">
								<div class="col-sm-12 no-padding">
									<div class="col-sm-4">
										<label class="control-label" style="text-decoration: underline;">DOC</label>
									</div>
									<div class="col-sm-4">
										<label class="control-label" style="text-decoration: underline;">SUPPLIER</label>
									</div>
									<div class="col-sm-4">
										<label class="control-label" style="text-decoration: underline;">PETERNAK</label>
									</div>
								</div>
								<?php foreach ($v_hs['detail'] as $k_det => $v_det): ?>
									<?php if ( $v_det['jenis'] == 'doc' ): ?>
										<div class="col-sm-12 no-padding row_doc" style="margin-top: 5px;">
											<div class="col-sm-4">
												<?php echo strtoupper($v_det['d_barang']['nama']); ?>
											</div>
											<div class="col-sm-4">
												<?php echo 'Rp. '.angkaDecimal($v_det['hrg_supplier']) ?>
											</div>
											<div class="col-sm-4">
												<?php echo 'Rp. '.angkaDecimal($v_det['hrg_peternak']) ?>
											</div>
										</div>
									<?php endif ?>
								<?php endforeach ?>
							</div>
							<div class="col-sm-12 no-padding">
								<div class="col-sm-12"><label class="control-label" style="text-decoration: underline;">PAKAN</label></div>
								<div class="col-sm-12 no-padding hrg_sapronak_pakan1" style="margin-top: 5px;">
									<?php foreach ($v_hs['detail'] as $k_det => $v_det): ?>
										<?php if ( $v_det['jenis'] == 'pakan1' ): ?>
											<div class="col-sm-12 no-padding row_doc" style="margin-top: 5px;">
												<div class="col-sm-4">
													<?php echo strtoupper($v_det['d_barang']['nama']); ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_supplier']) ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_peternak']) ?>
												</div>
											</div>
										<?php endif ?>
									<?php endforeach ?>
								</div>
								<div class="col-sm-12 no-padding hrg_sapronak_pakan2" style="margin-top: 5px;">
									<?php foreach ($v_hs['detail'] as $k_det => $v_det): ?>
										<?php if ( $v_det['jenis'] == 'pakan2' ): ?>
											<div class="col-sm-12 no-padding row_doc" style="margin-top: 5px;">
												<div class="col-sm-4">
													<?php echo strtoupper($v_det['d_barang']['nama']); ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_supplier']) ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_peternak']) ?>
												</div>
											</div>
										<?php endif ?>
									<?php endforeach ?>
								</div>
								<div class="col-sm-12 no-padding hrg_sapronak_pakan3" style="margin-top: 5px;">
									<?php foreach ($v_hs['detail'] as $k_det => $v_det): ?>
										<?php if ( $v_det['jenis'] == 'pakan3' ): ?>
											<div class="col-sm-12 no-padding row_doc" style="margin-top: 5px;">
												<div class="col-sm-4">
													<?php echo strtoupper($v_det['d_barang']['nama']); ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_supplier']) ?>
												</div>
												<div class="col-sm-4">
													<?php echo 'Rp. '.angkaDecimal($v_det['hrg_peternak']) ?>
												</div>
											</div>
										<?php endif ?>
									<?php endforeach ?>
								</div>
							</div>
							<div class="col-sm-12 no-padding">
								<div class="col-sm-12"><label class="control-label" style="text-decoration: underline;">LAMPIRAN</label></div>
								<div class="col-sm-12">
									<div class="col-sm-1 no-padding"><label class="control-label">DOC</label></div>
									<div class="col-sm-1 no-padding" style="width: 1%;"><label class="control-label">:</label></div>
									<div class="col-sm-10" style="padding-top: 7px;">
										<?php if ( !empty( $v_hs['doc_dok_cp']) ): ?>
											<a name="dokumen" class="text-right doc" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $v_hs['doc_dok_cp'] ?>"><?php echo $v_hs['doc_dok_cp']; ?></a>
										<?php else: ?>
											-
										<?php endif ?>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="col-sm-1 no-padding"><label class="control-label">PAKAN</label></div>
									<div class="col-sm-1 no-padding" style="width: 1%;"><label class="control-label">:</label></div>
									<div class="col-sm-10" style="padding-top: 7px;">
										<?php if ( !empty( $v_hs['pakan_dok_cp']) ): ?>
											<a name="dokumen" class="text-right pakan" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $v_hs['pakan_dok_cp'] ?>"><?php echo $v_hs['pakan_dok_cp']; ?></a>
										<?php else: ?>
											-
										<?php endif ?>
									</div>
								</div>
							</div>
						</div>
					</fieldset>
				</div>
				<br>
			<?php endforeach ?>
			<div class="col-sm-12 no-padding">
				<fieldset>
					<legend>Harga Kesepakatan</legend>
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
								<?php for ($i=0; $i < 5; $i++) { ?>
									<?php 
										if ($i == 0) {
											$_range_awal = '<=';
											$range_akhir = 1.49;
											$_range_akhir = angkaDecimal($range_akhir);
										} elseif ($i == 1) {
											$range_awal = $range_akhir + 0.01;
											$range_akhir = $range_awal + 0.10;
											$_range_awal = angkaDecimal($range_awal);
											$_range_akhir = angkaDecimal($range_akhir);
										} elseif ($i == 4) {
											$range_awal = $range_akhir + 0.01;
											$_range_awal = angkaDecimal($range_awal);
											$_range_akhir = '>=';
										} else {
											$range_awal = $range_akhir + 0.01;
											$range_akhir = $range_awal + 0.09;
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
				</fieldset>
			</div>
			<div class="col-sm-12">
				<br>
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
				<fieldset>
					<legend>Performa</legend>
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
								<!-- <td class="col-sm-4 text-right"><label>Pakan 1</label></td> -->
								<td class="col-sm-4 text-right">
									<label><?php echo empty($data['harga_performa'][0]['d_pakan1']['nama']) ? 'Pakan 1' : $data['harga_performa'][0]['d_pakan1']['nama']; ?></label>
								</td>
								<td><label>:</label></td>
								<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan1'], 3); ?></label></td>
								<td class="col-sm-1"></td>
							</tr>
							<tr class="v-center">
								<td class="col-sm-3 text-right"><label>FCR</label></td>
								<td><label>:</label></td>
								<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['fcr'], 3); ?></label></td>
								<td class="col-sm-1"></td>
								<!-- <td class="col-sm-4 text-right"><label>Pakan 2</label></td> -->
								<td class="col-sm-4 text-right">
									<label><?php echo empty($data['harga_performa'][0]['d_pakan2']['nama']) ? 'Pakan 2' : $data['harga_performa'][0]['d_pakan2']['nama']; ?></label>
								</td>
								<td><label>:</label></td>
								<td class="col-sm-2"><label class="pull-right"><?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan2'], 3); ?></label></td>
								<td class="col-sm-1"></td>
							</tr>
							<tr class="v-center">
								<td class="col-sm-3 text-right"><label>Umur (hari)</label></td>
								<td><label>:</label></td>
								<td class="col-sm-2"><label class="pull-right"><?php echo angkaRibuan($data['harga_performa'][0]['umur']); ?></label></td>
								<td class="col-sm-1"></td>
								<!-- <td class="col-sm-4 text-right"><label>Pakan 3</label></td> -->
								<td class="col-sm-4 text-right">
									<label><?php echo empty($data['harga_performa'][0]['d_pakan3']['nama']) ? 'Pakan 3' : $data['harga_performa'][0]['d_pakan3']['nama']; ?></label>
								</td>
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
				</fieldset>
			</div>
			<div class="col-sm-12 no-padding">
				<br>
				<fieldset>
					<legend>Bonus FCR</legend>
					<table class="table no-border custom_table">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-4 text-center">Range</th>
								<th class="col-sm-4 text-center">Tarif (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data['selisih_pakan'] as $key => $v_slsp): ?>
								<?php 
									$range_awal_pp = ($v_slsp['range_awal'] > 0) ? angkaDecimalFormat($v_slsp['range_awal'], 3) : '<=';
									$range_akhir_pp = ($v_slsp['range_akhir'] > 0) ? angkaDecimalFormat($v_slsp['range_akhir'], 3) : '>=';
									$tarif_pp = ($v_slsp['tarif'] > 0) ? angkaDecimal($v_slsp['tarif']) : 0;
								?>
								<tr class="data v-center">
									<td class="text-center range_awal"><label><?php echo $range_awal_pp ?></label></td>
									<td class="text-center"><label>-</label></td>
									<td class="text-center range_akhir"><label><?php echo $range_akhir_pp ?></label></td>
									<td>
										<label class="pull-right"><?php echo 'Rp. ' . $tarif_pp; ?></label>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="col-sm-12 no-padding">
				<br>
				<fieldset>
					<legend>Bonus</legend>
					<table class="table no-border custom_table">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-6 text-center">Nilai IP</th>
								<th class="col-sm-3 text-center">Bonus Kematian</th>
								<th class="col-sm-3 text-center">Bonus Harga</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data['hitung_budidaya'] as $key => $v_hp): ?>
								<?php 
									$ip_awal_pp = ($v_hp['ip_awal'] > 0) ? angkaDecimalFormat($v_hp['ip_awal'], 2) : '<=';
									$ip_akhir_pp = ($v_hp['ip_akhir'] > 0) ? angkaDecimalFormat($v_hp['ip_akhir'], 2) : '>=';
									$bonus_kematian = ($v_hp['bonus_dh'] > 0) ? angkaDecimal($v_hp['bonus_dh']) : 0;
									$bonus_harga = ($v_hp['bonus_ip'] > 0) ? angkaDecimal($v_hp['bonus_ip']) : 0;
								?>									
								<tr>
									<td class="col-sm-2 text-center"><label><?php echo $ip_awal_pp; ?></label></td>
									<td class="col-sm-1 text-center"><label>-</label></td>
									<td class="col-sm-2 text-center"><label><?php echo $ip_akhir_pp; ?></label></td>
									<td class="col-sm-2 text-right">
										<label class="pull-left"><?php echo 'Rp.'; ?></label>
										<label class="pull-right"><?php echo $bonus_kematian; ?></label>
									</td>
									<td class="col-sm-3 text-right"><label class="pull-right"><?php echo $bonus_harga . ' %'; ?></label></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="col-sm-12 no-padding">
				<br>
				<fieldset>
					<legend style="width: 40%;">Bonus Insentif Listrik</legend>
					<table  class="table no-border custom_table">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-4 text-center">Nilai IP</th>
								<th class="col-sm-4 text-center">Bonus (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<?php if ( count($data['bonus_insentif_listrik']) == 0 ): ?>
								<tr class="data v-center">
									<td class="text-center range_awal"><label>380</label></td>
									<td class="text-center"><label>-</label></td>
									<td class="text-center range_akhir"><label>>=</label></td>
									<td class="text-right"><label>Rp. 0</label></td>
								</tr>
							<?php else: ?>
								<?php foreach ($data['bonus_insentif_listrik'] as $key => $v_bil): ?>
									<tr class="data v-center">
										<td class="text-center range_awal"><label><?php echo ($v_bil['ip_awal'] == 0) ? '<=' : angkaDecimalFormat($v_bil['ip_awal'], 2); ?></label></td>
										<td class="text-center"><label>-</label></td>
										<td class="text-center range_akhir"><label><?php echo ($v_bil['ip_akhir'] == 0) ? '>=' : angkaDecimalFormat($v_bil['ip_akhir'], 2); ?></label></td>
										<td class="text-right"><label><?php echo ($v_bil['bonus'] == 0) ? 0 : 'Rp. '.angkaRibuan($v_bil['bonus']); ?></label></td>
									</tr>
								<?php endforeach ?>
							<?php endif ?>
						</tbody>
					</table>
				</fieldset>
			</div>
			<br>
			<div class="col-sm-12 no-padding">
				<br>
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