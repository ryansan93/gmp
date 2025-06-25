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
					<input type="text" class="form-control no_order" placeholder="No. Order" readonly>
				</div>
				<div class="col-sm-1 no-padding">
					<span>Tanggal</label>
				</div>
				<div class="col-sm-3">
					<div class="input-group date datetimepicker" name="tgl_order_voadip" id="TglOrder_Voadip">
				        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
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
							<option value="<?php echo $v_supl['nomor']; ?>"><?php  echo $v_supl['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-sm-12" style="padding-right: 30px; padding-left: 0px;">
				<small>
					<table class="table table-bordered table-hover tbl_voadip" id="dataTable" width="100%" cellspacing="0" style="padding-right: 30px;">
						<thead>
							<tr>
								<td class="text-right" colspan="7"><b>Total</b></td>
								<td class="total_beli text-right"><b>0</b></td>
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
								<th class="text-center" colspan="2">Kirim</th>
								<th class="text-center hide" rowspan="2">Alamat</th>
							</tr>
							<tr class="v-center">
								<th class="col-sm-1 text-center">Gudang</th>
								<th class="col-sm-1 text-center">Peternak</th>
							</tr>
						</thead>
						<tbody class="list">
							<tr class="child inactive">
								<td>
									<select class="form-control perusahaan" data-required="1">
										<option value="">-- Pilih Perusahaan --</option>
										<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
											<option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
										<?php endforeach ?>
									</select>
								</td>
								<td>
									<select class="form-control kategori" onchange="odvp.set_item_voadip(this)" data-required="1">
										<option value="">-- Pilih Kategori --</option>
										<?php foreach ($kategori_voadip as $k_kat => $v_kat): ?>
											<option value="<?php echo $k_kat; ?>"><?php echo $v_kat; ?></option>
										<?php endforeach ?>
									</select>
								</td>
								<td>
									<select class="form-control barang" data-required="1" onchange="odvp.cek_decimal_harga(this)">
										<option value="">-- Pilih Barang --</option>
									</select>
								</td>
								<td>
									<input type="text" class="form-control kemasan" placeholder="Kemasan" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right harga" placeholder="Harga" data-tipe="decimal" maxlength="10" onblur="odvp.hit_hrg_jual_voadip(this)" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right harga_jual" placeholder="Harga" data-tipe="decimal" maxlength="10" data-required="1" readonly>
								</td>
								<td>
									<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" maxlength="10" onblur="odvp.hit_total_order_voadip(this)" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right total" placeholder="Total" data-tipe="decimal" data-required="1" readonly>
								</td>
								<td>
									<input type="text" class="form-control text-center date" placeholder="Kirim" id="tgl_kirim" name="tgl_kirim" />
								</td>
								<td>
									<select class="form-control gudang" data-jenis="gudang" data-required="1" onchange="odvp.set_alamat_order_pakan(this)">
										<option value="">-- Pilih Gudang --</option>
										<?php foreach ($gudang as $k_gudang => $v_gudang): ?>
											<option value="<?php echo $v_gudang['id']; ?>" data-alamat="<?php echo $v_gudang['alamat']; ?>"><?php echo strtoupper($v_gudang['nama']); ?></option>
										<?php endforeach ?>
									</select>
								</td>
								<td>
									<select class="form-control peternak" data-jenis="peternak" data-required="1" onchange="odvp.set_alamat_order_pakan(this)">
										<option value="">-- Pilih Peternak --</option>
										<?php foreach ($peternak as $k_peternak => $v_peternak): ?>
											<option value="<?php echo $v_peternak['noreg']; ?>" data-alamat="<?php echo $v_peternak['alamat']; ?>"><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
										<?php endforeach ?>
									</select>
									<div class="btn-ctrl">
										<span onclick="odvp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
										<span onclick="odvp.addRowChildVoadip(this)" class="btn_add_row_2x"></span>
									</div>
								</td>
								<td class="alamat hide">
									<div class="alamat">-</div>
								</td>
								<!-- <td class="hide">
									<input type="text" class="form-control alamat_peternak" placeholder="Alamat Peternak" readonly>
								</td> -->
							</tr>
						</tbody>
					</table>
				</small>
			</div>
			<div class="col-sm-12 no-padding">
				<div class="col-sm-2 no-padding">
					<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="SAVE" onclick="odvp.save_order_voadip(this)"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</div>
	</div>
</div>