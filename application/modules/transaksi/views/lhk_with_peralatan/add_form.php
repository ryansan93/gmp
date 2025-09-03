<div class="row">
	<!-- <div class="col-xs-12">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<h3 class="text-center" style="margin-top: 0px;">Laporan Harian Kandang</h3>
	</div>

	<div class="col-xs-12"><br></div> -->

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" data-placeholder="Pilih Mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo date('Y-m-d'); ?>" disabled />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12">
		<div class="col-xs-6" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Umur</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-center" data-tipe="integer" type="text" name="umur" data-required="1" placeholder="UMUR" readonly />
			</div>
		</div>

		<div class="col-xs-6" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Pakai Pakan (Zak)</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="pakai_pakan" data-required="1" placeholder="PAKAI PAKAN" />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Sisa Pakan (Zak)</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="sisa_pakan" data-required="1" placeholder="SISA PAKAN" />
			</div>
			<div class="col-xs-12 no-padding contain">
				<div class="col-xs-6 no-padding attachment">
					<label class="col-xs-12" style="padding: 0px 5px 0px 0px;">
						<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_sisa_pakan" data-name="name" data-required="1" onchange="lhk.uploadSisaPakan(this)" />
	                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Sisa Pakan"></i> 
	              	</label>
				</div>
				<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Sisa Pakan" onclick="lhk.preview_file_attachment(this)">
					<label class="col-xs-12" style="padding: 0px 0px 0px 5px;">
	                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
	              	</label>
				</div>
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Ekor Mati</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="ekor_mati" data-required="1" placeholder="EKOR MATI" />
			</div>
			<div class="col-xs-12 no-padding contain">
				<div class="col-xs-6 no-padding attachment">
					<label class="col-xs-12" style="padding: 0px 5px 0px 0px;">
						<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_ekor_mati" data-name="name" data-required="1" onchange="lhk.uploadKematian(this)" />
	                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Ekor Mati"></i> 
	              	</label>
					<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
				</div>
				<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Kematian" onclick="lhk.preview_file_attachment(this)">
					<label class="col-xs-12" style="padding: 0px 0px 0px 5px;">
	                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
	              	</label>
				</div>
			</div>
		</div>

		<div class="col-xs-12"><br></div>

		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered tbl_sekat" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3">Sekat Ke</th>
							<th class="col-xs-5">BB Rata2 (Kg)</th>
							<th class="col-xs-4">Tindakan</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="no_urut">1</td>
							<td><input class="form-control text-right sekat" data-tipe="decimal3" type="text" name="bb" data-required="1" placeholder="BB"></td>
							<td class="text-center">
								<a class="btn btn-primary" onclick="lhk.add_row(this)"><i class="fa fa-plus"></i></a>
								<a class="btn btn-danger" onclick="lhk.remove_row(this)"><i class="fa fa-trash"></i></a>
							</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
	</div>

	<div class="col-xs-12"><br></div>

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<label class="col-xs-12 no-padding">Keterangan</label>
			<textarea id="keterangan_rhk" class="col-xs-12 form-control keterangan" name="keterangan" rows="4" data-required="1"></textarea>
		</div>
	</div>

	<div class="col-xs-12"><br></div>

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-left" style="margin-right: 5px;" data-toggle="modal" data-target="#myNekropsi"><i class="fa fa-list-alt" aria-hidden="true"></i> Check List Nekropsi</button>
			<button type="button" class="btn btn-default pull-left" style="margin-right: 5px;" data-toggle="modal" data-target="#mySolusi"><i class="fa fa-list-alt" aria-hidden="true"></i> Solusi</button>
			<button type="button" class="btn btn-default pull-left" data-toggle="modal" data-target="#myPeralatan" disabled><i class="fa fa-list-alt" aria-hidden="true"></i> Peralatan</button>
		</div>
	</div>

	<div class="col-xs-12"><hr></div>

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-primary pull-right" onclick="lhk.getLocation(this)" style="width: 100%;" data-lat="" data-long="" data-jenis="save"><i class="fa fa-save"></i> Simpan</button>
		</div>
	</div>
</div>

