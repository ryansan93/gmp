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
							<?php
								$selected = null;
								if ( $v_dm['nomor'] == $data['nomor'] ) {
									$selected = 'selected';
								}
							?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['noreg']; ?>" disabled>
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
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>
			<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo $data['mitra']; ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo $data['noreg']; ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Tanggal</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo tglIndonesia($data['tanggal'], '-', ' '); ?></label>
				</div>
			</div> -->
		</div>
	</div>
	<div class="col-xs-12">
		<div class="col-xs-6" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Umur</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-center" data-tipe="integer" type="text" name="umur" data-required="1" placeholder="UMUR" value="<?php echo $data['umur']; ?>" readonly />
			</div>
		</div>

		<div class="col-xs-6" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Pakai Pakan (Zak)</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="pakai_pakan" data-required="1" placeholder="PAKAI PAKAN" value="<?php echo angkaRibuan($data['pakai_pakan']); ?>" />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Sisa Pakan (Zak)</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="sisa_pakan" data-required="1" placeholder="SISA PAKAN" value="<?php echo angkaRibuan($data['sisa_pakan']); ?>" />
			</div>
			<div class="col-xs-12 no-padding contain">
				<div class="col-xs-6 no-padding attachment">
					<label class="col-xs-12" style="padding: 0px 5px 0px 0px;">
						<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_sisa_pakan" data-name="name" data-jenis="sisa_pakan" onchange="lhk.uploadSisaPakan(this)" data-url="uploads/LHK/SISA_PAKAN/<?php echo $data['id']; ?>" />
	                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Sisa Pakan"></i> 
	              	</label>
				</div>
				<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Sisa Pakan" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/SISA_PAKAN/<?php echo $data['id']; ?>'>
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
				<input class="form-control text-right" data-tipe="integer" type="text" name="ekor_mati" data-required="1" placeholder="EKOR MATI" value="<?php echo angkaRibuan($data['ekor_mati']); ?>" />
			</div>
			<div class="col-xs-12 no-padding contain">
				<div class="col-xs-6 no-padding attachment">
					<label class="col-xs-12" style="padding: 0px 5px 0px 0px;">
						<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_ekor_mati" data-name="name" data-jenis="ekor_mati" onchange="lhk.uploadKematian(this)" data-url="uploads/LHK/KEMATIAN/<?php echo $data['id']; ?>" />
	                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Ekor Mati"></i> 
	              	</label>
					<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
				</div>
				<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Kematian" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/KEMATIAN/<?php echo $data['id']; ?>'>
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
						<?php $idx = 0; ?>
						<?php foreach ($data['lhk_sekat'] as $k_ls => $v_ls): ?>
							<?php $idx++; ?>
							<tr>
								<td class="no_urut"><?php echo $idx; ?></td>
								<td><input class="form-control text-right sekat" data-tipe="decimal3" type="text" name="bb" data-required="1" placeholder="BB" value="<?php echo angkaDecimalFormat($v_ls['bb'], 3); ?>"></td>
								<td class="text-center">
									<a class="btn btn-primary" onclick="lhk.add_row(this)"><i class="fa fa-plus"></i></a>
									<a class="btn btn-danger" onclick="lhk.remove_row(this)"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</small>
		</div>
	</div>

	<div class="col-xs-12"><br></div>

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<label class="col-xs-12 no-padding">Keterangan</label>
			<textarea id="keterangan_rhk" class="col-xs-12 form-control keterangan" name="keterangan" rows="4" data-required="1"><?php echo $data['keterangan']; ?></textarea>
		</div>
	</div>

	<div class="col-xs-12"><br></div>

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<button type="button" class="btn btn-default pull-left" style="margin-right: 5px;" data-toggle="modal" data-target="#myNekropsi"><i class="fa fa-list-alt" aria-hidden="true"></i> Check List Nekropsi</button>
			<button type="button" class="btn btn-default pull-left" data-toggle="modal" data-target="#mySolusi"><i class="fa fa-list-alt" aria-hidden="true"></i> Solusi</button>
		</div>
	</div>

	<div class="col-xs-12"><hr></div>

	<div class="col-xs-12">
		<div class="col-xs-12" style="padding: 0px 0px 5px 0px;">
			<button type="button" class="btn btn-primary" onclick="lhk.getLocation(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>" data-lat="<?php echo $data['lat']; ?>" data-long="<?php echo $data['long']; ?>" data-jenis="edit"><i class="fa fa-save"></i> Simpan Edit</button>
		</div>
		<div class="col-xs-12" style="padding: 5px 0px 0px 0px;">
			<button type="button" class="btn btn-danger" onclick="lhk.batal_edit(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>"><i class="fa fa-times"></i> Batal</button>
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
					        				<?php
					        					$checked = null;
					        					$disable = 'disable';
					        					$disabled = 'disabled';
					        					$keterangan = null;
					        					$ada = 0;
					        					foreach ($data['lhk_nekropsi'] as $k_ln => $v_ln) {
						        					if ( $v_dn['id'] == $v_ln['id_nekropsi'] ) {
						        						$checked = 'checked';
														$disable = null;
														$disabled = null;
						        						$keterangan = $v_ln['keterangan'];
														$ada = 1;
						        					}
					        					}
					        				?>
						        			<tr class="head" data-id="<?php echo $v_dn['id']; ?>">
						        				<td rowspan="2" class="text-center"><input type="checkbox" <?php echo $checked; ?> onchange="lhk.checkboxCheck(this)" data-dirstatus="<?php echo $ada; ?>" ></td>
						        				<td><?php echo $v_dn['keterangan']; ?></td>
						        				<td>
						        					<!-- <div class="col-xs-12 no-padding">
														<div class="col-xs-12 no-padding attachment <?php echo $disable; ?>" style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
																<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_nekropsi" data-name="name" data-required="1" disabled="<?php echo $disabled; ?>" data-jenis="nekropsi" onchange="lhk.uploadNekropsi(this)" />
											                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Nekropsi"></i> 
											              	</label>
															<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
														</div>
														<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/NEKROPSI/<?php echo $data['id'].'_'.$v_dn['id']; ?>' style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
													</div> -->
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding attachment <?php echo $disable; ?>" style="padding-right: 5px;">
															<label class="col-xs-12 no-padding">
																<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_nekropsi" data-name="name" data-required="1" <?php echo $disabled; ?> onchange="lhk.uploadNekropsi(this)" />
											                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Nekropsi"></i> 
											              	</label>
															<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
														</div>
														<div class="col-xs-6 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/NEKROPSI/<?php echo $data['id'].'_'.$v_dn['id']; ?>' style="padding-left: 5px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
													</div>
						        				</td>
						        			</tr>
											<tr class="detail">
												<td colspan="2">
												<textarea class="form-control uppercase ket" placeholder="Keterangan"><?php echo strtoupper($keterangan); ?></textarea>
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
					        				<?php
					        					$checked = null;
					        					foreach ($data['lhk_solusi'] as $k_ls => $v_ls) {
						        					if ( $v_ds['id'] == $v_ls['id_solusi'] ) {
						        						$checked = 'checked';
						        					}
					        					}
					        				?>
						        			<tr data-id="<?php echo $v_ds['id']; ?>">
						        				<td><?php echo $v_ds['keterangan']; ?></td>
						        				<td class="text-center"><input type="checkbox" <?php echo $checked; ?> ></td>
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