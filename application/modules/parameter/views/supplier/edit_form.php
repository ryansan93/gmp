<div class="col-sm-12 no-padding">
	<form class="form-horizontal" role="form">
		<div name="data-supplier">
			<div id="jenis_supplier">
				<div class="col-sm-4">
					<input type="hidden" data-id="<?php echo $data->id; ?>" data-nomor="<?php echo $data->nomor; ?>" data-status="<?php echo $data->status; ?>" data-version="<?php echo $data->version; ?>" data-mstatus="<?php echo $data->mstatus; ?>" >

					<?php if ( $akses['a_ack'] == 1 ): ?>
						<div class="form-group align-items-center d-flex">
							<span class="col-sm-6 text-right">No. Induk</span>
							<div class="col-sm-6">
								<input class="form-control" type="text" name="nip_supplier" value="<?php echo $data->nomor; ?>" placeholder="MAX:6" maxlength="6" readonly>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Jenis Supplier</span>
						<div class="col-sm-6">
							<select class="form-control" name="jenis_supl" readonly>
								<?php if ( !empty($jenis) ) { ?>
									<?php foreach ($jenis as $key => $value) { ?>
										<?php
											$selected = '';
											if ( $value['kode'] == $data['jenis'] ) {
												$selected = 'selected';
											}
										?>
										<option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo $value['nama']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Nama Supplier</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="nama_supl" placeholder="Perusahaan/Perseorangan" value="<?php echo $data['nama']; ?>" >
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Contact Person</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="contact_supl" placeholder="Contact Person" value="<?php echo $data['cp']; ?>" >
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Plafon</span>
						<div class="col-sm-6">
							<input required="required" class="form-control text-right" type="text" name="plafon" placeholder="Plafon" data-tipe="integer" value="<?php echo angkaRibuan($data['plafon']); ?>">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Jatuh Tempo</span>
						<div class="col-sm-3">
							<input required="required" class="form-control text-right" type="text" name="jatuh_tempo" placeholder="Jatuh Tempo" data-tipe="integer" value="<?php echo angkaRibuan($data['jatuh_tempo']); ?>">
						</div>
						<div class="col-sm-3">Hari</div>
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
									<?php $idx_tlp = 0; ?>
									<?php foreach ($data['telepons'] as $telp) : ?>
										<?php $idx_tlp++; ?>
										<tr>
											<td>
												<input class="form-control" type="text" name="telp_supl" value="<?php echo $telp['nomor']; ?>" placeholder="Telepon" data-tipe="phone" required>
											</td>
											<td>
												<?php if ( count($data['telepons']) == $idx_tlp ): ?>
													<button type="button" class="btn btn-danger" onclick="supl.removeRowTable(this)"><i class="fa fa-minus"></i></button>
													<button type="button" class="btn btn-default" onclick="supl.addRowTable(this)"><i class="fa fa-plus"></i></button>
												<?php endif ?>
											</td>
										</tr>
									<?php endforeach ?>
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
							<input required="required" class="form-control" type="text" name="ktp_supl" placeholder="Nomer KTP" value="<?php echo $data['nik']; ?>">
						</div>
						<div class="col-sm-8 no-padding">
							<!-- <span class="file"><?php echo $l_ktp['filename']; ?></span> -->
							<a href="uploads/<?php echo $l_ktp['filename']; ?>" target="_blank"><?php echo $l_ktp['filename']; ?></a>
							<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_supplier['id'] ?>">
								<input type="file" onchange="showNameFile(this)" class="file_lampiran supplier" name="lampiran_ktp" data-allowtypes="doc|DOC|pdf|PDF|docx|DOCX|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;" placeholder="Lampiran File KTP" data-old="<?php echo $l_ktp['id']; ?>">
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
									<?php
										$selected = null;
										if ( $lokasi['prov_id'] == $prov['id'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $prov['id'] ?>" <?php echo $selected; ?> ><?php echo $prov['nama'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<div class="col-sm-6">
							<div class="col-sm-12 float-right padding-right-0">
								<?php
									$selected_kb = null;
									$selected_kt = null;
									if ( $lokasi['kota_jenis'] == 'KB' ) {
										$selected_kb = 'selected';
									} else {
										$selected_kt = 'selected';
									}
								?>
								<select class="form-control" onchange="supl.getListLokasi(this, '#alamat_supplier', 'kab', '')" name="tipe_lokasi">
									<option value="KB" <?php echo $selected_kb; ?> >Kabupaten</option>
									<option value="KT" <?php echo $selected_kt; ?> >Kota</option>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_supplier', 'kec', '')" name="kabupaten_supl" placeholder="Kab/Kota KTP" data-id="<?php echo $lokasi['kota_id']; ?>">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" name="kecamatan_supl" placeholder="Kecamatan KTP" data-id="<?php echo $lokasi['kec_id']; ?>">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
								<option>Gubeng</option>
								<option>Tenggilis</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_supl" placeholder="Kelurahan KTP" value="<?php echo $data['alamat_kelurahan']; ?>">
						</div>
					</div>
				</div>
				<div class="com-sm-8">
					<div class="col-sm-4">
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-12">
								<textarea required="required" id="text-alamat" class="form-control" name="alamat_supl" style="height: 111px;" placeholder="Alamat/jalan"><?php echo $data['alamat_jalan']; ?></textarea>
							</div>
						</div>
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RT</span>
								<div class="col-sm-11">
									<?php 
										$new_rt = null;
										$rt = $data['alamat_rt']; 
									?>
									<?php 
										if ( strlen($rt) == 1 ) {
											$new_rt = '00'.$rt;
										} elseif ( strlen($rt) == 2 ) {
											$new_rt = '0'.$rt;
										} elseif ( strlen($rt) == 3 ) {
											$new_rt = $rt;
										}
									?>
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_supl" placeholder="RT" value="<?php echo $new_rt; ?>">
								</div>
							</div>

							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RW</span>
								<div class="col-sm-11">
									<?php 
										$new_rw = null;
										$rw = $data['alamat_rw']; 
									?>
									<?php 
										if ( strlen($rw) == 1 ) {
											$new_rw = '00'.$rw;
										} elseif ( strlen($rw) == 2 ) {
											$new_rw = '0'.$rw;
										} elseif ( strlen($rw) == 3 ) {
											$new_rw = $rw;
										}
									?>
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_supl" placeholder="RW" value="<?php echo $new_rw; ?>">
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
							<input required="required" class="form-control" type="text" name="npwp_supl" placeholder="Nomer NPWP" value="<?php echo $data['npwp']; ?>">
						</div>
						<div class="col-sm-8 no-padding">
							<!-- <span class="file"><?php echo $l_npwp['filename']; ?></span> -->
							<a href="uploads/<?php echo $l_npwp['filename']; ?>" target="_blank"><?php echo $l_npwp['filename']; ?></a>
							<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_usaha_supplier['id'] ?>">
								<input type="file" onchange="showNameFile(this)" class="file_lampiran supplier" name="lampiran_npwp" data-allowtypes="doc|DOC|pdf|PDF|docx|DOCX|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;" placeholder="Lampiran NWPW" data-old="<?php echo $l_npwp['id']; ?>">
								<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran NPWP"></i>
							</label>
						</div>
					</div>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">No. SKB</span>
						<div class="col-sm-2" style="margin-left: 11px;">
							<input class="form-control" type="text" name="skb_supl" placeholder="No. SKB" value="<?php echo $data['skb']; ?>" maxlength="50">
						</div>
						<span class="col-sm-2 text-right">Tgl Habis Berlaku</span>
						<div class="col-sm-3">
							<div class="input-group date" id="tglHbsBerlaku">
								<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_habis_skb'] ?>" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
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
									<?php
										$selected = null;
										if ( $lokasi['prov_usaha_id'] == $prov['id'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $prov['id'] ?>" <?php echo $selected; ?> ><?php echo $prov['nama'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-6">
							<div class="col-sm-12 float-right padding-right-0">
								<?php
									$selected_kb = null;
									$selected_kt = null;
									if ( $lokasi['kota_usaha_jenis'] == 'KB' ) {
										$selected_kb = 'selected';
									} else {
										$selected_kt = 'selected';
									}
								?>
								<select class="form-control" onchange="supl.getListLokasi(this, '#alamat_usaha_supplier', 'kab', '_usaha')" name="tipe_lokasi_usaha">
									<option value="KB" <?php echo $selected_kb; ?> >Kabupaten</option>
									<option value="KT" <?php echo $selected_kt; ?> >Kota</option>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="supl.getListLokasi(this, '#alamat_usaha_supplier', 'kec', '_usaha')" name="kabupaten_usaha_supl" placeholder="Kab/Kota Usaha" data-id="<?php echo $lokasi['kota_usaha_id']; ?>">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" name="kecamatan_usaha_supl" placeholder="Kecamatan Usaha" data-id="<?php echo $lokasi['kec_usaha_id']; ?>">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_usaha_supl" placeholder="Kelurahan Usaha" value="<?php echo $data['usaha_kelurahan']; ?>" >
						</div>
					</div>
				</div>
				<div class="col-m-8">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="col-sm-12 align-items-center d-flex">
								<textarea required="required" id="text-alamat-usaha" class="form-control" name="alamat_usaha_supl" placeholder="Alamat Usaha" style="height: 111px;" placeholder="Alamat/jalan"><?php echo $data['usaha_jalan']; ?></textarea>
							</div>
						</div>
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RT</span>
								<div class="col-sm-11">
									<?php 
										$new_rt = null;
										$rt = $data['usaha_rt']; 
									?>
									<?php 
										if ( strlen($rt) == 1 ) {
											$new_rt = '00'.$rt;
										} elseif ( strlen($rt) == 2 ) {
											$new_rt = '0'.$rt;
										} elseif ( strlen($rt) == 3 ) {
											$new_rt = $rt;
										}
									?>
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_usaha_supl" placeholder="RT" value="<?php echo $new_rt; ?>">
								</div>
							</div>

							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RW</span>
								<div class="col-sm-11">
									<?php 
										$new_rw = null;
										$rw = $data['alamat_rw']; 
									?>
									<?php 
										if ( strlen($rw) == 1 ) {
											$new_rw = '00'.$rw;
										} elseif ( strlen($rw) == 2 ) {
											$new_rw = '0'.$rw;
										} elseif ( strlen($rw) == 3 ) {
											$new_rw = $rw;
										}
									?>
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_usaha_supl" placeholder="RW" value="<?php echo $new_rw; ?>">
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
							<?php foreach ($data['banks'] as $bank) : ?>
								<?php 
									$id_old = $bank['lampiran']['id'];
									$path = $bank['lampiran']['path'];
									$filename = $bank['lampiran']['filename'];
								?>
								<tr class="detail_rekening v-center">
									<td><input required="required" class="form-control" type="text" name="rekening_supl" data-id="<?php echo $bank['id']; ?>" placeholder="No Rekeneing" value="<?php echo $bank['rekening_nomor']; ?>" ></td>
									<td><input required="required" class="form-control" type="text" name="pemilik_rekening" placeholder="Nama Pemilik" value="<?php echo $bank['rekening_pemilik']; ?>" ></td>
									<td><input required="required" class="form-control" type="text" name="bank_rekening" placeholder="Nama Bank" value="<?php echo $bank['bank']; ?>" ></td>
									<td><input required="required" class="form-control" type="text" name="cabang_rekening" placeholder="Cabang Bank" value="<?php echo $bank['rekening_cabang_bank']; ?>" ></td>
									<td>
										<!-- <span class="file"><?php echo $filename; ?></span> -->
										<a href="uploads/<?php echo $filename; ?>" target="_blank"><?php echo $filename; ?></a>
										<label class="text-right" data-idnama="<?php echo $list_lampiran_rekening_supplier['id'] ?>">
											<input type="file" onchange="showNameFile(this)" class="file_lampiran bank_supplier" name="lampiran_dds[]" data-allowtypes="doc|DOC|pdf|PDF|docx|DOCX|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;" placeholder="Lampiran Rekening" data-old="<?php echo $id_old; ?>">
											<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran Rekening"></i>
										</label>
									</td>
									<td>
										<button type="button" class="btn btn-danger" onclick="supl.removeRowTable(this)"><i class="fa fa-minus"></i></button>
										<button type="button" class="btn btn-default" onclick="supl.addRowTable(this)"><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
			<div id="lampiran_supplier">
				<div class="col-sm-12"><b>Lampiran DDS</b></div>
				<!-- <span class="file"><?php echo $l_dds['filename']; ?></span> -->
				<a href="uploads/<?php echo $l_dds['filename']; ?>" target="_blank"><?php echo $l_dds['filename']; ?></a>
				<label class="col-sm-2 text-right" data-idnama="<?php echo $list_lampiran_dds_supplier['id'] ?>">
					<input type="file" onchange="showNameFile(this)" class="file_lampiran supplier" name="lampiran_dds" data-allowtypes="doc|DOC|pdf|PDF|docx|DOCX|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;" placeholder="Lampiran DDS" data-old="<?php echo $l_dds['id']; ?>">
					<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDS"></i>
				</label>
			</div>
		</div>
	</form>
</div>
<div class="col-sm-12 no-padding text-right">
	<hr>
	<?php if ( $akses['a_submit'] == 1): ?>
		<button type="button" class="btn btn-large btn-primary pull-right" id="submit_supplier" onclick="supl.edit()"><i class="fa fa-edit"></i> Edit</button>
	<?php endif; ?>
</div>