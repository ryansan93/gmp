<?php if ( isset($data['rdim_submit']['status-1'])): ?>
	<div class="row new-line">
		<div class="text-center col-sm-12">
			<div class="row">
				<div class="col-sm-12">
					<h5><?php echo $data['rdim']['nomor'] ?></h5>
					<h5>Periode : <?php echo tglIndonesia($data['rdim']['mulai'], '-', ' ') . ' s/d ' . tglIndonesia($data['rdim']['selesai'], '-', ' ') ?> </h5>
				</div>
			</div>
		</div>
		<div class="col-sm-12">

			<div class="col-sm-12">
				<div class="row">
					<a class="tu-float-btn tu-table-prev" style="margin-top:-30px;">
						<i class="fa fa-arrow-left my-float"></i>
					</a>

					<a class="tu-float-btn tu-float-btn-right tu-table-next" style="margin-top:-30px;">
						<i class="fa fa-arrow-right my-float"></i>
					</a>
				</div>
			</div>

			<table id="tb_rencana_doc_in_mingguan" name="tb_rencana_doc_in_mingguan" class="table table-hover table-bordered custom_table small">
				<thead>
					<tr>
						<th rowspan="2" class="page0 col-sm-1" style="height: 64px">Tanggal DOC In</th>
						<th rowspan="2" class="page0 col-sm-2">Mitra</th>
						<th rowspan="2" class="page0 col-sm-1 batas_kanan">Kandang</th>
						<th rowspan="2" class="page1 col-sm-1">Populasi</th>
						<th rowspan="1" class="page1 hide" colspan="3">IP Terakhir</th>
						<th rowspan="2" class="page1 col-sm-1">Kapasitas Kandang</th>
						<th rowspan="2" class="page1 hide">Istirahat Kandang</th>
						<th rowspan="1" class="page1 hide" colspan="2">Simp (Hutang) Mitra</th>
						<th rowspan="2" class="page1">Kecamatan</th>
						<th rowspan="2" class="page1">Kabupaten</th>
						<th rowspan="2" class="page1 col-sm-1">Noreg</th>
						<th rowspan="2" class="page1">Vaksin</th>

						<!-- page 2 -->
						<th rowspan="2" class="page2 hide">Program Kesehatan</th>
						<th rowspan="2" class="page2">Kanit</th>
						<th rowspan="2" class="page2">PPL</th>
						<th rowspan="2" class="page2">Marketing</th>
						<th rowspan="2" class="page2">Koordinator Area</th>
						<th rowspan="2" class="page2">Tipe Kandang Densitas</th>
						<th rowspan="2" class="page2">Format PB</th>
						<th rowspan="2" class="page2 hide">Pola</th>
						<th rowspan="2" class="page2 hide">Group</th>
					</tr>
					<tr>
						<th class="page1 hide">1</th>
						<th class="page1 hide">2</th>
						<th class="page1 hide">3</th>
						<th class="page1 hide">Hutang</th>
						<th class="page1 hide">JUT</th>
					</tr>
				</thead>
				<tbody class="list">
					<?php foreach ($data['rdim_submit']['status-1'] as $perwakilan_id => $rs): ?>
						<tr class="parent" data-key="<?php echo $perwakilan_id ?>">
							<th colspan="14">
								<div class="col-sm-10 no-padding text-left">
									Perwakilan <?php echo $rs['header']['perwakilan'] . ' ( ' . implode(', ', $rs['header']['units']) . ' )' ?>
								</div>
								<div class="col-sm-2 no-padding text-right">
									TOTAL : <?php echo angkaRibuan($rs['header']['populasi']); ?>
								</div>
							</th>
						</tr>
						<!-- looping/rendering detail rdim_submit -->
						<?php foreach ($rs['details'] as $detail): ?>
							<tr class="child" data-key="<?php echo $perwakilan_id ?>" data-id="<?php echo $detail['id'] ?>">
								<td class="page0"> <?php echo tglIndonesia($detail['tanggal']) ?> </td>
								<!-- <td class="page0"> <?php echo $detail['mitra'] ?> </td> -->
								<td class="page0"> 
									<a href="transaksi/Rdim/cetak_kontrak/<?php echo $detail['id']; //str_replace('/','_URT_',$data['nomor_ppah']); ?>" target="_blank" title="CETAK"><?php echo $detail['mitra'] ?></a> 
								</td>
								<td class="page0 batas_kanan text-center"><?php echo $detail['kandang'] ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['populasi']) ?></td>
								<td class="page1 text-right hide"><?php echo angkaRibuan($detail['ip1']) ?></td>
								<td class="page1 text-right hide"><?php echo angkaRibuan($detail['ip2']) ?></td>
								<td class="page1 text-right hide"><?php echo angkaRibuan($detail['ip3']) ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['kapasitas']) ?></td>
								<td class="page1 text-right hide"><?php echo $detail['istirahat'] ?></td>
								<td class="page1 text-right hide"><?php echo $detail['hutang'] ?></td>
								<td class="page1 text-right hide"><?php echo $detail['jut'] ?></td>
								<td class="page1 kecamatan"><?php echo $detail['kecamatan'] ?></td>
								<td class="page1 kabupaten"><?php echo $detail['kabupaten'] ?></td>
								<td class="page1"><?php echo $detail['noreg'] ?></td>
								<td class="page1"><?php echo $detail['vaksin'] ?></td>

								<!-- page 2 -->
								<td class="page2 hide"><?php echo $detail['prokes'] ?></td>
								<td class="page2">
									<a class="cursor-p" onclick="rdim.formPenanggungJawabKandang(this)" data-id="<?php echo $detail['id']; ?>"><?php echo strtoupper($detail['pengawas']) ?></a>
								</td>
								<td class="page2">
									<a class="cursor-p" onclick="rdim.formPenanggungJawabKandang(this)" data-id="<?php echo $detail['id']; ?>"><?php echo strtoupper($detail['sampling']) ?></a>
								</td>
								<td class="page2">
									<a class="cursor-p" onclick="rdim.formPenanggungJawabKandang(this)" data-id="<?php echo $detail['id']; ?>"><?php echo strtoupper($detail['tim_panen']) ?></a>
								</td>
								<td class="page2">
									<a class="cursor-p" onclick="rdim.formPenanggungJawabKandang(this)" data-id="<?php echo $detail['id']; ?>"><?php echo strtoupper($detail['koar']) ?></a>
								</td>
								<td class="page2"><?php echo $detail['densitas'] ?></td>
								<!-- <td class="page2"><?php echo tglIndonesia($detail['tgl_sk'], '-', ' ').' - '.$detail['format_pb'] ?></td> -->
								<td class="page2"><?php echo strtoupper($detail['nomor_sk']) ?></td>
								<td class="page2 hide"><?php echo $detail['pola'] ?></td>
								<td class="page2 text-right hide"><?php echo $detail['group'] ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?>


