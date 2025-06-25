<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12" id="penerimaan-pakan">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Unit</div>
					<div class="col-lg-2">
						<select class="form-control" name="unit" onchange="pp.set_noreg(this)" data-required="1">
							<option value="">-- Pilih Unit --</option>
							<?php foreach ($unit as $k_unit => $v_unit): ?>
								<option value="<?php echo $v_unit['id']; ?>"><?php echo $v_unit['nama']; ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Noreg</div>
					<div class="col-lg-2">
						<select class="form-control" name="noreg" onchange="pp.set_value(this)" data-required="1">
							<option value="">-- Pilih No. Reg --</option>
						</select>
					</div>
					<div  class="col-lg-1"></div>
					<div class="col-lg-1">Kandang</div>
					<div class="col-lg-1">
						<input type="text" class="form-control text-right" name="kandang" placeholder="KANDANG" data-required="1" readonly>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Peternak</div>
					<div class="col-lg-3">
						<input type="text" class="form-control" name="peternak" placeholder="PETERNAK" data-required="1" readonly>
					</div>
					<div class="col-lg-1">Populasi</div>
					<div class="col-lg-1">
						<input type="text" class="form-control text-right" name="populasi" placeholder="POPULASI" data-tipe="integer" data-required="1" readonly>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Ekspedisi SPM</div>
					<div class="col-lg-3">
						<input type="text" class="form-control" name="ekspedisi" placeholder="EKSPEDISI" data-required="1" readonly>
					</div>
					<div class="col-lg-1">No Polisi</div>
					<div class="col-lg-2">
						<input type="text" class="form-control" name="nopol" placeholder="NO. POLISI" data-required="1">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">No. SJ</div>
					<div class="col-lg-2">
						<input type="text" class="form-control" name="no_sj" placeholder="NO. SJ" data-required="1">
					</div>
					<div  class="col-lg-1"></div>
					<div class="col-lg-1">Sopir</div>
					<div class="col-lg-3">
						<input type="text" class="form-control" name="nama_sopir" placeholder="NAMA SOPIR" data-required="1">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Tgl & Jam Terima</div>
					<div class="col-lg-2">
						<div class="input-group date col-md-12" id="datetimepicker1" name="tgl_tiba_kdg">
					        <input type="text" class="form-control text-center" placeholder="TERIMA" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12">&nbsp</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<table class="table table-bordered tbl_list_pakan">
							<thead>
								<tr>
									<th class="text-center" colspan="3">SPM</th>
									<th class="text-center" colspan="3">Terima</th>
								</tr>
								<tr>
									<th class="text-center col-lg-3">Nama Pakan</th>
									<th class="text-center col-lg-1">Jml Pakan</th>
									<th class="text-center col-lg-2">Tonase</th>
									<th class="text-center col-lg-2">Nama Pakan</th>
									<th class="text-center col-lg-2">Zak</th>
									<th class="text-center col-lg-2">Tonase</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="6">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<button type="button" class="btn btn-primary pull-right" onclick="pp.save()"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>