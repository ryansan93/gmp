<style type="text/css">
	div.contain {
		font-size: 10pt;
	}

	div.page-break {
		page-break-after: always;
	}

	p {
		margin: 0px;
	}

	ol { 
		counter-reset: item;
		margin: 0px;
		vertical-align: top;
	}
	li { 
		display: block; 
		margin: 0px;
		padding: 0px;
		vertical-align: top;
	}
	li:before { 
		content: counters(item, ".") ". ";
		counter-increment: item;
		vertical-align: top;
	}

	table.border-field td, table.border-field th {
		border: 1px solid;
		border-collapse: collapse;
		padding-left: 3px;
		padding-right: 3px;
	}

	table {
		border-collapse: collapse;
	}

	.text-center {
		text-align: center;
	}

	.text-right {
		text-align: right;
	}

	.text-left {
		text-align: left;
	}

	.top td {
		border-top: 1px solid black;
	}

	.bottom td {
		border-bottom: 1px solid black;
	}

	td.kiri {
		border-left: 1px solid black;
		padding-left: 3px;
		padding-right: 3px;
	}

	td.kanan {
		border-right: 1px solid black;
		padding-left: 3px;
		padding-right: 3px;
	}

	@page{
		margin: 1em 1em 1em 2em;
	}

	.col-xs-1 {
		width: 8.33333333%;
	}
	.col-xs-2 {
		width: 16.66666667%;
	}
	.col-xs-3 {
		width: 25%;
	}
	.col-xs-4 {
		width: 33.33333333%;
	}
	.col-xs-5 {
		width: 41.66666667%;
	}
	.col-xs-6 {
		width: 50%;
	}
	.col-xs-7 {
		width: 58.33333333%;
	}
	.col-xs-8 {
		width: 66.66666667%;
	}
	.col-xs-9 {
		width: 75%;
	}
	.col-xs-10 {
		width: 83.33333333%;
	}
	.col-xs-11 {
		width: 91.66666667%;
	}
	.col-xs-12 {
		width: 100%;
	}
</style>

