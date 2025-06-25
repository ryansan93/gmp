<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Tgl Rek Koran</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2" style="padding: 0px 30px 0px 0px;">
		<div class="input-group date" id="tglBayar">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-val="<?php echo $data['tgl_bayar']; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
		<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
			<option value="all" > All </option>
			<?php foreach ($unit as $key => $v_unit): ?>
				<?php 
					$selected = null;
					if ( in_array($v_unit['kode'], $kode_unit) ) {
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
		<select class="form-control selectpicker perusahaan" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Perusahaan</option>
			<?php if ( count($perusahaan) > 0 ): ?>
				<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
					<?php
						$selected = null;
						if ( $v_perusahaan['kode'] == $kode_perusahaan ) {
							$selected = 'selected';
						}
					?>
					<option value="<?php echo $v_perusahaan['kode']; ?>" data-jenismitra="<?php echo $v_perusahaan['jenis_mitra']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_perusahaan['nama']); ?></option>
				<?php endforeach ?>
			<?php endif ?>
		</select>
	</div>
</div>
<div class="col-lg-12"></div>
<!-- <div class="col-lg-12 no-padding" style="height: 34px;">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Pelanggan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
		<label class="control-label"><?php echo strtoupper($data['pelanggan']['nama']).' ('.strtoupper(str_replace('Kab ', '', $data['pelanggan']['kecamatan']['d_kota']['nama'])).')'; ?></label>
	</div>
</div> -->
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Pelanggan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
		<select class="form-control selectpicker pelanggan" data-live-search="true" type="text" data-required="1">
			<option value="">Pilih Pelanggan</option>
			<?php if ( count($pelanggan) > 0 ): ?>
				<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
					<?php
						$selected = null;
						if ( $v_plg['nomor'] == $data['no_pelanggan'] ) {
							$selected = 'selected';
						}
					?>
					<option value="<?php echo $v_plg['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['kab_kota']).')'; ?></option>
				<?php endforeach ?>
			<?php endif ?>
		</select>
	</div>
	<div class="col-lg-6 no-padding">
		<button type="button" class="btn btn-primary btn-get-list-do" onclick="bakul.get_list_do()" data-id="<?php echo $data['id']; ?>"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Transfer</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Jumlah" onblur="bakul.hit_total_uang()" data-required="1" value="<?php echo angkaRibuan($data['jml_transfer']); ?>">
	</div>
	<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3 no-padding">
		<div class="col-lg-12" style="padding: 7px 0px 0px 0px;">
			<a href="uploads/<?php echo $data['lampiran_transfer']; ?>" target="_blank"><?php echo $data['lampiran_transfer']; ?></a>
			<label class="">
				<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="doc|pdf|docx|jpg|jpeg|png|DOC|PDF|DOCX|JPG|JPEG|PNG" style="display: none;">
				<i class="glyphicon glyphicon-paperclip cursor-p"></i>
			</label>
		</div>
	</div>
</div>
<div class="col-lg-12 no-padding">&nbsp;</div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Saldo</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" readonly value="<?php echo angkaDecimal($data['saldo']); ?>">
	</div>
	<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Uang</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" readonly value="<?php echo angkaDecimal($data['total_uang']); ?>">
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly value="<?php echo angkaDecimal($data['total_penyesuaian']); ?>">
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Nilai Pajak</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right nilai_pajak" placeholder="Nilai" data-tipe="decimal" onblur="bakul.hit_total_uang()" value="<?php echo angkaDecimal($data['nil_pajak']); ?>" />
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Lebih Bayar Non Saldo</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right lebih_bayar_non_saldo" placeholder="Nilai" data-tipe="decimal" onblur="bakul.hit_total_uang()" value="<?php echo angkaDecimal($data['non_saldo']); ?>" />
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly value="<?php echo angkaDecimal($data['total_bayar']); ?>">
	</div>
	<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding">
		<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" disabled="disabled" value="<?php echo angkaDecimal($data['lebih_kurang']); ?>">
	</div>
</div>
<div class="col-lg-12 no-padding"><hr></div>
<div class="col-lg-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-1 text-center">Tanggal Panen</th>
					<th class="col-lg-1 text-center">Plasma</th>
					<th class="col-lg-1 text-center">No. DO</th>
					<th class="col-lg-1 text-center">No. SJ</th>
					<th class="col-lg-1 text-center">No. Nota</th>
					<th class="text-center" style="width: 5%;">Ekor</th>
					<th class="text-center" style="width: 5%;">Kg</th>
					<th class="text-center" style="width: 7%;">Harga</th>
					<th class="col-lg-1 text-center">Total</th>
					<th class="col-lg-1 text-center">Sudah Bayar</th>
					<th class="col-lg-1 text-center">Jumlah Bayar</th>
					<th class="col-lg-1 text-center">Penyesuaian</th>
					<th class="text-center" style="width: 5%;">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr class="data" data-id="<?php echo $v_det['id_do']; ?>">
						<td class="text-center"><?php echo tglIndonesia($v_det['data_do']['header']['tgl_panen'], '-', ' '); ?></td>
						<td class="text-left"><?php echo strtoupper($v_det['nama']).'<br>'.'KDG : '.$v_det['kandang']; ?></td>
						<td class="text-center"><?php echo $v_det['data_do']['no_do']; ?></td>
						<td class="text-center"><?php echo $v_det['data_do']['no_sj']; ?></td>
						<td class="text-center"><?php echo $v_det['data_do']['no_nota']; ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_det['data_do']['ekor']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['tonase']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['harga']); ?></td>
						<td class="text-right total">
							<?php
								$total = $v_det['data_do']['tonase'] * $v_det['data_do']['harga'];
								echo angkaDecimal($total);
							?>
						</td>
						<td class="text-right"><?php echo angkaDecimal($v_det['sudah_bayar']); ?></td>
						<td class="text-right jml_bayar" data-sudah="<?php echo $v_det['sudah_bayar']; ?>" data-bayar="<?php echo $v_det['jumlah_bayar']; ?>"><?php echo angkaDecimal($v_det['jumlah_bayar']); ?></td>
						<td class="text-right penyesuaian">
							<div class="col-lg-12 no-padding">
								<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" maxlength="14" onblur="bakul.cek_status_pembayaran(this)" value="<?php echo angkaRibuan($v_det['penyesuaian']); ?>">
							</div>
							<div class="col-lg-12 no-padding"></div>
							<div class="col-lg-12 no-padding">
								<textarea class="form-control ket_penyesuaian" placeholder="Keterangan">
									<?php echo $v_det['ket_penyesuaian']; ?>
								</textarea>
							</div>
						</td>
						<td class="text-center status">
							<?php
								$ket = '';
								$total_bayar = ($v_det['jumlah_bayar']+$v_det['sudah_bayar']) + $v_det['penyesuaian'];
								if ( $total == $total_bayar ) {
									$ket = '<span style="color: blue;"><b>LUNAS</b></span>';
								} else if ( $total > $total_bayar ) {
									$ket = '<span style="color: red;"><b>BELUM</b></span>';
								}

								echo $ket;
							?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-lg-12 no-padding"><hr></div>
<div class="col-lg-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="bakul.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
	<button type="button" class="btn btn-danger pull-right" onclick="bakul.changeTabActive(this)" data-href="action" data-resubmit="" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-times"></i> Batal</button>
</div>