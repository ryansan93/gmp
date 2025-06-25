<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Supplier</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Daftar Supplier</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Master Supplier</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_supl" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="supl.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php // } else if ( $akses['a_ack'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="doc.ack(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> ACK
								</button> -->
							<?php // } else if ( $akses['a_approve'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="doc.approve(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> APPROVE
								</button> -->
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered tbl_supl">
							<thead>
								<tr>
									<th>NIP</th>
									<th>Jenis</th>
									<th>Nama Supplier</th>
									<th>NIK</th>
									<th>Alamat</th>
									<th>Status</th>
									<th>Saldo Awal (Rp)</th>
									<th>Keterangan</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="9"></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<div class="col-sm-12 no-padding">
								<form class="form-horizontal" role="form">
									<div name="data-supplier">
										<div id="jenis_supplier">
											<div class="col-sm-4">
												<?php if ( $akses['a_ack'] == 1 ): ?>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">NIP</span>
													<div class="col-sm-6">
														<input class="form-control" type="text" name="nip_supplier" >
													</div>
												</div>
												<?php endif; ?>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Jenis Supplier</span>
													<div class="col-sm-6">
														<select class="form-control" name="jenis_supl">
															<option value="internal">Internal</option>
															<option value="eksternal">Eksternal</option>
															<option value="ekspedisi">Ekspedisi</option>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Nama Supplier</span>
													<div class="col-sm-6">
														<input required="required" class="form-control" type="text" name="nama_supl" placeholder="Perusahaan/Perseorangan">
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Contact Person</span>
													<div class="col-sm-6">
														<input required="required" class="form-control" type="text" name="contact_supl" placeholder="Contact Person">
													</div>
												</div>
											</div>
											<div class="col-sm-4 hide">
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Platform</span>
													<div class="col-sm-6">
														<input required="required" class="form-control text-right" type="text" name="platform" placeholder="Platform" data-tipe="integer" value="0">
													</div>
												</div>
											</div>
											<div class="col-sm-12">
												<div class="form-group align-items-center d-flex">
													<label class="col-sm-2 text-right"></label>
													<div class="col-sm-3">
														<table class="table telepon">
															<thead>
																<tr>
																	<th class="col-sm-8 text-left">Telepon</th>
																	<th class="col-sm-4"></th>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>
																		<input class="form-control" type="text" name="telp_supl" value="" placeholder="Telepon" data-tipe="phone" required>
																	</td>
																	<td>
																		<button type="button" class="btn btn-danger" onclick="supl.removeRowTable(this)"><i class="fa fa-minus"></i></button>
																		<button type="button" class="btn btn-default" onclick="supl.addRowTable(this)"><i class="fa fa-plus"></i></button>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
										<div id="alamat_supplier">
											<div class="col-sm-12">Alamat Sesuai KTP</div>
											<div class="col-sm-12 no-padding">
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-2 text-right no-padding">NIK</span>
													<div class="col-sm-2" style="margin-left: 11px;">
														<input required="required" class="form-control" type="text" name="ktp_supl" placeholder="Nomer KTP">
													</div>
													<div class="col-sm-8 no-padding">
														<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_supplier['id'] ?>">
															<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_ktp" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran File KTP">
															<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDS"></i>
														</label>
													</div>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Provinsi</span>
													<div class="col-sm-6">
														<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_supplier', 'kab', '')" name="propinsi_supl" placeholder="Propinsi KTP">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Propinsi</option>
															<?php foreach ($list_provinsi as $prov): ?>
																<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
															<?php endforeach; ?>
														</select>
													</div>
													<!-- <div class="col-sm-4">
														<textarea required="required" id="text-alamat" class="form-control" name="alamat_supl" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
													</div> -->
												</div>
												<div class="form-group align-items-center d-flex">
													<div class="col-sm-6">
														<div class="col-sm-12 float-right padding-right-0">
															<select class="form-control" onchange="supl.getListLokasi(this, '#alamat_supplier', 'kab', '')" name="tipe_lokasi">
																<option value="KB">Kabupaten</option>
																<option value="KT">Kota</option>
															</select>
														</div>
													</div>
													<div class="col-sm-6">
														<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_supplier', 'kec', '')" name="kabupaten_supl" placeholder="Kab/Kota KTP">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Kecamatan</span>
													<div class="col-sm-6">
														<select required="required" class="form-control" name="kecamatan_supl" placeholder="Kecamatan KTP">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
															<option>Gubeng</option>
															<option>Tenggilis</option>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Kelurahan/Desa</span>
													<div class="col-sm-6">
														<input required="required" class="form-control" type="text" name="kelurahan_supl" placeholder="Kelurahan KTP">
													</div>
												</div>
											</div>
											<div class="com-sm-8">
												<div class="col-sm-4">
													<div class="form-group align-items-center d-flex">
														<div class="col-sm-12">
															<textarea required="required" id="text-alamat" class="form-control" name="alamat_supl" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
														</div>
													</div>
													<div class="form-group align-items-center d-flex">
														<div class="col-sm-3 no-padding align-items-center d-flex">
															<span class="col-sm-1 text-right">RT</span>
															<div class="col-sm-11">
																<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_supl" placeholder="RT">
															</div>
														</div>

														<div class="col-sm-3 no-padding align-items-center d-flex">
															<span class="col-sm-1 text-right">RW</span>
															<div class="col-sm-11">
																<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_supl" placeholder="RW">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div id="alamat_usaha_supplier">
											<div class="col-sm-12">Alamat Tempat Usaha</div>
											<div class="col-sm-12 no-padding">
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-2 text-right no-padding">NPWP</span>
													<div class="col-sm-2" style="margin-left: 11px;">
														<input required="required" class="form-control" type="text" name="npwp_supl" placeholder="Nomer NPWP">
													</div>
													<div class="col-sm-8 no-padding">
														<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_usaha_supplier['id'] ?>">
															<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_npwp" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran NWPW">
															<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran NPWP"></i>
														</label>
													</div>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Provinsi</span>
													<div class="col-sm-6">
														<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_usaha_supplier', 'kab', '_usaha')" name="propinsi_usaha_supl" placeholder="Propinsi Usaha">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Propinsi</option>
															<?php foreach ($list_provinsi as $prov): ?>
																<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<div class="col-sm-6">
														<div class="col-sm-12 float-right padding-right-0">
															<select class="form-control" onchange="supl.getListLokasi(this, '#alamat_usaha_supplier', 'kab', '_usaha')" name="tipe_lokasi_usaha">
																<option value="KB">Kabupaten</option>
																<option value="KT">Kota</option>
															</select>
														</div>
													</div>
													<div class="col-sm-6">
														<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_usaha_supplier', 'kec', '_usaha')" name="kabupaten_usaha_supl" placeholder="Kab/Kota Usaha">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Kecamatan</span>
													<div class="col-sm-6">
														<select required="required" class="form-control" name="kecamatan_usaha_supl" placeholder="Kecamatan Usaha">
															<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
														</select>
													</div>
												</div>
												<div class="form-group align-items-center d-flex">
													<span class="col-sm-6 text-right">Kelurahan/Desa</span>
													<div class="col-sm-6">
														<input required="required" class="form-control" type="text" name="kelurahan_usaha_supl" placeholder="Kelurahan Usaha">
													</div>
												</div>
											</div>
											<div class="col-m-8">
												<div class="col-sm-4">
													<div class="form-group">
														<div class="col-sm-12 align-items-center d-flex">
															<textarea required="required" id="text-alamat-usaha" class="form-control" name="alamat_usaha_supl" placeholder="Alamat Usaha" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
														</div>
													</div>
													<div class="form-group">
														<div class="col-sm-3 no-padding align-items-center d-flex">
															<span class="col-sm-1 text-right">RT</span>
															<div class="col-sm-11">
																<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_usaha_supl" placeholder="RT">
															</div>
														</div>

														<div class="col-sm-3 no-padding align-items-center d-flex">
															<span class="col-sm-1 text-right">RW</span>
															<div class="col-sm-11">
																<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_usaha_supl" placeholder="RW">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-12 no-padding">
										<div id="rekening_supplier">
											<div class="col-sm-12" style="margin-bottom: 10px;"><b>Rekening</b></div>
											<div class="col-sm-12">
												<table class="table table-bordered">
													<thead>
														<tr>
															<th class="col-sm-2">No Rekening</th>
															<th class="col-sm-2">Nama Pemilik Rekening</th>
															<th class="col-sm-2">Bank</th>
															<th class="col-sm-2">Cabang Bank</th>
															<th class="col-sm-2">Lampiran</th>
															<th class="col-sm-1">Action</th>
														</tr>
													</thead>
													<tbody>
														<tr class="detail_rekening v-center">
															<td><input required="required" class="form-control" type="text" name="rekening_supl" placeholder="No Rekeneing"></td>
															<td><input required="required" class="form-control" type="text" name="pemilik_rekening" placeholder="Nama Pemilik"></td>
															<td><input required="required" class="form-control" type="text" name="bank_rekening" placeholder="Nama Bank"></td>
															<td><input required="required" class="form-control" type="text" name="cabang_rekening" placeholder="Cabang Bank"></td>
															<td>
																<label class="text-right" data-idnama="<?php echo $list_lampiran_rekening_supplier['id'] ?>">
																	<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_dds[]" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran Rekening">
																	<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran Rekening"></i>
																</label>
															</td>
															<td>
																<button type="button" class="btn btn-danger" onclick="supl.removeRowTable(this)"><i class="fa fa-minus"></i></button>
																<button type="button" class="btn btn-default" onclick="supl.addRowTable(this)"><i class="fa fa-plus"></i></button>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
										<div id="lampiran_supplier">
											<div class="col-sm-12"><b>Lampiran DDS</b></div>
											<label class="col-sm-2 text-right" data-idnama="<?php echo $list_lampiran_dds_supplier['id'] ?>">
												<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_dds" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran DDS">
												<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDS"></i>
											</label>
										</div>
									</div>
								</form>
							</div>
							<div class="col-sm-12 no-padding text-right">
								<hr>
								<?php if ( $akses['a_submit'] == 1): ?>
									<button type="button" class="btn btn-large btn-primary pull-right" id="submit_supplier" onclick="supl.save()">Simpan</button>
								<?php endif; ?>
							</div>
						<?php else: ?>
							<h3>Master Supplier.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>