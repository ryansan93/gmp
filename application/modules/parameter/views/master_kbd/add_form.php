<div class="col-lg-12 no-padding">
	<div class="col-lg-1 no-padding pull-left">
		<h5>Tgl Berlaku</h5>
	</div>
	<div class="col-lg-2 no-padding action">
	    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
	        <input type="text" class="form-control text-center" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
	<div class="col-lg-1 no-padding pull-left"></div>
	<div class="col-lg-1 no-padding pull-left">
		<h5>Perusahaan</h5>
	</div>
	<div class="col-lg-2 no-padding action">
	    <select class="form-control" name="perusahaan" data-required="1">
	    	<!-- <option value="">-- Pilih Perusahaan --</option> -->
			<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan) { ?>
				<option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
			<?php } ?>
		</select>
	</div>
</div>
<!-- POLA KEMITRAAN -->
<div class="panel-body no-padding">
	<div class="col-lg-12 no-padding">
		<div class="col-lg-1 no-padding pull-left">
			<h5>Pola Kemitraan</h5>
		</div>
		<div class="col-lg-2 no-padding action">
		    <select class="form-control" name="pola_kemitraan" onchange="kbd.load_form_spp()" data-required="1">
				<?php foreach ($pola_kemitraan as $k_pola => $v_pola_km) { ?>
					<option value="<?php echo $v_pola_km['id']; ?>"><?php echo strtoupper($v_pola_km['item']); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="col-lg-1 no-padding pull-left"></div>
		<div class="col-lg-1 no-padding pull-left">
			<h5>Pola Budidaya</h5>
		</div>
		<div class="col-lg-2 no-padding action">
		    <select class="form-control" name="pola_budidaya" data-required="1">
				<?php foreach ($pola_budidaya as $k_pola => $v_pola_bdy) { ?>
					<option value="<?php echo $v_pola_bdy['id']; ?>"><?php echo strtoupper($v_pola_bdy['item']); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="col-lg-1 no-padding pull-left"></div>
		<div class="col-lg-1 no-padding pull-left">
			<h5>Item Pola</h5>
		</div>
		<div class="col-lg-2 no-padding action">
		    <select class="form-control" name="item_pola" data-required="1">
				<option value="0">0</option>
				<option value="+25">+25</option>
				<option value="+50">+50</option>
				<option value="-250">-250</option>
				<option value="-500">-500</option>
				<option value="-1000">-1000</option>
			</select>
		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-body no-padding">
		<div class="col-sm-7 no-padding sapronak">
			<!-- HARGA SAPRONAK -->
			<div class="panel-body hrg_sapronak">
				<fieldset>
					<legend>
						<div class="col-sm-8 no-padding">
							Harga Sapronak
						</div>
						<div class="col-sm-4 no-padding">
							<div class="col-sm-4 no-padding pull-right" style="margin-top: 3px;">
								<button type="button" class="btn btn-danger pull-right" onclick="kbd.removeSapronak(this)" style="font-size: 10px; padding: 0px 3px 0px 3px;"><i class="fa fa-trash"></i></button>
							</div>
							<div class="col-sm-1 no-padding pull-right" style="margin-left: 5px; margin-right: 4px;">|</div>
							<div class="col-sm-4 no-padding pull-right" style="margin-top: 3px;">
								<button type="button" class="btn btn-primary pull-right" onclick="kbd.addSapronak(this)" style="font-size: 10px; padding: 0px 3px 0px 3px;"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					</legend>
					<div class="col-sm-12 no-padding">
						<div class="col-sm-2"><h5>Supplier</h5></div>
						<div class="col-sm-1 no-padding" style="width: 3%;"><h5>:</h5></div>
						<div class="col-sm-6">
							<select class="form-control supplier" data-required="1">
								<option value="">Pilih Supplier</option>
								<?php foreach ($supplier as $k_supl => $v_supl): ?>
									<option value="<?php echo $v_supl['nomor']; ?>"><?php echo strtoupper($v_supl['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-sm-12"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
					<div class="col-sm-12 no-padding">
						<div class="col-sm-12 no-padding hrg_sapronak_doc">
							<div class="col-sm-12"><label class="control-label" style="text-decoration: underline;">DOC</label></div>
							<div class="col-sm-12 no-padding row_doc" style="margin-top: 5px;">
								<div class="col-sm-4">
									<select class="form-control" data-required="1">
										<option value="">Pilih Barang</option>
										<?php foreach ($jenis_doc as $k_doc => $v_doc): ?>
											<option value="<?php echo $v_doc['kode']; ?>"><?php echo strtoupper($v_doc['nama']); ?></option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_supplier" data-tipe="integer" placeholder="Harga Supplier" data-required="1">
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_peternak" data-tipe="integer" placeholder="Harga Peternak" data-required="1">
								</div>
								<div class="col-sm-2 btn_action" style="padding-left: 0px;">
									<button type="button" class="btn btn-danger pull-right" onclick="kbd.removeRowDoc(this)"><i class="fa fa-times"></i></button>
									<button type="button" class="btn btn-primary pull-right" onclick="kbd.addRowDoc(this)" style="margin-right: 3px;"><i class="fa fa-plus"></i></button>
								</div>
							</div>
						</div>
						<div class="col-sm-12 no-padding">
							<div class="col-sm-12"><label class="control-label" style="text-decoration: underline;">PAKAN</label></div>
							<div class="col-sm-12 no-padding hrg_sapronak_pakan1" style="margin-top: 5px;">
								<div class="col-sm-4">
									<select class="form-control pakan1" data-required="1">
										<option value="">PAKAN 1</option>
										<?php foreach ($jenis_pakan as $k_pakan => $v_pakan): ?>
											<option value="<?php echo $v_pakan['kode']; ?>"><?php echo strtoupper($v_pakan['nama']); ?></option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_supplier" data-tipe="integer" placeholder="Harga Supplier" data-required="1">
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_peternak" data-tipe="integer" placeholder="Harga Peternak" data-required="1">
								</div>
							</div>
							<div class="col-sm-12 no-padding hrg_sapronak_pakan2" style="margin-top: 5px;">
								<div class="col-sm-4">
									<select class="form-control pakan2" data-required="1">
										<option value="">PAKAN 2</option>
										<?php foreach ($jenis_pakan as $k_pakan => $v_pakan): ?>
											<option value="<?php echo $v_pakan['kode']; ?>"><?php echo strtoupper($v_pakan['nama']); ?></option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_supplier" data-tipe="integer" placeholder="Harga Supplier" data-required="1">
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_peternak" data-tipe="integer" placeholder="Harga Peternak" data-required="1">
								</div>
							</div>
							<div class="col-sm-12 no-padding hrg_sapronak_pakan3" style="margin-top: 5px;">
								<div class="col-sm-4">
									<select class="form-control pakan3" data-required="1">
										<option value="">PAKAN 3</option>
										<?php foreach ($jenis_pakan as $k_pakan => $v_pakan): ?>
											<option value="<?php echo $v_pakan['kode']; ?>"><?php echo strtoupper($v_pakan['nama']); ?></option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_supplier" data-tipe="integer" placeholder="Harga Supplier" data-required="1">
								</div>
								<div class="col-sm-3">
									<input type="text" class="form-control text-right" name="harga_peternak" data-tipe="integer" placeholder="Harga Peternak" data-required="1">
								</div>
							</div>
						</div>
						<div class="col-sm-12 no-padding">
							<div class="col-sm-12"><label class="control-label" style="text-decoration: underline;">LAMPIRAN</label></div>
							<div class="col-sm-12">
								<div class="col-sm-1 no-padding"><label class="control-label">DOC</label></div>
								<div class="col-sm-1 no-padding" style="width: 1%;"><label class="control-label">:</label></div>
								<div class="col-sm-10" style="padding-top: 7px;">
									<a name="dokumen" class="text-right hide doc" target="_blank" style="padding-right: 10px;">
										<!-- <i class="fa fa-file"></i> -->
									</a>
									<label class="">
			                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran_doc no-check" type="file" onchange="kbd.showNameFile(this)" data-name="name" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" data-idnama="<?php echo $nama_lampiran[0]['id']; ?>">
			                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment VOADIP"></i> 
			                      	</label>
								</div>
							</div>
							<div class="col-sm-12">
								<div class="col-sm-1 no-padding"><label class="control-label">PAKAN</label></div>
								<div class="col-sm-1 no-padding" style="width: 1%;"><label class="control-label">:</label></div>
								<div class="col-sm-10" style="padding-top: 7px;">
									<a name="dokumen" class="text-right hide pakan" target="_blank" style="padding-right: 10px;">
										<!-- <i class="fa fa-file"></i> -->
									</a>
									<label class="">
			                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran_pakan no-check" type="file" onchange="kbd.showNameFile(this)" data-name="name" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" data-idnama="<?php echo $nama_lampiran[0]['id']; ?>">
			                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment VOADIP"></i> 
			                      	</label>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>

		<div class="col-sm-5 no-padding">
			<!-- STANDAR PERFORMA -->
			<div class="panel-body">
				<fieldset>
					<legend>Performa</legend>
					<table class="table no-border custom_table">
						<tbody>
							<tr class="data v-center">
								<td class="col-sm-2 text-right"><span>DH (%)</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="dh" data-required="1" data-tipe="decimal" maxlength="9" /></td>
								<td class="col-sm-3 text-right">Kebutuhan Pakan</td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="kebutuhan_pakan" data-required="1" data-tipe="decimal3" maxlength="9" /></td>
							</tr>
							<tr class="data v-center">
								<td class="col-sm-2 text-right"><span>BB (Kg)</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="bb" data-required="1" data-tipe="decimal3" maxlength="7"/></td>
								<td class="col-sm-3 text-right pakan1" data-kode="">Pakan 1</td>
								<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan1" data-required="1" data-tipe="decimal3" maxlength="9" onchange="kbd.hitJmlPakan2()" /></td>
							</tr>
							<tr class="data v-center">
								<td class="col-sm-2 text-right"><span>FCR</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="fcr" data-required="1" data-tipe="decimal3" maxlength="7" /></td>
								<td class="col-sm-3 text-right pakan2" data-kode="">Pakan 2</td>
								<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan2" data-required="1" data-tipe="decimal3" maxlength="9" onchange="kbd.hitJmlPakan3()" /></td>
							</tr>
							<tr class="data v-center">
								<td class="col-sm-2 text-right"><span>Umur (Hari)</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="umur" data-required="1" data-tipe="integer" maxlength="7" /></td>
								<td class="col-sm-3 text-right pakan3" data-kode="">Pakan 3</td>
								<td class="col-sm-3 text-left"><input class="form-control performa jml_pakan text-right" name="pakan3" data-required="1" data-tipe="decimal3" maxlength="9" /></td>
							</tr>
							<tr class="data v-center">
								<td class="col-sm-2 text-right"><span>IP</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="ip" data-required="1" data-tipe="integer" maxlength="7" /></td>
								<td class="col-sm-3 text-right"><span>IE</span></td>
								<td class="col-sm-3 text-left"><input class="form-control performa text-right" name="ie" data-required="1" data-tipe="decimal" maxlength="9" /></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="col-sm-12 text-right">
				<!-- <button class="btn btn-default" name="hrg_kesepakatan" data-toggle="modal" data-target="#myModal" onclick="kbd.hpp()">Ajukan Harga Kesepakatan</button> -->
				<a class="btn btn-default" name="hrg_kesepakatan" data-toggle="modal" data-target="#modalHrgKesepakatan" onclick="kbd.hpp()">Ajukan Harga Kesepakatan</a>
			</div>
		</div>
		<div class="col-sm-12"><hr></div>
		<div class="col-sm-12 no-padding reguler aktif">
			<div class="panel-body col-sm-6">
				<fieldset>
					<legend>Bonus</legend>
					<table class="table table-bordered bonus">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-6 text-center">Nilai IP</th>
								<th class="col-sm-3 text-center">Bonus Kematian</th>
								<th class="col-sm-3 text-center">Bonus Harga</th>
							</tr>
						</thead>
						<tbody>
							<tr class="data v-center">
								<td class="text-center ip_awal">
									<input type="text" class="form-control text-right ip_awal" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(240); ?>" maxlength="6">
									<!-- 240 -->
								</td>
								<td class="text-center">-</td>
								<td class="text-center ip_akhir">
									<input type="text" class="form-control text-right ip_akhir" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(260); ?>" maxlength="6">
									<!-- 260 -->
								</td>
								<td><input class="form-control bonus_kematian text-right" type="text" data-tipe="integer" maxlength="9"></td>
								<td><input class="form-control bonus_harga text-right" type="text" data-tipe="decimal" maxlength="9"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center ip_awal">
									<input type="text" class="form-control text-right ip_awal" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(260); ?>" maxlength="6">
									<!-- 260 -->
								</td>
								<td class="text-center">-</td>
								<td class="text-center ip_akhir">
									<input type="text" class="form-control text-right ip_akhir" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(280); ?>" maxlength="6">
									<!-- 280 -->
								</td>
								<td><input class="form-control bonus_kematian text-right" type="text" data-tipe="integer" maxlength="9"></td>
								<td><input class="form-control bonus_harga text-right" type="text" data-tipe="decimal" maxlength="9"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center ip_awal">
									<input type="text" class="form-control text-right ip_awal" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(280); ?>" maxlength="6">
									<!-- 280 -->
								</td>
								<td class="text-center">-</td>
								<td class="text-center ip_akhir">
									<input type="text" class="form-control text-right ip_akhir" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(300); ?>" maxlength="6">
									<!-- 300 -->
								</td>
								<td><input class="form-control bonus_kematian text-right" type="text" data-tipe="integer" maxlength="9"></td>
								<td><input class="form-control bonus_harga text-right" type="text" data-tipe="decimal" maxlength="9"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center ip_awal">
									<input type="text" class="form-control text-right ip_awal" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(300); ?>" maxlength="6">
									<!-- 300 -->
								</td>
								<td class="text-center">-</td>
								<td class="text-center ip_akhir">></td>
								<td><input class="form-control bonus_kematian text-right" type="text" data-tipe="integer" maxlength="9"></td>
								<td><input class="form-control bonus_harga text-right" type="text" data-tipe="decimal" maxlength="9"></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
				<br>
				<fieldset>
					<legend style="width: 40%;">Bonus Insentif Listrik</legend>
					<table class="table table-bordered bonus_insentif_listrik">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-4 text-center">Nilai IP</th>
								<th class="col-sm-4 text-center">Bonus (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<tr class="data v-center">
								<td class="text-center range_awal"><input class="form-control range_awal text-right" type="text" data-tipe="decimal" maxlength="6"></td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">>=</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="decimal" maxlength="6"></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="panel-body col-sm-6">
				<fieldset>
					<legend>Bonus FCR</legend>
					<table class="table table-bordered bonus_fcr">
						<thead>
							<tr>
								<th colspan="3" class="col-sm-4 text-center">Range</th>
								<th class="col-sm-4 text-center">Tarif (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<tr class="data v-center">
								<td class="text-center range_awal"><=</td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">0,050</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center range_awal">0,051</td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">0,100</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center range_awal">0,101</td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">0,150</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center range_awal">0,151</td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">0,200</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6"></td>
							</tr>
							<tr class="data v-center">
								<td class="text-center range_awal">0,201</td>
								<td class="text-center">-</td>
								<td class="text-center range_akhir">>=</td>
								<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="6"></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>
		</div>

		<!-- PAKAI PAKAN BEBAS -->
		<div class="col-sm-12 no-padding bebas hide">
			<hr>
			<div class="panel-body col-sm-6 no-padding">
				<label class="control-label">Range Standar Pemakaian Pakan</label>
			</div>
			<div class="panel-body col-sm-6 no-padding">
				<label class="control-label">Bonus FCR</label>
			</div>
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
								<tr class="data v-center">
									<td class="text-center bb_awal"></td>
									<td class="text-center">-</td>
									<td class="text-center bb_akhir">4,00</td>
									<td><input class="form-control standar_min text-right" type="text" data-tipe="decimal3" maxlength="9"></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-sm-6 no-padding">
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
								<tr class="data v-center">
									<td class="text-center range_awal"></td>
									<td class="text-center">-</td>
									<td class="text-center range_akhir">0,049</td>
									<td><input class="form-control selisih text-right" type="text" data-tipe="integer" maxlength="3"></td>
									<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="5"></td>
								</tr>
								<tr class="data v-center">
									<td class="text-center range_awal">0,050</td>
									<td class="text-center">-</td>
									<td class="text-center range_akhir"></td>
									<td><input class="form-control selisih text-right" type="text" data-tipe="integer" maxlength="3"></td>
									<td><input class="form-control tarif text-right" type="text" data-tipe="integer" maxlength="5"></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- NOTE -->
		<div class="col-sm-12">
			<div class="col-sm-7 no-padding">
				<label class="control-label">Note :</label>
			</div>
			<div class="col-sm-12 no-padding">
				<textarea class="form-control aktif" data-required="1"></textarea>
			</div>
		</div>

		<div class="col-sm-12"><hr></div>
		<div class="col-sm-12" style="padding-bottom: 15px;">
			<!-- PERWAKILAN -->
			<div class="panel-body col-sm-6 no-padding">
				<fieldset>
					<legend>Koordinator Wilayah</legend>
					<?php
						if ( count($perwakilan) % 2 == 0 ) {
							$baris = (count($perwakilan)/2) + 1;
						} else {
							$baris = ceil(count($perwakilan)/2);
						}
					?>
					<div class="col-sm-5 perwakilan">
						<?php for ($i=0; $i < $baris; $i++) { ?>
							<div class="checkbox checkbox-primary d-flex align-items-center">
			                    <input type="checkbox" class="styled styled-primary" name="mark" data-id="<?php echo $perwakilan[$i]['id']; ?>" data-name="<?php echo $perwakilan[$i]['nama']; ?>">
			                    <span><?php echo $perwakilan[$i]['nama']; ?></span>
							</div>
						<?php } ?>
					</div>
					<div class="col-sm-6 perwakilan">
						<?php for ($j=0; $j < $baris; $j++) { ?>
							<?php if ( isset($perwakilan[$j+$baris]) ): ?>
								<div class="checkbox checkbox-primary d-flex align-items-center">
				                    <input type="checkbox" class="styled styled-primary" name="mark" data-id="<?php echo $perwakilan[$j+$baris]['id']; ?>" data-name="<?php echo $perwakilan[$j+$baris]['nama']; ?>">
				                    <span><?php echo $perwakilan[$j+$baris]['nama']; ?></span>
								</div>
							<?php endif ?>
						<?php } ?>
					</div>
				</fieldset>
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
				<!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		        <div class="panel-body no-padding">
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
										<th class="text-center" colspan="2">Range (Kg)</th>
										<th class="text-center">Harga</th>
										<th class="text-center">Sakit & Afkir</th>
										<th class="text-center">HPP</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										$range_awal = $range_akhir = null; 
										$_range_awal = $_range_akhir = null; 
									?>
									<?php for ($i=0; $i < 5; $i++) { ?>
										<?php 
											if ($i == 0) {
												$_range_awal = '<=';
												$range_akhir = 1.49;
												$_range_akhir = angkaDecimal($range_akhir);
											} elseif ($i == 1) {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = $range_awal + 0.10;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											} elseif ($i == 4) {
												$range_awal = $range_akhir + 0.01;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = '>=';
											} else {
												$range_awal = $range_akhir + 0.01;
												$range_akhir = $range_awal + 0.09;
												$_range_awal = angkaDecimal($range_awal);
												$_range_akhir = angkaDecimal($range_akhir);
											}
										?>
										<tr class="data v-center">
											<td class="col-sm-2"><input type="text" class="form-control text-center range_min" readonly value="<?php echo $_range_awal;?>" /></td>
											<td class="col-sm-2"><input type="text" class="form-control text-center range_max" readonly value="<?php echo $_range_akhir;?>" /></td>
											<td class="col-sm-2"><input type="text" class="form-control text-right" name="harga" data-tipe="integer" maxlength="6" /></td>
											<td class="col-sm-2 text-center">Harga Pasar</td>
											<td class="col-sm-1 text-center">
												<input type="checkbox" class="styled styled-primary" name="mark" >
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
<div class="col-sm-12 text-right no-padding">
	<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="kbd.save()"> 
		<i class="fa fa-save" aria-hidden="true"></i> Simpan
	</button>
	<!-- <button class="btn btn-primary save" type="button" onclick="kbd.save_harga_sk()">Simpan</button> -->
</div>