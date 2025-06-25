<div class="col-sm-12 no-padding">
	<form class="form-horizontal" role="form">
		<div name="data-pelanggan">
			<div id="jenis_pelanggan">
				<div class="col-sm-4">
					<input type="hidden" data-id="<?php echo $data['id']; ?>">
					
					<?php if ( $akses['a_ack'] == 1 ): ?>
						<div class="form-group align-items-center d-flex">
							<span class="col-sm-6 text-right">NIP</span>
							<div class="col-sm-6">
								<input class="form-control" type="text" name="nip_pelanggan" value="<?php echo $data['nomor']; ?>" readonly>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Jenis Pelanggan</span>
						<div class="col-sm-6">
							<input class="form-control" type="text" name="jenis_plg" value="<?php echo $data['jenis']; ?>" readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Nama Pelanggan</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="nama_plg" placeholder="Perusahaan/Perseorangan" value="<?php echo $data['nama']; ?>" readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Contact Person</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="contact_plg" placeholder="Contact Person" value="<?php echo $data['cp']; ?>" readonly>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Platform</span>
						<div class="col-sm-6">
							<input required="required" class="form-control text-right" type="text" name="platform" placeholder="Platform" data-tipe="integer" value="<?php echo $data['platform']; ?>" readonly>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="col-xs-6 no-padding">&nbsp;</div>
					<div class="col-xs-6 no-padding">
						<div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
							<?php 
								$disabled = 'disabled';
								if ( isset($data['posisi']) && !empty($data['posisi']) ) {
									$disabled = null;
								} 
							?>
							<a type="button" class="col-xs-12 btn btn-default pull-right <?php echo $disabled; ?>" href="https://www.google.com/maps/?q=<?php echo $data['posisi']['lat_long']; ?>" target="_blank">
								<i class="fa fa-map-marker"></i> Lokasi
							</a>
						</div>
						<div class="col-xs-12 no-padding">
							<?php 
								$disabled = 'disabled';
								if ( isset($data['posisi']) && !empty($data['posisi']) ) {
									$disabled = null;
								} 
							?>
							<a type="button" class="col-xs-12 btn btn-default pull-right <?php echo $disabled; ?>" href="uploads/<?php echo $data['posisi']['foto_kunjungan'] ?>" target="_blank">
								<i class="fa fa-camera"></i> Foto
							</a>
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
									</tr>
								</thead>
								<tbody>
									<?php foreach ($data['telepons'] as $telp) : ?>
										<tr>
											<td>
												<input class="form-control" type="text" name="telp_plg" placeholder="Telepon" data-tipe="phone" value="<?php echo $telp['nomor']; ?>" required readonly>
											</td>
										</tr>
									<?php endforeach ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div id="alamat_pelanggan">
				<div class="col-sm-12">Alamat Sesuai KTP</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">NIK</span>
						<div class="col-sm-2" style="margin-left: 11px; padding-right: 20px;">
							<input required="required" class="form-control" type="text" name="ktp_plg" placeholder="Nomer KTP" value="<?php echo $data['nik']; ?>" readonly>
						</div>
						<div class="col-sm-8 no-padding">
							<div class="col-sm-12">
								<a target="_blank" href="<?php echo 'uploads/'.$l_ktp['path']; ?>" ><?php echo $l_ktp['filename']; ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Provinsi</span>
						<div class="col-sm-6">
							<input class="form-control" type="text" name="propinsi_plg" placeholder="Provinsi" value="<?php echo $lokasi['prov']; ?>" required readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kabupaten / Kota</span>
						<div class="col-sm-6">
							<input class="form-control" type="text" name="kabupaten_plg" placeholder="Kabupaten / Kota" value="<?php echo $lokasi['kota']; ?>" required readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<input class="form-control" type="text" name="kecamatan_plg" placeholder="Kecamatan" value="<?php echo $lokasi['kec']; ?>" required readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan / Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_plg" placeholder="Kelurahan KTP" value="<?php echo $data['alamat_kelurahan']; ?>" readonly>
						</div>
					</div>
				</div>
				<div class="com-sm-8">
					<div class="col-sm-4">
						<div class="form-group align-items-center d-flex">
							<div class="col-sm-12">
								<textarea required="required" id="text-alamat" class="form-control" name="alamat_plg" style="height: 111px;" placeholder="Alamat/jalan" readonly><?php echo $data['alamat_jalan']; ?></textarea>
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
									<input required="required" class="form-control text-center" data-tipe="rt" type="text" name="rt_plg" placeholder="RT" value="<?php echo $new_rt; ?>" readonly>
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
									<input required="required" class="form-control text-center" data-tipe="rw" type="text" name="rw_plg" placeholder="RW" value="<?php echo $new_rw; ?>" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="alamat_usaha_pelanggan">
				<div class="col-sm-12">Alamat Tempat Usaha</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">NPWP</span>
						<div class="col-sm-2" style="margin-left: 11px; padding-right: 20px;">
							<input required="required" class="form-control" type="text" name="npwp_plg" placeholder="Nomer NPWP" value="<?php echo (!empty($data['npwp']) && $data['npwp'] != '') ? $data['npwp'] : '-'; ?>" readonly>
						</div>
						<div class="col-sm-8 no-padding">
							<div class="col-sm-12">
								<a target="_blank" href="<?php echo 'uploads/'.$l_npwp['path']; ?>" ><?php echo $l_npwp['filename']; ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-12 no-padding">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-2 text-right no-padding">No. SKB</span>
						<div class="col-sm-2" style="margin-left: 11px; padding-right: 20px;">
							<input required="required" class="form-control" type="text" name="skb_plg" placeholder="No. SKB" value="<?php echo (!empty($data['skb']) && $data['skb'] != '') ? $data['skb'] : '-'; ?>" readonly>
						</div>
						<span class="col-sm-2 text-right">Tgl Habis Berlaku</span>
						<div class="col-sm-3">
							<input type="text" class="form-control text-center" placeholder="Tanggal" value="<?php echo tglIndonesia($data['tgl_habis_skb'], '-', ' ') ?>" readonly />
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Provinsi</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="propinsi_usaha_plg" placeholder="Provinsi" value="<?php echo !empty($lokasi['prov_usaha']) ? $lokasi['prov_usaha'] : '-'; ?>" readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kabupaten / Kota</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kabupaten_usaha_plg" placeholder="Kabupaten / Kota" value="<?php echo !empty($lokasi['kota_usaha']) ? $lokasi['kota_usaha'] : '-'; ?>" readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kecamatan</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kecamatan_usaha_plg" placeholder="Kecamatan" value="<?php echo !empty($lokasi['kec_usaha']) ? $lokasi['kec_usaha'] : '-'; ?>" readonly>
						</div>
					</div>
					<div class="form-group align-items-center d-flex">
						<span class="col-sm-6 text-right">Kelurahan / Desa</span>
						<div class="col-sm-6">
							<input required="required" class="form-control" type="text" name="kelurahan_usaha_plg" placeholder="Kelurahan Usaha" value="<?php echo !empty($data['usaha_kelurahan']) ? $data['usaha_kelurahan'] : '-'; ?>" readonly>
						</div>
					</div>
				</div>
				<div class="col-m-8">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="col-sm-12 align-items-center d-flex">
								<textarea required="required" id="text-alamat-usaha" class="form-control" name="alamat_usaha_plg" placeholder="Alamat Usaha" style="height: 111px;" placeholder="Alamat/jalan" readonly><?php echo !empty($data['usaha_jalan']) ? $data['usaha_jalan'] : '-'; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RT</span>
								<div class="col-sm-11">
									<?php 
										$new_rt = null;
										$rt = $data['usaha_rt'];
										if ( !empty($rt) ) {
											if ( strlen($rt) == 1 ) {
												$new_rt = '00'.$rt;
											} elseif ( strlen($rt) == 2 ) {
												$new_rt = '0'.$rt;
											} elseif ( strlen($rt) == 3 ) {
												$new_rt = $rt;
											}
										} else {
											$new_rt = '-';
										}
									?>
									<input required="required" class="form-control text-center" data-tipe="rt" type="text" name="rt_usaha_plg" placeholder="RT" value="<?php echo $new_rt; ?>" readonly>
								</div>
							</div>

							<div class="col-sm-3 no-padding align-items-center d-flex">
								<span class="col-sm-1 text-right">RW</span>
								<div class="col-sm-11">
									<?php 
										$new_rw = null;
										$rw = $data['usaha_rw']; 
										if ( !empty($rw) ) {
											if ( strlen($rw) == 1 ) {
												$new_rw = '00'.$rw;
											} elseif ( strlen($rw) == 2 ) {
												$new_rw = '0'.$rw;
											} elseif ( strlen($rw) == 3 ) {
												$new_rw = $rw;
											}
										} else {
											$new_rw = '-';
										}
									?>
									<input required="required" class="form-control text-center" data-tipe="rw" type="text" name="rw_usaha_plg" placeholder="RW" value="<?php echo $new_rw; ?>" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 no-padding">
			<div id="rekening_pelanggan">
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
							</tr>
						</thead>
						<tbody>
							<?php if ( count($data['banks']) > 0 ): ?>
								<?php foreach ($data['banks'] as $bank) : ?>
									<?php 
										$path = null;
										$filename = null;
										if ( !empty($bank['lampiran']) ) {
											$path = $bank['lampiran']['path'];
											$filename = $bank['lampiran']['filename'];
										}
									?>
									<tr class="detail_rekening v-center">
										<td><?php echo $bank['rekening_nomor']; ?></td>
										<td><?php echo $bank['rekening_pemilik']; ?></td>
										<td><?php echo $bank['bank']; ?></td>
										<td><?php echo $bank['rekening_cabang_bank']; ?></td>
										<td>
											<?php if ( !empty($bank['lampiran']) ): ?>
												<a target="_blank" href="<?php echo 'uploads/'.$path;?> "><?php echo $filename;?></a>
											<?php else: ?>
												-
											<?php endif ?>
										</td>
									</tr>
								<?php endforeach ?>
							<?php else: ?>
								<tr>
									<td colspan="5">Data tidak ditemukan.</td>
								</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
			<div id="lampiran_pelanggan">
				<div class="col-sm-2"><b>Lampiran DDP</b></div>
				<div class="col-sm-10">
					<?php if ( !empty($l_ddp['filename']) ): ?>
						<a target="_blank" href="<?php echo 'uploads/'.$l_ddp['path']; ?>" ><?php echo $l_ddp['filename']; ?></a>
					<?php else: ?>
						<label class="control-label">-</label>
					<?php endif ?>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="col-sm-12" style="margin-top: 10px;">
	<p>
		<b>Keterangan : </b>
		<?php
			foreach ($tbl_logs as $log) {
				$temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
			}
			if ($temp) {
				echo '<ul>' . implode("", $temp) . '</ul>';
			}
		?>
	</p>
</div>
<div class="col-sm-12 no-padding">
	<hr>
	<?php if ( $akses['a_ack'] == 1 ): ?>
		<?php if ( $data['status'] == getStatus(1) ): ?>
			<div class="col-sm-12">
				<button type="button" class="btn btn-large btn-primary pull-right" id="submit_pelanggan" onclick="plg.ack()"><i class="fa fa-check"></i>ACK</button>
			</div>
		<?php endif ?>
	<?php endif ?>
</div>