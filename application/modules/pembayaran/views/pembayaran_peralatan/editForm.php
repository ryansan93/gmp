<input type="hidden" id="id" value="<?php echo $data['id']; ?>">
<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Bayar</label></div>
	<div class="input-group date" name="tglBayar" id="TglBayar">
        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal Bayar" data-tgl="<?php echo $data['tgl_bayar']; ?>" />
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php if ( isset($supplier) && !empty($supplier) ): ?>
					<?php foreach ($supplier as $k => $val): ?>
						<?php
							$selected = null;
							if ( $val['nomor'] == $data['supplier'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. Order</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control no_order" data-required="1" data-val="<?php echo $data['no_order']; ?>">
				<option value="">-- Pilih No. Order --</option>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Plasma</label></div>
	    <div class="col-xs-12 no-padding">
	    	<input type="text" class="form-control text-left mitra" data-required="1" placeholder="Plasma" value="<?php echo $data['nama_mitra']; ?>" readonly />
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. Faktur</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right no_faktur" data-required="1" placeholder="No. Faktur" value="<?php echo $data['no_faktur']; ?>" />
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Saldo</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right saldo" data-required="1" placeholder="Saldo" value="<?php echo angkaDecimal($data['saldo']); ?>" readonly />
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Bayar</label></div>
		<div class="col-xs-10 no-padding">
			<input type="text" class="form-control text-right jumlah_bayar" data-required="1" placeholder="Bayar" onblur="pp.hitTotalBayar()" data-tipe="decimal" value="<?php echo angkaDecimal($data['jml_bayar']); ?>" />
		</div>
		<div class="col-xs-2 no-padding" style="padding-left: 10px;">
			<div class="col-xs-12 text-right" style="padding: 7px 0px 0px 0px;">
				<a name="dokumen" class="" href="uploads/<?php echo $data['lampiran']; ?>" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
				<label class="">
					<input type="file" onchange="pp.showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">CN</label></div>
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<input type="text" class="form-control text-right tot_cn" placeholder="CN" value="<?php echo angkaDecimal($data['tot_cn']) ?>" readonly />
			<span class="d_cn hide"><?php echo (isset($data['cn']) && !empty($data['cn'])) ? json_encode($data['cn']) : null; ?></span>
		</div>
		<div class="col-xs-4 no-padding" style="padding-left: 5px;">
			<button type="button" class="btn btn-default" onclick="pp.modalPilihCN(this)">Pilih CN yang akan di gunakan</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">DN</label></div>
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<input type="text" class="form-control text-right tot_dn" placeholder="DN" value="<?php echo angkaDecimal($data['tot_dn']) ?>" readonly />
			<span class="d_dn hide"><?php echo (isset($data['dn']) && !empty($data['dn'])) ? json_encode($data['dn']) : null ?></span>
		</div>
		<div class="col-xs-4 no-padding" style="padding-left: 5px;">
			<button type="button" class="btn btn-default" onclick="pp.modalPilihDN(this)">Pilih DN yang akan di gunakan</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah_tagihan" data-required="1" placeholder="Tagihan" value="<?php echo angkaDecimal($data['jml_tagihan']); ?>" readonly />
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Tagihan</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right tot_tagihan" data-required="1" placeholder="Tagihan" value="<?php echo angkaDecimal($data['tot_tagihan']); ?>" readonly />
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Bayar</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right total_bayar" data-required="1" placeholder="Total Bayar" value="<?php echo angkaDecimal($data['tot_bayar']); ?>" readonly />
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">Barang</th>
					<th class="col-xs-2">Jumlah</th>
					<th class="col-xs-2">Harga</th>
					<th class="col-xs-2">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $key => $value): ?>
					<tr>
						<td><?php echo $value['nama_barang']; ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['jumlah']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['harga']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['total']); ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="pp.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<button type="button" class="col-xs-12 btn btn-danger" onclick="pp.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action"><i class="fa fa-times"></i> Batal</button>
	</div>
</div>