<div class="contain page-break" style="width: 100%;">
	<!-- <h1 class="text-center">CV. MITRA GEMUK BERSAMA</h1> -->
	<h1 class="text-center"><?php echo strtoupper($data['perusahaan']); ?></h1>
	<!-- <p>Hal : Laporan Hasil Plasma</p> -->
	<br>
	<p>Dengan Hormat,</p>
	<p>Bersama ini dilaporkan hasil pemeliharaan Ayam Broiler</p>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Nama Pemilik</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<?php echo ucwords(strtolower($data['mitra'])); ?>
		</label>
		<label class="col-xs-2" style="display: inline-block;">Unit</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;"><?php echo ucwords(strtolower($data['unit'])); ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Nomor NPWP</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<?php echo ucwords(strtolower(npwpFormat($data['npwp']))); ?>
		</label>
		<label class="col-xs-2" style="display: inline-block;">PPL</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;"><?php echo ucwords(strtolower($data['ppl'])); ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Populasi</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<?php echo angkaRibuan($data['populasi']).' Ekor'; ?>
		</label>
		<label class="col-xs-2" style="display: inline-block;">Tgl DOC In</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-2" style="display: inline-block;"><?php echo tglIndonesia($data['tgl_docin'], '-', ' ', true); ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Alamat Pemilik</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-9" style="display: inline-block;">
			<?php echo ucwords(strtolower($data['alamat_mitra'])); ?>
		</label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Alamat Kandang</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-9" style="display: inline-block;">
			<?php echo ucwords(strtolower($data['alamat_kdg'])); ?>
		</label>
	</div>
	<br>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-12" style="display: inline-block;"><b>PERFORMA</b></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Panen</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<label class="col-xs-6" style="display: inline-block;"><?php echo angkaDecimal($data['total_tonase_panen']).' Kg'; ?></label>
			<label class="col-xs-6" style="display: inline-block;"><?php echo angkaRibuan($data['total_ekor_panen']).' Ekor'; ?></label>
		</label>
		<label class="col-xs-2" style="display: inline-block;">Bobot Rata-Rata</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-2" style="display: inline-block;"><?php echo angkaDecimal($data['bb']); ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">&nbsp;</label>
		<label style="display: inline-block; width: 1%;">&nbsp;</label>
		<label class="col-xs-3" style="display: inline-block;">&nbsp;</label>
		<label class="col-xs-2" style="display: inline-block;">Deplesi</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-2" style="display: inline-block;"><?php echo angkaDecimal($data['deplesi']).' %'; ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Umur Panen</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<?php echo angkaDecimal($data['rata_umur_panen']); ?>
		</label>
		<label class="col-xs-2" style="display: inline-block;">FCR</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-2" style="display: inline-block;"><?php echo angkaDecimal($data['fcr']); ?></label>
	</div>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-2" style="display: inline-block;">Real Harga Panen</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-3" style="display: inline-block;">
			<?php echo 'Rp. '.angkaDecimal($data['rata_harga_panen']); ?>
		</label>
		<label class="col-xs-2" style="display: inline-block;">IP</label>
		<label style="display: inline-block; width: 1%;">:</label>
		<label class="col-xs-2" style="display: inline-block;"><b><?php echo angkaDecimal($data['ip']); ?></b></label>
	</div>
	<br>
	<br>
	<div style="display: inline; margin: 0px; padding: 0px;">
		<label class="col-xs-12" style="display: inline-block;"><b>ANALISA BIAYA DAN HASIL PRODUKSI</b></label>
	</div>
	<br>
	<?php $total_hp = 0; ?>
	<?php $total_bp = 0; ?>
	<ol style="padding-left: 0px;">
		<li>Hasil Produksi
			<ol style="padding-left: 15px;">
				<?php $idx_hp = 0; ?>
				<?php if ( isset($data['hasil_produksi']) && !empty($data['hasil_produksi']) ) { ?>
					<?php foreach ($data['hasil_produksi'] as $k_hp => $v_hp): ?>
						<?php $idx_hp++; ?>
						<?php $total_hp += $v_hp['total']; ?>
						<li>
							<label style="display: inline-block; width: 11.7%;"><?php echo ucwords(strtolower('Ayam '.$v_hp['jenis_ayam'])); ?></label>
							<label style="display: inline-block; width: 1%;">:</label>
							<label class="col-xs-3" style="display: inline-block; text-align: right;">
								<label class="col-xs-6" style="display: inline-block; text-align: right;"><?php echo angkaDecimal($v_hp['jumlah_ekor']).' Ekor'; ?></label>
								<label class="col-xs-6" style="display: inline-block; text-align: right;"><?php echo angkaDecimal($v_hp['jumlah_kg']).' Kg'; ?></label>
							</label>
							<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_hp['harga']); ?></label>
							<label style="display: inline-block; text-align: right; width: 36.8%;"><?php echo angkaRibuan($v_hp['total']); ?></label>
							<br>
							<?php if ( $idx_hp == (count($data['hasil_produksi'])) ): ?>
								<label style="display: inline-block; text-align: left; width: 79.8%;"><b style="padding-left: 35%;">&nbsp;</b></label>
								<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($total_hp); ?></b></label>
							<?php endif ?>
						</li>
					<?php endforeach ?>
				<?php } else { ?>
					<li>
						<label style="display: inline-block;">TIDAK ADA HASIL PRODUKSI</label>
					</li>
				<?php } ?>
			</ol>
		</li>
		<li>Biaya Produksi
			<ol style="padding-left: 15px;">
				<?php foreach ($data['biaya_produksi']['pakan'] as $k_bpp => $v_bpp): ?>
					<li>
						<label style="display: inline-block; width: 11.7%;">Pakan</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-3" style="display: inline-block; text-align: right;">
							<label class="col-xs-6" style="display: inline-block; text-align: left;"><?php echo $v_bpp['nama']; ?></label>
							<label class="col-xs-6" style="display: inline-block; text-align: right;"><?php echo angkaDecimal($v_bpp['jumlah']).' Kg'; ?></label>
						</label>
						<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
						<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_bpp['harga']); ?></label>
						<label style="display: inline-block; text-align: right; width: 36.8%;"><?php echo angkaRibuan($v_bpp['total']); ?></label>
						<?php $total_bp += $v_bpp['total']; ?>
					</li>
				<?php endforeach ?>
				<li>
					<label style="display: inline-block; width: 11.7%;">DOC</label>
					<label style="display: inline-block; width: 1%;">:</label>
					<label class="col-xs-3" style="display: inline-block; text-align: right;">
						<label class="col-xs-6" style="display: inline-block; text-align: left;"><?php echo $data['biaya_produksi']['doc']['nama']; ?></label>
						<label class="col-xs-6" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['jumlah']).' Ekor'; ?></label>
					</label>
					<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
					<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['harga']); ?></label>
					<label style="display: inline-block; text-align: right; width: 36.8%;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['total']); ?></label>
					<?php $total_bp += $data['biaya_produksi']['doc']['total']; ?>
				</li>
				<li>
					<label style="display: inline-block; width: 11.7%;">Vaksin</label>
					<label style="display: inline-block; width: 1%;">:</label>
					<label class="col-xs-3" style="display: inline-block; text-align: right;">
						<label class="col-xs-6" style="display: inline-block; text-align: left;"><?php echo $data['biaya_produksi']['vaksin']['nama']; ?></label>
						<label class="col-xs-6" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['jumlah']).' Ekor'; ?></label>
					</label>
					<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
					<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['harga']); ?></label>
					<label style="display: inline-block; text-align: right; width: 36.8%;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['total']); ?></label>
					<?php $total_bp += $data['biaya_produksi']['vaksin']['total']; ?>
				</li>
				<li>
					<label style="display: inline-block; width: 11.7%;">OVK</label>
					<label style="display: inline-block; width: 1%;">:</label>
					<label class="col-xs-3" style="display: inline-block; text-align: right;">
						<label class="col-xs-6" style="display: inline-block; text-align: left;">&nbsp;</label>
						<label class="col-xs-6" style="display: inline-block; text-align: right;">&nbsp;</label>
					</label>
					<label class="col-xs-1" style="display: inline-block; text-align: center;">&nbsp;</label>
					<label class="col-xs-1" style="display: inline-block; text-align: right;">&nbsp;</label>
					<label style="display: inline-block; text-align: right; width: 36.8%;"><?php echo angkaRibuan($data['biaya_produksi']['voadip']['total']); ?></label>
					<?php $total_bp += $data['biaya_produksi']['voadip']['total']; ?>
					<br>
					<label style="display: inline-block; text-align: left; width: 79.8%;"><b style="padding-left: 35%;">&nbsp;</b></label>
					<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($total_bp); ?></b></label>
					<br>
				</li>
			</ol>
			<br>
			<table class="border-field" style="width: 97%;">
				<tbody>
					<tr>
						<td class="col-xs-9 text-center"><b>Selisih Hasil Produksi & Biaya Produksi</b></td>
						<td class="col-xs-3 text-right"><b><?php echo angkaRibuan($total_hp - $total_bp); ?></b></td>
					</tr>
				</tbody>
			</table>
			<br>
		</li>
		<li>Bonus dan Biaya Non Produksi
			<ol style="padding-left: 15px;">
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Bonus Pasar <?php echo angkaDecimal($data['prs_bonus_pasar']); ?> %</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['bonus_pasar']); ?></label>
				</li>
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Bonus Kematian</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['bonus_kematian']); ?></label>
				</li>
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Bonus FCR</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['bonus_insentif_fcr']); ?></label>
				</li>
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Insentif Listrik</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['total_bonus_insentif_listrik']); ?></label>
				</li>
				<?php if ( isset($data_plasma['detail']['data_bonus']) && !empty($data_plasma['detail']['data_bonus']) ): ?>
					<?php foreach ($data_plasma['detail']['data_bonus'] as $k_db => $v_db): ?>
						<li>
							<label style="display: inline-block; text-align: left; width: 76.3%;"><?php echo $v_db['keterangan']; ?></label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_db['jumlah']); ?></label>
						</li>
					<?php endforeach ?>
				<?php endif ?>
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Biaya Materai</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo '('.angkaRibuan(abs($data['biaya_materai'])).')'; ?></label>
				</li>
				<?php if ( isset($data_plasma['detail']['data_potongan']) && !empty($data_plasma['detail']['data_potongan']) ): ?>
					<?php foreach ($data_plasma['detail']['data_potongan'] as $k_db => $v_db): ?>
						<li>
							<label style="display: inline-block; text-align: left; width: 76.3%;"><?php echo $v_db['keterangan']; ?></label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo '('.angkaRibuan(abs($v_db['sudah_bayar'])).')'; ?></label>
						</li>
					<?php endforeach ?>
				<?php endif ?>
			</ol>
			<br>
			<table class="border-field" style="width: 97%;">
				<tbody>
					<tr>
						<td class="col-xs-9 text-center"><b>Pendapatan Peternak Sebelum Pajak</b></td>
						<td class="col-xs-3 text-right"><b><?php echo ($data['pdpt_peternak_belum_pajak'] > 0) ? angkaRibuan($data['pdpt_peternak_belum_pajak']) : '('.angkaRibuan(abs($data['pdpt_peternak_belum_pajak'])).')'; ?></b></td>
					</tr>
				</tbody>
			</table>
			<br>
		</li>
		<li>Pajak Peternak
			<ol style="padding-left: 15px;">
				<li>
					<label style="display: inline-block; text-align: left; width: 76.3%;">Potongan Pajak <?php echo angkaDecimal($data['prs_potongan_pajak']); ?> % (PPh Pasal 23)</label>
					<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['potongan_pajak']); ?></label>
				</li>
			</ol>
			<br>
			<table class="border-field" style="width: 97%;">
				<tbody>
					<tr>
						<td class="col-xs-9 text-center"><b>Pendapatan Peternak Setelah Pajak</b></td>
						<td class="col-xs-3 text-right"><b><?php echo ($data['pdpt_peternak_sudah_pajak'] > 0) ? angkaRibuan($data['pdpt_peternak_sudah_pajak']) : '('.angkaRibuan(abs($data['pdpt_peternak_sudah_pajak'])).')'; ?></b></td>
					</tr>
				</tbody>
			</table>
			<br>
		</li>
		<?php if ( $data['total_bayar_hutang'] > 0 ) { ?>
			<li>Hutang Peternak
				<ol style="padding-left: 15px;">
					<li>
						<label style="display: inline-block; text-align: left; width: 76.3%;">Bayar Hutang Peternak</label>
						<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['total_bayar_hutang']); ?></label>
					</li>
				</ol>
				<br>
				<table class="border-field" style="width: 97%;">
					<tbody>
						<tr>
							<td class="col-xs-9 text-center"><b>Pendapatan Peternak Setelah Potong Hutang</b></td>
							<td class="col-xs-3 text-right"><b><?php echo angkaRibuan($data['pdpt_peternak_sudah_potong_hutang']); ?></b></td>
						</tr>
					</tbody>
				</table>
				<br>
			</li>
		<?php } ?>
		<label><b>Catatan : <?php echo $data['catatan']; ?></b></label>
	</ol>
	<br>
	<hr>
	<table style="width: 100%; margin-bottom: 0px;">
		<tbody>
			<tr>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Dibuat,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['user_cetak']); ?></b></div>
				</td>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Peternak,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['mitra']); ?></b></div>
				</td>
				<td class="col-xs-6" align="center">
					<div class="col-xs-12"><?php echo $data['unit_karyawan'].', '.tglIndonesia($data['tgl_tutup'], '-', ' ', true); ?></div>
					<div class="col-xs-12">Mengetahui,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['kanit']); ?></b></div>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- <ol style="padding-left: 0px;">
		<li>Data Teknis
			<ol>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Nama Pemilik</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower($data['mitra'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Nomor NPWP</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower(npwpFormat($data['npwp']))).'/'.ucwords(strtolower($data['mitra'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Alamat Pemilik</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower($data['alamat_mitra'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Lokasi Kandang</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower($data['alamat_kdg'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Unit</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower($data['unit'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">PPL</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo ucwords(strtolower($data['ppl'])); ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Periode Pemeliharaan</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<label class="col-xs-12" style="display: inline-block;"><?php echo (int) substr($data['noreg'], 7, 2); ?></label>
							<br>
							<label class="col-xs-12" style="display: inline-block;">
								<label class="col-xs-1" style="display: inline-block;">Tanggal</label>
								<label class="col-xs-3" style="display: inline-block; text-align: center;">
									<?php echo tglIndonesia($data['tgl_docin']); ?>
								</label>
								<label class="col-xs-1" style="display: inline-block; text-align: center;">s/d</label>
								<label class="col-xs-3" style="display: inline-block; text-align: center;">
									<?php echo tglIndonesia($data['tgl_selesai_panen']); ?>
								</label>
								<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data_plasma['detail']['data_doc']['doc']['box']).' Box'; ?></label>
							</label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Pemakaian Pakan</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php foreach ($data['data_pemakaian_pakan'] as $k_dpp => $v_dpp): ?>
								<label class="col-xs-2" style="display: inline-block;">Jenis</label>
								<label class="col-xs-10" style="display: inline-block;"><?php echo $v_dpp['nama']; ?></label>
								<br>
								<label class="col-xs-2" style="display: inline-block;">Jumlah</label>
								<label class="col-xs-3" style="display: inline-block;"><?php echo angkaRibuan($v_dpp['zak']).' Zak'; ?></label>
								<label class="col-xs-3" style="display: inline-block;"><?php echo angkaDecimal($v_dpp['jumlah']).' Kg'; ?></label>
								<br>
								<br>
							<?php endforeach ?>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Umur Panen</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaDecimal($data['rata_umur_panen']); ?>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Deplesi</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaDecimal($data['deplesi']); ?>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">FCR</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaDecimal($data['fcr']); ?>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Rata2 Bobot</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaDecimal($data['bb']); ?>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">IP</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaDecimal($data['ip']); ?>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label class="col-xs-3" style="display: inline-block;">Real Harga Panen</label>
						<label style="display: inline-block; width: 1%;">:</label>
						<label class="col-xs-8" style="display: inline-block;">
							<?php echo angkaRibuan($data['rata_harga_panen']).' Rupiah'; ?>
						</label>
					</div>
				</li>
			</ol>
		</li>
		<li>Hasil
			<ol>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<?php foreach ($data['hasil'] as $k_hasil => $v_hasil): ?>
								<label style="display: inline-block; width: 26.6%;"><?php echo ucwords(strtolower('AYAM '.$v_hasil['jenis_ayam'])); ?></label>
								<label style="display: inline-block; width: 1%;">:</label>
								<label class="col-xs-8" style="display: inline-block;">
									<label class="col-xs-3" style="display: inline-block; text-align: right;">
										<?php echo angkaDecimal($v_hasil['jumlah_kg']).' Kg'; ?>
									</label>
									<label class="col-xs-3" style="display: inline-block; text-align: right;">
										<?php echo angkaRibuan($v_hasil['jumlah_ekor']).' Ekor'; ?>
									</label>
								</label>
								<br>
							<?php endforeach ?>
						</label>
					</div>
				</li>
			</ol>
		</li>
		<li>Analisa Biaya Produksi & Hasil Produksi
			<ol>
				<?php $total_bp = 0; ?>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label class="col-xs-12" style="display: inline-block;">Biaya Produksi</label>
							<br>
							<?php foreach ($data['biaya_produksi']['pakan'] as $k_bpp => $v_bpp): ?>
								<label style="display: inline-block; width: 9.5%;">Pakan</label>
								<label class="col-xs-2" style="display: inline-block;"><?php echo $v_bpp['nama']; ?></label>
								<label style="display: inline-block; width: 1%;">:</label>
								<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_bpp['jumlah']); ?></label>
								<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
								<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_bpp['harga']); ?></label>
								<label class="col-xs-5" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_bpp['total']); ?></label>
								<br>
								<?php $total_bp += $v_bpp['total']; ?>
							<?php endforeach ?>
							<label style="display: inline-block; width: 9.5%;">DOC</label>
							<label class="col-xs-2" style="display: inline-block;"><?php echo $data['biaya_produksi']['doc']['nama']; ?></label>
							<label style="display: inline-block; width: 1%;">:</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['jumlah']); ?></label>
							<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['harga']); ?></label>
							<label class="col-xs-5" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['doc']['total']); ?></label>
							<?php $total_bp += $data['biaya_produksi']['doc']['total']; ?>
							<br>
							<label style="display: inline-block; width: 9.5%;">Vaksin</label>
							<label class="col-xs-2" style="display: inline-block;"><?php echo $data['biaya_produksi']['vaksin']['nama']; ?></label>
							<label style="display: inline-block; width: 1%;">:</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['jumlah']); ?></label>
							<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['harga']); ?></label>
							<label class="col-xs-5" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['vaksin']['total']); ?></label>
							<?php $total_bp += $data['biaya_produksi']['vaksin']['total']; ?>
							<br>
							<label style="display: inline-block; width: 9.5%;">OVK</label>
							<label class="col-xs-2" style="display: inline-block;">&nbsp;</label>
							<label style="display: inline-block; width: 1%;">:</label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;">&nbsp;</label>
							<label class="col-xs-1" style="display: inline-block; text-align: center;"></label>
							<label class="col-xs-1" style="display: inline-block; text-align: right;">&nbsp;</label>
							<label class="col-xs-5" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_produksi']['voadip']['total']); ?></label>
							<?php $total_bp += $data['biaya_produksi']['voadip']['total']; ?>
							<br>
							<label style="display: inline-block; text-align: left; width: 79.8%;"><b style="padding-left: 35%;">&nbsp;</b></label>
							<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($total_bp); ?></b></label>
						</label>
					</div>
				</li>
				<?php $total_hp = 0; ?>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label class="col-xs-12" style="display: inline-block;">Hasil Produksi</label>
							<br>
							<?php foreach ($data['hasil_produksi'] as $k_hp => $v_hp): ?>
								<label style="display: inline-block; width: 26.2%;"><?php echo ucwords(strtolower('Ayam '.$v_hp['jenis_ayam'])); ?></label>
								<label style="display: inline-block; width: 1%;">:</label>
								<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaDecimal($v_hp['jumlah_kg']); ?></label>
								<label class="col-xs-1" style="display: inline-block; text-align: center;">x</label>
								<label class="col-xs-1" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_hp['harga']); ?></label>
								<label class="col-xs-5" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($v_hp['total']); ?></label>
								<br>
								<?php $total_hp += $v_hp['total']; ?>
							<?php endforeach ?>
							<label style="display: inline-block; text-align: left; width: 79.8%;"><b style="padding-left: 35%;">&nbsp;</b></label>
							<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($total_hp); ?></b></label>
						</label>
						<label style="display: inline-block; width: 94%;">&nbsp;</label>
					</div>
				</li>
				<?php $selisih_hp = $total_hp - $total_bp; ?>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Selisih Hasil Produksi & Biaya Produksi</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($selisih_hp); ?></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Bonus FCR</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['bonus_insentif_fcr']); ?></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Bonus Kematian</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['bonus_kematian']); ?></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Pendapatan Peternak Sebelum Pajak Dan Materai</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($data['pdpt_peternak_belum_pajak_dan_materai']); ?></b></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Biaya Materai</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['biaya_materai']); ?></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Pendapatan Peternak Sebelum Pajak</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($data['pdpt_peternak_belum_pajak']); ?></b></label>
						</label>
					</div>
				</li>
				<li>&nbsp;&nbsp;
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Potongan Pajak <?php echo angkaDecimal($data['prs_potongan_pajak']); ?> % (PPh Pasal 23)</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right;"><?php echo angkaRibuan($data['potongan_pajak']); ?></label>
						</label>
					</div>
				</li>
				<li>
					<div style="display: inline; margin: 0px; padding: 0px;">
						<label style="display: inline-block; width: 94%;">
							<label style="display: inline-block; width: 79.8%;">Pendapatan Peternak Setelah Kena Pajak</label>
							<label class="col-xs-2" style="display: inline-block; text-align: right; border-top: 1px solid black;"><b><?php echo angkaRibuan($data['pdpt_peternak_sudah_pajak']); ?></b></label>
							<br>
							<label style="display: inline-block; width: 100%;"><b>Catatan : -</b></label>
						</label>
					</div>
				</li>
			</ol>
		</li>
	</ol>
	<br>
	<table style="width: 100%; margin-bottom: 0px;">
		<tbody>
			<tr>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Dibuat,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['user_cetak']); ?></b></div>
				</td>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Peternak,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['mitra']); ?></b></div>
				</td>
				<td class="col-xs-6" align="center">
					<div class="col-xs-12">Jember, 19 April 2023</div>
					<div class="col-xs-12">Mengetahui,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['kanit']); ?></b></div>
				</td>
			</tr>
		</tbody>
	</table> -->
