<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center no-padding filter">
        <div class="col-lg-2 text-left">Filter OP</div>
        <div class="col-lg-2">
            <select class="form-control unit">
                <option value="">-- Pilih Unit --</option>
                <?php if ( count($unit) > 0 ): ?>
                    <?php foreach ($unit as $k => $val): ?>
                        <option value="<?php echo $val['kode'] ?>"><?php echo strtoupper($val['nama']); ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
        <div class="col-lg-2" style="padding-left: 0px;">
            <div class="input-group date datetimepicker" name="tgl_kirim_op" id="tgl_kirim_op">
                <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div class="col-lg-2" style="padding-left: 0px;">
            <button type="button" class="btn btn-primary get_op_not_kirim" onclick="pp.get_op_not_kirim(this)">Ambil OP</button>
        </div>
    </div>
</div>
<hr style="margin-top: 10px; margin-bottom: 10px;">
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">Jenis Pengiriman</div>
		<div class="col-lg-2">
			<select class="form-control jenis_kirim" data-required="1" onchange="pp.cek_jenis(this)">
				<option value="">-- Pilih Jenis --</option>
				<option value="opks">Order Pabrik (OPKS)</option>
				<option value="opkp">Dari Peternak (OPKP)</option>
				<option value="opkg">Dari Gudang (OPKG)</option>
			</select>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ongkos Angkut</div>
		<div class="col-lg-2">
			<input type="text" class="form-control text-right ongkos_angkut" placeholder="Ongkos Angkut" data-required="1" data-tipe="decimal" maxlength="14">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">No. Order</div>
		<div class="col-lg-2">
			<select class="form-control no_order" data-jenis="opks" data-required="1" onchange="pp.get_asal(this)" disabled>
				<option value="">-- Pilih No. Order --</option>
				<!-- <?php foreach ($order_pakan as $k_op => $v_op): ?>
					<option value="<?php echo $v_op['no_order']; ?>" data-supplier="<?php echo $v_op['d_supplier']['nama']; ?>" data-idsupplier="<?php echo $v_op['supplier']; ?>"><?php echo $v_op['no_order']; ?></option>
				<?php endforeach ?> -->
			</select>
			<input type="text" class="form-control no_order hide" data-jenis="non_opks" placeholder="No. Order" readonly>
		</div>
	</div>
