<div class="panel-body no-padding">
	<div class="padding-left-15">
		<?php if ($akses['submit']): ?>
			<button id="submit_pelanggan" class="btn btn-primary" onclick="PLG.save()">Submit</button>
		<?php endif; ?>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-12">
				<form class="form-horizontal" role="form">
					<div name="data-pelanggan">
						<div id="jenis_pelanggan">
							<?php if ($akses['ack']): ?>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">NIP</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="nip_pelanggan" >
								</div>
							</div>
							<?php endif; ?>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Jenis Pelanggan</label>
								<div class="col-sm-3">
									<select class="form-control" name="jenis_plg">
										<option value="internal">Internal</option>
										<option value="eksternal">Eksternal</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Nama Pelanggan</label>
								<div class="col-sm-5">
									<input required="required" class="form-control" type="text" name="nama_plg" placeholder="Perusahaan/Perseorangan">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Contact Person</label>
								<div class="col-sm-5">
									<input required="required" class="form-control" type="text" name="contact_plg" placeholder="Contact Person">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">No. Telp/HP</label>
								<div class="col-sm-4">
									<table class="table table-borderless">
										<tbody>
											<tr>
												<td>
													<input required="required" class="form-control" type="text" name="telp_plg" data-tipe="phone" placeholder="Telepon">
												</td>
												<td>
													<button type="button" class="btn btn-danger" onclick="PLG.removeRowTable(this)"><i class="fa fa-minus"></i></button>
													<button type="button" class="btn btn-default" onclick="PLG.addRowTable(this)"><i class="fa fa-plus"></i></button>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div id="alamat_pelanggan">
							<div class="col-sm-12"><h4>Alamat Sesuai KTP</h4></div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">NIK</label>
								<div class="col-sm-4">
									<input required="required" class="form-control" type="text" name="ktp_plg" placeholder="Nomer KTP">
								</div>
								<div class="col-sm-3">
									<label class="float-right margin-top-5" data-idnama="<?php echo $list_lampiran_pelanggan['id'] ?>">
										<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_ktp" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran File KTP">
										<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran KTP"></i>
									</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Propinsi</label>
								<div class="col-sm-3">
									<select required="required" class="form-control" onchange="PLG.getListLokasi(this, '#alamat_pelanggan', 'kab', '')" name="propinsi_plg" placeholder="Propinsi KTP">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Propinsi</option>
										<?php foreach ($list_provinsi as $prov): ?>
											<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-4">
									<textarea required="required" id="text-alamat" class="form-control" name="alamat_plg" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-4">
									<div class="col-sm-4 float-right padding-right-0">
										<select class="form-control" onchange="PLG.getListLokasi(this, '#alamat_pelanggan', 'kab', '')" name="tipe_lokasi">
											<option value="KB">Kabupaten</option>
											<option value="KT">Kota</option>
										</select>
									</div>
								</div>
								<div class="col-sm-3">
									<select required="required" class="form-control" onchange="PLG.getListLokasi(this, '#alamat_pelanggan', 'kec', '')" name="kabupaten_plg" placeholder="Kab/Kota KTP">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kecamatan</label>
								<div class="col-sm-3">
									<select required="required" class="form-control" name="kecamatan_plg" placeholder="Kecamatan KTP">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
										<option>Gubeng</option>
										<option>Tenggilis</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kelurahan/Desa</label>
								<div class="col-sm-3">
									<input required="required" class="form-control" type="text" name="kelurahan_plg" placeholder="Kelurahan KTP">
								</div>

								<label class="col-sm-1 text-right margin-top-5">RT</label>
								<div class="col-sm-1">
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_plg" placeholder="RT KTP">
								</div>

								<label class="col-sm-1 text-right margin-top-5">RW</label>
								<div class="col-sm-1">
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_plg" placeholder="RW KTP">
								</div>
							</div>
						</div>
						<div id="alamat_usaha_pelanggan">
							<div class="col-sm-12"><h4>Alamat Tempat Usaha</h4></div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">NPWP</label>
								<div class="col-sm-4">
									<input required="required" class="form-control" type="text" name="npwp_plg" placeholder="Nomer NPWP">
								</div>
								<div class="col-sm-3">
									<label class="float-right margin-top-5" data-idnama="<?php echo $list_lampiran_usaha_pelanggan['id'] ?>">
										<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_npwp" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran NWPW">
										<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran NPWP"></i>
									</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Propinsi</label>
								<div class="col-sm-3">
									<select required="required" class="form-control" onchange="PLG.getListLokasi(this, '#alamat_usaha_pelanggan', 'kab', '_usaha')" name="propinsi_usaha_plg" placeholder="Propinsi Usaha">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Propinsi</option>
										<?php foreach ($list_provinsi as $prov): ?>
											<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-4">
									<textarea required="required" id="text-alamat-usaha" class="form-control" name="alamat_usaha_plg" placeholder="Alamat Usaha" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-4">
									<div class="col-sm-4 float-right padding-right-0">
										<select class="form-control" onchange="PLG.getListLokasi(this, '#alamat_usaha_pelanggan', 'kab', '_usaha')" name="tipe_lokasi_usaha">
											<option value="KB">Kabupaten</option>
											<option value="KT">Kota</option>
										</select>
									</div>
								</div>
								<div class="col-sm-3">
									<select required="required" class="form-control" onchange="PLG.getListLokasi(this, '#alamat_usaha_pelanggan', 'kec', '_usaha')" name="kabupaten_usaha_plg" placeholder="Kab/Kota Usaha">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kecamatan</label>
								<div class="col-sm-3">
									<select required="required" class="form-control" name="kecamatan_usaha_plg" placeholder="Kecamatan Usaha">
										<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kelurahan/Desa</label>
								<div class="col-sm-3">
									<input required="required" class="form-control" type="text" name="kelurahan_usaha_plg" placeholder="Kelurahan Usaha">
								</div>

								<label class="col-sm-1 text-right margin-top-5">RT</label>
								<div class="col-sm-1">
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_usaha_plg" placeholder="RT Usaha">
								</div>

								<label class="col-sm-1 text-right margin-top-5">RW</label>
								<div class="col-sm-1">
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_usaha_plg" placeholder="RW Usaha">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
					<div id="rekening_pelanggan" class="row">
							<div class="col-sm-12"><h4>Rekening</h4></div>
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
										<tr class="detail_rekening">
											<td><input required="required" class="form-control" type="text" name="rekening_plg" placeholder="No Rekeneing"></td>
											<td><input required="required" class="form-control" type="text" name="pemilik_rekening" placeholder="Nama Pemilik"></td>
											<td><input required="required" class="form-control" type="text" name="bank_rekening" placeholder="Nama Bank"></td>
											<td><input required="required" class="form-control" type="text" name="cabang_rekening" placeholder="Cabang Bank"></td>
											<td>
												<label class="margin-top-5 text-right" data-idnama="<?php echo $list_lampiran_rekening_pelanggan['id'] ?>"">
													<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_ddp[]" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran Rekening">
													<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran Rekening"></i>
												</label>
											</td>
											<td>
												<button type="button" class="btn btn-danger" onclick="PLG.removeRowTable(this)"><i class="fa fa-minus"></i></button>
												<button type="button" class="btn btn-default" onclick="PLG.addRowTable(this)"><i class="fa fa-plus"></i></button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div id="lampiran_pelanggan" class="row">
							<div class="col-sm-12"><h4>Lampiran DDP</h4></div>
							<label class="col-sm-2 text-right" data-idnama="<?php echo $list_lampiran_ddp_pelanggan['id'] ?>">
								<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="lampiran_ddp" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran DDP">
								<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDP"></i>
							</label>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>