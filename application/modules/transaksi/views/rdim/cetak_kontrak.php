<style type="text/css">
	table.border-field td, table.border-field th {
		border: 1px solid;
		border-collapse: collapse;
	}

	.header-title{
		font-size: 14px;
		text-align: center;
	}

	.sapronak{
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		margin-bottom: 1px;
		font-size: 12px;
	}

	.ttah{
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		margin-bottom: 1px;
		font-size: 12px;
	}

	.table-bordered{
		border: 1px solid #000;
	}

	.table-nobordered{
		border: 0px solid #000;
	}

	th.bordered, td.bordered{
		border: 1px solid #000;	
		background-color: #d1d1d1;
	}

	.col-sm-2{
		width: 20%;
	}

	.col-sm-1{
		width: 10%;
	}

	.col-sm-3{
		width: 30%;
	}

	.col-sm-6{
		width: 50%;
	}

	.table-nobordered-padding td, .table-nobordered-padding th{
		padding-left: 3px;
	}

	.angka {
		text-align: right;
		padding-right: 3px;
	}

	.sapronak td, .sapronak th{
		padding: 3px;
	}

	/* @page{
		margin: 2em 1em 1em 1em;
	} */

	@page{
		size: A4;
		margin: 2em 1em 1em 1em;
		width: 210mm;
		height: 297mm;
	}

	/* @media print {
		html, body {
			width: 210mm;
			height: 297mm;
		}
		/* ... the rest of the rules ... */
	} */
