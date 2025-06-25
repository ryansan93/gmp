<div class="col-md-12">
	<form class="form form-horizontal" role="form">
		<div name="data-mitra">
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Jenis Mitra</span>
				<div class="col-sm-2">
					<select class="form-control" name="jenis_mitra" required>
						<?php foreach ($jenis_mitra as $key => $jmitra): ?>
							<option value="<?php echo $key ?>"><?php echo $jmitra ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Perusahaan</span>
				<div class="col-sm-4">
					<select class="form-control" name="perusahaan" required>
						<?php foreach ($perusahaan as $key => $value): ?>
							<option value="<?php echo $value['kode'] ?>"><?php echo strtoupper($value['nama']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">KTP</span>
				<div class="col-sm-3">
					<input type="text" class="form-control" name="ktp" placeholder="nomor ktp" required data-tipe="ktp">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">Nama Mitra</span>
				<div class="col-sm-5">
					<input type="text" class="form-control" name="nama_mitra" placeholder="nama mitra" required>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">NPWP</span>
				<div class="col-sm-3">
					<input type="email" class="form-control" name="npwp" placeholder="npwp">
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<span class="col-sm-2 text-right">No. SKB</span>
				<div class="col-sm-3">
					<input type="text" class="form-control" name="skb" placeholder="No. SKB" maxlength="50">
				</div>
				<span class="col-sm-2 text-right">Tgl Habis Berlaku</span>
				<div class="col-sm-3">
					<div class="input-group date" id="tglHbsBerlaku">
						<input type="text" class="form-control text-center" placeholder="Tanggal" />
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
								<th class="col-sm-7 text-left">Telepon</th>
								<th class="col-sm-5"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<input class="form-control" type="text" name="telepon" value="" placeholder="telepon" data-tipe="phone" required>
								</td>
								<td>
									<button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
									<button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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
								<?php foreach ($list_provinsi as $prov): ?>
									<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-6 no-padding">
							<div class="col-sm-8 pull-right no-padding">
								<select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')">
									<?php foreach ($tipe_lokasi as $key => $lokasi): ?>
										<option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="kabupaten/kota" required>
								<option value="">pilih kota/kabupaten</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select class="form-control" name="kecamatan" placeholder="kecamatan" required>
								<option value="">pilih kecamatan</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input type="text" class="form-control" name="kelurahan" placeholder="kelurahan/desa" data-id="" required>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<div class="col-sm-8">
							<textarea class="form-control" name="alamat" style="height: 73px;" placeholder=" . . . alamat / jalan mitra" required></textarea>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-1 text-right">RT</span>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="rt" placeholder="RT" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-1 text-right">RW</span>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="rw" placeholder="RW" required="1">
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
							<input type="text" class="form-control" name="bank" placeholder="bank" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Cabang Bank</span>
						<div class="col-sm-3">
							<input type="text" class="form-control" name="cabang-bank" placeholder="cabang bank" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">No. Rekening</span>
						<div class="col-sm-3">
							<input type="text" class="form-control" name="no-rekening" placeholder="no. rekening" required="1">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right">Pemilik Rekening</span>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="pemilik-rekening" placeholder="pemilik rekening" required="1">
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
							<textarea name="jaminan" class="form-control" rows="2" placeholder="keterangan jaminan" required></textarea>
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

							<div name="data-perwakilan">
								<div class="col-lg-12 div-bordered align-items-center d-flex">
									<div class="col-sm-5 align-items-center d-flex">
										<span class="col-sm-5 text-right">Perwakilan</span>
										<div class="col-sm-7">
											<select class="form-control" name="perwakilan" onchange="ptk.getListUnitPerwakilan(this)" placeholder="perwakilan" required>
												<option value="">pilih perwakilan</option>
												<?php foreach ($list_perwakilan as $perwakilan): ?>
													<option value="<?php echo $perwakilan['id'] ?>"><?php echo $perwakilan['nama'] ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div class="col-sm-5 align-items-center d-flex">
										<span class="col-sm-1 text-right">NIM</span>
										<div class="col-sm-5">
											<input type="text" class="form-control" name="nim" value="" placeholder="nim" maxlength="8" disabled="">
										</div>
									</div>
									<div class="col-sm-2">
										<button type="button" class="btn btn-danger pull-right" onclick="ptk.hapusPerwakilan(this)"><i class="fa fa-trash"></i></button>
									</div>
								</div>

								<div name="data-kandang">
									<div class="col-sm-12 no-padding" style="padding-bottom: 10px;">
										<fieldset>
											<legend> <button type="button" class="btn btn-xs btn-danger" onclick="ptk.hapusKandang(this)"><i class="fa fa-trash"></i></button> | Kandang</legend>
											<div class="row col-sm-12">
												<div class="col-sm-4 no-padding">
													<div class="col-sm-12">
														<div class="form-group align-items-center d-flex">
															<span class="col-sm-4 text-right">Grup</span>
															<div class="col-sm-3">
																<input type="text" class="form-control text-center" name="grup" data-tipe="integer" value="" maxlength="2" placeholder="grup" required>
															</div>
														</div>
														<div class="form-group align-items-center d-flex">
															<span class="col-sm-4 text-right">No. Kandang</span>
															<div class="col-sm-3">
																<input type="text" class="form-control text-center" name="no-kandang" value="" maxlength="2" placeholder="no. kandang" required>
															</div>
														</div>
														<div class="form-group align-items-center d-flex">
															<span class="col-sm-4 text-right">Kapasitas</span>
															<div class="col-sm-4">
																<input type="text" class="form-control" name="kapasitas" value="" data-tipe="integer" placeholder="kapasitas" required>
															</div>
															<span class="col-sm-2">Ekor</span>
														</div>
														<div class="form-group align-items-center d-flex">
															<span class="col-sm-4 text-right">Tipe Kandang</span>
															<div class="col-sm-8">
																<select class="form-control" name="tipe_kandang" placeholder="tipe kandang" required>
																	<option value="">pilih tipe kandang</option>
																	<?php foreach ($tipe_kandang as $key => $kandang): ?>
																		<option value="<?php echo $key ?>"><?php echo $kandang ?></option>
																	<?php endforeach; ?>
																</select>
															</div>
														</div>
														<div class="form-group align-items-center d-flex">
															<span class="col-sm-4 text-right">Status</span>
															<div class="col-sm-6">
																<select class="form-control" name="status" placeholder="status kandang" required>
																	<option value="">pilih status</option>
																	<?php foreach ($status_kandang as $key => $s_kandang): ?>
																		<option value="<?php echo $key ?>"><?php echo $s_kandang ?></option>
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
																	<select class="form-control" name="unit" placeholder="unit" required>
																	</select>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-4 text-right">Provinsi</span>
																<div class="col-sm-8">
																	<select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="provinsi" required>
																		<option value="">pilih provinsi</option>
																		<?php foreach ($list_provinsi as $prov): ?>
																			<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
																		<?php endforeach; ?>
																	</select>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<div class="col-sm-4 no-padding">
																	<div class="col-sm-11 pull-right no-padding">
																		<select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')">
																			<?php foreach ($tipe_lokasi as $key => $lokasi): ?>
																				<option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
																			<?php endforeach; ?>
																		</select>
																	</div>
																</div>
																<div class="col-sm-8">
																	<select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="kabupaten/kota" required>
																		<option value="">pilih kota/kabupaten</option>
																	</select>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-4 text-right">Kecamatan</span>
																<div class="col-sm-8">
																	<select class="form-control" name="kecamatan" placeholder="kecamatan" required>
																		<option value="">pilih kecamatan</option>
																	</select>
																</div>
															</div>
															<div class="form-group align-items-center d-flex">
																<span class="col-sm-4 text-right">Kelurahan/Desa</span>
																<div class="col-sm-8">
																	<input type="text" class="form-control autocomplete_lokasi" name="kelurahan" placeholder="kelurahan/desa" required>
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="col-sm-4">
													<div class="form-group">
														<div class="col-sm-12">
															<textarea class="form-control" name="alamat" style="height: 73px;" placeholder=" . . . alamat / jalan kandang" required></textarea>
														</div>
													</div>
													<div class="form-group align-items-center d-flex">
														<span class="col-sm-1 text-right">RT</span>
														<div class="col-sm-3">
															<input type="text" class="form-control" name="rt" placeholder="RT">
														</div>
													</div>
													<div class="form-group align-items-center d-flex">
														<span class="col-sm-1 text-right">RW</span>
														<div class="col-sm-3">
															<input type="text" class="form-control" name="rw" placeholder="RW">
														</div>
													</div>
													<div class="form-group align-items-center d-flex">
														<span class="col-sm-1 text-right">OA</span>
														<div class="col-sm-8">
															<input type="text" class="form-control" name="ongkos-angkut" placeholder="ongkos angkut" data-tipe="decimal" required>
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
														<tr>
															<td class=""><input class="form-control text-center" type="text" name="no" placeholder="no bangunan kandang" value="" required></td>
															<td class=""><input class="form-control text-right" type="text" name="panjang" placeholder="panjang bangunan kandang" value="" required data-tipe="decimal"></td>
															<td class=""><input class="form-control text-right" type="text" name="lebar" placeholder="lebar bangunan kandang" value="" required data-tipe="decimal"></td>
															<td class=""><input class="form-control text-right" type="text" name="jml" placeholder="jumlah bangunan kandang" value="" data-tipe="integer" required></td>
															<td class="text-center">
																<button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
																<button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
															</td>
														</tr>
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
														<?php foreach ($list_lampiran_kandang as $lkandang): ?>
															<tr data-idnama="<?php echo $lkandang['id'] ?>">
																<td><?php echo $lkandang['sequence'] ?></td>
																<td class=""><?php echo $lkandang['nama'] ?></td>
																<td class="col-sm-5 lampiran">
																	<label class="">
																		<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lkandang['required'] ?>" name="" placeholder="lampiran kandang - <?php echo $lkandang['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;">
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

								<div class="text-right" style="margin-bottom:10px;">
									<button type="button" class="btn btn-default" onclick="ptk.tambahKandang(this)">Tambah Kandang</button>
								</div>
							</div>
							<!-- end - data perwakilan -->

						</form>
						<div class="row" style="margin-bottom:10px;">
							<div class="col-sm-12">
								<div class="pull-right">
									<button type="button" class="btn btn-default" onclick="ptk.tambahPerwakilan(this)">Tambah Perwakilan</button>
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
											<?php foreach ($list_lampiran_mitra as $lmitra): ?>
												<tr data-idnama="<?php echo $lmitra['id'] ?>">
													<td><?php echo $lmitra['sequence'] ?></td>
													<td class=""><?php echo $lmitra['nama'] ?></td>
													<td class="col-sm-5">
														<label class="">
															<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lmitra['required'] ?>" name="" placeholder="lampiran mitra - <?php echo $lmitra['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;">
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
														<label class="">
															<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $ljaminan['required'] ?>" name="" placeholder="dokumen jaminan <?php echo $ljaminan['nama'] ?>" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;">
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
		<button type="button" class="btn btn-large btn-primary pull-right" onclick="ptk.save()"> <i class="fa fa-save"></i> Simpan</button>
	</div>
</div>