<!-- Modal Nekropsi -->
<div id="myNekropsi" class="modal fade my-style" role="dialog">
	<div class="modal-dialog">
	    <!-- Modal content-->
	    <div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Check List Nekropsi</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		        <div class="panel-body no-padding">
		        	<div class="col-xs-12 no-padding">
			        	<small>
				        	<table class="table table-bordered tbl_nekropsi" style="margin-bottom: 0px;">
				        		<thead>
				        			<tr>
				        				<th class="col-xs-1">Pilih</th>
				        				<th class="col-xs-9">Parameter</th>
				        				<th class="col-xs-2">Lampiran</th>
				        			</tr>
				        		</thead>
				        		<tbody>
				        			<?php if ( count($data_nekropsi) > 0 ): ?>
					        			<?php foreach ($data_nekropsi as $k_dn => $v_dn): ?>
						        			<tr class="head" data-id="<?php echo $v_dn['id']; ?>">
						        				<td class="text-center" rowspan="2"><input type="checkbox" onchange="lhk.checkboxCheck(this)"></td>
						        				<td class="ket_nekropsi"><?php echo $v_dn['keterangan']; ?></td>
						        				<td class="">
						        					<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding attachment disable" style="padding-right: 5px;">
															<label class="col-xs-12 no-padding">
																<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_nekropsi" data-name="name" data-required="1" disabled="disabled" onchange="lhk.uploadNekropsi(this)" />
											                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Nekropsi"></i> 
											              	</label>
															<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
														</div>
														<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" style="padding-left: 5px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
														<!-- <div class="col-xs-12 no-padding upload" data-title="Upload" onclick="lhk.upload(this)" style="margin-top: 0px; display: none;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-upload cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div> -->
													</div>
						        				</td>
						        			</tr>
											<tr class="detail">
												<td colspan="2">
													<textarea class="form-control uppercase ket" placeholder="Keterangan"></textarea>
												</td>
											</tr>
					        			<?php endforeach ?>
					        		<?php else: ?>
					        			<tr>
					        				<td colspan="3">Data tidak ditemukan.</td>
					        			</tr>
				        			<?php endif ?>
				        		</tbody>
				        	</table>
				        </small>
		        	</div>
		        </div>
		    </div>
		</div>
	</div>
</div>

<!-- Modal Solusi -->
<div id="mySolusi" class="modal fade my-style" role="dialog">
	<div class="modal-dialog">
	    <!-- Modal content-->
	    <div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Pilihan Solusi</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		        <div class="panel-body no-padding">
		        	<div class="col-xs-12 no-padding">
			        	<small>
				        	<table class="table table-bordered tbl_solusi" style="margin-bottom: 0px;">
				        		<thead>
				        			<tr>
				        				<th class="col-xs-10">Parameter</th>
				        				<th class="col-xs-2">Check</th>
				        			</tr>
				        		</thead>
				        		<tbody>
				        			<?php if ( count($data_solusi) > 0 ): ?>
					        			<?php foreach ($data_solusi as $k_ds => $v_ds): ?>
						        			<tr data-id="<?php echo $v_ds['id']; ?>">
						        				<td><?php echo $v_ds['keterangan']; ?></td>
						        				<td class="text-center"><input type="checkbox"></td>
						        			</tr>
					        			<?php endforeach ?>
					        		<?php else: ?>
					        			<tr>
					        				<td colspan="2">Data tidak ditemukan.</td>
					        			</tr>
				        			<?php endif ?>
				        		</tbody>
				        	</table>
				        </small>
		        	</div>
		        </div>
		    </div>
		</div>
	</div>
</div>

