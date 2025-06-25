<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
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
								if ( $data['nomor'] == $v_dm['nomor'] ) {
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
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['noreg']; ?>" data-old="<?php echo $data['noreg']; ?>" disabled>
						<option value="">Pilih Noreg</option>
						<option data-tokens="<?php echo strtoupper(tglIndonesia($data['tiba'], '-', ' ').' | KD - '.(int)substr($data['noreg'], -2).' | '.$data['noreg']) ?>" data-umur="<?php echo selisihTanggal($data['kirim'], $data['tiba']); ?>" data-tgldocin="<?php echo $data['tiba']; ?>" data-populasi="<?php echo $data['ekor']; ?>" value="<?php echo $data['noreg']; ?>" selected><?php echo strtoupper(tglIndonesia($data['tiba'], '-', ' ').' | KD - '.(int)substr($data['noreg'], -2).' | '.$data['noreg']) ?></option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Order</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_no_order" data-placeholder="Pilih No. Order" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['no_order']; ?>" data-old="<?php echo $data['no_order']; ?>" disabled>
						<option value="">Pilih No. Order</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. SJ</label>
				</div>
				<div class="col-xs-10 no-padding">
					<input type="text" class="form-control no_sj" placeholder="No. SJ" data-required="1" value="<?php echo $data['no_sj']; ?>">
				</div>
				<div class="col-xs-2 no-padding">
					<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px; padding-top: 7px;">
						<?php $hide = empty($v_dk['lampiran_sj']) ? 'hide' : ''; ?>
			            <a href="uploads/<?php echo $data['lampiran_sj']; ?>" name="dokumen" class="text-right <?php echo $hide; ?>" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
			            <label class="" style="margin-bottom: 0px;">
			                <input style="display: none;" placeholder="Dokumen" class="file_lampiran_sj no-check" type="file" onchange="pdm.showNameFile(this)" data-name="no-name" data-allowtypes="pdf|jpg|jpeg|png" data-old="<?php echo $data['lampiran_sj']; ?>">
			                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
			            </label>
			        </div>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Kirim</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal_kirim" id="tanggal_kirim">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['kirim']; ?>" />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Tiba</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal_tiba" id="tanggal_tiba">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tiba']; ?>" />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Polisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control nopol uppercase" placeholder="No. Polisi" data-required="1" value="<?php echo $data['nopol']; ?>">
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Kondisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control kondisi uppercase" placeholder="Kondisi" data-required="1" value="<?php echo $data['kondisi']; ?>">
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Jumlah Ekor</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right ekor" placeholder="Ekor" data-tipe="integer" data-required="1" onblur="pdm.hit_jml_box(this)" value="<?php echo angkaRibuan($data['ekor']); ?>" />
				</div>
			</div>

			<div class="col-xs-4 no-padding" style="margin-bottom: 5px; padding-right: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Jumlah Box</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right box" placeholder="Box" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['box']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-2 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">BB</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bb" placeholder="BB" data-tipe="decimal3" data-required="1" value="<?php echo angkaDecimalFormat($data['bb'], 3); ?>" />
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Uniformity (%)</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right uniformity" placeholder="Uniformity" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['uniformity']); ?>" />
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Keterangan DOC</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered ket_doc" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-6">Keterangan</th>
							<th class="col-xs-3">Lampiran</th>
							<th class="col-xs-3">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data['data_ket']) ): ?>
							<?php foreach ($data['data_ket'] as $k_dk => $v_dk): ?>
								<tr class="v-center">
									<td class="text-center">
										<textarea class="form-control keterangan" placeholder="Keterangan"><?php echo $v_dk['keterangan']; ?></textarea>
									</td>
									<td class="text-center" style="vertical-align: top;">
										<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px; padding-top: 7px;">
											<?php $hide = empty($v_dk['lampiran']) ? 'hide' : ''; ?>
								            <a href="uploads/<?php echo $v_dk['lampiran']; ?>" name="dokumen" class="text-right <?php echo $hide; ?>" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
								            <label class="" style="margin-bottom: 0px;">
								                <input style="display: none;" placeholder="Dokumen" class="file_lampiran_ket no-check" type="file" onchange="pdm.showNameFile(this)" data-name="no-name" data-allowtypes="pdf|jpg|jpeg|png" data-old="<?php echo $v_dk['lampiran']; ?>">
								                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
								            </label>
								        </div>
									</td>
									<td style="vertical-align: top;">
										<div class="col-xs-12 no-padding">
											<div class="col-xs-6 no-padding">
												<button type="button" class="btn btn-primary" onclick="pdm.add_row(this)"><i class="fa fa-plus"></i></button>
											</div>
											<div class="col-xs-6 no-padding">
												<button type="button" class="btn btn-danger" onclick="pdm.remove_row(this)"><i class="fa fa-times"></i></button>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr class="v-center">
								<td class="text-center">
									<textarea class="form-control keterangan" placeholder="Keterangan"></textarea>
								</td>
								<td class="text-center" style="vertical-align: top;">
									<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px; padding-top: 7px;">
							            <a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
							            <label class="" style="margin-bottom: 0px;">
							                <input style="display: none;" placeholder="Dokumen" class="file_lampiran_ket no-check" type="file" onchange="pdm.showNameFile(this)" data-name="no-name" data-allowtypes="pdf|jpg|jpeg|png">
							                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
							            </label>
							        </div>
								</td>
								<td style="vertical-align: top;">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding">
											<button type="button" class="btn btn-primary" onclick="pdm.add_row(this)"><i class="fa fa-plus"></i></button>
										</div>
										<div class="col-xs-6 no-padding">
											<button type="button" class="btn btn-danger" onclick="pdm.remove_row(this)"><i class="fa fa-times"></i></button>
										</div>
									</div>
								</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="pdm.edit()"><i class="fa fa-save"></i> Simpan Perubahan</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger pull-right col-xs-12" onclick="pdm.change_tab(this)" data-id="<?php echo $data['no_order']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="" data-href="transaksi"><i class="fa fa-times"></i> Batal</button>
			</div>
		</form>
	</div>
</div>
