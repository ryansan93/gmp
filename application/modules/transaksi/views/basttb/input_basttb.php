<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>Periode RDIM</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-2">
		<div class="input-group date" id="periode">
	        <input type="text" class="form-control text-center" data-required="1" onblur="basttb.getNoreg(this)" placeholder="Periode" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>Noreg</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-2">
		<select class="form-control noreg" onchange="basttb.setNamaMitra(this)" data-required="1">
			<option value="">-- Pilih Noreg --</option>
		</select>
	</div>
</div>
<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>Nama Mitra</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-4">
		<input class="form-control mitra" data-required="1" readonly>
	</div>
</div>
<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>Tanggal Terima</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-2">
		<div class="input-group date" id="tgl_terima">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal Terima" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>No SJ</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-2">
		<input class="form-control nosj" data-required="1">
	</div>
</div>
<div class="col-lg-12 form-group align-items-center d-flex">
	<div class="col-md-1 text-right no-padding">
		<h5>Keterangan SJ</h5>
	</div>
	<div class="text-center" style="width: 3%;">:</div>
	<div class="col-md-5">
		<textarea class="form-control ketsj" data-required="1"></textarea>
	</div>
</div>
<div class="col-lg-12 no-padding">
	<hr>
</div>
<div class="col-lg-12 no-padding">
	<small>
		<table class="table table-bordered custom_table">
			<thead>
				<tr>
					<th class="text-center" colspan="2">Jumlah SJ</th>
					<th class="text-center" colspan="6">Jumlah Terima</th>
					<th class="text-center" colspan="2">Selisih</th>
					<th class="text-center" rowspan="2">Keterangan</th>
				</tr>
				<tr>
					<th class="text-center col-lg-1">Box</th>
					<th class="text-center col-lg-1">Ekor</th>
					<th class="text-center col-lg-1">Box</th>
					<th class="text-center col-lg-1">Ekor</th>
					<th class="text-center col-lg-1">Mati</th>
					<th class="text-center col-lg-1">Afkir</th>
					<th class="text-center col-lg-1">Stok Awal</th>
					<th class="text-center col-lg-1">BB</th>
					<th class="text-center col-lg-1">+/-</th>
					<th class="text-center col-lg-1">%</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="text" class="form-control text-right sj_box" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right sj_ekor" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right terima_box" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right terima_ekor" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right terima_mati" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right terima_afkir" data-tipe="integer" maxlength="7" data-required="1" onchange="basttb.hitungAll()"></td>
					<td><input type="text" class="form-control text-right terima_awal" data-tipe="integer" maxlength="7" data-required="1" readonly></td>
					<td><input type="text" class="form-control text-right terima_bb" data-tipe="integer" maxlength="7" data-required="1"></td>
					<td><input type="text" class="form-control text-right selisih_ekor" data-tipe="integer" maxlength="7" data-required="1" readonly></td>
					<td><input type="text" class="form-control text-right selisih_persen" data-tipe="decimal" maxlength="7" data-required="1" readonly></td>
					<td><input type="text" class="form-control ket_terima" data-required="1"></td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-lg-12 text-right no-padding">
	<button type="button" class="btn btn-primary save" href='#action' onclick="basttb.save(this)"><i class="fa fa-save"></i> Simpan</button>
</div>