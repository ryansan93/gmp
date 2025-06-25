<div class="modal-header header">
	<span class="modal-title">Order VOADIP</span>
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
							<tr class="v-center">
								<th class="col-sm-1 text-center" rowspan="2">Kategori</th>
								<th class="col-sm-2 text-center" rowspan="2">Nama Item</th>
								<th class="col-sm-1 text-center" rowspan="2">Kemasan</th>
								<th class="col-sm-1 text-center" rowspan="2">Harga</th>
								<th class="col-sm-1 text-center" rowspan="2">Jumlah</th>
								<th class="col-sm-2 text-center" rowspan="2">Total</th>
								<th class="text-center" colspan="2">Kirim</th>
								<th class="col-sm-3 text-center" rowspan="2">Alamat</th>
							</tr>
							<tr class="v-center">
								<th class="col-sm-1 text-center">Kantor</th>
								<th class="col-sm-1 text-center">Peternak</th>
							</tr>
						</thead>
						<tbody class="list">
							<tr class="child inactive">
								<td>
									<select class="form-control kategori" onchange="odvp.set_item_voadip(this)" data-required="1">
										<option value="">-- Pilih Kategori --</option>
										<?php foreach ($kategori_voadip as $k_kat => $v_kat): ?>
											<option value="<?php echo $k_kat; ?>"><?php echo $v_kat; ?></option>
										<?php endforeach ?>
									</select>
								</td>
								<td>
									<select class="form-control barang" data-required="1">
										<option value="">-- Pilih Barang --</option>
									</select>
								</td>
								<td>
									<input type="text" class="form-control kemasan" placeholder="Kemasan" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right harga" placeholder="Harga" data-tipe="decimal" maxlength="10" onblur="odvp.hit_total_order_voadip(this)" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" maxlength="10" onblur="odvp.hit_total_order_voadip(this)" data-required="1">
								</td>
								<td>
									<input type="text" class="form-control text-right total" placeholder="Total" data-tipe="decimal" data-required="1" readonly>
								</td>
								<td>
									<select class="form-control kantor" onchange="odvp.set_item_pwk(this)" data-required="1">
										<option value="">-- Pilih Kantor --</option>
										<?php foreach ($perwakilan as $k_pwk => $v_pwk): ?>
											<?php foreach ($v_pwk['perwakilan'] as $key => $val): ?>
												<option value="<?php echo $val['id']; ?>"><?php echo $val['nama']; ?></option>
											<?php endforeach ?>
										<?php endforeach ?>
									</select>
								</td>
								<td>
									<select class="form-control peternak" onchange="odvp.set_alamat(this)">
										<option value="">-- Pilih Peternak --</option>
									</select>
								</td>
								<td>
									<input type="text" class="form-control alamat_peternak" placeholder="Alamat Peternak" readonly>
									<div class="btn-ctrl">
										<span onclick="odvp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
										<span onclick="odvp.addRowChild(this)" class="btn_add_row_2x"></span>
									</div>
								</td>
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