</style>
<div style="font-size:12px; font-style:Calibri; width: 90%; ">
	<div style="margin-left: 50px; margin-right: 50px; width: 100%; font-size: 20pt; text-align: center;">
		<div style="margin-bottom: 10px;">
			<span>
				<b>
					<span><?php echo !empty($data_kontrak['data_perusahaan']) ? $data_kontrak['data_perusahaan']['perusahaan'] : 'KOP Mengikuti DOnya Masing-Masing'; ?></span>
					<?php if ( !empty($data_kontrak['data_perusahaan']) ): ?>
						<br>
						<span style="color: red;">
							<?php 
								$str = '';
								$len = count($data_kontrak['hitung_budidaya']) - 1;
								$idx = 0;
								foreach ($data_kontrak['hitung_budidaya'][$len]['perwakilan_maping'] as $key => $value) {
									$str .= $value['nama_pwk'];
									if ( (count($data_kontrak['hitung_budidaya'][$len]['perwakilan_maping']) - 1) != $idx ) {
										$str .= ', ';
										$idx++;
									}
								}

								echo '(' . $str . ')';
							?>
						</span>
					<?php else: ?>
						<span style="color: red;">(CV. Mitra Gemuk Bersama:Jbr, Lmj, Pasur, Probo, Mlg), (CV. Mitra Gemilang Bersinar : Jbg, Mjk, Kdr, Tag, Gsk, Lmg, Bjn, Mgtn), (Anton Cindrawan Purnomo : Bwi)</span>
					<?php endif ?>
					<br>
					<?php
						$alamat_perusahaan = !empty($data_kontrak['data_perusahaan']) ? $data_kontrak['data_perusahaan']['alamat'] : 'Jl. Gajah Mada XVIII /No. 14, Kaliwates';
						$kota_perusahaan = '';
						if ( !empty($data_kontrak['data_perusahaan']) ) {
							if ( !empty($data_kontrak['data_perusahaan']['d_kota']) ) {
								if ( substr($data_kontrak['data_perusahaan']['d_kota']['nama'], 0, 4) == 'Kab ' ) {
									$kota_perusahaan = !empty($data_kontrak['data_perusahaan']['d_kota']['nama']) ? ', ' . str_replace('Kab ', '', $data_kontrak['data_perusahaan']['d_kota']['nama']) : '';
								} else {
									$kota_perusahaan = !empty($data_kontrak['data_perusahaan']['d_kota']['nama']) ? ', ' . str_replace('Kota ', '', $data_kontrak['data_perusahaan']['d_kota']['nama']) : '';
								}
							} else {
								$kota_perusahaan = ', Jember';
							}
						} else {
							$kota_perusahaan = ', Jember';
						}
					?>
					<span><?php echo $alamat_perusahaan . $kota_perusahaan; ?></span>
				</b>
			</span>
		</div>
		<div style="width: 100%; height: 1px; border-bottom: 1px solid black;"></div>
		<div style="width: 100%; height: 1px; border-bottom: 3px solid black;"></div>
		<div style="font-size: 12pt; text-align: center; margin-top: 15px;">
			<span><u>SURAT KESEPAKATAN KERJASAMA</u></span>
		</div>
		<div style="font-size: 12pt; text-align: center;">
			<span>Berlaku : <?php echo tglIndonesia($data_kontrak['mulai'], '-', ' ', true); ?></span>
		</div>
		<div style="font-size: 12pt; text-align: left; margin-top: 15px;">
			<span><span style="padding-right: 50px;"> </span>CV. Mitra Gemuk Bersama sebagai <span style="font-style:italic;"><b>Pihak Pertama</b></span> dan menyediakan pengadaan Sapronak kepada <span style="font-style:italic;"><b>Pihak Kedua</b></span> :</span>
		</div>
		<div style="font-size: 12pt; text-align: left; margin-top: 15px;">
			<table>
				<tbody>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">Periode</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo strtoupper($data['periode']); ?></td>
					</tr>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">Nama</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo strtoupper($data['nama']); ?></td>
					</tr>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">No. KTP/SIM</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo strtoupper($data['ktp']); ?></td>
					</tr>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">Alamat</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo strtoupper($data['alamat']); ?></td>
					</tr>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">Alamat Farm</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo strtoupper($data['alamat_kdg']); ?></td>
					</tr>
					<tr style="page-break-after: auto;">
						<td style="width: 20%; vertical-align: top;">Populasi</td>
						<td style="width: 3%; vertical-align: top;">:</td>
						<td style="width: 77%;"><?php echo angkaRibuan($data['populasi']); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<br>
		<!-- <div style="font-size: 12pt; text-align: left;">
			</div> -->
		<div style="font-size: 12pt; text-align: left; page-break-after: auto;">
			<span><b>Kesepakatan Harga Sapronak :</b></span>
			<table class="table table-bordered border-field sapronak">
				<thead>
					<tr>
						<th class="bordered" style="text-align: center;" rowspan="2">Sapronak</th>

						<?php $jml_max_supplier = array('', '', ''); ?>
						<?php foreach ($jml_max_supplier as $k_jml => $value): ?>
							<?php if ( isset($data_kontrak['harga_sapronak'][$k_jml]) ): ?>
								<th class="bordered" style="text-align: center;" colspan="2">
									<?php echo $data_kontrak['harga_sapronak'][$k_jml]['d_supplier']['nama']; ?>
								</th>
							<?php endif ?>
						<?php endforeach ?>
					</tr>
					<tr>
						<?php $jml_max_supplier = array('', '', ''); ?>
						<?php foreach ($jml_max_supplier as $k_jml => $value): ?>
							<?php if ( isset($data_kontrak['harga_sapronak'][$k_jml]) ): ?>
								<th class="bordered">Jenis</th>
								<th class="bordered">Harga</th>
							<?php endif ?>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php $sapronak = array('doc', 'pakan1', 'pakan2', 'pakan3'); ?>
					<?php foreach ($sapronak as $k_sapronak => $v_sapronak): ?>
						<tr>
							<td><?php echo strtoupper($v_sapronak); ?></td>

							<?php
								$jml_max_supplier = array('', '', '');

								foreach ($jml_max_supplier as $k_jml => $v_jml) {
									if ( isset($data_kontrak['harga_sapronak'][$k_jml]['detail']) !== false ) {
										foreach ($data_kontrak['harga_sapronak'][$k_jml]['detail'] as $k_hs_det => $v_hs_det) {
											if ( stristr($v_hs_det['jenis'], $v_sapronak) ) { ?>
												<td><?php echo strtoupper($v_hs_det['d_barang']['nama']); ?></td>
												<td align="right"><?php echo ($v_hs_det['hrg_peternak'] != 0) ? angkaRibuan($v_hs_det['hrg_peternak']) : '-'; ?></td>
											<?php }
										}
									}
								}
							?>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
			<span>*DOC CP kontrak harga tetap semua</span><br>
			<span>**DOC MB kontrak harga baru</span>
		</div>
		<!-- <div style="font-size: 12pt; text-align: left;">
		</div> -->
		<br>
		<div style="font-size: 12pt; text-align: left; margin-bottom: 10px;">
			<span>Obat/Vitamin/Vaksin/Desinfektan<span style="margin-left: 150px;">: Sesuai Pemakaian</span></span>
		</div>
		<div style="page-break-after: auto;">
			<div style="font-size: 12pt; text-align: left;">
				<span><b>Kesepakatan Harga Panen :</b></span>
			</div>
			<div style="font-size: 12pt; text-align: left;">
				<table class="table table-bordered border-field sapronak" style="width: 80%;">
					<thead>
						<tr>
							<th class="bordered" style="text-align: center;">Ukuran Berat (Kg)</th>
							<th class="bordered" style="text-align: center;">Harga Ayam Sehat</th>
							<th class="bordered" style="text-align: center;">Harga Ayam Sakit & Afkir</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( count($data_kontrak['harga_sepakat']) ): ?>
							<?php $index = 0; ?>
							<?php foreach ($data_kontrak['harga_sepakat'] as $k_hs => $v_hs): ?>
								<?php if ( $v_hs['range_max'] != 1.49 ): ?>
									<?php
										$range_min = ($v_hs['range_min'] != 0) ? angkaDecimal($v_hs['range_min']) : '<=';
										$range_max = ($v_hs['range_max'] != 0) ? angkaDecimal($v_hs['range_max']) : '>=';

										$range = $range_min . ' - ' . $range_max;
										if ( $range_max == '>=' ) {
											$range = '>= ' . $range_min;
										}

										$hrg = angkaRibuan($v_hs['harga']);
									?>
									<tr>
										<td align="center"><?php echo $range ?></td>
										<td align="right"><?php echo 'Rp. ' . $hrg; ?></td>
										<?php if ( $index == 0 ): ?>
											<td rowspan="<?php echo count($data_kontrak['harga_sepakat']) - 1; ?>" align="center">Sesusai Harga Pasar</td>
										<?php endif ?>
									</tr>
									<?php $index++; ?>
								<?php endif ?>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="4">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</div>
		<br>
		<div style="page-break-inside: auto;">
			<div style="font-size: 12pt; text-align: left;">
				<span><b>Kompensasi Peternak :</b></span>
			</div>
			<div style="font-size: 12pt; text-align: left;">
				<span><b><span style="margin-left: 50px;"> </span>1. Bonus FCR </b>(Dihitung dari : Selisih berat badan rata-rata ( Kg/ekor) – FCR Actual)</span>
			</div>
			<div style="font-size: 12pt; text-align: left;">
				<table class="table table-bordered border-field sapronak" style="width: 50%;">
					<thead>
						<tr>
							<th class="bordered" style="text-align: center;">No.</th>
							<th class="bordered" style="text-align: center;">Berat Badan - FCR</th>
							<th class="bordered" style="text-align: center;">Rp / Kg</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( count($data_kontrak['selisih_pakan']) > 0 ): ?>
							<?php $no = 0; ?>
							<?php foreach ($data_kontrak['selisih_pakan'] as $k_sp => $v_sp): ?>
								<?php
									$no++;
									$range_awal = angkaDecimalFormat($v_sp['range_awal'], 3);
									$range_akhir = ($v_sp['range_akhir'] != 0) ? angkaDecimalFormat($v_sp['range_akhir'], 3) : '>';

									$range = $range_awal . ' - ' . $range_akhir;
									if ( $range_akhir == '>' ) {
										$range = '>= ' . $range_awal;
									}
									$tarif = angkaRibuan($v_sp['tarif']);
								?>
								<tr>
									<td align="center"><?php echo $no; ?></td>
									<td align="center"><?php echo $range; ?></td>
									<td align="right"><?php echo 'Rp. ' . $tarif; ?></td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="4">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</div>
		<br>
		<!-- <div style="font-size: 12pt; text-align: left;">
			</div> -->
		<div style="font-size: 12pt; text-align: left; page-break-after: auto;">
			<span><b><span style="margin-left: 50px;"> </span>2. Bonus Kematian </b>(Persentase Kematian Maksimal 5%)</span>
			<table class="table table-bordered border-field sapronak" style="width: 50%;">
				<thead>
					<tr>
						<th class="bordered" style="text-align: center;">Nilai IP</th>
						<th class="bordered" style="text-align: center;">Bonus Kematian</th>
						<th class="bordered" style="text-align: center;">Bonus Harga Pasar</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( count($data_kontrak['hitung_budidaya']) > 0 ): ?>
						<?php foreach ($data_kontrak['hitung_budidaya'] as $k_hb => $v_hb): ?>
							<?php
								$ip_awal = !empty($v_hb['ip_awal']) ? angkaRibuan($v_hb['ip_awal']) : '<=';
								$ip_akhir = !empty($v_hb['ip_akhir']) ? angkaRibuan($v_hb['ip_akhir']) : '>=';

								$ip = $ip_awal . ' - ' . $ip_akhir;
								if ( $ip_akhir == '>=' ) {
									$ip = '> ' . $ip_awal;
								}

								$bonus_kematian = angkaRibuan($v_hb['bonus_dh']);
								$bonus_harga = angkaRibuan($v_hb['bonus_ip']);
							?>
							<tr>
								<td align="center"><?php echo $ip; ?></td>
								<td align="right"><?php echo 'Rp. ' . $bonus_kematian; ?></td>
								<td align="right"><?php echo $bonus_harga . '%'; ?></td>
							</tr>
						<?php endforeach ?>
					<?php else: ?>
						<tr>
							<td colspan="4">Data tidak ditemukan.</td>
						</tr>
					<?php endif ?>
				</tbody>
			</table>
		</div>
		<br>
		<br>
		<div style="font-size: 12pt; text-align: left;">
			<span><b><u>CATATAN :<u></b></span>
		</div>
		<div style="font-size: 12pt; text-align: left;">
			<ol type="1">
				<li style="page-break-inside: avoid;">Semua penjualan menjadi tanggung jawab <b>Pihak Pertama</b>, sehingga <b>Pihak Kedua</b> tidak berhak menjual ayam saat panen.</li>
				<li style="page-break-inside: avoid;"><b>Pihak Kedua</b> wajib membantu dalam pemeliharaan ayam dengan kontrol/pengawasan manajemen dari <b>Pihak Pertama</b>.</li>
				<li style="page-break-inside: avoid;">Jika ayam sakit atau kualitasnya jelek (termasuk ayam afkir) atau hasil panen tidak rasional (<span style="font-style: italic;">bobot x FCR tidak standart</span>), maka <b>Pihak Pertama</b> akan melakukan pemotongan harga kontrak (sesuai dengan harga ayam sakit/afkir di pasar).</li>
				<li style="page-break-inside: avoid;">Menunjuk pasal 3 di atas, yang dimaksud dengan harga pasar adalah harga pasar yang berlaku di MGB.</li>
				<li style="page-break-inside: avoid;">Harga kontrak sewaktu-waktu bisa berubah, jika terjadi perubahan harga DOC atau harga pakan tanpa pemberitahuan lebih dahulu.</li>
				<li style="page-break-inside: avoid;">
					Apabila terjadi selisih jumlah ayam (ayam hilang) antara yang ada di catatan laporan (recording) dengan kenyataan saat panen, maka <b>Pihak Kedua</b> diwajibkan untuk mengganti selisih jumlah ayam tersebut kepada <b>Pihak Pertama</b> dengan perhitungan:
					<br>
					<br>
						<div style="font-size: 12pt; text-align: center;">
							<span style="font-style:italic; text-align: center;"><b><u>Jumlah ayam hilang x BB x harga kontrak x 2</u></b></span>
						</div>
					<br>
					Dalam hal ini <b>Pihak Pertama</b> hanya memberikan toleransi atas ayam hilang tersebut kepada <b>Pihak Kedua</b> dengan syarat tidak lebih dari 0.5% dari DOC datang.
				</li>
				<li style="page-break-inside: avoid;">
					Apabila terjadi selisih jumlah pakan (pakan hilang) antara yang ada pada daftar stok pakan (Recording) dengan kenyataan fisik di lapangan, baik selama pemeliharaan maupun pada saat akhir masa pemeliharaan ayam (panen). Maka <b>Pihak Kedua</b> di wajibkan untuk mengganti selisih jumlah pakan tersebut kepada <b>Pihak Pertama</b> dengan perhitungan:
					<br>
					<br>
						<div style="font-size: 12pt; text-align: center;">
							<span style="font-style:italic; text-align: center;"><b><u>Jumlah pakan hilang (kg) x harga kontrak x 2</u></b></span>
						</div>
					<br>
					Dalam hal ini, untuk pengiriman pakan selanjutnya, <b>Pihak Pertama</b> akan mengirimkan tetap sesuai dengan jadwal pengiriman yang telah diatur sebelumnya.
				</li>
				<li style="page-break-inside: avoid;"><b>Pihak Kedua</b> diwajibkan mengikuti program pemeliharaan ayam dari <b>Pihak Pertama</b>, apabila akan menambah/mengganti, harus dengan sepengetahuan <b>Pihak Pertama</b>.</li>
				<li style="page-break-inside: avoid;">Apabila <b>Pihak Kedua</b> menambah pakan sehingga melampaui batas standart FCR MGB, maka <b>Pihak Pertama</b> tidak berkewajiban membayar kelebihan dari standart FCR tersebut.</li>
				<li style="page-break-inside: avoid;">Berdasarkan Peraturan Menteri Keuangan No. 141/PMK.03/2015, maka mulai masa Januari 2019 akan dilakukan pemotongan sebesar 2% (bagi yang memiliki NPWP) atau 4% (yang tidak memiliki NPWP) atas pembayaran jasa sesuai dengan Pajak Penghasilan Pasal 23, Pajak yang dipotong oleh <b>Pihak Pertama</b> (MGB) tersebut adalah pajak penghasilan yang wajib dipotong oleh <b>Pihak Pertama</b> sebagai pengguna jasa <b>Pihak Kedua</b> (plasma). 
					Atas pemotongan tersebut <b>Pihak Pertama</b> akan memberikan Bukti Potong PPh Pasal 23. Bagi Peternak yang tidak ingin dilakukan pemotongan, mohon untuk memberikan Surat Keterangan Bebas (SKB) / Surat Keterangan (SK) sebagai dasar <b>Pihak Pertama</b> agar tidak melakukan pemotongan dan juga menyiapkan invoice.
				</li>
				<li style="page-break-inside: avoid;">
					Keuntungan hasil panen dari perjanjian kemitraan ini akan dibayarkan <b>Pihak Pertama</b> kepada <b>Pihak Kedua </b>:
					<br>
					<br>
					<div style="font-size: 12pt; text-align: center;">
						<span style="font-style:italic; text-align: center;"><b><u>Dari total keuntungan maximum 7(tujuh)hari kerja, setelah panen terakhir</u></b></span>
					</div>
					<br>
				</li>
				<li style="page-break-inside: avoid;">Apabila <b>Pihak Kedua</b> mengalami kerugian (akibat pasal 6 dan pasal 7) yang mengakibatkan timbulnya hutang pada <b>Pihak Pertama</b> maka <b>Pihak Kedua</b> berkewajiban membayar hutangnya.</li>
			</ol> 
		</div>
		<div style="font-size: 12pt; text-align: left; page-break-inside: avoid;">
			<span><span style="margin-left: 40px;"> </span>Demikian surat kesepakatan bersama antara Pihak Pertama dan Pihak Kedua yang tak terpisahkan dari perjanjian kerjasama dalam jual beli Sarana Produksi Ternak (Sapronak) dan Pemasaran Hasil Panen.</span>
		</div>
		<br>
		<!-- <div style="font-size: 12pt; text-align: right; page-break-inside: avoid;">
			</div> -->
		<div style="font-size: 12pt; text-align: center; page-break-inside: auto;">
			<div style="font-size: 12pt; text-align: right; page-break-inside: avoid;">
				<span style="text-align: right; margin-right: 40px;">Jember, <?php echo tglIndonesia(date('Y-m-d'), '-', ' ', true); ?></span>
			</div>
			<table style="width: 100%;">
				<tbody>
					<tr>
						<td style="text-align: center;">Yang Membuat Persetujuan,</td>
						<td style="text-align: center;">Mengetahui dan menyetujui,</td>
					</tr>
					<tr>
						<td style="text-align: center;">Pihak Pertama a/n MGB</td>
						<td style="text-align: center;">Pihak Kedua</td>
					</tr>
					<tr>
						<td>
							<br>
							<br>
							<br>
							<br>
							<br>
						</td>
						<td style="text-align: center;">
							<div style="border: 1px solid black; width: 30%; margin-left: 35%;">
								<span style="font-style: italic;">Materai<br>6.000</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<table style="width: 100%;">
								<tbody>
									<tr>
										<td style="text-align: left; padding-left: 40px;">(</td>
										<td style="text-align: right; padding-right: 40px;">)</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td>
							<table style="width: 100%;">
								<tbody>
									<tr>
										<td style="text-align: left; padding-left: 40px;">(</td>
										<td style="text-align: right; padding-right: 40px;">)</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>