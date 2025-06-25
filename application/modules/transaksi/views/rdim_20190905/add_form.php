<div class="col-sm-12 no-padding">
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

			<!-- <div class="input-group">
				<input value="" type="text" class="form-control text-center date" placeholder="Start Date" id="StartDate_RDIM" name="startPeriode" readonly data-required="1">
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div> -->
		</div>
		<div class="col-sm-1 text-center" style="max-width: 4%; margin-top:7px;">s/d</div>
		<div class="col-sm-2">
			<div class="input-group date" id="datetimepicker2" name="endPeriode" id="EndDate_RDIM">
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
			<a class="tu-float-btn tu-float-btn-left tu-table-prev" >
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
				<th rowspan="2" class="page0 col-sm-1" style="height: 64px">Tanggal DOC In</th>
				<th rowspan="2" class="page0 col-sm-2">Mitra</th>
				<th rowspan="2" class="page0 col-sm-1 batas_kanan">Kandang</th>
				<th rowspan="2" class="page1">Populasi</th>
				<th rowspan="1" class="page1" colspan="3">IP Terakhir</th>
				<th rowspan="2" class="page1">Kapasitas Kandang</th>
				<th rowspan="2" class="page1">Istirahat Kandang</th>
				<th rowspan="1" class="page1" colspan="2">Simp (Hutang) Mitra</th>
				<th rowspan="2" class="page1">Kecamatan</th>
				<th rowspan="2" class="page1">Kabupaten</th>
				<th rowspan="2" class="page1 col-sm-1">Noreg</th>

				<!-- page 2 -->
				<th rowspan="2" class="page2">Program Kesehatan</th>
				<th rowspan="2" class="page2">Pengawas</th>
				<th rowspan="2" class="page2">Tim Sampling</th>
				<th rowspan="2" class="page2">Tim Panen</th>
				<th rowspan="2" class="page2">Koordinator Area</th>
				<th rowspan="2" class="page2">Tipe Kandang Densitas</th>
				<th rowspan="2" class="page2">Format PB</th>
				<th rowspan="2" class="page2">Pola Kemitraan</th>
				<th rowspan="2" class="page2">Group</th>
			</tr>
			<tr>
				<th class="page1">1</th>
				<th class="page1">2</th>
				<th class="page1">3</th>
				<th class="page1">Hutang</th>
				<th class="page1">JUT</th>
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
									<option value="<?php echo $mitra['mitra_id'] ?>" data-jenis="<?php echo $mitra['jenis'] ?>" data-kandangs='<?php echo json_encode($mitra['kandangs']) ?>' ><?php echo $mitra['nama'] ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td class="page0 batas_kanan">
							<select class="form-control" name="kandang">
								<option value="">-</option>
							</select>
						</td>
						<td class="page1"><input class="form-control text-right" type="text" name="populasi" value="" data-tipe="integer" onkeyup="rdim.checkBatasPopulasi(this)"></td>
						<td class="page1"><input class="form-control no-check text-right" type="text" name="ip_terakhir_1" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control no-check text-right" type="text" name="ip_terakhir_2" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control no-check text-right" type="text" name="ip_terakhir_3" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control text-right" type="text" name="kapasitas_kandang" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control no-check" type="text" name="istirahat_kandang" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control no-check text-right" type="text" name="hutang" value="" data-tipe="integer" readonly></td>
						<td class="page1"><input class="form-control no-check text-right" type="text" name="jut" value="" data-tipe="integer" readonly></td>
						<td class="page1 kecamatan">Kecamatan</td>
						<td class="page1 kabupaten">Kabupaten</td>
						<td class="page1">
							<input class="form-control" type="text" name="noreg" value="" data-nim="" data-tipe="text" readonly>
							<div class="btn-ctrl">
								<span onclick="rdim.removeRowChild(this)" class="btn_del_row_2x"></span>
								<span onclick="rdim.addRowChild(this)" class="btn_add_row_2x"></span>
							</div>
						</td>

						<!-- page 2 -->
						<td class="page2"><input class="form-control" type="text" name="program_kesehatan" value="" data-tipe="text"></td>
						<td class="page2"><input class="form-control" type="text" name="pengawas" value="" data-tipe="text"></td>
						<td class="page2"><input class="form-control" type="text" name="tim_sampling" value="" data-tipe="text"></td>
						<td class="page2">
							<select class="form-control" name="tim_panen">
								<option value="">-- pilih --</option>
								<?php foreach ($tim_panens as $tp): ?>
									<option value="<?php echo $tp->nik_timpanen ?>"><?php echo $tp->nama_timpanen ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td class="page2"><input class="form-control" type="text" name="koordinator_area" value="" data-tipe="text"></td>
						<td class="page2"><input class="form-control" type="text" name="tipe_densitas" value="" data-tipe="text" readonly></td>
						<td class="page2">
							<select class="form-control" name="formatPb">
								<option value="">pilih</option>
								<?php foreach ($perwakilan['parent']['formatPb'] as $format): ?>
									<option value="<?php echo $format['id'] ?>"><?php echo $format['format'] ?></option>
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
</div>