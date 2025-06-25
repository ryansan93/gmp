<div class="col-sm-12 no-padding">
	<form class="form-horizontal" role="form">
		<div name="data-ekspedisi">
			<div id="jenis_ekspedisi">
				<div class="col-sm-4">
					<?php if ( $akses['a_ack'] == 1 ): ?>
						<div class="form-group align-items-center d-flex">
							<span class="col-sm-6 text-right">NIP</span>
							<div class="col-sm-6">
								<input class="form-control" type="text" name="nip_ekspedisi" >
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Jenis Ekspedisi</span>
						<div class="col-sm-6">
							<select class="form-control" name="jenis_ekspedisi">
								<option value="internal">Internal</option>
								<option value="eksternal">Eksternal</option>
								<!-- <option value="ekspedisi">Ekspedisi</option> -->
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Nama Ekspedisi</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="nama_ekspedisi" placeholder="Perusahaan/Perseorangan">
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Contact Person</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="contact_ekspedisi" placeholder="Contact Person">
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
											<input class="form-control" type="text" name="telp_ekspedisi" value="" placeholder="Telepon" data-tipe="phone" required>
										</td>
										<td>
											<button type="button" class="btn btn-danger" onclick="ekspedisi.removeRowTable(this)"><i class="fa fa-minus"></i></button>
											<button type="button" class="btn btn-default" onclick="ekspedisi.addRowTable(this)"><i class="fa fa-plus"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div id="alamat_ekspedisi">
				<div class="col-sm-12">Alamat Sesuai KTP</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">NIK</span>
						<div class="col-sm-2" style="margin-left: 11px;">
							<input required="required" class="form-control" type="text" name="ktp_ekspedisi" placeholder="Nomer KTP">
						</div>
						<div class="col-sm-8 no-padding">
							<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_ekspedisi['id'] ?>">
								<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran ekspedisi" data-required="1" name="lampiran_ktp" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran File KTP">
								<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDS"></i>
							</label>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Provinsi</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_ekspedisi', 'kab', '')" name="propinsi_ekspedisi" placeholder="Propinsi KTP">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Propinsi</option>
								<?php foreach ($list_provinsi as $prov): ?>
									<option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<!-- <div class="col-sm-4">
							<textarea required="required" id="text-alamat" class="form-control" name="alamat_ekspedisi" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
						</div> -->
					</div>
					<div class="form-group align-items-center d-flex">
						<div class="col-sm-6">
							<div class="col-sm-12 float-right padding-right-0">
								<select class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_ekspedisi', 'kab', '')" name="tipe_lokasi">
									<option value="KB">Kabupaten</option>
									<option value="KT">Kota</option>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_ekspedisi', 'kec', '')" name="kabupaten_ekspedisi" placeholder="Kab/Kota KTP">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" name="kecamatan_ekspedisi" placeholder="Kecamatan KTP">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
								<option>Gubeng</option>
								<option>Tenggilis</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_ekspedisi" placeholder="Kelurahan KTP">
						</div>
					</div>
				</div>
				<div class="com-sm-8">
					<div class="col-sm-4">
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-12">
								<textarea required="required" id="text-alamat" class="form-control" name="alamat_ekspedisi" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
							</div>
						</div>
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RT</span>
								<div class="col-sm-11">
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_ekspedisi" placeholder="RT">
								</div>
							</div>

							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RW</span>
								<div class="col-sm-11">
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_ekspedisi" placeholder="RW">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="alamat_usaha_ekspedisi">
				<div class="col-sm-12">Alamat Tempat Usaha</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">NPWP</span>
						<div class="col-sm-2" style="margin-left: 11px;">
							<input required="required" class="form-control" type="text" name="npwp_ekspedisi" placeholder="Nomer NPWP">
						</div>
						<div class="col-sm-8 no-padding">
							<label class="col-sm-1" data-idnama="<?php echo $list_lampiran_usaha_ekspedisi['id'] ?>">
								<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran ekspedisi" data-required="1" name="lampiran_npwp" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran NWPW">
								<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran NPWP"></i>
							</label>
						</div>
					</div>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">No. SKB</span>
						<div class="col-sm-2" style="margin-left: 11px;">
							<input class="form-control" type="text" name="skb_ekspedisi" placeholder="No. SKB">
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
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Provinsi</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_usaha_ekspedisi', 'kab', '_usaha')" name="propinsi_usaha_ekspedisi" placeholder="Propinsi Usaha">
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
								<select class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_usaha_ekspedisi', 'kab', '_usaha')" name="tipe_lokasi_usaha">
									<option value="KB">Kabupaten</option>
									<option value="KT">Kota</option>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<select required="required" class="form-control" onchange="ekspedisi.getListLokasi(this, '#alamat_usaha_ekspedisi', 'kec', '_usaha')" name="kabupaten_usaha_ekspedisi" placeholder="Kab/Kota Usaha">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kab/Kota</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<select required="required" class="form-control" name="kecamatan_usaha_ekspedisi" placeholder="Kecamatan Usaha">
								<option value="" disabled="" selected="selected" hidden="hidden">Pilih Kecamatan</option>
							</select>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan/Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_usaha_ekspedisi" placeholder="Kelurahan Usaha">
						</div>
					</div>
				</div>
				<div class="col-m-8">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="col-sm-12 align-items-center d-flex">
								<textarea required="required" id="text-alamat-usaha" class="form-control" name="alamat_usaha_ekspedisi" placeholder="Alamat Usaha" style="height: 111px;" placeholder="Alamat/jalan"></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RT</span>
								<div class="col-sm-11">
									<input required="required" class="form-control" data-tipe="rt" type="text" name="rt_usaha_ekspedisi" placeholder="RT">
								</div>
							</div>

							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RW</span>
								<div class="col-sm-11">
									<input required="required" class="form-control" data-tipe="rw" type="text" name="rw_usaha_ekspedisi" placeholder="RW">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 no-padding">
				<div class="col-sm-4 align-items-center d-flex">
					<span class="col-sm-6 text-right">Potongan PPH 23</span>
					<div class="col-sm-6" style="padding-right: 0px;">
						<select class="form-control potongan_pph" required="required">
							<option value="">-- Pilih Potongan --</option>
							<?php if ( !empty($ekspedisi_pph23) ): ?>
								<?php foreach ($ekspedisi_pph23 as $key => $value): ?>
									<option value="<?php echo $value['id']; ?>"><?php echo $value['nama'].' ( '.angkaDecimal($value['persen']).'% )'; ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 no-padding">
			<div id="rekening_ekspedisi">
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
								<td><input required="required" class="form-control" type="text" name="rekening_ekspedisi" placeholder="No Rekeneing"></td>
								<td><input required="required" class="form-control" type="text" name="pemilik_rekening" placeholder="Nama Pemilik"></td>
								<td><input required="required" class="form-control" type="text" name="bank_rekening" placeholder="Nama Bank"></td>
								<td><input required="required" class="form-control" type="text" name="cabang_rekening" placeholder="Cabang Bank"></td>
								<td>
									<label class="text-right" data-idnama="<?php echo $list_lampiran_rekening_ekspedisi['id'] ?>">
										<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran bank_ekspedisi" data-required="1" name="lampiran_dds[]" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran Rekening">
										<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran Rekening"></i>
									</label>
								</td>
								<td>
									<button type="button" class="btn btn-danger" onclick="ekspedisi.removeRowTable(this)"><i class="fa fa-minus"></i></button>
									<button type="button" class="btn btn-default" onclick="ekspedisi.addRowTable(this)"><i class="fa fa-plus"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div id="lampiran_ekspedisi">
				<div class="col-sm-12"><b>Lampiran DDP</b></div>
				<label class="col-sm-2 text-right" data-idnama="<?php echo $list_lampiran_dds_ekspedisi['id'] ?>">
					<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran ekspedisi" data-required="1" name="lampiran_dds" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran DDS">
					<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran DDS"></i>
				</label>
			</div>
		</div>
	</form>
</div>
<div class="col-sm-12 no-padding text-right">
	<hr>
	<?php if ( $akses['a_submit'] == 1): ?>
		<button type="button" class="btn btn-large btn-primary pull-right" id="submit_ekspedisi" onclick="ekspedisi.save()"><i class="fa fa-save"></i> Simpan</button>
	<?php endif; ?>
</div>