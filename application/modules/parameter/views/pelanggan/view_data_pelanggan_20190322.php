<div class="panel-body no-padding">
	<div class="padding-left-15">
		<?php if ($akses['approve'] && $akses['reject']): ?>
			<button class="btn btn-primary" onclick="PLG.approve()">Approve</button>
			<button class="btn btn-danger" onclick="PLG.reject()">Reject</button>
		<?php endif; ?>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-12">
				<form class="form-horizontal" role="form">
					<div name="data-pelanggan">
						<div id="jenis_pelanggan">
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">NIP</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="nip_pelanggan" value="<?php echo $data->nomor; ?>" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Jenis Pelanggan</label>
								<div class="col-sm-3">
									<input type="text" class="form-control" name="jenis_plg" value="<?php echo $data['jenis'] ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Nama Pelanggan</label>
								<div class="col-sm-5">
									<input class="form-control" type="text" name="nama_plg" value="<?php echo $data['nama']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Contact Person</label>
								<div class="col-sm-5">
									<input class="form-control" type="text" name="contact_plg" value="<?php echo $data['cp']; ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">No. Telp/HP</label>
								<div class="col-sm-4">
									<table class="table table-borderless">
										<tbody>
											<?php foreach ($data['telepons'] as $telp) : ?>
											<tr>
												<td>
													<input class="form-control" type="text" name="telp_plg" value="<?php echo $telp['nomor']; ?>">
												</td>
											</tr>
											<?php endforeach ?>
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
									<input class="form-control" type="text" name="ktp_plg" value="<?php echo $data['nik']; ?>" >
								</div>
								<div class="col-sm-3 padding-top-5">
									<a target="_blank" href="<?php echo 'uploads/'.$l_ktp['path']; ?>" ><?php echo $l_ktp['filename']; ?></a>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Propinsi</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="propinsi_plg" value="<?php echo $lokasi['prov']; ?>" >
								</div>
								<div class="col-sm-4">
									<textarea id="text-alamat" class="form-control" name="alamat_plg" style="height: 111px;" ><?php echo $data['alamat_jalan']; ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kabupaten/Kota</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kabupaten_plg" value="<?php echo $lokasi['kota']; ?>" >
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kecamatan</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kecamatan_plg" value="<?php echo $lokasi['kec']; ?>" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kelurahan/Desa</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kelurahan_plg" value="<?php echo $data['alamat_kelurahan']; ?>" >
								</div>

								<label class="col-sm-1 text-right margin-top-5">RT</label>
								<div class="col-sm-1">
									<input class="form-control" type="text" name="rt_plg" value="<?php echo $data['alamat_rt']; ?>" >
								</div>

								<label class="col-sm-1 text-right margin-top-5">RW</label>
								<div class="col-sm-1">
									<input class="form-control" type="text" name="rw_plg" value="<?php echo $data['alamat_rw']; ?>" >
								</div>
							</div>
						</div>
						<div id="alamat_usaha_pelanggan">
							<div class="col-sm-12"><h4>Alamat Tempat Usaha</h4></div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">NPWP</label>
								<div class="col-sm-4">
									<input class="form-control" type="text" name="npwp_plg" value="<?php echo $data['npwp']; ?>" >
								</div>
								<div class="col-sm-3 padding-top-5">
									<a target="_blank" href="<?php echo 'uploads/'.$l_npwp['path']; ?>" ><?php echo $l_npwp['filename']; ?></a>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Propinsi</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="propinsi_usaha_plg" value="<?php echo $lokasi['prov_usaha']; ?>" >
								</div>
								<div class="col-sm-4">
									<textarea id="text-alamat-usaha" class="form-control" name="alamat_usaha_plg" placeholder="Alamat Usaha" style="height: 111px;" ><?php echo $data['usaha_jalan']; ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right">Kabupaten/Kota</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kabupaten_usaha_plg" value="<?php echo $lokasi['kota_usaha']; ?>" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kecamatan</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kecamatan_usaha_plg" value="<?php echo $lokasi['kec_usaha']; ?>" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 text-right margin-top-5">Kelurahan/Desa</label>
								<div class="col-sm-3">
									<input class="form-control" type="text" name="kelurahan_usaha_plg" value="<?php echo $data['usaha_kelurahan']; ?>" >
								</div>

								<label class="col-sm-1 text-right margin-top-5">RT</label>
								<div class="col-sm-1">
									<input class="form-control" type="text" name="rt_usaha_plg" value="<?php echo $data['usaha_rt']; ?>" >
								</div>

								<label class="col-sm-1 text-right margin-top-5">RW</label>
								<div class="col-sm-1">
									<input class="form-control" type="text" name="rw_usaha_plg" value="<?php echo $data['usaha_rw']; ?>" >
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div id="info_extra_pelanggan" class="row">
							<div class="col-sm-12"><h4>Rekening</h4></div>
							<div class="col-sm-12">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th class="col-sm-2">No Rekening</th>
											<th class="col-sm-2">Nama Pemilik Rekening</th>
											<th class="col-sm-2">Bank</th>
											<th class="col-sm-2">Cabang Bank</th>
											<th class="col-sm-3">Lampiran</th>
										</tr>
									</thead>
									<tbody>
										<?php $counter = 0;
										foreach ($data['banks'] as $bank) : ?>
											<tr>
												<td><?php echo $bank['rekening_nomor']; ?></td>
												<td><?php echo $bank['rekening_pemilik']; ?></td>
												<td><?php echo $bank['bank']; ?></td>
												<td><?php echo $bank['rekening_cabang_bank']; ?></td>
												<td>
													<a target="_blank" href="<?php echo 'uploads/'.$l_rekening[$counter]['path'];?> "><?php echo $l_rekening[$counter]['filename'];?></a>
												</td>
											</tr>
										<?php endforeach ?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="lampiran_pelanggan" class="row">
							<div class="col-sm-12"><h4>Lampiran DDP</h4></div>
							<div class="col-sm-4 text-right">
								<a target="_blank" href="<?php echo 'uploads/'.$l_ddp['path']; ?>" ><?php echo $l_ddp['filename']; ?></a>
							</div>
						</div>

						<div id="keterangan_pelanggan" class="row">
							<div class="col-sm-12"><h4>Keterangan</h4></div>
							<div class="col-sm-4 text-right">
							<?php foreach ($data['logs'] as $log) : ?>
								<label>* <?php echo $log['deskripsi'] . ' pada ' . dateTimeFormat($log['waktu']); ?></label>
							<?php endforeach; ?>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>