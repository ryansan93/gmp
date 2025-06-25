<div class="col-sm-12 no-padding">
	<form class="form-horizontal">
		<input type="hidden" data-id="<?php echo $data['rdim']['id']; ?>">
		<div class="col-sm-1">
			<label class="control-label"> Periode </label>
		</div>
		<div class="col-sm-2">
			<div class="input-group date" id="datetimepicker1" name="startPeriode" id="StartDate_RDIM" data-tgl="<?php echo $data['rdim']['mulai']; ?>">
		        <input type="text" class="form-control text-center" placeholder="Start Date" id="StartDate_RDIM" name="startPeriode" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>

			<!-- <div class="input-group">
				<input value="" type="text" class="form-control text-center date" placeholder="Start Date" id="StartDate_RDIM" name="startPeriode" readonly data-required="1">
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div> -->
		</div>
	<div class="col-sm-1 text-center" style="max-width: 4%; margin-top:7px;">s/d</div>
	<div class="col-sm-2">
		<div class="input-group date" id="datetimepicker2" name="endPeriode" id="EndDate_RDIM" data-tgl="<?php echo $data['rdim']['selesai']; ?>">
	        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>

		<!-- <div class="input-group">
			<input value="" type="text" class="form-control text-center date" placeholder="End Date" id="EndDate_RDIM" name="endPeriode" readonly data-required="1" disabled>
			<span class="input-group-addon">
				<span class="glyphicon glyphicon-calendar"></span>
			</span>
		</div> -->
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
				<!-- <th rowspan="1" class="page1 hide" colspan="3">IP Terakhir</th> -->
				<th rowspan="2" class="page1">Kapasitas Kandang</th>
				<th rowspan="2" class="page1">Istirahat Kandang</th>
				<!-- <th rowspan="1" class="page1 hide" colspan="2">Simp (Hutang) Mitra</th> -->
				<th rowspan="2" class="page1">Kecamatan</th>
				<th rowspan="2" class="page1">Kabupaten</th>
				<th rowspan="2" class="page1 col-sm-1">Noreg</th>
				<th rowspan="2" class="page1">Vaksin</th>

				<!-- page 2 -->
				<!-- <th rowspan="2" class="page2 hide">Program Kesehatan</th> -->
				<th rowspan="2" class="page2">Kanit</th>
				<th rowspan="2" class="page2">PPL</th>
				<th rowspan="2" class="page2">Marketing</th>
				<th rowspan="2" class="page2">Koordinator Area</th>
				<th rowspan="2" class="page2">Tipe Kandang Densitas</th>
				<th rowspan="2" class="page2">Format PB</th>
				<th rowspan="2" class="page2">Pola</th>
				<th rowspan="2" class="page2">Group</th>
			</tr>
			<!-- <tr>
				<th class="page1 hide">1</th>
				<th class="page1 hide">2</th>
				<th class="page1 hide">3</th>
				<th class="page1 hide">Hutang</th>
				<th class="page1 hide">JUT</th>
			</tr> -->
		</thead>
		<tbody class="list">
			<?php $_key = null; ?>
			<?php foreach ($rdim_data_perwakilan_mitra as $key => $perwakilan): ?>
				<?php $_key = $key; ?>
				<?php if ($perwakilan['child']): ?>
					<?php 
						$hide = null;
						$inactive = 'inactive';
						$jml_data = 0;
						$data_details = array();
						foreach ($data['rdim_submit'] as $k_rdim => $v_rdim) {
							if ( isset($v_rdim[$_key]) ) {
								$jml_data = count($v_rdim[$_key]['details']);
								$hide = 'style="display: none;"';
								$inactive = null;

								foreach ($v_rdim[$_key]['details'] as $k_det => $v_det) {
									$data_details[$_key][] = $v_det;
								}
							}
						}
					?>

					<tr class="parent v-center" data-key="<?php echo $_key ?>">
						<th colspan="14">
							Perwakilan <?php echo $perwakilan['parent']['nama'] . ' ( ' . implode(', ', $perwakilan['parent']['units']) . ' )' ?>
							<div class="btn-ctrl" <?php echo $hide; ?> >
								<span class="btn_add_row_2x" onclick="rdim.addFirstChild(this)" ></span>
							</div>
						</th>
					</tr>
					<?php 
						$index = 0;
						do { ?>
							<?php 
								// cetak_r($data_details[$_key][$index]['populasi']);
								$dtgl = null;
								if ( isset($data_details[$_key][$index]['tanggal']) ) {
									$dtgl = 'data-tgl="' . $data_details[$_key][$index]['tanggal'] . '"';
								} else {
									$inactive = 'inactive';
								}

								$hide = 'style="display: none;"';
								if ( $index == $jml_data - 1 ) {
									$hide = null;
								}
							?>
							<tr class="child v-center <?php echo $inactive; ?>" data-key="<?php echo $_key ?>">
								<td class="page0">
									<div class="input-group date" id="datetimepicker3" name="tanggal" <?php echo $dtgl; ?> >
								        <input type="text" class="form-control text-center" placeholder="Tanggal" name="tanggal" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</td>
								<td class="page0">
									<select class="form-control" name="mitra" onchange="rdim.changeMitraRow(this)" >
										<option value="">-- pilih mitra --</option>
										<?php foreach ($perwakilan['child'] as $key => $mitra): ?>
											<?php 
												$selected = null;
												if ( isset($data_details[$_key][$index]['tanggal']) ) {
													if ( $mitra['mitra_id'] == $data_details[$_key][$index]['id_mitra'] ) {
														$selected = 'selected';
													}
												}
											?>
											<option value="<?php echo $mitra['mitra_id'] ?>" data-jenis="<?php echo $mitra['jenis'] ?>" data-kandangs='<?php echo json_encode($mitra['kandangs']) ?>' <?php echo $selected; ?> ><?php echo $mitra['nama'] ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="page0 batas_kanan">
									<select class="form-control" name="kandang" data-kdg="<?php echo $data_details[$_key][$index]['kandang']; ?>">
										<option value="">-</option>
									</select>
								</td>
								<td class="page1">
									<?php 
										$populasi = !isset($data_details[$_key][$index]['populasi']) ? 0 : angkaRibuan($data_details[$_key][$index]['populasi']);
									?>
									<input class="form-control text-right" type="text" name="populasi" value="<?php echo $populasi; ?>" data-tipe="integer" onkeyup="rdim.checkBatasPopulasi(this)" />
								</td>
								<!-- <td class="page1 hide"><input class="form-control no-check text-right" type="text" name="ip_terakhir_1" value="" data-tipe="integer" readonly></td>
								<td class="page1 hide"><input class="form-control no-check text-right" type="text" name="ip_terakhir_2" value="" data-tipe="integer" readonly></td>
								<td class="page1 hide"><input class="form-control no-check text-right" type="text" name="ip_terakhir_3" value="" data-tipe="integer" readonly></td> -->
								<td class="page1"><input class="form-control text-right" type="text" name="kapasitas_kandang" value="" data-tipe="integer" readonly></td>
								<td class="page1"><input class="form-control no-check" type="text" name="istirahat_kandang" value="" data-tipe="integer" readonly></td>
								<!-- <td class="page1 hide"><input class="form-control no-check text-right" type="text" name="hutang" value="" data-tipe="integer" readonly></td>
								<td class="page1 hide"><input class="form-control no-check text-right" type="text" name="jut" value="" data-tipe="integer" readonly></td> -->
								<td class="page1 kecamatan">Kecamatan</td>
								<td class="page1 kabupaten">Kabupaten</td>
								<td class="page1">
									<input class="form-control" type="text" name="noreg" value="" data-nim="" data-tipe="text" readonly>
								</td>
								<td class="page1">
									<select class="form-control" name="vaksin">
										<option value="">-- Pilih Vaksin --</option>
										<?php foreach ($vaksin as $k_vaksin => $v_vaksin): ?>
											<?php
												$selected = null;
												if ( $v_vaksin['id'] == $data_details[$_key][$index]['id_vaksin'] ) {
													$selected = 'selected';
												}
											?>
											<option value="<?php echo $v_vaksin['id']; ?>" <?php echo $selected; ?> ><?php echo $v_vaksin['nama_vaksin']; ?></option>
										<?php endforeach ?>
									</select>
									<div class="btn-ctrl" <?php echo $hide; ?> >
										<span onclick="rdim.removeRowChild(this)" class="btn_del_row_2x"></span>
										<span onclick="rdim.addRowChild(this)" class="btn_add_row_2x"></span>
									</div>
								</td>

								<!-- page 2 -->
								<!-- <td class="page2 hide"><input class="form-control" type="text" name="program_kesehatan" value="<?php echo !isset($data_details[$_key][$index]['prokes']) ?: $data_details[$_key][$index]['prokes']; ?>" data-tipe="text"></td> -->
								<td class="page2">
									<select class="form-control" name="pengawas">
										<option value="">-- Pilih Kanit --</option>
										<?php foreach ($perwakilan['parent']['kanit'] as $k_kanit => $v_kanit): ?>
											<?php
												$selected = null;
												if ( $v_kanit['id'] == $data_details[$_key][$index]['id_pengawas'] ) {
													$selected = 'selected';
												}
											?>
											<option value="<?php echo $v_kanit['id']; ?>" <?php echo $selected; ?> ><?php echo $v_kanit['nama']; ?></option>
										<?php endforeach ?>
									</select>
									<!-- <input class="form-control" type="text" name="pengawas" value="<?php echo !isset($data_details[$_key][$index]['pengawas']) ?: $data_details[$_key][$index]['pengawas']; ?>" data-tipe="text"> -->
								</td>
								<td class="page2">
									<select class="form-control" name="tim_sampling">
										<option value="">-- Pilih PPL --</option>
										<?php foreach ($perwakilan['parent']['ppl'] as $k_ppl => $v_ppl): ?>
											<?php
												$selected = null;
												if ( $v_ppl['id'] == $data_details[$_key][$index]['id_sampling'] ) {
													$selected = 'selected';
												}
											?>
											<option value="<?php echo $v_ppl['id']; ?>" <?php echo $selected; ?> ><?php echo $v_ppl['nama']; ?></option>
										<?php endforeach ?>
									</select>
									<!-- <input class="form-control" type="text" name="tim_sampling" value="<?php echo !isset($data_details[$_key][$index]['sampling']) ?: $data_details[$_key][$index]['sampling']; ?>" data-tipe="text"> -->
								</td>
								<td class="page2">
									<select class="form-control" name="tim_panen">
										<option value="">-- Pilih Marketing --</option>
										<?php foreach ($perwakilan['parent']['marketing'] as $k_marketing => $v_marketing): ?>
											<?php
												$selected = null;
												if ( $v_marketing['id'] == $data_details[$_key][$index]['id_tim_panen'] ) {
													$selected = 'selected';
												}
											?>
											<option value="<?php echo $v_marketing['id']; ?>" <?php echo $selected; ?> ><?php echo $v_marketing['nama']; ?></option>
										<?php endforeach ?>
									</select>
									<!-- <select class="form-control" name="tim_panen">
										<option value="">-- pilih --</option>
										<?php foreach ($tim_panens as $tp): ?>
											<?php
												$selected = null;
												if ( isset($data_details[$_key][$index]['id_tim_panen']) ) {
													if ( $tp->nik_timpanen == $data_details[$_key][$index]['id_tim_panen'] ) {
														$selected = 'selected';
													} 
												}
											?>
											<option value="<?php echo $tp->nik_timpanen ?>" <?php echo $selected; ?> ><?php echo $tp->nama_timpanen ?></option>
										<?php endforeach; ?>
									</select> -->
								</td>
								<td class="page2">
									<select class="form-control" name="koordinator_area">
										<option value="">-- Pilih Koordinator --</option>
										<?php foreach ($perwakilan['parent']['koordinator'] as $k_koordinator => $v_koordinator): ?>
											<?php
												$selected = null;
												if ( $v_koordinator['id'] == $data_details[$_key][$index]['id_koar'] ) {
													$selected = 'selected';
												}
											?>
											<option value="<?php echo $v_koordinator['id']; ?>" <?php echo $selected; ?> ><?php echo $v_koordinator['nama']; ?></option>
										<?php endforeach ?>
									</select>
									<!-- <input class="form-control" type="text" name="koordinator_area" value="<?php echo !isset($data_details[$_key][$index]['koar']) ?: $data_details[$_key][$index]['koar']; ?>" data-tipe="text"> -->
								</td>
								<td class="page2"><input class="form-control" type="text" name="tipe_densitas" value="<?php echo !isset($data_details[$_key][$index]['densitas']) ?: $data_details[$_key][$index]['densitas']; ?>" data-tipe="text" readonly></td>
								<td class="page2">
									<select class="form-control" name="formatPb">
										<option value="">pilih</option>
										<?php foreach ($perwakilan['parent']['formatPb'] as $format): ?>
											<?php 
												$selected = null;
												if ( isset($data_details[$_key][$index]['format_pb']) ) {
													if ( trim($format['format']) == trim($data_details[$_key][$index]['format_pb']) ) {
														if ( $data_details[$_key][$index]['perusahaan'] == $format['perusahaan'] ) {
															$selected = 'selected';
														}
													}
												}
											?>
											<option value="<?php echo $format['id'] ?>" data-perusahaan="<?php echo $format['perusahaan']; ?>" data-pola="<?php echo $format['pola']; ?>" <?php echo $selected; ?> ><?php echo $format['format'] ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="page2"><input class="form-control" type="text" name="jenis" value="<?php echo !isset($data_details[$_key][$index]['pola']) ?: $data_details[$_key][$index]['pola']; ?>" data-tipe="text" readonly></td>
								<td class="page2">
									<input class="form-control text-center" type="text" name="group" value="<?php echo !isset($data_details[$_key][$index]['group']) ?: $data_details[$_key][$index]['group']; ?>" data-tipe="text" readonly>
									<div class="btn-ctrl" <?php echo $hide; ?> >
										<span onclick="rdim.removeRowChild(this)" class="btn_del_row_2x"></span>
										<span onclick="rdim.addRowChild(this)" class="btn_add_row_2x"></span>
									</div>
								</td>
							</tr>
						<?php $index++;
						} while ( $index <= $jml_data); 
					?>

				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="col-sm-12 no-padding text-right">
		<button type="button" class="btn btn-primary" onclick="rdim.edit()"> <span class="fa fa-edit"> |</span> Edit</button>
	</div>
</div>