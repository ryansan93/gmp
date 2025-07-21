<div class="modal-header header">
	<span class="modal-title">Order Pakan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<input type="hidden" data-noreg="">

			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4 d-flex align-items-center no-padding">
						<div class="col-lg-4">No PO</div>
						<div class="col-lg-8">
							<input type="text" class="form-control no_po" placeholder="No. PO" value="<?php echo $data['no_po']; ?>" readonly>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4 d-flex align-items-center no-padding">
						<div class="col-lg-4">No Order</div>
						<div class="col-lg-8">
							<input type="text" class="form-control no_order" placeholder="No. Order" value="<?php echo $data['no_order']; ?>" readonly>
						</div>
					</div>
					<div class="col-lg-5 d-flex align-items-center">
						<div class="col-lg-4">Tanggal</div>
						<div class="col-lg-5">
							<div class="input-group date datetimepicker" name="tanggal" id="tanggal" data-tanggal="<?php echo $data['tgl_trans']; ?>">
						        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" value="<?php echo tglIndonesia($data['tgl_trans'], '-', ' '); ?>" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4 d-flex align-items-center no-padding">
						<div class="col-lg-4">Supplier</div>
						<div class="col-lg-8">
							<select class="form-control supplier" data-required="1">
								<option value="">-- Pilih Supplier --</option>
								<?php foreach ($supplier as $k_supl => $v_supl): ?>
									<?php
										$selected = null;
										if ( $v_supl['nomor'] == $data['supplier'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $v_supl['nomor']; ?>" <?php echo $selected; ?> ><?php echo $v_supl['nama']; ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-lg-5 d-flex align-items-center">
						<div class="col-lg-4">Rencana Kirim</div>
						<div class="col-lg-5">
							<div class="input-group date datetimepicker" name="rcn_kirim" id="rcn_kirim" data-tanggal="<?php echo $data['rcn_kirim']; ?>">
						        <input type="text" class="form-control text-center" placeholder="Rencana Kirim" data-required="1" value="<?php echo tglIndonesia($data['rcn_kirim'], '-', ' '); ?>" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
					</div>
				</div>
				<div class="col-sm-12 no-padding" style="padding-right: 30px;">
					<small>
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<thead>
								<?php 
									$total_beli = 0;
									foreach ($data['detail'] as $k_det => $v_det) {
										$total_beli += $v_det['total'];
									}
								?>

								<tr>
									<td class="text-right" colspan="5"><b>Total</b></td>
									<td class="total_beli text-right"><b><?php echo angkaRibuan( $total_beli );?></b></td>
									<td colspan="3"></td>
								</tr>
								<tr>
									<th class="text-center col-sm-2" rowspan="2">Perusahaan</th>
									<th class="text-center col-sm-1" rowspan="2">Nama Item</th>
									<th class="text-center" rowspan="2">Bentuk</th>
									<th class="text-center col-sm-1" rowspan="2">Harga Beli</th>
									<th class="text-center col-sm-1 hide" rowspan="2">Harga Jual</th>
									<th class="text-center col-sm-1" rowspan="2">Jumlah</th>
									<th class="text-center col-sm-1" rowspan="2">Total Beli</th>
									<th class="text-center" colspan="2">Kirim</th>
									<th class="text-center col-sm-3" rowspan="2">Alamat</th>
								</tr>
								<tr>
									<th class="text-center col-sm-1">Gudang</th>
									<th class="text-center col-sm-2">Peternak</th>
								</tr>
							</thead>
							<tbody class="list">
								<?php $index = 0; ?>
								<?php foreach ($data['detail'] as $k_det => $v_det): ?>
									<?php $index++; ?>
									<tr class="child inactive">
										<td>
											<select class="form-control perusahaan" data-required="1">
												<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
													<?php
														$selected = null;
														if ( $v_perusahaan['kode'] == $v_det['perusahaan'] ) {
															$selected = 'selected';
														}
													?>
													<option value="<?php echo $v_perusahaan['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<?php $bentuk = '-'; ?>
										<td>
											<select class="form-control barang" data-required="1"onchange="odvp.set_bentuk_order_pakan(this)">
												<option value="">-- Pilih Barang --</option>
												<?php foreach ($barang as $k_barang => $v_barang): ?>
													<?php
														$selected = null;
														if ( $v_barang['kode'] == $v_det['barang'] ) {
															$selected = 'selected';
															$bentuk = $v_barang['bentuk'];
														}
													?>
													<option value="<?php echo $v_barang['kode']; ?>" data-bentuk="<?php echo $v_barang['bentuk']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_barang['nama']); ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td class="text-center bentuk"><?php echo strtoupper($bentuk); ?></td>
										<td>
											<input type="text" class="form-control text-right harga" placeholder="Harga" maxlength="9" data-tipe="integer" onblur="odvp.hit_total_order_pakan(this)" value="<?php echo angkaRibuan($v_det['harga']); ?>" data-required="1">
										</td>
										<td class="hide">
											<input type="text" class="form-control text-right harga_jual" placeholder="Harga" maxlength="9" data-tipe="integer" value="<?php echo angkaRibuan($v_det['harga_jual']); ?>">
										</td>
										<td>
											<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" maxlength="6" data-tipe="integer" onblur="odvp.hit_total_order_pakan(this)" value="<?php echo angkaRibuan($v_det['jumlah']); ?>" data-required="1">
										</td>
										<td class="text-right total"><?php echo angkaRibuan($v_det['total']); ?></td>
										<td>
											<?php
												$disabled = 'disabled';
												$data_required = 0;
												$aktif = null;
												if ( $v_det['tujuan_kirim'] == 'gudang' ) {
													$disabled = null;
													$data_required = 1;
													$aktif = 'aktif';
												}
											?>
											<select class="form-control gudang <?php echo $aktif; ?>" data-jenis="gudang" data-required="<?php echo $data_required; ?>" onchange="odvp.set_alamat_order_pakan(this)" <?php echo $disabled; ?> >
												<option value="">-- Pilih Gudang --</option>
												<?php foreach ($gudang as $k_gudang => $v_gudang): ?>
													<?php
														$selected = null;
														if ( $v_gudang['id'] == $v_det['id_tujuan_kirim'] ) {
															$selected = 'selected';
														}
													?>
													<option value="<?php echo $v_gudang['id']; ?>" data-alamat="<?php echo $v_gudang['alamat']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_gudang['nama']); ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td>
											<?php
												$disabled = 'disabled';
												$data_required = 0;
												$aktif = null;
												if ( $v_det['tujuan_kirim'] == 'peternak' ) {
													$disabled = null;
													$data_required = 1;
													$aktif = 'aktif';
												}
											?>
											<select class="form-control peternak <?php echo $aktif; ?>" data-jenis="peternak" data-required="<?php echo $data_required; ?>" onchange="odvp.set_alamat_order_pakan(this)" <?php echo $disabled; ?> >
												<option value="">-- Pilih Peternak --</option>
												<?php foreach ($peternak as $k_peternak => $v_peternak): ?>
													<?php
														$selected = null;
														if ( $v_peternak['noreg'] == $v_det['id_tujuan_kirim'] ) {
															$selected = 'selected';
														}
													?>
													<option value="<?php echo $v_peternak['noreg']; ?>" data-alamat="<?php echo $v_peternak['alamat']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td class="alamat">
											<div class="alamat"><?php echo $v_det['alamat']; ?></div>
											<?php $display = 'display:none'; ?>
											<?php if ( $index == count($data['detail']) ): ?>
												<?php $display = null; ?>
											<?php endif ?>
											<div class="btn-ctrl" style="margin-top: 15px; <?php echo $display; ?>">
											<!-- <div class="btn-ctrl" style="<?php echo $display; ?>"> -->
												<?php $hide = null; ?>
												<?php if ( $index == 1 ): ?>
													<?php $hide = 'hide'; ?>
												<?php endif ?>
												<span onclick="odvp.removeRowChild(this)" class="btn_del_row_2x <?php echo $hide; ?>"></span>
												<span onclick="odvp.addRowChild(this)" class="btn_add_row_2x"></span>
											</div>
										</td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</small>
				</div>
				<div class="form-group">
					<div class="col-lg-12 no-padding">
						<hr>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.edit_order_pakan(this)" style="margin-left: 10px;" data-id="<?php echo $data['id']; ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i> Edit
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>