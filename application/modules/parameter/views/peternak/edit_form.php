<div class="col-md-12">
	<label class="head pull-right" data-id="<?php echo $mitra->id; ?>">Nomor : <?php echo $mitra->nomor ?>, Status : <?php echo strtoupper($mitra->status) ?></label>
	<input type="hidden" data-idmitra="<?php echo $mitra->id; ?>" />
</div>
<div class="col-md-12">
	<form class="form form-horizontal" role="form">
		<div name="data-mitra">
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Jenis Mitra</span>
				<div class="col-sm-2">
					<select class="form-control" name="jenis_mitra" required>
						<?php foreach ($jenis_mitra as $key => $jmitra): ?>
							<?php $selected = null; ?>
							<?php if ($mitra['jenis'] == $key):
								$selected = 'selected';
							endif; ?>
							<option value="<?php echo $key ?>" <?php echo $selected; ?> ><?php echo $jmitra ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Perusahaan</span>
				<div class="col-sm-4">
					<select class="form-control" name="perusahaan" disabled required>
						<?php foreach ($perusahaan as $key => $value): ?>
							<?php
								$selected = null;
								if ( $mitra['perusahaan'] == $value['kode'] ) {
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $value['kode'] ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Nama Plasma</span>
				<div class="col-sm-5">
					<input type="text" class="form-control" name="nama_mitra" placeholder="nama mitra" value="<?php echo $mitra['nama'] ?>" required="1">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Nama Group</span>
				<div class="col-sm-5">
					<select class="form-control" name="pemilik">
						<option value="">-- Pilih Pemilik Jika Ada --</option>
						<?php if ( !empty( $pemilik ) ) { ?>
							<?php foreach ($pemilik as $key => $value) { ?>
								<?php
									$selected = null;
									if ( $value['nomor'] == $mitra->d_pemilik->nomor ) {
										$selected = 'selected';
									}
								?>
								<option value="<?php echo $value['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nomor'].' | '.$value['nama']); ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">KTP</span>
				<div class="col-sm-3">
					<input type="text" class="form-control" name="ktp" placeholder="nomor ktp" required data-tipe="ktp" value="<?php echo $mitra['ktp'] ?>">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">NPWP</span>
				<div class="col-sm-3">
					<input type="email" class="form-control" name="npwp" placeholder="npwp" value="<?php echo $mitra['npwp'] ?>">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">No. SKB</span>
				<div class="col-sm-3">
					<input type="text" class="form-control" name="skb" placeholder="No. SKB" value="<?php echo $mitra['skb'] ?>" maxlength="50">
				</div>
				<span class="col-sm-2 text-right">Tgl Habis Berlaku</span>
				<div class="col-sm-3">
					<div class="input-group date" id="tglHbsBerlaku">
						<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $mitra['tgl_habis_skb'] ?>" />
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2"></label>
				<div class="col-sm-3">
					<table class="table telepon">
						<thead>
							<tr>
								<th class="text-left">Telepon</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($mitra['telepons'] as $telp): ?>
								<tr>
									<td>
										<!-- <input class="form-control" type="text" name="telepon" value="<?php echo $telp['nomor'] ?>" placeholder="telepon"> -->
										<input class="form-control" type="text" name="telepon" value="<?php echo $telp['nomor'] ?>" placeholder="telepon" data-tipe="phone" required>
									</td>
									<td>
										<button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
										<button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Plafon</span>
				<div class="col-sm-2">
					<input type="text" class="form-control text-right" name="plafon" placeholder="Plafon" data-tipe="integer" value="<?php echo angkaRibuan($mitra['plafon']); ?>">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Jatuh Tempo</span>
				<div class="col-sm-1">
					<input type="text" class="form-control text-right" name="jatuh_tempo" placeholder="Jatuh Tempo" data-tipe="integer" value="<?php echo angkaRibuan($mitra['jatuh_tempo']); ?>">
				</div>
				<div class="col-sm-1">Hari</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<span for="">Alamat</span>
				</div>
			</div>
			<div class="row form-lokasi">
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Provinsi</span>
						<div class="col-sm-6">
							<select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="provinsi" required>
								<option value="">pilih provinsi</option>
								<?php foreach ($list_provinsi as $prov): 
									$select_prov = null;
									if ( $prov['id'] == $mitra['dKecamatan']['dKota']['dProvinsi']['id'] ) {
										$select_prov = 'selected';
									}
								?>
								<option value="<?php echo $prov['id'] ?>" <?php echo $select_prov; ?> ><?php echo $prov['nama'] ?></option>
								<?php endforeach; ?>
							</select>
							<!-- <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')">
							<option value="<?php echo $mitra['dKecamatan']['dKota']['dProvinsi']['id'] ?>"><?php echo $mitra['dKecamatan']['dKota']['dProvinsi']['nama'] ?></option>
							</select> -->									
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-6 no-padding">
							<div class="col-sm-8 pull-right no-padding">
								<select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')">
									<?php foreach ($tipe_lokasi as $key => $lokasi): ?>
										<?php $select_lok = null; ?>
										<?php if ($key == $mitra['dKecamatan']['dKota']['jenis']): ?>
											<?php $sl = 'selected'; ?>
										<?php endif; ?>
										<option value="<?php echo $key ?>" <?php echo $select_lok; ?> ><?php echo $lokasi ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="kabupaten/kota" required>
								<option value="">pilih kota/kabupaten</option>
                        		<option value="<?php echo $mitra['dKecamatan']['dKota']['id'] ?>" selected><?php echo $mitra['dKecamatan']['dKota']['nama'] ?></option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select class="form-control" name="kecamatan" placeholder="kecamatan" required>
								<option value="">pilih kecamatan</option>
                        		<option value="<?php echo $mitra['dKecamatan']['id'] ?>" selected><?php echo $mitra['dKecamatan']['nama'] ?></option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input type="text" class="form-control" name="kelurahan" placeholder="kelurahan/desa" data-id="" value="<?php echo $mitra['alamat_kelurahan'] ?>" required="1">
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<div class="col-sm-8">
							<textarea class="form-control" name="alamat" style="height: 73px;"><?php echo $mitra['alamat_jalan'] ?></textarea>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-1 text-right">RT</span>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="rt" placeholder="RT" value="<?php echo $mitra['alamat_rt'] ?>" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-1 text-right">RW</span>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="rw" placeholder="RW" value="<?php echo $mitra['alamat_rw'] ?>" required="1">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<span for="">Rekening</span>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Bank</span>
						<div class="col-sm-3">
							<input type="text" class="form-control" name="bank" placeholder="bank" value="<?php echo $mitra['bank'] ?>" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Cabang Bank</span>
						<div class="col-sm-3">
							<input type="text" class="form-control" name="cabang-bank" placeholder="cabang bank" value="<?php echo $mitra['rekening_cabang_bank'] ?>" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">No. Rekening</span>
						<div class="col-sm-3">
							<input type="text" class="form-control" name="no-rekening" placeholder="no. rekening" value="<?php echo $mitra['rekening_nomor'] ?>" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Pemilik Rekening</span>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="pemilik-rekening" placeholder="pemilik rekening" value="<?php echo $mitra['rekening_pemilik'] ?>" required="1">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<span for="">Jaminan</span>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Keterangan Jaminan</span>
						<div class="col-sm-5">
							<textarea name="jaminan" class="form-control" rows="2"><?php echo $mitra['keterangan_jaminan'] ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<!-- Nav tabs -->
				<div class="panel-heading no-padding">
					<ul class="nav nav-tabs nav-justified">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#lampiran_mitra" data-tab="lampiran">Lampiran</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#lampiran_jaminan_mitra" data-tab="jaminan">Jaminan</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#kandang" data-tab="kandang">Kandang</a>
						</li>
					</ul>
				</div>

				<div class="tab-content new-line">
					<!-- tab kandang -->
					<div id="kandang" class="tab-pane fade">
						<form class="form form-horizontal">
							<?php foreach ( $mitra['perwakilans'] as $perwakilan): ?>
								<div name="data-perwakilan" data-id="<?php echo $perwakilan['id'] ?>">
									<div class="col-lg-12 div-bordered align-items-center d-flex head_pwk">
										<div class="col-sm-5 align-items-center d-flex">
											<span class="col-sm-5 text-right">Perwakilan</span>
											<div class="col-sm-7">
												<select class="form-control" name="perwakilan" onchange="ptk.getListUnitPerwakilan(this)" placeholder="perwakilan" required disabled>
													<option value="">pilih perwakilan</option>
													<?php foreach ($list_perwakilan as $lperwakilan): ?>
														<?php $selected = null; ?>
														<?php if ( $lperwakilan['id'] == $perwakilan->dPerwakilan['id'] ): ?>
															<?php $selected = 'selected'; ?>
														<?php endif ?>
														<option value="<?php echo $lperwakilan['id'] ?>" <?php echo $selected; ?> ><?php echo $lperwakilan['nama'] ?></option>
													<?php endforeach; ?>
												</select>
												<!-- <select class="form-control" name="perwakilan" onchange="ptk.getListUnitPerwakilan(this)">
												<option value="<?php echo $perwakilan->dPerwakilan['id'] ?>"><?php echo $perwakilan->dPerwakilan['nama'] ?></option>
												</select> -->															
											</div>
										</div>
										<div class="col-sm-5 align-items-center d-flex">
											<span class="col-sm-1 text-right">NIM</span>
											<div class="col-sm-5">
												<input type="text" class="form-control" name="nim" value="<?php echo $perwakilan->nim ?>" placeholder="nim" maxlength="8" disabled>
											</div>
										</div>
										<div class="col-sm-2">
											<button type="button" class="btn btn-danger pull-right" onclick="ptk.hapusPerwakilan(this)"><i class="fa fa-trash"></i></button>
										</div>
									</div>

									<?php foreach ($perwakilan->kandangs as $kandang): ?>
										<div name="data-kandang" data-id="<?php echo $kandang['id']; ?>">
											<div class="col-sm-12 no-padding" style="padding-bottom: 10px;">
												<fieldset>
													<legend> <button type="button" class="btn btn-xs btn-danger" onclick="ptk.hapusKandang(this)"><i class="fa fa-trash"></i></button> | Kandang</legend>
													<div class="row col-sm-12">
														<div class="col-sm-4 no-padding">
															<div class="col-sm-12">
																<div class="form-group align-items-center d-flex">
																	<span class="col-sm-4 text-right">Grup</span>
																	<div class="col-sm-3">
																		<input type="text" class="form-control text-center" name="grup" data-tipe="integer" value="<?php echo $kandang->grup ?>" maxlength="2" placeholder="Grup" required>
																	</div>
																</div>
																<div class="form-group align-items-center d-flex">
																	<span class="col-sm-4 text-right">No. Kandang</span>
																	<div class="col-sm-3">
																		<input type="text" class="form-control text-center" name="no-kandang" value="<?php echo $kandang->kandang ?>" maxlength="2" placeholder="No. Kandang" required>
																	</div>
																</div>
																<div class="form-group align-items-center d-flex">
																	<span class="col-sm-4 text-right">Kapasitas</span>
																	<div class="col-sm-4">
																		<input type="text" class="form-control" name="kapasitas" value="<?php echo $kandang->ekor_kapasitas ?>" data-tipe="integer"  placeholder="Ekor Kapasitas" required>
																	</div>
																	<span class="col-sm-2 text-right">Ekor</span>
																</div>
																<div class="form-group align-items-center d-flex">
																	<span class="col-sm-4 text-right">Tipe Kandang</span>
																	<div class="col-sm-8">
																		<select class="form-control" name="tipe_kandang" placeholder="Tipe Kandang" required>
																			<?php foreach ($tipe_kandang as $key_kandang => $vkandang): ?>
																				<?php 
																					$selected = null;
																					if ($key_kandang == $kandang['tipe']) {
																						$selected = 'selected';
																					}
																				?>
																				<option value="<?php echo $key_kandang ?>" <?php echo $selected; ?> ><?php echo $vkandang ?></option>
																			<?php endforeach; ?>
																		</select>
																	</div>
																</div>
																<div class="form-group align-items-center d-flex">
																	<span class="col-sm-4 text-right">Status</span>
																	<div class="col-sm-6">
																		<select class="form-control" name="status" placeholder="Status" required>
																			<?php foreach ($status_kandang as $key => $s_kandang): ?>
																				<?php 
																					$select = null;
																						if ($key == $kandang['status']) {
																					$select = 'selected';
																				} 
																				?>
																			<option value="<?php echo $key ?>" <?php echo $select; ?> ><?php echo $s_kandang ?></option>
																			<?php endforeach; ?>
																		</select>
																	</div>
																</div>
															</div>
														</div>

														<div class="col-sm-4 no-padding form-lokasi">
															<div class="form-group">
																<div class="col-sm-12">
																	<div class="form-group align-items-center d-flex">
																		<span class="col-sm-4 text-right">Unit</span>
																		<div class="col-sm-8">
																			<select class="form-control" name="unit" placeholder="Unit" required>
																				<option value="<?php echo $kandang->d_unit->id ?>"><?php echo $kandang->d_unit->nama ?></option>
																			</select>
																		</div>
																	</div>
																	<div class="form-group align-items-center d-flex">
																		<span class="col-sm-4 text-right">Provinsi</span>
																		<div class="col-sm-8">
																			<select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="Provinsi" required>
																				<option value="">pilih provinsi</option>
																				<?php foreach ($list_provinsi as $prov): 
																					$selec_prov = null;
																					if ( $prov['id'] == $kandang->dKecamatan['dKota']['dProvinsi']['id'] ) {
																						$selec_prov = 'selected';
																					}
																				?>
																					<option value="<?php echo $prov['id'] ?>" <?php echo $selec_prov; ?> ><?php echo $prov['nama'] ?></option>
																				<?php endforeach; ?>
																			</select>
																			<!-- <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')">
																			<option value="<?php echo $kandang->dKecamatan['dKota']['dProvinsi']['id'] ?>"><?php echo $kandang->dKecamatan['dKota']['dProvinsi']['nama'] ?></option>
																			</select> -->
																		</div>
																	</div>
																	<div class="form-group">
																		<div class="col-sm-4 no-padding">
																			<div class="col-sm-11 pull-right no-padding">
																				<select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="Tipe Lokasi" required>
																					<?php foreach ($tipe_lokasi as $key => $lokasi): ?>
																						<option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
																					<?php endforeach; ?>
																				</select>
																			</div>
																		</div>
																		<div class="col-sm-8">
																			<select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="Kabupaten" required>
																				<option value="<?php echo $kandang->dKecamatan['dKota']['id'] ?>"><?php echo $kandang->dKecamatan['dKota']['nama'] ?></option>
																			</select>
																		</div>
																	</div>
																	<div class="form-group align-items-center d-flex">
																		<span class="col-sm-4 text-right">Kecamatan</span>
																		<div class="col-sm-8">
																			<select class="form-control" name="kecamatan" placeholder="Kecamatan" required>
																				<option value="<?php echo $kandang->dKecamatan['id'] ?>"><?php echo $kandang->dKecamatan['nama'] ?></option>
																			</select>
																		</div>
																	</div>
																	<div class="form-group align-items-center d-flex">
																		<span class="col-sm-4 text-right">Kelurahan/Desa</span>
																		<div class="col-sm-8">
																			<input type="text" class="form-control autocomplete_lokasi" name="kelurahan" placeholder="kelurahan/desa" value="<?php echo $kandang->alamat_kelurahan ?>" required>
																		</div>
																	</div>
																</div>
															</div>
														</div>

														<div class="col-sm-4">
															<div class="form-group">
																<div class="col-sm-12">
																	<textarea class="form-control" name="alamat" style="height: 73px;" placeholder="Alamat" required><?php echo $kandang->alamat_jalan ?></textarea>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-1 text-right">RT</span>
																<div class="col-sm-3">
																	<input type="text" class="form-control" name="rt" placeholder="RT" value="<?php echo $kandang->alamat_rt ?>" required>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-1 text-right">RW</span>
																<div class="col-sm-3">
																	<input type="text" class="form-control" name="rw" placeholder="RW" value="<?php echo $kandang->alamat_rw ?>" required="1">
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-1 text-right">OA</span>
																<div class="col-sm-8">
																	<input type="text" class="form-control" name="ongkos-angkut" value="<?php echo $kandang->ongkos_angkut ?>" placeholder="ongkos angkut" data-tipe="decimal" required>
																</div>
															</div>
														</div>
													</div>

													<div class="row col-sm-12">
														<span for="">Bangunan Kandang</span>
														<table class="table table-bordered bangunan-kandang">
															<thead>
																<tr>
																	<th class="text-center">#</th>
																	<th class="text-right">Panjang (m)</th>
																	<th class="text-right">Lebar (m)</th>
																	<th class="text-right">Jumlah Unit</th>
																	<th></th>
																</tr>
															</thead>
															<tbody>
																<?php if ( $kandang->bangunans->count() > 0 ): ?>
																	<?php foreach ($kandang->bangunans as $bangunan): ?>
																		<tr>
																			<td class=""><input class="form-control text-center" type="text" name="no" value="<?php echo $bangunan['bangunan'] ?>"  placeholder="No. Bangunan Kandang" required></td>
																			<td class=""><input class="form-control text-right" type="text" name="panjang" value="<?php echo angkaDecimal($bangunan['meter_panjang']) ?>"  data-tipe="decimal" placeholder="Panjang Bangunan Kandang" required></td>
																			<td class=""><input class="form-control text-right" type="text" name="lebar" value="<?php echo angkaDecimal($bangunan['meter_lebar']) ?>"  data-tipe="decimal" placeholder="Lebar Bangunan Kandang" required></td>
																			<td class=""><input class="form-control text-right" type="text" name="jml" value="<?php echo $bangunan['jumlah_unit'] ?>" data-tipe="integer" placeholder="Jumlah Bangunan Kandang" required></td>
																			<td class="text-center">
																				<button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
																				<button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
																			</td>
																		</tr>
																	<?php endforeach; ?>
																<?php else: ?>
																	<tr>
																		<td class=""><input class="form-control text-center" type="text" name="no" placeholder="No. Bangunan Kandang" value="" required></td>
																		<td class=""><input class="form-control text-right" type="text" name="panjang" placeholder="Panjang Bangunan Kandang" value="" required data-tipe="decimal"></td>
																		<td class=""><input class="form-control text-right" type="text" name="lebar" placeholder="Lebar Bangunan Kandang" value="" required data-tipe="decimal"></td>
																		<td class=""><input class="form-control text-right" type="text" name="jml" placeholder="Jumlah Bangunan Kandang" value="" data-tipe="integer" required></td>
																		<td class="text-center">
																			<button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
																			<button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
																		</td>
																	</tr>
																<?php endif ?>
															</tbody>
														</table>
													</div>

													<div class="row col-sm-12">
														<table class="table table-bordered tpanel lampiran-kandang">
															<thead>
																<tr>
																	<th colspan="3">Lampiran <span class="cursor-p pull-right tpanel-collapsed" onclick="ptk.collapseLampiran(this)"><i class="glyphicon glyphicon-chevron-down"></i></span>  </th>
																</tr>
															</thead>
															<tbody class="tpanel-body">
																<?php foreach ($list_lampiran_kandang as $lmprn): ?>
																	<tr data-idnama="<?php echo $lmprn['id'] ?>">
																		<td><?php echo $lmprn['sequence'] ?></td>
																		<td class=""><?php echo $lmprn['nama'] ?></td>
																		<td class="col-sm-5 lampiran">
																			<?php 
																				$id_old = null;
																				$filename = null;
																				foreach ($kandang->lampirans as $lkandang) {
																					if ( $lkandang['d_nama_lampiran']['id'] == $lmprn['id'] ){ ?>
																						<span>
																							<u>
																							<?php 
																								$id_old = $lkandang['id'];
																								$filename = $lkandang['path'];
																								echo $lkandang['filename'];
																							?>
																							</u>
																						</span>
																					<?php }
																				}
																			?>
																			<label class="">
																				<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lmprn['required'] ?>" name="" placeholder="lampiran kandang - <?php echo $lmprn['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" data-filename="<?php echo $filename; ?>" data-old="<?php echo $id_old; ?>">
																				<i class="glyphicon glyphicon-paperclip cursor-p"></i>
																			</label>
																		</td>
																	</tr>
																<?php endforeach; ?>
															</tbody>
														</table>
													</div>
												</fieldset>
											</div>
										</div>
									<?php endforeach ?>

									<div class="text-right" style="margin-bottom:10px;">
										<button type="button" class="btn btn-default" onclick="ptk.tambahKandang(this)">Tambah Kandang</button>
									</div>
								</div>
								<!-- end - data perwakilan -->
							<?php endforeach ?>
						</form>
						<div class="row" style="margin-bottom:10px;">
							<div class="col-sm-12">
								<div class="pull-right">
									<button type="button" class="btn btn-default" onclick="ptk.tambahPerwakilanAfterApprove(this)">Tambah Perwakilan</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end - tab kandang -->

					<!-- tab lampiran_mitra -->
					<div id="lampiran_mitra" class="tab-pane fade show active" style="padding-top: 10px;">
						<form class="form form-horizontal">
							<div class="row">
								<div class="col-sm-12">
									<table class="table table-bordered lampiran-mitra">
										<thead>
											<tr>
												<th colspan="3">Mitra</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($list_lampiran_mitra as $lmprn): ?>
												<tr data-idnama="<?php echo $lmprn['id'] ?>">
													<td><?php echo $lmprn['sequence'] ?></td>
													<td class=""><?php echo $lmprn['nama'] ?></td>
													<td class="col-sm-5">
														<?php 
															$id_old = null;
															$filename = null;
															$path = null;
															foreach ( $mitra['lampirans'] as $lmitra ) {
																if ( $lmitra['d_nama_lampiran']['id'] == $lmprn['id'] ){ ?>
																	<span>
																		<u>
																			<?php 
																				$id_old = $lmitra['id'];
																				$filename = $lmitra['path'];
																				echo $lmitra['filename'];
																			?>
																		</u>
																	</span>
																<?php }
															}
														?>
														<label class="">
															<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lmprn['required'] ?>" name="" placeholder="lampiran mitra - <?php echo $lmprn['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" data-filename="<?php echo $filename; ?>" data-old="<?php echo $id_old; ?>">
															<i class="glyphicon glyphicon-paperclip cursor-p"></i>
														</label>
													</td>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</form>
					</div>
					<!-- end - tab lampiran_mitra -->

					<!-- tab lampiran_jaminan_mitra -->
					<div id="lampiran_jaminan_mitra" class="tab-pane fade" style="padding-top: 10px;">
						<form class="form form-horizontal">
							<div class="row">
								<div class="col-sm-12">
									<table class="table table-bordered lampiran-jaminan">
										<thead>
											<tr>
												<th colspan="3">Jaminan</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($list_lampiran_jaminan as $ljaminan): ?>
												<tr data-idnama="<?php echo $ljaminan['id'] ?>">
													<td><?php echo $ljaminan['sequence'] ?></td>
													<td class=""><?php echo $ljaminan['nama'] ?></td>
													<td class="col-sm-5">
														<?php 
															$id_old = null;
															$filename = null;
															foreach ( $mitra['lampirans_jaminan'] as $lmitra ) {
																if ( $lmitra['d_nama_lampiran']['id'] == $ljaminan['id'] ){ ?>
																	<span>
																		<u>
																			<?php 
																				$id_old = $lmitra['id'];
																				$filename = $lmitra['path'];
																				echo $lmitra['filename'];
																			?>
																		</u>
																	</span>
															<?php }
															}
														?>
														<label class="">
															<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $ljaminan['required'] ?>" name="" placeholder="dokumen jaminan <?php echo $ljaminan['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" data-filename="<?php echo $filename; ?>" data-old="<?php echo $id_old; ?>">
															<i class="glyphicon glyphicon-paperclip cursor-p"></i>
														</label>
													</td>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</form>
					</div>
					<!-- end - tab lampiran_jaminan_mitra -->
				</div>
			</div>
		</div>
	</form>
	<hr>
	<div class="col-md-12 no-padding">
		<button type="button" class="btn btn-large btn-primary pull-right" onclick="ptk.simpanPerwakilanAfterApprove()"> <i class="fa fa-edit"></i> Edit</button>
	</div>
</div>