<?php if ( isset($data['rdim_submit']['status-2'])): ?>
	<!-- RDIM batal -->
	<div class="row new-line">
		<div class="text-center col-sm-12">
			<h4 class="">Batal Rencana DOC In Mingguan (RDIM) </h4>

			<div class="row">
				<div class="col-sm-12">
					<h5><?php echo $data['rdim']['nomor'] ?></h5>
					<h5>Periode : <?php echo tglIndonesia($data['rdim']['mulai'], '-', ' ') . ' s/d ' . tglIndonesia($data['rdim']['selesai'], '-', ' ') ?> </h5>
				</div>
			</div>
		</div>
		<div class="col-sm-12">

		<div class="col-sm-12">
			<div class="row">
				<a class="tu-float-btn tu-float-btn-left tu-table-prev" >
					<i class="fa fa-arrow-left my-float"></i>
				</a>

				<a class="tu-float-btn tu-float-btn-right tu-table-next" >
					<i class="fa fa-arrow-right my-float"></i>
				</a>
			</div>
			</div>

			<table id="tb_batal_rencana_doc_in_mingguan" class="table table-hover table-bordered custom_table small">
				<thead>
					<tr>
						<th rowspan="2" class="page0 col-sm-1" style="height: 64px">Tanggal DOC In</th>
						<th rowspan="2" class="page0 col-sm-2">Mitra</th>
						<th rowspan="2" class="page0 col-sm-1 batas_kanan">Kandang</th>
						<th rowspan="2" class="page1">Populasi</th>
						<th rowspan="1" class="page1" colspan="3">IP Terakhir</th>
						<th rowspan="2" class="page1 col-sm-1">Noreg</th>

						<!-- page 2 -->
						<th rowspan="2" class="page2">File</th>
						<th rowspan="2" class="page2 col-sm-5">Alasan Dibatalkan</th>
					</tr>
					<tr>
						<th class="page1">1</th>
						<th class="page1">2</th>
						<th class="page1">3</th>
					</tr>
				</thead>
				<tbody class="list">
					<?php foreach ($data['rdim_submit']['status-2'] as $perwakilan_id => $rs): ?>
						<tr class="parent" data-key="<?php echo $perwakilan_id ?>">
							<th colspan="14">
								Perwakilan <?php echo $rs['header']['perwakilan'] . ' ( ' . implode(', ', $rs['header']['units']) . ' )' ?>
							</th>
						</tr>
						<!-- looping/rendering detail rdim_submit -->
						<?php foreach ($rs['details'] as $detail): ?>
							<tr class="child" data-key="<?php echo $perwakilan_id ?>" data-id="<?php echo $detail['id'] ?>">
								<td class="page0"> <?php echo tglIndonesia($detail['tanggal']) ?> </td>
								<td class="page0"> <?php echo $detail['mitra'] ?> </td>
								<td class="page0 batas_kanan text-center"><?php echo $detail['kandang'] ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['populasi']) ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['ip1']) ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['ip2']) ?></td>
								<td class="page1 text-right"><?php echo angkaRibuan($detail['ip3']) ?></td>
								<td class="page1"><?php echo $detail['noreg'] ?></td>

								<!-- page 2 -->
								<td class="page2 text-center"> <a href="<?php echo $detail['lampirans']['batal']['path'] ?>" target="_blank" title="<?php echo $detail['lampirans']['batal']['filename'] ?>"><i class="fa fa-file-o"></i></a> </td>
								<td class="page2"><?php echo $detail['ket_alasan'] ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php endif; ?>