</div>
<div class="contain" style="width: 100%;">
	<label style="text-decoration: underline;"><b>DETAIL TRANSAKSI</b></label>
	<br>
	<br>
	<table class="col-xs-12 border-field">
		<thead>
			<tr class="head">
				<td colspan="7" style="border: none;"><b>DOC</b></td>
			</tr>
			<tr>
				<th class="col-xs-2 text-center">Tanggal</th>
				<th class="col-xs-2 text-center">Nota / SJ</th>
				<th class="col-xs-3 text-center">Barang</th>
				<th class="col-xs-1 text-center">Box / Sak</th>
				<th class="col-xs-1 text-center">Jumlah</th>
				<th class="col-xs-1 text-center">Harga</th>
				<th class="col-xs-2 text-center">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php $total_pemakaian = 0; ?>
			<?php if ( !empty($data_plasma['detail']['data_doc']) ): ?>
				<?php 
					$data_doc = $data_plasma['detail']['data_doc']['doc'];
					$data_vaksin = $data_plasma['detail']['data_doc']['vaksin'];
					$total_box = 0;
					$total_jumlah = 0;
					$total_nilai = 0;
				?>
				<?php if ( !empty($data_doc) ): ?>
        			<tr class="data_doc">
        				<td class="text-left"><?php echo tglIndonesia($data_doc['tgl_docin'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo $data_doc['sj']; ?></td>
        				<td><?php echo $data_doc['barang']; ?></td>
        				<td class="text-right"><?php echo angkaRibuan($data_doc['box']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($data_doc['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($data_doc['harga']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($data_doc['total']); ?></td>
        			</tr>
        			<?php
        				$total_box += $data_doc['box'];
    					$total_jumlah += $data_doc['jumlah'];
    					$total_nilai += $data_doc['total'];
        			?>
				<?php endif ?>
				<?php if ( !empty($data_vaksin) ): ?>
        			<tr class="data_vaksin">
        				<td colspan="2"></td>
        				<td><?php echo $data_vaksin['barang']; ?></td>
        				<td colspan="2"></td>
        				<td class="text-right"><?php echo angkaRibuan($data_vaksin['harga']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($data_vaksin['total']); ?></td>
        			</tr>
        			<?php
    					$total_nilai += $data_vaksin['total'];
        			?>
				<?php endif ?>
				<tr>
					<td class="text-right" colspan="3"><b>TOTAL</b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_box); ?></b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_jumlah) ?></b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaRibuan($total_nilai); ?></b></td>
				</tr>
				<?php $total_nilai_doc = $total_nilai; ?>
			<?php endif ?>
		</tbody>
	</table>
	<br>
	<table class="col-xs-12 border-field">
		<thead>
			<tr class="head">
				<td colspan="7" style="border: none;"><b>PAKAN</b></td>
			</tr>
			<tr>
				<th class="col-xs-2 text-center">Tanggal</th>
				<th class="col-xs-2 text-center">Nota / SJ</th>
				<th class="col-xs-3 text-center">Barang</th>
				<th class="col-xs-1 text-center">Box / Sak</th>
				<th class="col-xs-1 text-center">Jumlah</th>
				<th class="col-xs-1 text-center">Harga</th>
				<th class="col-xs-2 text-center">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php $total_pemakaian_zak = 0; ?>
			<?php $total_pemakaian_jumlah = 0; ?>
			<?php $total_pemakaian_nilai = 0; ?>
			<tr class="head">
				<td colspan="7"><b>PENGIRIMAN PAKAN</b></td>
			</tr>
			<?php if ( !empty($data_plasma['detail']['data_pakan']) ): ?>
				<?php 
					$data_pakan = $data_plasma['detail']['data_pakan']; 
					$total_zak = 0;
					$total_jumlah = 0;
					$total_nilai = 0;
				?>
				<?php foreach ($data_pakan as $k => $val): ?>
					<tr class="data_pakan">
        				<td class="text-left"><?php echo tglIndonesia($val['tanggal'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo empty($val['sj']) ? '-' : $val['sj']; ?></td>
        				<td ><?php echo $val['barang']; ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['zak']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['harga']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['total']); ?></td>
        			</tr>
        			<?php
        				$total_zak += $val['zak'];
    					$total_jumlah += $val['jumlah'];
    					$total_nilai += $val['total'];
        			?>
        			<?php $total_pemakaian_zak += $val['zak']; ?>
        			<?php $total_pemakaian_jumlah += $val['jumlah']; ?>
        			<?php $total_pemakaian_nilai += $val['total']; ?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="3"><b>TOTAL PENGIRIMAN</b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_zak); ?></b></td>
					<td class="text-right"><b><?php echo angkaDecimal($total_jumlah) ?></b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaRibuan($total_nilai); ?></b></td>
				</tr>
				<?php 
					$total_nilai_pakan = $total_nilai; 
					$total_jumlah_pakan = $total_jumlah;
				?>
			<?php endif ?>

			<?php if ( !empty($data_plasma['detail']['data_pindah_pakan']) ): ?>
				<tr class="head">
					<td colspan="7"><b>PINDAH PAKAN</b></td>
				</tr>
				<?php 
					$data_pindah_pakan = $data_plasma['detail']['data_pindah_pakan']; 
					$total_zak = 0;
					$total_jumlah = 0;
					$total_nilai = 0;
				?>
				<?php foreach ($data_pindah_pakan as $k => $val): ?>
					<tr class="data_pindah_pakan">
        				<td class="text-left"><?php echo tglIndonesia($val['tanggal'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo empty($val['sj']) ? '-' : $val['sj']; ?></td>
        				<td ><?php echo $val['barang']; ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['zak']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['harga']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['total']); ?></td>
        			</tr>
        			<?php
        				$total_zak += $val['zak'];
    					$total_jumlah += $val['jumlah'];
    					$total_nilai += $val['total'];
        			?>
        			<?php $total_pemakaian_zak -= $val['zak']; ?>
        			<?php $total_pemakaian_jumlah -= $val['jumlah']; ?>
        			<?php $total_pemakaian_nilai -= $val['total']; ?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="3"><b>TOTAL PINDAH PAKAN</b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_zak); ?></b></td>
					<td class="text-right"><b><?php echo angkaDecimal($total_jumlah) ?></b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaRibuan($total_nilai); ?></b></td>
				</tr>
				<?php 
					$total_nilai_pakan -= $total_nilai; 
					$total_jumlah_pakan -= $total_jumlah;
				?>
			<?php endif ?>

			<?php if ( !empty($data_plasma['detail']['data_retur_pakan']) ): ?>
				<tr class="head">
					<td colspan="7"><b>RETUR PAKAN</b></td>
				</tr>
				<?php 
					$data_retur_pakan = $data_plasma['detail']['data_retur_pakan'];
					$total_zak = 0;
					$total_jumlah = 0;
					$total_nilai = 0;
				?>
				<?php foreach ($data_retur_pakan as $k => $val): ?>
					<tr class="data_retur_pakan">
        				<td class="text-left"><?php echo tglIndonesia($val['tanggal'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo $val['nota']; ?></td>
        				<td ><?php echo $val['barang']; ?></td>
        				<?php $zak = ($val['jumlah'] > 0) ? $val['jumlah']/50 : 0; ?>
        				<td class="text-right"><?php echo angkaRibuan($zak); ?></td>
        				<td class="text-right"><?php echo angkaDecimal($val['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['harga']); ?></td>
        				<td class="text-right"><?php echo angkaRibuan($val['total']); ?></td>
        			</tr>
        			<?php $total_zak += $zak; ?>
        			<?php $total_nilai += $val['total']; ?>
        			<?php $total_jumlah += $val['jumlah']; ?>

        			<?php $total_pemakaian_zak -= $zak; ?>
					<?php $total_pemakaian_jumlah -= $val['jumlah']; ?>
					<?php $total_pemakaian_nilai -= $val['total']; ?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="3"><b>TOTAL RETUR</b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_zak); ?></b></td>
					<td class="text-right"><b><?php echo angkaDecimal($total_jumlah) ?></b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaRibuan($total_nilai); ?></b></td>
				</tr>
				<?php $total_nilai_pakan -= $total_nilai; ?>
				<?php $total_jumlah_pakan -= $total_jumlah; ?>

			<?php endif ?>
			<tr>
				<td class="text-right" colspan="3"><b>TOTAL PEMAKAIAN</b></td>
				<td class="text-right"><b><?php echo angkaRibuan($total_pemakaian_zak); ?></b></td>
				<td class="text-right"><b><?php echo angkaDecimal($total_pemakaian_jumlah) ?></b></td>
				<td class="text-right" colspan="2"><b><?php echo angkaRibuan($total_pemakaian_nilai); ?></b></td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="col-xs-12 border-field">
		<thead>
			<tr class="head">
				<td colspan="6" style="border: none;"><b>OVK</b></td>
			</tr>
			<tr>
				<th class="col-xs-2 text-center">Tanggal</th>
				<th class="col-xs-2 text-center">Nota / SJ</th>
				<th class="col-xs-4 text-center">Barang</th>
				<th class="col-xs-1 text-center">Jumlah</th>
				<th class="col-xs-1 text-center">Harga</th>
				<th class="col-xs-2 text-center">Total</th>
			</tr>
		</thead>
		<tbody>
			<tr class="head">
				<td colspan="6"><b>PENGIRIMAN OVK</b></td>
			</tr>
			<?php if ( !empty($data_plasma['detail']['data_voadip']) ): ?>
				<?php 
					$data_voadip = $data_plasma['detail']['data_voadip'];
					$total_nilai = 0;
				?>
				<?php foreach ($data_voadip as $k => $val): ?>
					<tr class="data_voadip">
        				<td class="text-left"><?php echo tglIndonesia($val['tanggal'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo $val['sj']; ?></td>
        				<td ><?php echo $val['barang']; ?></td>
        				<td class="text-right"><?php echo angkaDecimal($val['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaDecimalFormat($val['harga'], $val['decimal']); ?></td>
        				<td class="text-right"><?php echo angkaDecimal($val['total']); ?></td>
        			</tr>
        			<?php $total_nilai += $val['total']; ?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="4"><b>TOTAL PENGIRIMAN</b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaDecimal($total_nilai); ?></b></td>
				</tr>
				<?php $total_pemakaian += $total_nilai; ?>
			<?php endif ?>

			<?php if ( !empty($data_plasma['detail']['data_retur_voadip']) ): ?>
				<tr class="head">
					<td colspan="6"><b>RETUR OVK</b></td>
				</tr>
				<?php 
					$data_retur_voadip = $data_plasma['detail']['data_retur_voadip'];
					$total_nilai = 0;
				?>
				<?php foreach ($data_retur_voadip as $k => $val): ?>
					<tr class="data_retur_voadip">
        				<td class="text-left"><?php echo tglIndonesia($val['tanggal'], '-', ' '); ?></td>
        				<td class="text-center"><?php echo $val['no_retur']; ?></td>
        				<td><?php echo $val['barang']; ?></td>
        				<td class="text-right"><?php echo angkaDecimal($val['jumlah']); ?></td>
        				<td class="text-right"><?php echo angkaDecimalFormat($val['harga'], $val['decimal']); ?></td>
        				<td class="text-right"><?php echo angkaDecimal($val['total']); ?></td>
        			</tr>
        			<?php $total_nilai += $val['total']; ?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="4"><b>TOTAL RETUR</b></td>
					<td class="text-right" colspan="2"><b><?php echo angkaDecimal($total_nilai); ?></b></td>
				</tr>
				<?php $total_pemakaian -= $total_nilai; ?>
			<?php endif ?>

			<tr>
				<td class="text-right" colspan="4"><b>TOTAL PEMAKAIAN</b></td>
				<td class="text-right" colspan="2"><b><?php echo angkaDecimal($total_pemakaian); ?></b></td>
			</tr>
		</tbody>
	</table>
	<?php 
		$total_ekor = 0;
		$total_tonase = 0;
		$total_nilai_kontrak = 0;
		$total_nilai_pasar = 0;
	?>
	<br>
	<table class="col-xs-12 border-field" style="font-size: 8px;">
		<thead>
			<tr class="head">
				<td colspan="11" style="border: none; font-size: 10pt;"><b>PENJUALAN AYAM</b></td>
			</tr>
			<tr>
				<th class="col-xs-1 text-center">Tanggal</th>
				<th class="col-xs-1 text-center">DO</th>
				<th class="col-xs-2 text-center">Pembeli</th>
				<th class="col-xs-1 text-center">Ket</th>
				<th class="col-xs-1 text-center">Ekor</th>
				<th class="col-xs-1 text-center">Tonase</th>
				<th class="col-xs-1 text-center">BB</th>
				<th class="col-xs-1 text-center">Kontrak</th>
				<th class="col-xs-1 text-center">Total</th>
				<th class="col-xs-1 text-center">Hrg Pasar</th>
				<th class="col-xs-1 text-center">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( !empty($data_plasma['detail']['data_rpah']) ): ?>
				<?php $data_rpah = $data_plasma['detail']['data_rpah']; ?>
				<?php foreach ($data_rpah as $k_dr => $v_dr): ?>
					<tr class="data_penjualan">
						<td><?php echo tglIndonesia($v_dr['tanggal'], '-', ' '); ?></td>
						<td class="text-center"><?php echo $v_dr['do'] ?></td>
						<td><?php echo $v_dr['pembeli'] ?></td>
						<td><?php echo '-'; ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_dr['ekor']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_dr['tonase']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_dr['bb']); ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_dr['hrg_kontrak']); ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_dr['total_kontrak']); ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_dr['hrg_pasar']); ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_dr['total_pasar']); ?></td>
					</tr>
					<?php
						$total_ekor += $v_dr['ekor'];
						$total_tonase += $v_dr['tonase'];
						$total_nilai_kontrak += $v_dr['total_kontrak'];
						$total_nilai_pasar += $v_dr['total_pasar'];
					?>
				<?php endforeach ?>
				<tr>
					<td class="text-right" colspan="4"><b>TOTAL</b></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_ekor); ?></b></td>
					<td class="text-right"><b><?php echo angkaDecimal($total_tonase); ?></b></td>
					<td colspan="2"></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_nilai_kontrak); ?></b></td>
					<td></td>
					<td class="text-right"><b><?php echo angkaRibuan($total_nilai_pasar); ?></b></td>
				</tr>
			<?php else: ?>
				<tr>
					<td colspan="11">Data tidak ditemukan.</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
	<br>
	<?php
		$tot_pemasukan = 0;
		$tot_pengeluaran = 0;
	?>
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td class="col-xs-6">
					<table class="col-xs-12" style="font-size: 10pt;">
						<thead>
							<tr class="head">
								<td colspan="3" style="border: none;"><b>REKAPITULASI PETERNAK</b></td>
							</tr>
						</thead>
						<tbody>
							<tr class="top">
								<td class="col-xs-6 kiri kanan">Penjualan Ayam</td>
								<td class="col-xs-3 text-right kanan tot_penjualan_ayam"><?php echo angkaRibuan($data['tot_penjualan_ayam']); ?></td>
								<td class="col-xs-3 text-right kanan">-</td>
							</tr>
							<tr>
								<td class="col-xs-6 kiri kanan">Total Pembelian Sapronak</td>
								<td class="col-xs-3 text-right kanan">-</td>
								<td class="col-xs-3 text-right kanan tot_pembelian_sapronak"><?php echo angkaRibuan($data['tot_pembelian_sapronak']); ?></td>
							</tr>
							<tr>
								<td class="col-xs-6 kiri kanan">Biaya Materai</td>
								<td class="col-xs-3 text-right kanan">-</td>
								<td class="col-xs-3 text-right kanan">
									<span class="biaya_materai"><?php echo angkaRibuan($data['biaya_materai']); ?></span>
								</td>
							</tr>
							<tr>
								<td class="col-xs-6 kiri kanan persen_bonus_pasar">Bonus Pasar <?php echo angkaRibuan($data['prs_bonus_pasar']) ?>%</td>
								<td class="col-xs-3 text-right kanan bonus_pasar"><?php echo angkaRibuan($data['bonus_pasar']); ?></td>
								<td class="col-xs-3 text-right kanan">-</td>
							</tr>
							<tr>
								<td class="col-xs-6 kiri kanan">Bonus Kematian</td>
								<td class="col-xs-3 text-right kanan bonus_kematian"><?php echo angkaRibuan($data['bonus_kematian']); ?></td>
								<td class="col-xs-3 text-right kanan">-</td>
							</tr>
							<tr>
								<td class="col-xs-6 kiri kanan">Bonus Insentif FCR</td>
								<td class="col-xs-3 text-right kanan bonus_insentif_fcr"><?php echo angkaRibuan($data['bonus_insentif_fcr']); ?></td>
								<td class="col-xs-3 text-right kanan">-</td>
							</tr>
							<tr class="bottom">
								<td class="col-xs-6 kiri kanan">Bonus Insentif Listrik</td>
								<td class="col-xs-3 text-right kanan bonus_insentif_listrik"><?php echo angkaRibuan($data['total_bonus_insentif_listrik']); ?></td>
								<td class="col-xs-3 text-right kanan">-</td>
							</tr>
							<tr class="bottom">
								<?php 
									$tot_pemasukan = $data['tot_penjualan_ayam'] + $data['bonus_pasar'] + $data['bonus_kematian'] + $data['bonus_insentif_fcr'] + $data['total_bonus_insentif_listrik'];
									$tot_pengeluaran = $data['tot_pembelian_sapronak'] + $data['biaya_materai'];
								?>

								<td class="col-xs-6 kiri kanan text-right" style="border-top: 1px solid black;"><b>TOTAL</b></td>
								<td class="col-xs-3 text-right kanan total_pemasukan" style="border-top: 1px solid black;"><b><?php echo angkaRibuan($tot_pemasukan); ?></b></td>
								<td class="col-xs-3 text-right kanan total_pengeluaran" style="border-top: 1px solid black;"><b><?php echo angkaRibuan($tot_pengeluaran); ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td class="col-xs-1"></td>
				<td class="col-xs-4" style="vertical-align: top;">
					<table class="col-xs-12" style="font-size: 10pt;">
						<thead>
							<tr class="head">
								<td colspan="2" style="border: none;"><b>PERFORMANCE PETERNAK</b></td>
							</tr>
						</thead>
						<tbody>
							<tr class="top">
								<td class="col-xs-8 kiri">Jumlah Panen (Ekor)</td>
								<td class="col-xs-4 kanan text-right"><?php echo angkaRibuan($total_ekor); ?></td>
							</tr>
							<tr>
								<td class="col-xs-8 kiri">Berat Badan (Kg)</td>
								<td class="col-xs-4 kanan text-right"><?php echo angkaDecimal($total_tonase); ?></td>
							</tr>
							<tr>
								<td class="col-xs-8 kiri">BB Rata-Rata / Ekor (Kg)</td>
								<td class="col-xs-4 kanan text-right"><?php echo ($total_tonase > 0 && $total_ekor > 0) ? angkaDecimal($total_tonase / $total_ekor) : 0; ?></td>
							</tr>
							<tr>
								<td class="col-xs-8 kiri">FCR</td>
								<td class="col-xs-4 kanan text-right"><?php echo angkaDecimal($data['fcr']); ?></td>
							</tr>
							<tr>
								<td class="col-xs-8 kiri">Deplesi</td>
								<td class="col-xs-4 kanan text-right"><?php echo angkaDecimal($data['deplesi']); ?></td>
							</tr>
							<tr>
								<td class="col-xs-8 kiri">Rata-Rata Umur</td>
								<td class="col-xs-4 kanan text-right"><?php echo angkaDecimalFormat($data['rata_umur_panen'], 2); ?></td>
							</tr>
							<tr class="bottom">
								<td class="col-xs-8 kiri"><b>IP</b></td>
								<td class="col-xs-4 kanan text-right"><b><?php echo angkaDecimal($data['ip']); ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align: top;">
					<br>
					<table class="col-xs-12" style="font-size: 10pt;">
						<tr>
							<td class="col-xs-5" style="vertical-align: top;">
								<table class="col-xs-12" style="font-size: 10pt;">
									<?php $total_potongan = 0; ?>
									<?php if ( isset($data_plasma['detail']['data_potongan']) && !empty($data_plasma['detail']['data_potongan']) ) { ?>
										<thead>
											<tr class="head">
												<td colspan="2" style="border: none;"><b>POTONGAN PETERNAK</b></td>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($data_plasma['detail']['data_potongan'] as $k_dpotongan => $v_dpotongan) { ?>
												<tr>
													<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper($v_dpotongan['keterangan']); ?></td>
													<td class="text-right" style="border: 1px solid black; border-width: thin;"><?php echo angkaRibuan($v_dpotongan['sudah_bayar']); ?></td>
												</tr>
												<?php $total_potongan += $v_dpotongan['sudah_bayar']; ?>
											<?php } ?>
											<tr>
												<td class="text-right" style="border: 1px solid black; border-width: thin;"><b>TOTAL</b></td>
												<td class="text-right" style="border: 1px solid black; border-width: thin;"><b><?php echo angkaRibuan($total_potongan); ?></b></td>
											</tr>
										</tbody>
									<?php } else { ?>
										<thead>
											<tr class="head">
												<td style="border: none; vertical-align: top;"><b>POTONGAN PETERNAK</b></td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-left" style="border: 1px solid black; border-width: thin; vertical-align: top;">TIDAK ADA POTONGAN.</td>
											</tr>
										</tbody>
									<?php } ?>
								</table>
							</td>
							<td class="col-xs-1"></td>
							<td class="col-xs-5" style="vertical-align: top;">
								<table class="col-xs-12" style="font-size: 10pt;">
									<?php $total_bonus = 0; ?>
									<?php if ( isset($data_plasma['detail']['data_bonus']) && !empty($data_plasma['detail']['data_bonus']) ) { ?>
										<thead>
											<tr class="head">
												<td colspan="2" style="border: none;"><b>BONUS TAMBAHAN PETERNAK</b></td>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($data_plasma['detail']['data_bonus'] as $k_dbonus => $v_dbonus) { ?>
												<tr>
													<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper($v_dbonus['keterangan']); ?></td>
													<td class="text-right" style="border: 1px solid black; border-width: thin;"><?php echo angkaRibuan($v_dbonus['jumlah']); ?></td>
												</tr>
												<?php $total_bonus += $v_dbonus['jumlah']; ?>
											<?php } ?>
											<tr>
												<td class="text-right" style="border: 1px solid black; border-width: thin;"><b>TOTAL</b></td>
												<td class="text-right" style="border: 1px solid black; border-width: thin;"><b><?php echo angkaRibuan($total_bonus); ?></b></td>
											</tr>
										</tbody>
									<?php } else { ?>
										<thead>
											<tr class="head">
												<td style="border: none;"><b>BONUS TAMBAHAN PETERNAK</b></td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td style="border: 1px solid black; border-width: thin;">TIDAK ADA BONUS.</td>
											</tr>
										</tbody>
									<?php } ?>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php if ( $data['total_bayar_hutang'] > 0 ) { ?>
				<tr>
					<td colspan="3">
						<br>
						<table class="col-xs-12" style="font-size: 10pt;">
							<thead>
								<tr class="head">
									<td colspan="6" style="border: none;"><b>HUTANG PLASMA</b></td>
								</tr>
								<tr>
									<td style="border: 1px solid black; border-width: thin;"><b>Perusahaan</b></td>
									<td style="border: 1px solid black; border-width: thin;"><b>Tanggal</b></td>
									<td style="border: 1px solid black; border-width: thin;"><b>Kode</b></td>
									<td style="border: 1px solid black; border-width: thin;"><b>Keterangan</b></td>
									<td style="border: 1px solid black; border-width: thin;"><b>Sisa Hutang (Rp.)</b></td>
									<td style="border: 1px solid black; border-width: thin;"><b>Bayar (Rp.)</b></td>
								</tr>
							</thead>
							<tbody>
							<?php $tot_hutang = 0; ?>
								<?php foreach ($data_plasma['detail']['data_piutang_plasma'] as $k_dpp => $v_dpp) { ?>
									<tr>
										<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper($v_dpp['nama_perusahaan']); ?></td>
										<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper(tglIndonesia($v_dpp['tanggal'], '-', ' ')); ?></td>
										<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper($v_dpp['kode']); ?></td>
										<td style="border: 1px solid black; border-width: thin;"><?php echo strtoupper($v_dpp['keterangan']); ?></td>
										<td class="text-right" style="border: 1px solid black; border-width: thin;"><?php echo angkaRibuan($v_dpp['sisa_piutang']); ?></td>
										<td class="text-right" style="border: 1px solid black; border-width: thin;"><?php echo angkaRibuan($v_dpp['nominal']); ?></td>
									</tr>
									<?php $tot_hutang += $v_dpp['sisa_piutang']; ?>
								<?php } ?>
								<tr>
									<td colspan="4" class="text-right" style="border: 1px solid black; border-width: thin;"><b>Total</b></td>
									<td class="text-right" style="border: 1px solid black; border-width: thin;"><b><?php echo angkaRibuan($tot_hutang); ?></b></td>
									<td class="text-right" style="border: 1px solid black; border-width: thin;"><b><?php echo angkaRibuan($data['total_bayar_hutang']); ?></b></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="col-xs-6">
					<br>
					<br>
					<table class="col-xs-12" style="font-size: 10pt;">
						<tbody>
							<tr>
								<td class="col-xs-9"><b>Pendapatan Peternak Sebelum Pajak</b></td>
								<td class="col-xs-3 text-right"><b><?php echo ($data['pdpt_peternak_belum_pajak'] > 0) ? angkaRibuan($data['pdpt_peternak_belum_pajak']) : '('.angkaRibuan(abs($data['pdpt_peternak_belum_pajak'])).')'; ?></b></td>
							</tr>
						</tbody>
					</table>
					<table class="col-xs-12" style="font-size: 10pt;">
						<tbody>
							<tr>
								<td class="col-xs-9">Potongan Pajak <?php echo angkaDecimal($data['prs_potongan_pajak']); ?> % (PPh Pasal 23)</td>
								<td class="col-xs-3 text-right"><?php echo angkaRibuan($data['potongan_pajak']); ?></td>
							</tr>
						</tbody>
					</table>
					<table class="col-xs-12" style="font-size: 10pt;">
						<tbody>
							<tr>
								<td class="col-xs-9"><b>Pendapatan Peternak Setelah Kena Pajak</b></td>
								<td class="col-xs-3 text-right"><b><?php echo ($data['pdpt_peternak_sudah_pajak'] > 0) ? angkaRibuan($data['pdpt_peternak_sudah_pajak']) : '('.angkaRibuan(abs($data['pdpt_peternak_sudah_pajak'])).')'; ?></b></td>
							</tr>
						</tbody>
					</table>
					<?php if ( $data['total_bayar_hutang'] > 0 ) {  ?>
						<table class="col-xs-12" style="font-size: 10pt;">
							<tbody>
								<tr>
									<td class="col-xs-9"><b>Pendapatan Peternak Setelah Potong Hutang</b></td>
									<td class="col-xs-3 text-right"><b><?php echo angkaRibuan($data['pdpt_peternak_sudah_potong_hutang']); ?></b></td>
								</tr>
							</tbody>
						</table>
					<?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
	<hr>
	<table style="width: 100%; margin-bottom: 0px;">
		<tbody>
			<tr>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Dibuat,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['user_cetak']); ?></b></div>
				</td>
				<td class="col-xs-3" align="center">
					<div class="col-xs-12">&nbsp;</div>
					<div class="col-xs-12">Peternak,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['mitra']); ?></b></div>
				</td>
				<td class="col-xs-6" align="center">
					<div class="col-xs-12"><?php echo $data['unit_karyawan'].', '.tglIndonesia($data['tgl_tutup'], '-', ' ', true); ?></div>
					<div class="col-xs-12">Mengetahui,</div>
					<div class="col-xs-12">
						<br>
						<br>
						<br>
						<br>
						<br>
					</div>
					<div class="col-xs-12"><b><?php echo strtoupper($data['kanit']); ?></b></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>