</div>
<div class="form-group align-items-center opks">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">Perusahaan</div>
		<div class="col-lg-2">
			<input type="text" class="form-control perusahaan" placeholder="Perusahaan" readonly>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Asal</div>
		<div class="col-lg-4 opks">
			<input type="text" class="form-control asal" placeholder="Asal" data-required="1" readonly>
		</div>
		<div class="col-lg-4 opkp hide">
			<div class="col-lg-12 div_peternak no-padding">
				<div class="col-lg-3 no-padding">
					<input type="text" class="form-control text-center datetimepicker" placeholder="Bulan" name="bulan_docin" id="bulan_docin" onblur="pp.get_peternak(this)" />
		        </div>
		        <div class="col-lg-9">
					<select class="form-control peternak_asal" onchange="pp.getSjAsal(this)">
						<option value="">-- Pilih Peternak --</option>
						<?php foreach ($peternak as $k_peternak => $v_peternak): ?>
							<!-- <option value="<?php echo $v_peternak['noreg']; ?>"><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option> -->
						<?php endforeach ?>
					</select>
		        </div>
		    </div>
		</div>
		<div class="col-lg-4 opkg hide">
			<select class="form-control gudang_asal" onchange="pp.cek_gudang(this)">
				<option value="">-- Pilih Gudang --</option>
				<?php foreach ($gudang_asal as $k_gudang => $v_gudang): ?>
					<option value="<?php echo $v_gudang['id']; ?>"><?php echo strtoupper($v_gudang['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tujuan</div>
		<div class="col-lg-2">
			<!-- <input type="text" class="form-control tujuan" placeholder="Tujuan" data-required="1" readonly> -->
			<select class="form-control tujuan" onchange="pp.cek_tujuan(this)">
				<option value="peternak">Peternak</option>
				<option value="gudang">Gudang</option>
			</select>
		</div>
		<div class="col-lg-6" style="padding-left: 0px;">
			<div class="col-lg-12 div_peternak no-padding">
				<div class="col-lg-3 no-padding">
					<input type="text" class="form-control text-center datetimepicker" placeholder="Bulan" name="bulan_docin" id="bulan_docin" onblur="pp.get_peternak(this)" />
		        </div>
		        <div class="col-lg-9">
					<select class="form-control peternak">
						<option value="">-- Pilih Peternak --</option>
						<!-- <?php foreach ($peternak as $k_peternak => $v_peternak): ?>
							<option value="<?php echo $v_peternak['noreg']; ?>"><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
						<?php endforeach ?> -->
					</select>
		        </div>
			</div>
			<div class="col-lg-6 gudang hide no-padding">
				<select class="form-control gudang">
					<option value="">-- Pilih Gudang --</option>
					<?php foreach ($gudang_tujuan as $k_gudang => $v_gudang): ?>
						<option value="<?php echo $v_gudang['id']; ?>"><?php echo strtoupper($v_gudang['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Rencana Kirim</div>
		<div class="col-lg-2">
			<div class="input-group date datetimepicker" name="rcn_kirim" id="rcn_kirim">
		        <input type="text" class="form-control text-center" placeholder="Rencana Kirim" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ekspedisi</div>
		<div class="col-lg-4">
			<!-- <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1"> -->
			<select class="form-control ekspedisi" data-required="1">
				<option value="">-- Piliih Ekspedisi --</option>
				<?php foreach ($ekspedisi as $k_eks => $v_eks): ?>
					<option value="<?php echo $v_eks['nomor']; ?>" data-nama="<?php echo $v_eks['nama']; ?>" ><?php echo $v_eks['nomor'].' | '.$v_eks['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tgl Kirim</div>
		<div class="col-lg-2">
			<div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
		        <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">No. Polisi</div>
		<div class="col-lg-2">
			<input type="text" class="form-control no_pol" placeholder="No. Polisi" data-required="1">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">No. SJ</div>
		<div class="col-lg-3">
			<input type="text" class="form-control no_sj" placeholder="No. SJ" data-required="1" readonly>
		</div>
		<div class="col-lg-1"></div>
		<div class="col-lg-2">Sopir</div>
		<div class="col-lg-2">
			<input type="text" class="form-control sopir" placeholder="Sopir" data-required="1">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center" style="padding-right: 30px;">
	<div class="col-lg-12 d-flex align-items-center">
		<table class="table table-bordered table-hover tbl_detail_brg non_opkp" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">Jenis Pakan</th>
					<th class="col-lg-2">Jumlah</th>
					<th class="col-lg-2">Kondisi</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select class="form-control barang">
							<?php foreach ($pakan as $k_pakan => $v_pakan): ?>
								<option value="<?php echo $v_pakan['kode']; ?>"><?php echo $v_pakan['nama']; ?></option>
							<?php endforeach ?>
						</select>
					</td>
					<td>
						<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" onblur="pp.cek_stok_gudang(this)">
					</td>
					<td>
						<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1">
						<div class="btn-ctrl">
							<span onclick="pp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
							<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table table-bordered table-hover tbl_detail_brg opkp hide" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">No. SJ Asal</th>
					<th class="col-lg-2">Jenis Pakan</th>
					<th class="col-lg-2">Jumlah</th>
					<th class="col-lg-2">Kondisi</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select class="form-control no_sj_asal" data-required="1" onchange="pp.getBarang(this)">
							<option value="">No. SJ Asal</option>
						</select>
					</td>
					<td>
						<select class="form-control barang" data-required="1" onchange="pp.pilihBarang(this)">
							<option value="">Pilih Barang</option>
						</select>
					</td>
					<td>
						<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" onkeyup="pp.cekJmlPindah(this)" disabled>
					</td>
					<td>
						<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1">
						<div class="btn-ctrl">
							<span onclick="pp.removeRowChild(this)" class="btn_del_row_2x hide"></span>
							<span onclick="pp.addRowChild(this)" class="btn_add_row_2x"></span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
</div>
<div class="form-group">
	<div class="col-lg-12">
		<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="pp.save_kirim_pakan()"> 
			<i class="fa fa-save" aria-hidden="true"></i> Simpan
		</button>
	</div>
</div>