<div class="row">
	<!-- <div class="col-xs-12">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<h3 class="text-center" style="margin-top: 0px;">Laporan Harian Kandang</h3>
	</div>

	<div class="col-xs-12"><br></div> -->

	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
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
			</div>
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
				<input class="form-control text-right" data-tipe="integer" type="text" name="pakai_pakan" data-required="1" placeholder="PAKAI PAKAN" value="<?php echo angkaRibuan($data['pakai_pakan']); ?>" readonly />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Sisa Pakan (Zak)</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="integer" type="text" name="sisa_pakan" data-required="1" placeholder="SISA PAKAN" value="<?php echo angkaRibuan($data['sisa_pakan']); ?>" readonly />
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Sisa Pakan" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/SISA_PAKAN/<?php echo $data['id']; ?>' data-jenis="view">
					<label class="col-xs-12 no-padding">
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
				<input class="form-control text-right" data-tipe="integer" type="text" name="ekor_mati" data-required="1" placeholder="EKOR MATI" value="<?php echo angkaRibuan($data['ekor_mati']); ?>" readonly />
			</div>
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Ekor Mati" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/KEMATIAN/<?php echo $data['id']; ?>' data-jenis="view">
					<label class="col-xs-12 no-padding">
	                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
	              	</label>
				</div>
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">BB</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="decimal3" type="text" name="bb" data-required="1" placeholder="BB" value="<?php echo angkaDecimalFormat($data['bb'], 3); ?>" readonly />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">ADG</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="decimal3" type="text" name="adg" data-required="1" placeholder="ADG" value="<?php echo angkaDecimalFormat($data['adg'], 3); ?>" readonly />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">FCR</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="decimal3" type="text" name="fcr" data-required="1" placeholder="FCR" value="<?php echo angkaDecimalFormat($data['fcr'], 3); ?>" readonly />
			</div>
		</div>

		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding">
				<label class="control-label">IP</label>
			</div>
			<div class="col-xs-12 no-padding">
				<input class="form-control text-right" data-tipe="decimal" type="text" name="ip" data-required="1" placeholder="IP" value="<?php echo angkaDecimal($data['ip']); ?>" readonly />
			</div>
		</div>

		<div class="col-xs-12"><br></div>

		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered tbl_pakan" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3">Sekat Ke</th>
							<th class="col-xs-5">BB Rata2 (Kg)</th>
						</tr>
					</thead>
					<tbody>
						<?php $idx = 0; ?>
						<?php foreach ($data['lhk_sekat'] as $k_ls => $v_ls): ?>
							<?php $idx++; ?>
							<tr>
								<td class="text-center no_urut"><?php echo $idx; ?></td>
								<td class="text-right"><?php echo angkaDecimalFormat($v_ls['bb'], 3); ?></td>
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
			<textarea id="keterangan_rhk" class="col-xs-12 form-control keterangan" name="keterangan" rows="4" data-required="1" readonly><?php echo strtoupper($data['keterangan']); ?></textarea>
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

	<?php if ( $data['status'] <> 2 ) { ?>
		<div class="col-xs-12">
			<div class="col-xs-12" style="padding: 0px 0px 5px 0px;">
				<?php if ( $akses['a_edit'] == 1 ) { ?>
					<button type="button" class="btn btn-primary pull-right" onclick="lhk.change_tab(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
				<?php } ?>
			</div>
			<div class="col-xs-12" style="padding: 5px 0px 0px 0px;">
				<?php if ( $akses['a_delete'] == 1 ) { ?>
					<button type="button" class="btn btn-danger pull-right" onclick="lhk.delete(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-trash"></i> Hapus</button>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
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
				        				<th class="col-xs-5">Parameter</th>
				        				<th class="col-xs-6">Keterangan</th>
				        				<th class="col-xs-1">Lampiran</th>
				        			</tr>
				        		</thead>
				        		<tbody>
				        			<?php foreach ($data['lhk_nekropsi'] as $k_ln => $v_ln): ?>
					        			<tr data-id="<?php echo $v_ln['id']; ?>">
					        				<td><?php echo $v_ln['d_nekropsi']['keterangan']; ?></td>
					        				<td><?php echo $v_ln['keterangan']; ?></td>
					        				<td>
												<div class="col-xs-12 no-padding">
													<?php $json_url = null; ?>
													<?php if ( !empty($v_ln['foto_nekropsi']) ): ?>
														<?php
															$_url = array();
															foreach ($v_ln['foto_nekropsi'] as $k_fn => $v_fn) {
																array_push($_url, $v_fn['path']);
															}

															$json_url = json_encode($_url, JSON_FORCE_OBJECT);
														?>
													<?php endif ?>
													<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='uploads\LHK\NEKROPSI\<?php echo $data['id'].'_'.$v_ln['d_nekropsi']['id']; ?>' data-jenis="view" style="margin-top: 0px;">
													<!-- <div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='<?php echo $json_url; ?>' data-jenis="view" style="margin-top: 0px;"> -->
														<label class="col-xs-12 no-padding">
															<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
														</label>
													</div>
												</div>
					        					<!-- <?php if ( !empty($v_ln['foto_nekropsi']) ): ?>
						        					<div class="col-xs-12 no-padding">
														<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='uploads/LHK/NEKROPSI/<?php echo $data['id'].'_'.$v_ln['d_nekropsi']['id']; ?>' style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
													</div>
												<?php else: ?>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-12 no-padding attachment" style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
																<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_nekropsi" data-name="name" data-required="1" onchange="lhk.cek_file_exist(this)" />
											                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Nekropsi"></i> 
											              	</label>
															<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
														</div>
														<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
														<div class="col-xs-12 no-padding upload hide" data-title="Upload" onclick="lhk.upload_nekropsi(this)" style="margin-top: 0px;">
															<label class="col-xs-12 no-padding">
											                	<i class="fa fa-upload cursor-p col-xs-12 text-center"></i> 
											              	</label>
														</div>
													</div>
					        					<?php endif ?> -->
					        				</td>
					        			</tr>
				        			<?php endforeach ?>
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
				        			</tr>
				        		</thead>
				        		<tbody>
				        			<?php foreach ($data['lhk_solusi'] as $k_ls => $v_ls): ?>
					        			<tr>
					        				<td><?php echo $v_ls['d_solusi']['keterangan']; ?></td>
					        			</tr>
				        			<?php endforeach ?>
				        		</tbody>
				        	</table>
				        </small>
		        	</div>
		        </div>
		    </div>
		</div>
	</div>
</div>