<!-- Modal Peralatan -->
<div id="myPeralatan" class="modal fade my-style" role="dialog">
	<div class="modal-dialog">
	    <!-- Modal content-->
	    <div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Check List Peralatan</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		        <div class="panel-body no-padding">
		        	<div class="col-xs-12 no-padding">
			        	<small>
				        	<table class="table table-bordered tbl_peralatan" style="margin-bottom: 0px;">
				        		<thead>
				        			<tr>
				        				<th class="col-xs-6">Keterangan</th>
				        				<th class="col-xs-3">Controller</th>
				        				<th class="col-xs-3">Kessler</th>
				        			</tr>
				        		</thead>
				        		<tbody>
				        			<tr>
										<td>Umur</td>
										<td class="umur text-center" colspan="2"></td>
									</tr>
									<tr>
										<td>Waktu Cek</td>
										<td class="waktu text-center" colspan="2"></td>
									</tr>
									<tr>
										<td>Flok / Lantai</td>
										<td colspan="2">
											<input type="text" class="form-control flok_lantai" data-required="1" placeholder="Flok 1 Lantai 1 (MAX:25)" maxlength="25">
										</td>
									</tr>
									<tr>
										<td>Tipe Controller</td>
										<td colspan="2">
											<input type="text" class="form-control tipe_controller" data-required="1" placeholder="Temptron 304D (MAX:50)" maxlength="50">
										</td>
									</tr>
									<tr>
										<td>Kelembapan (%)</td>
										<td><input type="text" class="form-control text-right kelembapan1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right kelembapan2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Suhu Current &#8451;</td>
										<td><input type="text" class="form-control text-right suhu_current1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right suhu_current2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Suhu Experience &#8451;</td>
										<td><input type="text" class="form-control text-right suhu_experience1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right suhu_experience2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Air Speed Depan Inlet</td>
										<td><input type="text" class="form-control text-right air_speed_depan_inlet1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right air_speed_depan_inlet2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Kerataan Air Speed</td>
										<td><input type="text" class="form-control text-right kerataan_air_speed1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right kerataan_air_speed2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Ukuran Kipas</td>
										<td><input type="text" class="form-control text-right ukuran_kipas1" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
										<td><input type="text" class="form-control text-right ukuran_kipas2" data-tipe="decimal" data-required="1" placeholder="0.00" maxlength="6"></td>
									</tr>
									<tr>
										<td>Jumlah Kipas Total</td>
										<td><input type="text" class="form-control text-right jumlah_kipas1" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
										<td><input type="text" class="form-control text-right jumlah_kipas2" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
									</tr>
									<tr>
										<td>Jumlah Kipas On</td>
										<td><input type="text" class="form-control text-right jumlah_kipas_on1" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
										<td><input type="text" class="form-control text-right jumlah_kipas_on2" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
									</tr>
									<tr>
										<td>Jumlah Kipas Off</td>
										<td><input type="text" class="form-control text-right jumlah_kipas_off1" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
										<td><input type="text" class="form-control text-right jumlah_kipas_off2" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
									</tr>
									<tr>
										<td>Waktu Kipas On (menit)</td>
										<td><input type="text" class="form-control text-right waktu_kipas_on1" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
										<td><input type="text" class="form-control text-right waktu_kipas_on2" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
									</tr>
									<tr>
										<td>Waktu Kipas Off (menit)</td>
										<td><input type="text" class="form-control text-right waktu_kipas_off1" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
										<td><input type="text" class="form-control text-right waktu_kipas_off2" data-tipe="integer" data-required="1" placeholder="0" maxlength="2"></td>
									</tr>
									<tr class="cooling_pad_status">
										<td>Cooling Pad Status</td>
										<td class="data">
											<div class="form-group">
												<div class="radio" style="padding-top: 0px; margin-top: 0px; margin-bottom: 0px;">
													<label>
														<input type="radio" id="cooling_pad_status_on1" name="cooling_pad_status1" value="1" style="margin-left: 0px; margin-right: 0px; margin-top: 2px;"> 
														<label class="label-control">ON</label>
													</label>
												</div>
												<div class="radio" style="padding-top: 0px; margin-top: 0px; margin-bottom: 0px;">
													<label>
														<input type="radio" id="cooling_pad_status_off1" name="cooling_pad_status1" value="0" style="margin-left: 0px; margin-right: 0px; margin-top: 2px;"> 
														<label class="label-control">OFF</label>
													</label>
												</div>
											</div>
										</td>
										<td class="data">
											<div class="form-group">
												<div class="radio" style="padding-top: 0px; margin-top: 0px; margin-bottom: 0px;">
													<label>
														<input type="radio" id="cooling_pad_status_on2" name="cooling_pad_status2" value="1" style="margin-left: 0px; margin-right: 0px; margin-top: 2px;"> 
														<label class="label-control">ON</label>
													</label>
												</div>
												<div class="radio" style="padding-top: 0px; margin-top: 0px; margin-bottom: 0px;">
													<label>
														<input type="radio" id="cooling_pad_status_off2" name="cooling_pad_status2" value="0" style="margin-left: 0px; margin-right: 0px; margin-top: 2px;"> 
														<label class="label-control">OFF</label>
													</label>
												</div>
											</div>
										</td>
									</tr>
				        		</tbody>
				        	</table>
				        </small>
		        	</div>
		        </div>
		    </div>
		</div>
	</div>
</div>