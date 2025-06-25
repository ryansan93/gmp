<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Bayar</label></div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date" id="tglBayar">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_bayar']; ?>" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Pelanggan</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_pelanggan" class="form-control selectpicker" data-live-search="true" data-required="1" onchange="bakul.get_list_do(this)" data-edit="edit">
			<option value="">Pilih Pelanggan</option>
			<?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
				<?php
					$selected = null;
					if ( $data['no_pelanggan'] == $v_dp['nomor'] ) {
						$selected = 'selected';
					}
				?>
				<option data-tokens="<?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?>" value="<?php echo $v_dp['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<button type="button" class="btn btn-primary col-xs-12" onclick="bakul.get_list_do()"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
</div> -->
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Transfer</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Jumlah" onblur="bakul.hit_total_uang()" value="<?php echo angkaRibuan($data['jml_transfer']); ?>" data-required="1">
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12" style="padding: 7px 0px 0px 0px;">
			<a href="uploads/<?php echo $data['lampiran_transfer']; ?>" name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;"><?php echo $data['lampiran_transfer']; ?></a>
			<label class="">
				<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" data-old="<?php echo $data['lampiran_transfer']; ?>">
				<i class="glyphicon glyphicon-paperclip cursor-p"></i>
			</label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Saldo</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" value="<?php echo angkaDecimal($data['saldo']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Uang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" value="<?php echo angkaDecimal($data['total_uang']); ?>" readonly>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['total_penyesuaian']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['total_bayar']); ?>" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" value="<?php echo angkaDecimal($data['lebih_kurang']); ?>" disabled="disabled">
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1 text-center">Tanggal Panen</th>
					<th class="col-xs-2 text-center">No. DO</th>
					<th class="col-xs-2 text-center">No. SJ</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty( $data['detail'] ) ): ?>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr class="data header" data-id="<?php echo $v_det['id']; ?>">
							<td class="text-center"><?php echo tglIndonesia($v_det['data_do']['header']['tgl_panen'], '-', ' '); ?></td>
							<td class="text-center"><?php echo $v_det['data_do']['no_do']; ?></td>
							<td class="text-center"><?php echo $v_det['data_do']['no_sj']; ?></td>
						</tr>
						<tr class="detail">
							<td colspan="3">
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<tbody>
										<tr>
											<th class="col-xs-2 text-center">Ekor</th>
											<th class="col-xs-3 text-center">Kg</th>
											<th class="col-xs-3 text-center">Harga</th>
											<th class="col-xs-4 text-center">Total</th>
										</tr>
										<tr>
											<td class="text-right"><?php echo angkaRibuan($v_det['data_do']['ekor']); ?></td>
											<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['tonase']); ?></td>
											<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['harga']); ?></td>
											<td class="text-right total"><?php echo angkaDecimal(($v_det['data_do']['tonase'] * $v_det['data_do']['harga'])); ?></td>
										</tr>
									</tbody>
								</table>
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<tbody>
										<tr>
											<th class="col-xs-4 text-left">Sudah Bayar</th>
											<td class="col-xs-8 sudah_bayar text-right"><?php echo angkaDecimal(($v_det['data_do']['tonase'] * $v_det['data_do']['harga']) - $v_det['jumlah_bayar']); ?></td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Jumlah Bayar</th>
											<td class="col-xs-8 jml_bayar text-right" data-sudah="<?php echo $v_det['jumlah_bayar']; ?>"><?php echo angkaDecimal($v_det['jumlah_bayar']); ?></td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Penyesuaian</th>
											<td class="col-xs-8 penyesuaian">
												<div class="col-xs-12 no-padding">
													<input type="text" class="form-control text-right penyesuaian" data-tipe="decimal" placeholder="Penyesuaian" value="<?php echo angkaDecimal($v_det['penyesuaian']); ?>" maxlength="14" onblur="bakul.cek_status_pembayaran(this)">
												</div>
												<div class="col-xs-12 no-padding"></div>
												<div class="col-xs-12 no-padding">
													<textarea class="form-control ket_penyesuaian" placeholder="Keterangan"><?php echo strtoupper($v_det['ket_penyesuaian']); ?></textarea>
												</div>
											</td>
										</tr>
										<tr>
											<th class="col-xs-4 text-left">Status</th>
											<td class="col-xs-8 status">
												<?php
													$ket = '';
													if ( $v_det['status'] == 'LUNAS' ) {
														$ket = '<span style="color: blue;"><b>LUNAS</b></span>';
													} else {
														$ket = '<span style="color: red;"><b>BELUM</b></span>';
													}

													echo $ket;
												?>
											</td>
										</tr>
									</tbody>
								</table>
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
<div class="col-xs-12 no-padding"><hr></div>
<div class="col-lg-12 no-padding">
	<form role="form" class="form-horizontal">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="bakul.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<button type="button" class="btn btn-danger pull-right col-xs-12" onclick="bakul.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="" data-href="transaksi"><i class="fa fa-times"></i> Batal</button>
		</div>
	</form>
</div>