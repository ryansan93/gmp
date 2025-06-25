<?php // if (empty($data_hk)) : ?>
	<!-- <div class="panel panel-default text-center">
		<h3>Data Tidak Ditemukan</h3>
	</div> -->
<?php // else : ?>
	<div class="panel-body no-padding">
		<div class="text-center">
			<h3><?php echo $title_panel;?></h3>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-12">
					<small>
						<table id="table-list-PLG">
							<thead>
								<tr>
									<th class="no"></th>
									<th class="kode_plg"><input type="text" id="kode_plg" class="form-control" name="kode_plg" data-target="kode_plg" onkeyup="filter_content(this)"></th>
									<th class="nama_plg"><input type="text" id="nama_plg" class="form-control" name="nama_plg" data-target="nama_plg" onkeyup="filter_content(this)"></th>
									<th class="alamat1"></th>
									<th class="alamat2"></th>
									<th class="kelurahan"></th>
									<th class="kecamatan"></th>
									<th class="kota_plg"><input type="text" id="kota_plg" class="form-control" name="kota_plg" data-target="kota_plg" onkeyup="filter_content(this)"></th>
									<th class="contact_person"></th>
									<th class="telp"></th>
									<th class="npwp"></th>
									<th class="ktp"></th>
									<th class="status"></th>
								</tr>
								<tr>
									<th class="no text-center">No.</th>
									<th class="kode_plg text-center">No. Induk</th>
									<th class="nama_plg text-center">Nama</th>
									<th class="alamat1 text-center">Alamat 1</th>
									<th class="alamat2 text-center">Alamat 2</th>
									<th class="kelurahan text-center">Kelurahan</th>
									<th class="kecamatan text-center">Kecamatan</th>
									<th class="kota_plg text-center">Kota</th>
									<th class="contact_person text-center">Contact<br>Person</th>
									<th class="telp text-center">No. Telp</th>
									<th class="npwp text-center">NPWP</th>
									<th class="ktp text-center">No. KTP &<br>Masa Berlaku</th>
									<th class="status text-center">Status</th>
								</tr>
							</thead>
							<tbody>
								<?php // $no = 1;?>
								<?php // foreach ($list_pelanggan as $key => $value) { ?>
								<!-- <tr style="font-size:11px;">
									<td class="no"><?php echo $no; ?></td>
									<td class="kode_plg text-center"> <?php echo $value['FCUS_KD'];?> </td>
									<td class="nama_plg"> <?php echo $value['FCUS_NM'];?> </td>
									<td class="alamat1"> <?php echo $value['FCUS_AL1'];?> </td>
									<td class="alamat2"> <?php echo $value['FCUS_AL2'];?> </td>
									<td class="kelurahan"> <?php echo $value['FCUS_LURAH'];?> </td>
									<td class="kecamatan"> <?php echo $value['FCUS_CAMAT'];?> </td>
									<td class="kota_plg text-center"> <?php echo $value['FCUS_KO'];?> </td>
									<td class="contact_person"> <?php echo $value['FCUS_TL2'];?> </td>
									<td class="telp"> <?php echo $value['FCUS_TL1'];?> </td>
									<td class="npwp"> <?php echo $value['FCUS_NPWP'];?> </td>
									<td class="ktp"> <?php echo $value['FCUS_KOM1'];?> </td>
									<td class="status text-center"><?php echo $value['FCUS_AKT']; ?></td>
								</tr> -->
								<?php // $no+=1;?>
								<?php // } ?>
								<tr style="font-size:11px;">
									<td class="no">-</td>
									<td class="kode_plg text-center">-</td>
									<td class="nama_plg">-</td>
									<td class="alamat1">-</td>
									<td class="alamat2">-</td>
									<td class="kelurahan">-</td>
									<td class="kecamatan">-</td>
									<td class="kota_plg text-center">-</td>
									<td class="contact_person">-</td>
									<td class="telp">-</td>
									<td class="npwp">-</td>
									<td class="ktp">-</td>
									<td class="status text-center">-</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</div>
	</div>
<?php // endif ?>