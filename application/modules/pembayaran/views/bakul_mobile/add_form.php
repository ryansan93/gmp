<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Bayar</label></div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date" id="tglBayar">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Pelanggan</label></div>
	<div class="col-xs-12 no-padding">
		<select id="select_pelanggan" class="form-control selectpicker" data-live-search="true" data-required="1" onchange="bakul.get_list_do(this)">
			<option value="">Pilih Pelanggan</option>
			<?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
				<option data-tokens="<?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?>" value="<?php echo $v_dp['nomor']; ?>"><?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?></option>
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
		<input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Jumlah" onblur="bakul.hit_total_uang()" data-required="1">
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12" style="padding: 7px 0px 0px 0px;">
			<label class="">
				<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="" placeholder="Bukti Transfer" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;">
				<i class="glyphicon glyphicon-paperclip cursor-p"></i>
			</label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Saldo</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Uang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" readonly>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
	</div>
</div>
<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" disabled="disabled">
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
				<tr>
					<td colspan="3">Data tidak ditemukan.</td>
				</tr>
				<tr>
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
									<td colspan="4">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<tbody>
								<tr>
									<th class="col-xs-4 text-center">Sudah Bayar</th>
									<td class="col-xs-8">-</td>
								</tr>
								<tr>
									<th class="col-xs-4 text-center">Jumlah Bayar</th>
									<td class="col-xs-8">-</td>
								</tr>
								<tr>
									<th class="col-xs-4 text-center">Penyesuaian</th>
									<td class="col-xs-8">-</td>
								</tr>
								<tr>
									<th class="col-xs-4 text-center">Status</th>
									<td class="col-xs-8">-</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="bakul.save()"><i class="fa fa-save"></i> Simpan</button>
</div>