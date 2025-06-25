<div class="row content-panel detailed">
	<!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Rencana Chick In Mingguan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Rencana Chick In Mingguan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#batal" data-tab="batal">Pembatalan Chick In</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rdim" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="rdim.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered table-hover tbl_rdim" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-2 text-center">Periode</th>
									<th class="col-sm-3 text-center">Nomor</th>
									<th class="col-sm-1 text-center">Status</th>
									<th class="col-sm-6 text-center">Keterangan</th>
								</tr>
							</thead>
							<tbody class="list">
								<tr>
									<td class="text-center" colspan="4">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
							<!-- <div class="col-sm-12 no-padding">
								<form class="form-horizontal">
									<div class="col-sm-1">
										<label class="control-label"> Periode </label>
									</div>
									<div class="col-sm-2">
										<div class="input-group date" id="datetimepicker1" name="startPeriode" id="StartDate_RDIM">
									        <input type="text" class="form-control text-center" placeholder="Start Date" id="StartDate_RDIM" name="startPeriode" data-required="1" />
									        <span class="input-group-addon">
									            <span class="glyphicon glyphicon-calendar"></span>
									        </span>
									    </div>
									</div>
									<div class="col-sm-1 text-center" style="max-width: 4%; margin-top:7px;">s/d</div>
									<div class="col-sm-2">
										<div class="input-group date" id="datetimepicker2" name="endPeriode" id="EndDate_RDIM">
									        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
									        <span class="input-group-addon">
									            <span class="glyphicon glyphicon-calendar"></span>
									        </span>
									    </div>
									</div>
								</form>
							</div>

							<div class="col-sm-12" style="padding-right: 30px; padding-left: 0px; padding-top: 10px;">
								<div class="col-sm-12">
									<div class="row">
										<a class="tu-float-btn tu-table-prev" >
											<i class="fa fa-arrow-left my-float"></i>
										</a>

										<a class="tu-float-btn tu-float-btn-right tu-table-next" >
											<i class="fa fa-arrow-right my-float"></i>
										</a>
									</div>
								</div>


								<table id="tb_rencana_doc_in_mingguan" name="tb_rencana_doc_in_mingguan" class="table table-hover table-bordered custom_table table-form small" style="padding-top: 10px;">
									<thead>
										<tr>
											<th rowspan="2" class="page0 col-sm-2" style="height: 64px">Tanggal DOC In</th>
											<th rowspan="2" class="page0 col-sm-2">Mitra</th>
											<th rowspan="2" class="page0 col-sm-1 batas_kanan">Kandang</th>
											<th rowspan="2" class="page1">Populasi</th>
											<th rowspan="2" class="page1">Kapasitas Kandang</th>
											<th rowspan="2" class="page1">Istirahat Kandang</th>
											<th rowspan="2" class="page1">Kecamatan</th>
											<th rowspan="2" class="page1">Kabupaten</th>
											<th rowspan="2" class="page1 col-sm-1">Noreg</th>
											<th rowspan="2" class="page1">Vaksin</th>
											<th rowspan="2" class="page2 hide">Program Kesehatan</th>
											<th rowspan="2" class="page2">Kanit</th>
											<th rowspan="2" class="page2">PPL</th>
											<th rowspan="2" class="page2">Marketing</th>
											<th rowspan="2" class="page2">Koordinator Area</th>
											<th rowspan="2" class="page2">Tipe Kandang Densitas</th>
											<th rowspan="2" class="page2">Format PB</th>
											<th rowspan="2" class="page2">Pola</th>
											<th rowspan="2" class="page2">Group</th>
										</tr>
									</thead>
									<tbody class="list">
										<?php foreach ($rdim_data_perwakilan_mitra as $key => $perwakilan): ?>
											<?php if ($perwakilan['child']): ?>

												<tr class="parent v-center" data-key="<?php echo $key ?>">
													<th colspan="14">
														Perwakilan <?php echo $perwakilan['parent']['nama'] . ' ( ' . implode(', ', $perwakilan['parent']['units']) . ' )' ?>
														<div class="btn-ctrl">
															<span class="btn_add_row_2x" onclick="rdim.addFirstChild(this)" ></span>
														</div>
													</th>
												</tr>
												<tr class="child inactive v-center" data-key="<?php echo $key ?>">
													<td class="page0">
														<div class="input-group date" id="datetimepicker3" name="tanggal">
													        <input type="text" class="form-control text-center" placeholder="Tanggal" name="tanggal" data-required="1" />
													        <span class="input-group-addon">
													            <span class="glyphicon glyphicon-calendar"></span>
													        </span>
													    </div>
													</td>
													<td class="page0">
														<select class="form-control" name="mitra" onchange="rdim.changeMitraRow(this)">
															<option value="">-- pilih mitra --</option>
															<?php foreach ($perwakilan['child'] as $key => $mitra): ?>
																<?php 
																	$_unit = null;
																	if ( !empty($mitra['kandangs']) ) {
																		foreach ($mitra['kandangs'] as $k_kdg => $v_kdg) {
																			$_unit[ $v_kdg['unit'] ] = $v_kdg['unit'];
																		}
																	}
																	
																	$unit = !empty($_unit) ? '('.implode(', ', $_unit).')' : null;
																?>
																<option value="<?php echo $mitra['mitra_id'] ?>" data-jenis="<?php echo $mitra['jenis'] ?>" data-kandangs='<?php echo json_encode($mitra['kandangs']) ?>' ><?php echo $mitra['nama'].' '.$unit; ?></option>
															<?php endforeach; ?>
														</select>
													</td>
													<td class="page0 batas_kanan">
														<select class="form-control" name="kandang">
															<option value="">-</option>
														</select>
													</td>
													<td class="page1"><input class="form-control text-right" type="text" name="populasi" value="" data-tipe="integer" onkeyup="rdim.checkBatasPopulasi(this)"></td>
													<td class="page1"><input class="form-control text-right" type="text" name="kapasitas_kandang" value="" data-tipe="integer" readonly></td>
													<td class="page1"><input class="form-control no-check" type="text" name="istirahat_kandang" value="" data-tipe="integer" readonly></td>
													<td class="page1 kecamatan">Kecamatan</td>
													<td class="page1 kabupaten">Kabupaten</td>
													<td class="page1">
														<input class="form-control" type="text" name="noreg" value="" data-nim="" data-tipe="text" readonly>
													</td>
													<td class="page1">
														<select class="form-control" name="vaksin">
															<option value="">-- Pilih Vaksin --</option>
															<?php foreach ($vaksin as $k_vaksin => $v_vaksin): ?>
																<option value="<?php echo $v_vaksin['id']; ?>"><?php echo $v_vaksin['nama_vaksin']; ?></option>
															<?php endforeach ?>
														</select>
														<div class="btn-ctrl">
															<span onclick="rdim.removeRowChild(this)" class="btn_del_row_2x"></span>
															<span onclick="rdim.addRowChild(this)" class="btn_add_row_2x"></span>
														</div>
													</td>
													<td class="page2">
														<select class="form-control" name="pengawas">
															<option value="">-- Pilih Kanit --</option>
															<?php foreach ($perwakilan['parent']['kanit'] as $k_kanit => $v_kanit): ?>
																<option value="<?php echo $v_kanit['nik']; ?>"><?php echo $v_kanit['nama']; ?></option>
															<?php endforeach ?>
														</select>
													</td>
													<td class="page2">
														<select class="form-control" name="tim_sampling">
															<option value="">-- Pilih PPL --</option>
															<?php foreach ($perwakilan['parent']['ppl'] as $k_ppl => $v_ppl): ?>
																<option value="<?php echo $v_ppl['nik']; ?>"><?php echo $v_ppl['nama']; ?></option>
															<?php endforeach ?>
														</select>
													</td>
													<td class="page2">
														<select class="form-control" name="tim_panen">
															<option value="">-- Pilih Marketing --</option>
															<?php foreach ($perwakilan['parent']['marketing'] as $k_marketing => $v_marketing): ?>
																<option value="<?php echo $v_marketing['nik']; ?>"><?php echo $v_marketing['nama']; ?></option>
															<?php endforeach ?>
														</select>
													</td>
													<td class="page2">
														<select class="form-control" name="koordinator_area">
															<option value="">-- Pilih Koordinator --</option>
															<?php foreach ($perwakilan['parent']['koordinator'] as $k_koordinator => $v_koordinator): ?>
																<option value="<?php echo $v_koordinator['nik']; ?>"><?php echo $v_koordinator['nama']; ?></option>
															<?php endforeach ?>
														</select>
													</td>
													<td class="page2"><input class="form-control" type="text" name="tipe_densitas" value="" data-tipe="text" readonly></td>
													<td class="page2">
														<select class="form-control" name="formatPb">
															<option value="">pilih</option>
															<?php foreach ($perwakilan['parent']['formatPb'] as $format): ?>
																<option value="<?php echo $format['id'] ?>" data-perusahaan="<?php echo $format['perusahaan']; ?>" data-pola="<?php echo $format['pola']; ?>" ><?php echo tglIndonesia($format['tgl_berlaku'], '-', ' ').' - '.$format['format']; ?></option>
															<?php endforeach; ?>
														</select>
													</td>
													<td class="page2"><input class="form-control" type="text" name="jenis" value="" data-tipe="text" readonly></td>
													<td class="page2">
														<input class="form-control text-center" type="text" name="group" value="" data-tipe="text" readonly>
														<div class="btn-ctrl">
															<span onclick="rdim.removeRowChild(this)" class="btn_del_row_2x"></span>
															<span onclick="rdim.addRowChild(this)" class="btn_add_row_2x"></span>
														</div>
													</td>
												</tr>

											<?php endif; ?>
										<?php endforeach; ?>
									</tbody>
								</table>
								<div class="col-sm-12 no-padding text-right">
									<button type="button" class="btn btn-primary" onclick="rdim.save()"> <span class="fa fa-save"> |</span> Simpan</button>
								</div>
							</div> -->
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>

					<div id="batal" class="tab-pane fade">
						<?php if ( !$akses['a_approve'] == 1 ): ?>
							<div class="col-sm-12 no-padding">
								<form class="form-horizontal">
									<div class="col-sm-1 no-padding" style="width: inherit;">
										<label class="control-label"> Periode </label>
									</div>
									<div class="col-sm-3">
										<select class="form-control " name="periode" onchange="rdim.getDataPembatalanRdim(this)">
											<option value="">-- pilih periode --</option>
											<?php foreach ($periodes as $periode): ?>
												<option value="<?php echo $periode->id ?>"><?php echo tglIndonesia($periode->mulai, '-', ' ') .' s.d '. tglIndonesia($periode->selesai, '-', ' ') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</form>
							</div>
							<div class="col-sm-12 no-padding" style="margin-top: 12px;">
								<table id="tb_pembatalan_rdim" class="table table-hover table-bordered custom_table table-form">
									<thead>
										<tr>
											<th class="col-sm-3">Mitra</th>
											<th class="col-sm-1">Noreg</th>
											<th class="col-sm-2">Document</th>
											<th>Alasan batal</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="4">pilih periode untuk menampilkan data</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-sm-12 no-padding">
								<button type="button" class="btn btn-primary pull-right" onclick="rdim.savePembatalanRdim()"><i class="fa fa-save"></i> Save</button>
							</div>
						<?php else: ?>
							<h4 class="text-center">Pembatalan Rencana DOC in Mingguan</h4>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>