<div class="modal-header header">
	<span class="modal-title">Order OVK</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<div class="col-lg-8 action d-flex align-items-center">
				<div class="col-sm-1 no-padding">
					<span>No. Order</label>
				</div>
				<div class="col-sm-3">
					<input type="text" class="form-control no_order" placeholder="No. Order" value="<?php echo $data['no_order']; ?>" data-version="<?php echo $data['version']; ?>" readonly>
				</div>
				<div class="col-sm-1 no-padding">
					<span>Tanggal</label>
				</div>
				<div class="col-sm-3">
					<div class="input-group date datetimepicker" name="tgl_order_voadip" id="TglOrder_Voadip" data-tanggal="<?php echo $data['tanggal']; ?>">
				        <input type="text" class="form-control text-center" placeholder="Tanggal" value="<?php echo tglIndonesia($data['tanggal'], '-', ' '); ?>"  data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
			</div>
			<div class="col-lg-8 action d-flex align-items-center">
				<div class="col-sm-1 no-padding">
					<span>No. PO</label>
				</div>
				<div class="col-sm-3">
					<input type="text" class="form-control no_po" placeholder="No. Order" value="<?php echo $data['no_po']; ?>" data-version="<?php echo $data['version']; ?>" readonly>
				</div>
			</div>
			<div class="col-lg-8 action left-inner-addon d-flex align-items-center">
				<div class="col-sm-1 no-padding">
					<span>Supplier</label>
				</div>
				<div class="col-sm-3">
					<select class="form-control supplier" data-required="1">
						<option value="">-- Pilih Supplier --</option>
						<?php foreach ($supplier as $k_supl => $v_supl): ?>
							<?php 
								$selected = null;
								if ( $v_supl['nomor'] == $data['no_supl'] ):
									$selected = 'selected';
								endif
							?>
							<option value="<?php echo $v_supl['nomor']; ?>" <?php echo $selected; ?> ><?php  echo $v_supl['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-sm-12" style="padding-right: 30px; padding-left: 0px;">
				<small>
					<table class="table table-bordered table-hover tbl_voadip" id="dataTable" width="100%" cellspacing="0" style="padding-right: 30px;">
						<thead>
							<?php 
								$total_beli = 0;
								foreach ($data['detail'] as $k_detail => $v_detail) {
									$total_beli += $v_detail['total'];
								}
							?>

							<tr>
								<td class="text-right" colspan="7"><b>Total</b></td>
								<td class="total_beli text-right"><b><?php echo angkaDecimal( $total_beli );?></b></td>
								<td colspan="4"></td>
							</tr>
							<tr class="v-center">
								<th class="col-sm-1 text-center" rowspan="2">Perusahaan</th>
								<th class="col-sm-1 text-center" rowspan="2">Kategori</th>
								<th class="col-sm-2 text-center" rowspan="2">Nama Item</th>
								<th class="col-sm-1 text-center" rowspan="2">Kemasan</th>
								<th class="col-sm-1 text-center" rowspan="2">Harga Beli</th>
								<th class="col-sm-1 text-center" rowspan="2">Harga Jual</th>
								<th class="col-sm-1 text-center" rowspan="2">Jumlah</th>
								<th class="col-sm-1 text-center" rowspan="2">Total Beli</th>
								<th class="col-sm-1 text-center" rowspan="2">Tgl Kirim</th>
								<th class="text-center" colspan="1">Kirim</th>
								<th class="text-center" rowspan="2">Alamat</th>
							</tr>
							<tr class="v-center">
								<th class="col-sm-1 text-center">Gudang</th>
								<th class="col-sm-1 text-center hide">Peternak</th>
							</tr>
						</thead>
						<tbody class="list">
							<?php $index = 0; ?>
							<?php foreach ($data['detail'] as $k_detail => $v_detail): ?>
								<?php $index++; ?>
								<tr class="child inactive">
									<td>
										<select class="form-control perusahaan" data-required="1">
											<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
												<?php
													$selected = null;
													if ( $v_perusahaan['kode'] == $v_detail['perusahaan'] ) {
														$selected = 'selected';
													}
												?>
												<option value="<?php echo $v_perusahaan['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td>
										<select class="form-control kategori" onchange="odvp.set_item_voadip(this)" data-required="1">
											<option value="">-- Pilih Kategori --</option>
											<?php foreach ($kategori_voadip as $k_kat => $v_kat): ?>
												<?php
													$selected = null;
													if ( $k_kat == $v_detail['kategori'] ) {
														$selected = 'selected';
													}
												?>
												<option value="<?php echo $k_kat; ?>" <?php echo $selected; ?> ><?php echo $v_kat; ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td>
										<select class="form-control barang" data-required="1" data-barang="<?php echo $v_detail['id_barang']; ?>" onchange="odvp.cek_decimal_harga(this)">
											<option value="">-- Pilih Barang --</option>
										</select>
									</td>
									<td>
										<input type="text" class="form-control kemasan" placeholder="Kemasan" value="<?php echo $v_detail['kemasan']; ?>" data-required="1">
									</td>
									<td>
										<input type="text" class="form-control text-right harga" placeholder="Harga" value="<?php echo angkaDecimal($v_detail['harga']); ?>" data-tipe="decimal" maxlength="10" onblur="odvp.hit_hrg_jual_voadip(this)" data-required="1">
									</td>
									<td>
										<input type="text" class="form-control text-right harga_jual" placeholder="Harga" value="<?php echo angkaDecimal($v_detail['harga_jual']); ?>" data-tipe="decimal" maxlength="10" data-required="1" readonly>
									</td>
									<td>
										<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" value="<?php echo angkaRibuan($v_detail['jumlah']); ?>" data-tipe="integer" maxlength="10" onblur="odvp.hit_total_order_voadip(this)" data-required="1">
									</td>
									<td>
										<input type="text" class="form-control text-right total" placeholder="Total" value="<?php echo angkaDecimal($v_detail['total']); ?>" data-tipe="decimal" data-required="1" readonly>
									</td>
									<td>
										<input type="text" class="form-control text-center date" placeholder="Kirim" id="tgl_kirim" name="tgl_kirim" data-tanggal="<?php echo $v_detail['tgl_kirim']; ?>" value="<?php echo tglIndonesia($v_detail['tgl_kirim'], '-', ' ') ?>" />
									</td>
									<td>
										<select class="form-control gudang <?php echo ($v_detail['kirim_ke'] == 'gudang') ? 'aktif' : ''; ?>" data-jenis="gudang" <?php echo ($v_detail['kirim_ke'] == 'gudang') ? 'data-required=1' : 'disabled'; ?> onchange="odvp.set_alamat_order_pakan(this)">
											<option value="">-- Pilih Gudang --</option>
											<?php foreach ($gudang as $k_gudang => $v_gudang): ?>
												<?php
													$selected = null;
													if ( $v_detail['kirim_ke'] == 'gudang' ) {
														if ( $v_gudang['id'] == $v_detail['kirim'] ) {
															$selected = 'selected';
														}
													}
												?>
												<option value="<?php echo $v_gudang['id']; ?>" data-alamat="<?php echo $v_gudang['alamat']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_gudang['nama']); ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td class="hide">
										<select class="form-control peternak <?php echo ($v_detail['kirim_ke'] == 'peternak') ? 'aktif' : ''; ?>" data-jenis="peternak" <?php echo ($v_detail['kirim_ke'] == 'peternak') ? 'data-required=1' : 'disabled'; ?> onchange="odvp.set_alamat_order_pakan(this)">
											<option value="">-- Pilih Peternak --</option>
											<?php foreach ($peternak as $k_peternak => $v_peternak): ?>
												<?php
													$selected = null;
													if ( $v_detail['kirim_ke'] == 'peternak' ) {
														if ( $v_peternak['noreg'] == $v_detail['kirim'] ) {
															$selected = 'selected';
														}
													}
												?>
												<option value="<?php echo $v_peternak['noreg']; ?>" data-alamat="<?php echo $v_peternak['alamat']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
											<?php endforeach ?>
										</select>

										<?php $display = 'display:none'; ?>
										<?php if ( $index == count($data['detail']) ): ?>
											<?php $display = null; ?>
										<?php endif ?>
									</td>
									<td class="alamat">
										<div class="alamat"><?php echo $v_detail['alamat']; ?></div>
										<div class="btn-ctrl" style="<?php echo $display; ?>">
											<?php $hide = null; ?>
											<?php if ( $index == 1 ): ?>
												<?php $hide = 'hide'; ?>
											<?php endif ?>
											<span onclick="odvp.removeRowChild(this)" class="btn_del_row_2x <?php echo $hide; ?>"></span>
											<span onclick="odvp.addRowChildVoadip(this)" class="btn_add_row_2x"></span>
										</div>
									</td>
									<!-- <td class="hide">
										<input type="text" class="form-control alamat_peternak" placeholder="Alamat Peternak" value="<?php echo $v_detail['alamat']; ?>" readonly>
									</td> -->
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</small>
			</div>
			<div class="col-sm-12 no-padding">
				<div class="col-sm-2 no-padding">
					<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="EDIT" onclick="odvp.edit_order_voadip(this)"><i class="fa fa-edit"></i> Edit</button>
				</div>
			</div>
		</div>
	</div>
</div>