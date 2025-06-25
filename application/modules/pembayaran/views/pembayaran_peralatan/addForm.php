<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Bayar</label></div>
	<div class="input-group date" name="tglBayar" id="TglBayar">
        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal Bayar" />
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
						<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>"><?php echo strtoupper($val['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Plasma</label></div>
	    <div class="col-xs-12 no-padding">
	    	<input type="text" class="form-control text-left mitra" data-required="1" placeholder="Plasma" readonly />
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. Order</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control no_order" data-required="1" disabled>
				<option value="">-- Pilih No. Order --</option>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. Faktur</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right no_faktur" data-required="1" placeholder="No. Faktur" />
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Saldo</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right saldo" data-required="1" placeholder="Saldo" readonly />
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Bayar</label></div>
		<div class="col-xs-10 no-padding">
			<input type="text" class="form-control text-right jumlah_bayar" data-required="1" placeholder="Bayar" onblur="pp.hitTotalBayar()" data-tipe="decimal" />
		</div>
		<div class="col-xs-2 no-padding" style="padding-left: 10px;">
			<div class="col-xs-12 text-right" style="padding: 7px 0px 0px 0px;">
				<a name="dokumen" class="hide" href="" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
				<label class="">
					<input type="file" onchange="pp.showNameFile(this)" class="file_lampiran" data-required="1" name="" placeholder="Bukti Transfer" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right jumlah_tagihan" data-required="1" placeholder="Tagihan" readonly />
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Bayar</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right total_bayar" data-required="1" placeholder="Total Bayar" readonly />
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
				<tr>
					<td colspan="4">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="pp.save()"><i class="fa fa-save"></i> Simpan</button>
</div>