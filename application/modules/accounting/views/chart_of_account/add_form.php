<div class="modal-header header">
	<span class="modal-title">Add COA</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<table class="table no-border">
				<tbody>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Perusahaan</label>
						</td>
						<td class="col-md-9">
							<select id="perusahaan" class="form-control" type="text" data-required="1">
								<option value="">Pilih Perusahaan</option>
								<?php foreach ($perusahaan as $k => $val): ?>
									<option value="<?php echo $val['kode']; ?>"><?php echo strtoupper($val['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Unit</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-12 form-control unit uppercase" placeholder="UNIT" data-required="1">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Nama</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-4 form-control nama uppercase" placeholder="NAMA COA" data-required="1">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">COA</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-4 form-control coa uppercase" placeholder="COA" data-required="1">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Laporan</label>
						</td>
						<td class="col-md-9">
							<select class="form-control laporan" data-required="1">
								<option value="N">Neraca</option>
								<option value="L">Laba / Rugi</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Posisi COA</label>
						</td>
						<td class="col-md-9">
							<select class="form-control posisi" data-required="1">
								<option value="D">DEBIT</option>
								<option value="K">KREDIT</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-sm-12 no-padding">
			<hr style="margin-top: 0px;">
		</div>
		<div class="col-sm-12 no-padding" style="padding-right: 8px; padding-left: 8px;">
			<button type="button" class="btn btn-primary pull-right" onclick="coa.save(this)">
				<i class="fa fa-save"></i>
				Simpan
			</button>
		</div>
	</div>
</div>