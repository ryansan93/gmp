<?php 
	// NOTE : GET ID NAMA_LAMPIRAN
	$id_nama_hrg_voadip_sup = null;
	$id_nama_hrg_doc_sup = null;
	$id_nama_hrg_pakan_sup = null;
	// $id_nama_oa_doc = null;
	// $id_nama_oa_pakan = null;
	foreach ($nama_lampiran as $key => $value) {
		if ( $value['sequence'] == 1 ) {
			$id_nama_hrg_voadip_sup = $value['id'];
		}
		if ( $value['sequence'] == 2 ) {
			$id_nama_hrg_doc_sup = $value['id'];
		}
		if ( $value['sequence'] == 3 ) {
			$id_nama_hrg_pakan_sup = $value['id'];
		}
		// if ( $value['sequence'] == 4 ) {
		// 	$id_nama_oa_doc = $value['id'];
		// }
		// if ( $value['sequence'] == 5 ) {
		// 	$id_nama_oa_pakan = $value['id'];
		// }
	}
?>
<div class="col-md-12 no-padding">
	<input type="hidden" data-id="<?php echo $data['id']; ?>" >
	<div class="col-md-1 no-padding pull-left">
		<h5>Tgl Berlaku : </h5>
	</div>
	<div class="col-md-2 no-padding action">
	    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
	        <input type="text" class="form-control text-center" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<!-- POLA KEMITRAAN -->
