<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tipe DN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jurnal_trans" data-required="1">
				<option value="">-- Pilih Transaksi Jurnal --</option>
				<?php foreach ($jurnal_trans as $key => $value): ?>
                    <?php
                        $selected = null;
                        if ( $value['kode'] == $data['kode_jurnal_trans'] ) {
                            $selected = 'selected';
                        }    
                    ?>
					<option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Jenis DN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jenis_dn" data-required="1" disabled>
				<?php foreach ($jenis_dn as $key => $value) { ?>
					<?php
						$selected = null;
						if ( $data['jenis_dn'] == $key ) {
							$selected = 'selected';
						}	
					?>
					<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="col-xs-3 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control no_dn" placeholder="No. DN" value="<?php echo $data['nomor']; ?>" disabled>
		</div>
	</div>
    <div class="col-xs-4 no-padding">&nbsp;</div>
	<div class="col-xs-3 no-padding text-right" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Total DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right tot_dn" data-tipe="decimal" placeholder="Total DN" data-required="1"value="<?php echo angkaDecimal($data['tot_dn']); ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal DN</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php foreach ($supplier as $key => $value): ?>
                    <?php
                        $selected = null;
                        if ( $value['nomor'] == $data['supplier'] ) {
                            $selected = 'selected';
                        }
                    ?>
					<option value="<?php echo $value['nomor']; ?>" <?php echo $selected; ?> ><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Gudang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control gudang">
				<?php if ( !empty($data['gudang']) ) { ?>
					<option value="<?php echo $data['gudang']; ?>" selected="selected"><?php echo $data['nama_gudang']; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Keterangan DN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_dn" placeholder="Keterangan DN" data-required="1"><?php echo $data['ket_dn']; ?></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="overflow-x: auto;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
			<thead>
				<tr>
					<th class="col-xs-2">No. SJ</th>
					<th class="col-xs-2">Barang</th>
					<th class="col-xs-1">Jumlah</th>
					<th class="col-xs-4">Keterangan</th>
					<th class="col-xs-2">Nominal</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det) { ?>
					<tr class="head">
						<td>
							<select class="form-control no_sj">
								<?php if ( !empty($v_det['no_sj']) ) { ?>
									<option value="<?php echo $v_det['no_sj']; ?>" selected="selected"><?php echo $v_det['tgl_sj'].' | '.$v_det['no_sj']; ?></option>
								<?php } ?>
							</select>
						</td>
						<td>
							<select class="form-control barang">
								<?php if ( !empty($v_det['kode_brg']) ) { ?>
									<option value="<?php echo $v_det['kode_brg']; ?>" selected="selected"><?php echo $v_det['kode_brg'].' | '.$v_det['nama_brg']; ?></option>
								<?php } ?>
							</select>
						</td>
						<td>
							<input type="text" class="form-control text-right jumlah" data-tipe="decimal" data-required="1" placeholder="Jumlah" value="<?php echo angkaDecimal($v_det['jumlah']); ?>">
						</td>
						<td>
							<textarea class="form-control ket" data-required="1" placeholder="Keterangan"><?php echo $v_det['ket']; ?></textarea>
						</td>
						<td>
							<input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" value="<?php echo angkaDecimal($v_det['nominal']); ?>" onblur="dn.hitTot()">
						</td>
						<td>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-6 no-padding" style="padding-right: 5px;">
									<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRow(this)"><i class="fa fa-plus"></i></button>
								</div>
								<div class="col-xs-6 no-padding" style="padding-left: 5px;">
									<button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRow(this)"><i class="fa fa-trash"></i></button>
								</div>
							</div>
						</td>
					</tr>
					<tr class="detail">
						<td colspan="5" style="background-color: #dedede;">
							<table class="table table-bordered" style="margin-bottom: 0px;">
								<thead>
									<tr>
										<td class="col-xs-5"><b>Akun</b></td>
										<td class="col-xs-3"><b>Kredit</b></td>
										<td class="col-xs-3"><b>Debet</b></td>
										<td class="col-xs-1"></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) { ?>
										<tr>
											<td>
												<select class="form-control det_jurnal_trans" data-required="1">
													<option value="<?php echo $v_djt['kode_det_jurnal_trans']; ?>" data-asal="<?php echo $v_djt['asal'] ?>" data-coa_asal="<?php echo $v_djt['coa_asal'] ?>" data-tujuan="<?php echo $v_djt['tujuan'] ?>" data-coa_tujuan="<?php echo $v_djt['coa_tujuan'] ?>" selected="selected"><?php echo $v_djt['nama_det_jurnal_trans']; ?></option>
												</select>
											</td>
											<td class="asal"><?php echo $v_djt['coa_asal'].' | '.$v_djt['asal'] ?></td>
											<td class="tujuan"><?php echo $v_djt['coa_tujuan'].' | '.$v_djt['tujuan'] ?></td>
											<td>
												<div class="col-xs-12 no-padding">
													<div class="col-xs-6 no-padding" style="padding-right: 5px;">
														<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRowDet(this)"><i class="fa fa-plus"></i></button>
													</div>
													<div class="col-xs-6 no-padding" style="padding-left: 5px;">
														<button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRowDet(this)"><i class="fa fa-trash"></i></button>
													</div>
												</div>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
        <button type="button" class="col-xs-12 btn btn-danger" onclick="dn.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="" data-href="action"><i class="fa fa-times"></i> Batal</button>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="dn.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
    </div>
</div>