<div class="modal-header header">
	<span class="modal-title">Edit COA</span>
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
									<?php
										$selected = null;
										if ( $data['id_perusahaan'] == $val['kode'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $val['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Unit</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-12 form-control unit uppercase" placeholder="UNIT" data-required="1" value="<?php echo $data['id_unit']; ?>">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Kode</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-4 form-control nama uppercase" placeholder="Kode COA" data-required="1" value="<?php echo $data['nama_coa']; ?>">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">COA</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-4 form-control coa uppercase" placeholder="COA" data-required="1" value="<?php echo $data['coa']; ?>">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Laporan</label>
						</td>
						<td class="col-md-9">
							<select class="form-control laporan" data-required="1">
								<option value="N" <?php echo ($data['lap'] == 'N') ? 'selected' : null; ?> >Neraca</option>
								<option value="L" <?php echo ($data['lap'] == 'L') ? 'selected' : null; ?> >Laba / Rugi</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Posisi COA</label>
						</td>
						<td class="col-md-9">
							<select class="form-control posisi" data-required="1">
								<option value="D" <?php echo ($data['coa_pos'] == 'D') ? 'selected' : null; ?> >DEBIT</option>
								<option value="K" <?php echo ($data['coa_pos'] == 'K') ? 'selected' : null; ?> >KREDIT</option>
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
			<button type="button" class="btn btn-primary pull-right" onclick="coa.edit(this)" data-id="<?php echo $data['id']; ?>" >
				<i class="fa fa-save"></i>
				Simpan Perubahan
			</button>
			<button type="button" class="btn btn-danger pull-right" onclick="coa.batal(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;" >
				<i class="fa fa-times"></i>
				Batal
			</button>
		</div>
	</div>
</div>