<div class="panel-body no-padding">
	<div class="col-md-12 no-padding">
		<div class="col-md-1 no-padding pull-left">
			<h5>Pola Kemitraan</h5>
		</div>
		<div class="col-md-2 no-padding action">
		    <select class="form-control" name="pola_kemitraan" onchange="kbd.load_form_spp()" data-required="1">
					<?php foreach ($pola_kemitraan as $k_pola => $v_pola_km) { ?>
						<?php  
							$selected = null;
							if ( $data['hitung_budidaya']['pola_kemitraan'] == $v_pola_km['id'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_pola_km['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_pola_km['item']); ?></option>
					<?php } ?>
				</select>
		</div>
		<div class="col-md-1 no-padding pull-left"></div>
		<div class="col-md-1 no-padding pull-left">
			<h5>Pola Budidaya</h5>
		</div>
		<div class="col-md-2 no-padding action">
		    <select class="form-control" name="pola_budidaya" data-required="1">
					<?php foreach ($pola_budidaya as $k_pola => $v_pola_bdy) { ?>
						<?php  
							$selected = null;
							if ( $data['pola'] == $v_pola_km['id'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_pola_bdy['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_pola_bdy['item']); ?></option>
					<?php } ?>
				</select>
		</div>
		<div class="col-md-1 no-padding pull-left"></div>
		<div class="col-md-1 no-padding pull-left">
			<h5>Item Pola</h5>
		</div>
		<div class="col-md-2 no-padding action">
		    <select class="form-control" name="item_pola" data-required="1">
		    	<?php 
		    		$select1 = null;
		    		$select2 = null;
		    		$select3 = null;
		    		$select4 = null;
		    		$select5 = null;
		    		$select6 = null;
		    		if ( $data['item_pola'] == '0' ){
		    			$select1 = 'selected';
		    		} else if ( $data['item_pola'] == '+25' ){
		    			$select2 = 'selected'; 
		    		} else if ( $data['item_pola'] == '+50' ){
		    			$select3 = 'selected';
		    		} else if ( $data['item_pola'] == '-250' ){
		    			$select4 = 'selected';
		    		} else if ( $data['item_pola'] == '-500' ){
		    			$select5 = 'selected';
		    		} else if ( $data['item_pola'] == '-1000' ){
		    			$select6 = 'selected';
		    		}
		    	?>
				<option value="0" <?php echo $select1; ?> >0</option>
				<option value="+25" <?php echo $select2; ?> >+25</option>
				<option value="+50" <?php echo $select3; ?> >+50</option>
				<option value="-250" <?php echo $select4; ?> >-250</option>
				<option value="-500" <?php echo $select5; ?> >-500</option>
				<option value="-1000" <?php echo $select6; ?> >-1000</option>
			</select>
		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<div class="col-sm-7 no-padding">
			<div class="panel-body no-padding">
				<label class="control-label">Harga Sapronak</label>
			</div>
			<!-- HARGA SAPRONAK -->
			<div class="panel panel-default" style="margin-right: 10px;">
				<div class="panel-body">
					<table class="table no-border">
						<tbody>
							<tr class="data">
								<td class="col-sm-3 text-right"><span>Biaya Operasional</span></td>
								<td></td>
								<td class="text-left">
									<div class="col-sm-5 no-padding">
										<input class="form-control harga_sapronak text-right" name="biaya_opr" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['biaya_ops']); ?>" />
									</div>
								</td>
								<td></td>
							</tr>
							<tr class="data">
								<td class="text-right"><span>Jaminan Keuntungan</span></td>
								<td></td>
								<td class="text-left">
									<div class="col-sm-5 no-padding">
										<input class="form-control harga_sapronak text-right" name="jaminan_keuntungan" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['jam_keuntungan']); ?>" /></td>
									</div>
								<td></td>
							</tr>
							<tr>
								<td colspan="3">
									<table class="table no-border custom_table">
										<thead>
											<tr class="head">
												<td></td>
												<td class="text-center"><b><u>Harga Supplier</u></b></td>
												<td></td>
												<!-- <td class="text-center"><b><u>Ongkos Angkut</u></b></td>
												<td></td> -->
												<td class="text-center"><b><u>Harga Mitra</u></b></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td class="text-center"><b><u>Review Dirut</u></b></td>
												<?php endif ?>
											</tr>
										</thead>
										<tbody>
											<tr class="row-data v-center">
												<td class="text-right">VOADIP</td>
												<td><input class="form-control harga_sapronak text-right" name="voadip_supl" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['voadip']); ?>" /></td>
												<td>
													<a name="dokumen" class="text-right voadip" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $l_voadip_sup['path']; ?>" title="<?php echo $l_voadip_sup['filename']; ?>" ><i class="fa fa-file"></i></a>
													<label class="" data-idnama="<?php echo $id_nama_hrg_voadip_sup ?>">
							                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="kbd.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx" data-old="<?php echo $l_voadip_sup['path']; ?>">
							                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment VOADIP"></i> 
							                      	</label>
												</td>
												<!-- <td colspan="2"></td> -->
												<td><input class="form-control harga_sapronak text-right" name="voadip_mitra" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['voadip_mitra']); ?>" /></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td><input class="form-control harga_sapronak text-right" name="voadip_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right col-sm-1">DOC</td>
												<td class="col-sm-2"><input class="form-control harga_sapronak text-right" name="doc_supl" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['doc']); ?>" /></td>
												<td class="col-sm-1">
													<a name="dokumen" class="text-right doc" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $l_doc_sup['path']; ?>" title="<?php echo $l_doc_sup['filename']; ?>" ><i class="fa fa-file"></i></a>
													<label class="" data-idnama="<?php echo $id_nama_hrg_doc_sup; ?>">
							                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="kbd.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx" data-old="<?php echo $l_doc_sup['path']; ?>">
							                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment DOC"></i> 
							                      	</label>
												</td>															
												<!-- <td class="col-sm-2"><input class="form-control harga_sapronak text-right" name="oa_doc" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['oa_doc']); ?>" /></td>
												<td class="col-sm-1">
													<a name="dokumen" class="text-right oa_doc" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $l_oa_doc['path']; ?>" title="<?php echo $l_oa_doc['filename']; ?>" ><i class="fa fa-file"></i></a>
													<label class="" data-idnama="<?php echo $id_nama_oa_doc; ?>">
							                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="kbd.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx" data-old="<?php echo $l_oa_doc['path']; ?>">
							                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment OA DOC"></i> 
							                      	</label>
												</td> -->
												<td class="col-sm-2"><input class="form-control harga_sapronak text-right" name="doc_mitra" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['doc_mitra']); ?>" /></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td class="col-sm-2"><input class="form-control harga_sapronak text-right" name="doc_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right">Pakan 1</td>
												<td><input class="form-control harga_sapronak text-right" name="pakan1_supl" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan1']); ?>" /></td>
												<td>
													<a name="dokumen" class="text-right pakan1" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $l_pakan_sup['path']; ?>" title="<?php echo $l_pakan_sup['filename']; ?>" ><i class="fa fa-file"></i></a>
													<label class="" data-idnama="<?php echo $id_nama_hrg_pakan_sup; ?>">
							                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="kbd.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx" data-old="<?php echo $l_pakan_sup['path']; ?>">
							                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment Pakan"></i> 
							                      	</label>
												</td>
												<!-- <td><input class="form-control harga_sapronak text-right" name="oa_pakan" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['oa_pakan']); ?>" /></td>
												<td>
													<a name="dokumen" class="text-right oa_pakan" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $l_oa_pakan['path']; ?>" title="<?php echo $l_oa_pakan['filename']; ?>" ><i class="fa fa-file"></i></a>
													<label class="" data-idnama="<?php echo $id_nama_oa_pakan; ?>">
							                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="kbd.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx" data-old="<?php echo $l_oa_pakan['path']; ?>">
							                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment OA Pakan"></i> 
							                      	</label>
												</td> -->
												<td><input class="form-control harga_sapronak hrg_pakan text-right" name="pakan1_mitra" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan1_mitra']); ?>" /></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td><input class="form-control harga_sapronak text-right" name="pakan1_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right">Pakan 2</td>
												<td><input class="form-control harga_sapronak text-right" name="pakan2_supl" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan2']); ?>" /></td>
												<td></td>
												<td><input class="form-control harga_sapronak hrg_pakan text-right" name="pakan2_mitra" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan2_mitra']); ?>" /></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td><input class="form-control harga_sapronak text-right" name="pakan2_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php endif ?>
											</tr>
											<tr class="row-data v-center">
												<td class="text-right">Pakan 3</td>
												<td><input class="form-control harga_sapronak text-right" name="pakan3_supl" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan3']); ?>" /></td>
												<td></td>
												<td><input class="form-control harga_sapronak hrg_pakan text-right" name="pakan3_mitra" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_sapronak'][0]['pakan3_mitra']); ?>" /></td>
												<?php if ( $akses['a_approve'] == 1 ): ?>
													<td><input class="form-control harga_sapronak text-right" name="pakan3_dirut" data-required="1" data-tipe="decimal" maxlength="9" /></td>
												<?php endif ?>
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

		<div class="col-sm-5 no-padding">
			<div class="panel-body no-padding">
				<label class="control-label">Performa</label>
			</div>
			<!-- STANDAR PERFORMA -->
			<div class="panel panel-default">
				<div class="panel-body">
					<!-- <small> -->
						<table class="table no-border custom_table">
							<tbody>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>DH (%)</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="dh" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_performa'][0]['dh']); ?>" /></td>
									<td class="col-sm-3 text-right">Kebutuhan Pakan</td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="kebutuhan_pakan" data-required="1" data-tipe="decimal3" maxlength="9" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['tot_pakan'], 3); ?>" /></td>
								</tr>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>BB (Kg)</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="bb" data-required="1" data-tipe="decimal3" maxlength="7" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['bb'], 3); ?>" /></td>
									<td class="col-sm-3 text-right">Pakan 1</td>
									<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan1" data-required="1" data-tipe="decimal3" maxlength="9" onchange="kbd.hitJmlPakan2()" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan1'], 3); ?>" /></td>
								</tr>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>FCR</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="fcr" data-required="1" data-tipe="decimal3" maxlength="7" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['fcr'], 3); ?>" /></td>
									<td class="col-sm-3 text-right">Pakan 2</td>
									<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan2" data-required="1" data-tipe="decimal3" maxlength="9" onchange="kbd.hitJmlPakan3()" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan2'], 3); ?>" /></td>
								</tr>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>Umur (Hari)</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="umur" data-required="1" data-tipe="integer" maxlength="7" value="<?php echo angkaRibuan($data['harga_performa'][0]['umur']); ?>" /></td>
									<td class="col-sm-3 text-right">Pakan 3</td>
									<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan3" data-required="1" data-tipe="decimal3" maxlength="9" value="<?php echo angkaDecimalFormat($data['harga_performa'][0]['pakan3'], 3); ?>" /></td>
								</tr>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>IP</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="ip" data-required="1" data-tipe="integer" maxlength="7" value="<?php echo angkaRibuan($data['harga_performa'][0]['ip']); ?>" /></td>
									<td class="col-sm-3 text-right"></td>
									<td class="col-sm-3 text-left"></td>
								</tr>
								<tr class="data v-center">
									<td class="col-sm-2 text-right"><span>IE</span></td>
									<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="ie" data-required="1" data-tipe="decimal" maxlength="9" value="<?php echo angkaDecimal($data['harga_performa'][0]['ie']); ?>" /></td>
									<td class="col-sm-3 text-right"></td>
									<td class="col-sm-3 text-left"></td>
								</tr>
							</tbody>
						</table>
					<!-- </small> -->
				</div>
			</div>
			<div class="col-sm-12 text-right no-padding">
				<!-- <button class="btn btn-default" name="hrg_kesepakatan" data-toggle="modal" data-target="#myModal" onclick="kbd.hpp()">Ajukan Harga Kesepakatan</button> -->
				<a class="btn btn-default" name="hrg_kesepakatan" data-toggle="modal" data-target="#modalHrgKesepakatan" onclick="kbd.hpp()">Ajukan Harga Kesepakatan</a>
			</div>
		</div>

		<div class="col-sm-12 no-padding reguler aktif">
			<hr>
			<div class="panel-body col-sm-6 no-padding">
				<label class="control-label">Range Standar Pemakaian Pakan</label>
			</div>
			<div class="panel-body col-sm-6 no-padding">
				<label class="control-label">Selisih Pemakaian Pakan</label>
			</div>
			<!-- PAKAI PAKAN -->
			<div class="col-sm-6 no-padding">
				<div class="panel panel-default" style="margin-right: 10px;">
					<div class="panel-body">
						<table class="table table-bordered range">
							<thead>
								<tr>
									<th colspan="3" class="col-sm-6 text-center">Range</th>
									<th class="col-sm-6 text-center">Standar Minimum</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data['standar_pakan'] as $key => $v_sp): ?>
									<tr class="data v-center">
										<td class="text-center bb_awal"><?php echo angkaDecimal($v_sp['bb_awal']); ?></td>
										<td class="text-center">-</td>
										<td class="text-center bb_akhir"><?php echo ($v_sp['bb_akhir'] == 0) ? '>=' : angkaDecimal($v_sp['bb_akhir']); ?></td>
										<td><input class="form-control standar_min text-right" type="text" data-tipe="decimal3" maxlength="9" value="<?php echo angkaDecimalFormat($v_sp['standar_min'], 3); ?>" ></td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-sm-6 no-padding" style="padding-left: 5px;">
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table table-bordered selisih">
							<thead>
								<tr>
									<th colspan="3" class="col-sm-4 text-center">Range</th>
									<th class="col-sm-4 text-center">Selisih (%)</th>
									<th class="col-sm-4 text-center">Tarif (Rp)</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data['selisih_pakan'] as $key => $v_sp): ?>
									<tr class="data v-center">
										<td class="text-center range_awal"><?php echo ($v_sp['range_awal'] == 0) ? 0 : angkaDecimalFormat($v_sp['range_awal'], 3); ?></td>
										<td class="text-center">-</td>
										<td class="text-center range_akhir"><?php echo ($v_sp['range_akhir'] == 0) ? '>=' : angkaDecimalFormat($v_sp['range_akhir'], 3); ?></td>
										<td><input class="form-control selisih text-right" type="text" data-tipe="integer" maxlength="3" value="<?php echo angkaRibuan($v_sp['selisih']); ?>" ></td>
										<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6" value="<?php echo angkaRibuan($v_sp['tarif']); ?>" ></td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- NOTE -->
		<div class="col-sm-12 no-padding">
			<div class="col-sm-7 no-padding">
				<label class="control-label">Note :</label>
			</div>
			<div class="col-sm-12 no-padding">
				<textarea class="form-control aktif" data-required="1"><?php echo $data['note']; ?></textarea>
			</div>
		</div>

		<div class="col-sm-12 no-padding">
			<hr>
			<div class="panel-body col-sm-5 no-padding">
				<label class="control-label">Koordinator Wilayah</label>
			</div>
			<div class="panel-body col-sm-7 no-padding">
				<label class="control-label" style="padding-left: 10px;">Bonus</label>
			</div>
			<!-- PERWAKILAN -->
			<div class="col-sm-5 no-padding">
				<div class="panel panel-default" style="margin-right: 10px;">
					<div class="panel-body">
						<?php 
						if ( count($perwakilan) % 2 == 0 ) {
							$baris = (count($perwakilan)/2) + 1;
						} else {
							$baris = ceil(count($perwakilan)/2);
						}?>
						<div class="col-sm-6 perwakilan">
							<?php for ($i=0; $i < $baris; $i++) { ?>
								<?php 
									$checked = null;
									foreach ($data['hitung_budidaya']['perwakilan_maping'] as $key => $v_pwk):
										if ( $perwakilan[$i]['id'] == $v_pwk['id_pwk'] ) {
											$checked = 'checked';
											break;
										}
									endforeach 
								?>
								<div class="checkbox checkbox-primary d-flex align-items-center">
				                    <input type="checkbox" class="styled styled-primary" name="mark" data-id="<?php echo $perwakilan[$i]['id']; ?>" data-name="<?php echo $perwakilan[$i]['nama']; ?>" <?php echo $checked; ?> >
				                    <span><?php echo $perwakilan[$i]['nama']; ?></span>
								</div>
							<?php } ?>
						</div>
						<div class="col-sm-6 perwakilan">
							<?php for ($j=0; $j < $baris; $j++) { ?>
								<?php if ( isset($perwakilan[$j+$baris]) ): ?>
									<?php 
										$checked = null;
										foreach ($data['hitung_budidaya']['perwakilan_maping'] as $key => $v_pwk):
											if ( $perwakilan[$j+$baris]['id'] == $v_pwk['id_pwk'] ) {
												$checked = 'checked';
												break;
											}
										endforeach 
									?>
									<div class="checkbox checkbox-primary d-flex align-items-center">
					                    <input type="checkbox" class="styled styled-primary" name="mark" data-id="<?php echo $perwakilan[$j+$baris]['id']; ?>" data-name="<?php echo $perwakilan[$j+$baris]['nama']; ?>" <?php echo $checked; ?> >
					                    <span><?php echo $perwakilan[$j+$baris]['nama']; ?></span>
									</div>
								<?php endif ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>

			<!-- PERHITUNGAN BUDIDAYA -->
			<div class="col-md-7" style="padding-left: 10px;">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12" style="padding: 5px;">
								<div class="col-md-2 text-right">
									<h5>Bonus FCR :</h5>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control text-right" name="bonus_fcr" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['hitung_budidaya']['bonus_fcr']); ?>" />
								</div>
								<div class="col-md-1"></div>
								<div class="col-md-2 text-right">
									<h5>Bonus DH :</h5>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control text-right" name="bonus_dh" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['hitung_budidaya']['bonus_dh']); ?>" />
								</div>
							</div>
							<div class="col-md-12" style="padding: 5px;">
								<div class="col-md-2 text-right">
									<h5>Bonus CH :</h5>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control text-right" name="bonus_ch" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['hitung_budidaya']['bonus_ch']); ?>" />
								</div>
								<div class="col-md-1"></div>
								<div class="col-md-2 text-right">
									<h5>Bonus BB :</h5>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control text-right" name="bonus_bb" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['hitung_budidaya']['bonus_bb']); ?>" />
								</div>
							</div>
							<div class="col-md-12" style="padding: 5px;">
								<div class="col-md-2 text-right">
									<h5>Bonus IP :</h5>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control text-right" name="bonus_ip" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['hitung_budidaya']['bonus_ip']); ?>" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	<!---->

<!-- Modal -->
<div id="modalHrgKesepakatan" class="bootbox modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Harga Kesepakatan</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
		        <div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
							<table class="table no-border custom_table">
								<tbody>
									<tr>
										<td class="col-sm-1">
											<div class="col-sm-1 text-left">
												<label class="control-label">HPP</label>
											</div>
										</td>
										<td class="col-sm-11">
											<div class="col-sm-4">
												<input type="text" class="form-control text-right hpp" name="hpp" readonly />
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-sm-12">
							<table class="table no-border custom_table">
								<thead>
									<tr>
										<th colspan="2">Range (Kg)</th>
										<th>Harga</th>
										<th>HPP</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										$range_awal = $range_akhir = null; 
										$_range_awal = $_range_akhir = null; 
									?>
									<?php for ($i=0; $i < 9; $i++) { ?>
										<?php 
											if ($i == 0) {
												$_range_awal = '<=';
												$range_akhir = 1.20;
												$_range_akhir = angkaDecimal($range_akhir);
											} elseif ($i == 7) {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = $range_awal + 0.28;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											} elseif ($i == 8) {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = 3.00;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											} elseif ($i >= 3) {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = $range_awal + 0.09;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											} else {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = $range_awal + 0.14;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											}

											$hrg = 0;
											if ( !empty($data['harga_sepakat'][$i]) ) {
												$hrg = $data['harga_sepakat'][$i]['harga'];
											}
										?>
										<tr class="data v-center">
											<td class="col-sm-2"><input type="text" class="form-control text-center range_min" readonly value="<?php echo $_range_awal;?>" /></td>
											<td class="col-sm-2"><input type="text" class="form-control text-center range_max" readonly value="<?php echo $_range_akhir;?>" /></td>
											<td class="col-sm-4"><input type="text" class="form-control text-right" name="harga" data-tipe="integer" maxlength="6" value="<?php echo angkaRibuan($hrg); ?>" /></td>
											<td class="col-sm-1 text-center">
												<?php 
													$checked = null;
													if ( !empty($data['harga_sepakat'][$i]) ) {
														if ( $data['harga_sepakat'][$i]['hpp'] == 1 ) {
															$checked = 'checked';
														}
													}
												?>
												<input type="checkbox" class="styled styled-primary" name="mark" <?php echo $checked; ?> >
												<label></label>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a class="btn btn-default" onclick="kbd.save_harga_kesepakatan()">Set</a>
			</div>
	    </div>
	</div>
</div>
<div class="col-sm-12 text-right">
	<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="kbd.edit()"> 
		<i class="fa fa-edit" aria-hidden="true"></i> Edit
	</button>

	<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="kbd.save_copy()" style="margin-right: 10px;"> 
		<i class="fa fa-save" aria-hidden="true"></i> Simpan Baru
	</button>
	<!-- <button class="btn btn-primary save" type="button" onclick="kbd.save_harga_sk()">Simpan</button> -->
</div>