<!-- Keterangan -->
<div class="col-sm-12 no-padding">
	<div class="col-sm-8 no-padding">
		<p>
			<b><u>Keterangan : </u></b>
			<?php
				foreach ($data['rdim']['logs'] as $log) {
					$temp[] = '<li class="list">' . $log->deskripsi . ' pada ' . dateTimeFormat( $log->waktu ) . '</li>';
				}
				if ($temp) {
					echo '<ul>' . implode("", $temp) . '</ul>';
				}
			?>
		</p>
		<?php if (! empty($data->alasan_tolak)): ?>
		<p>
			<b><u>Alasan Reject :</u></b>
			<ul class="list">
				<li><?php echo $data->alasan_tolak ?></li>
			</ul>
		</p>
		<?php endif; ?>
	</div>

	<div class="col-sm-4 text-right no-padding">
		<?php if ($data['rdim']['status'] == getStatus('ack') && $akses['a_approve'] == 1 ): ?>
			<!-- <button type="button" class="btn btn-danger" onclick="Rdim.approveReject(this)" data-action="reject" data-id="<?php echo $data['rdim']['id'] ?>" > <i class="fa fa-close"></i> Reject</button> -->
			<button type="button" class="btn btn-primary" onclick="rdim.approveReject(this)" data-action="approve" data-id="<?php echo $data['rdim']['id'] ?>" > <i class="fa fa-check"></i> Approve</button>
		<?php elseif ($data['rdim']['status'] == getStatus('submit') && $akses['a_ack'] == 1 ): ?>
			<!-- <button data-id="<?php echo $data['rdim']['id'] ?>" data-resubmit="1" type="button" class="btn btn-primary" onclick="Rdim.changeTabActive(this)" href='#standar_budidaya' data-toggle="tooltip" title="Buat Standar Budidaya (Ajukan ulang)"><i class="fa fa-copy"></i> Ajukan Ulang</button> -->
			<button type="button" class="btn btn-primary" onclick="rdim.ack(this)" data-action="ack" data-id="<?php echo $data['rdim']['id'] ?>" > <i class="fa fa-check"></i> ACK</button>
		<?php endif; ?>
	</div>
</div>
