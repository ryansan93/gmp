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
						<div class="col-lg-4">No Order</div>
						<div class="col-lg-8">
							<input type="text" class="form-control no_order" placeholder="No. Order" readonly>
						</div>
					</div>
					<div class="col-lg-5 d-flex align-items-center">
						<div class="col-lg-4">Tanggal</div>
						<div class="col-lg-5">
							<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
						        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
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
									<option value="<?php echo $v_supl['nomor']; ?>"><?php echo $v_supl['nama']; ?></option>								
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-lg-5 d-flex align-items-center">
						<div class="col-lg-4">Rencana Kirim</div>
						<div class="col-lg-5">
							<div class="input-group date datetimepicker" name="rcn_kirim" id="rcn_kirim">
						        <input type="text" class="form-control text-center" placeholder="Rencana Kirim" data-required="1" />
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
								<tr>
									<td class="text-right" colspan="5"><b>Total</b></td>
									<td class="total_beli text-right"><b>0</b></td>
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
									<th class="text-center" colspan="1">Kirim</th>
									<th class="text-center col-sm-2" rowspan="2">Alamat</th>
								</tr>
								<tr>
									<th class="text-center col-sm-2">Gudang</th>
									<th class="text-center col-sm-2 hide">Peternak</th>
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
										<select class="form-control barang" data-required="1"onchange="odvp.set_bentuk_order_pakan(this)">
											<option value="">-- Pilih Barang --</option>
											<?php foreach ($barang as $k_barang => $v_barang): ?>
												<option value="<?php echo $v_barang['kode']; ?>" data-bentuk="<?php echo $v_barang['bentuk']; ?>"><?php echo strtoupper($v_barang['nama']); ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td class="text-center bentuk">-</td>
									<td>
										<input type="text" class="form-control text-right harga" placeholder="Harga" maxlength="9" data-tipe="integer" onblur="odvp.hit_total_order_pakan(this)" data-required="1">
									</td>
									<td class="hide">
										<input type="text" class="form-control text-right harga_jual" placeholder="Harga" maxlength="9" data-tipe="integer">
									</td>
									<td>
										<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" maxlength="6" data-tipe="integer" onblur="odvp.hit_total_order_pakan(this)" data-required="1">
									</td>
									<td class="text-right total">0</td>
									<td>
										<select class="form-control gudang" data-jenis="gudang" data-required="1" onchange="odvp.set_alamat_order_pakan(this)">
											<option value="">-- Pilih Gudang --</option>
											<?php foreach ($gudang as $k_gudang => $v_gudang): ?>
												<option value="<?php echo $v_gudang['id']; ?>" data-alamat="<?php echo $v_gudang['alamat']; ?>"><?php echo strtoupper($v_gudang['nama']); ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td class="hide">
										<select class="form-control peternak" data-jenis="peternak" data-required="1" onchange="odvp.set_alamat_order_pakan(this)">
											<option value="">-- Pilih Peternak --</option>
											<?php foreach ($peternak as $k_peternak => $v_peternak): ?>
												<option value="<?php echo $v_peternak['noreg']; ?>" data-alamat="<?php echo $v_peternak['alamat']; ?>"><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
											<?php endforeach ?>
										</select>
									</td>
									<td class="alamat">
										<div class="alamat">-</div>
										<div class="btn-ctrl" style="margin-top: 13px;">
											<span onclick="odvp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
											<span onclick="odvp.addRowChildOrderPakan(this)" class="btn_add_row_2x"></span>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
				<div class="form-group">
					<div class="col-lg-12 no-padding">
						<hr>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.save_order_pakan(this)" style="margin-left: 10px;"> 
							<i class="fa fa-save" aria-hidden="true"></i> Simpan
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>