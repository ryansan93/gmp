<div class="modal-header header">
	<span class="modal-title">Add Gudang</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<table class="table no-border">
				<tbody>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Nama Gudang</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-4 form-control nama uppercase" placeholder="Nama Gudang" data-required="1">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Alamat</label>
						</td>
						<td class="col-md-9">
							<textarea class="col-sm-7 form-control alamat uppercase" data-required="1"></textarea>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Jenis Gudang</label>
						</td>
						<td class="col-md-9">
							<select class="col-sm-2 form-control jenis" data-required="1">
								<option value="">Gudang</option>
								<option value="pakan">Pakan</option>
								<option value="obat">Obat</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Penanggung Jawab</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-5 form-control penanggung_jawab uppercase" placeholder="Penanggung Jawab" data-required="1">
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Unit</label>
						</td>
						<td class="col-md-9">
							<select class="col-sm-5 form-control unit" data-required="1">
								<option value="">Pilih Unit</option>
								<?php foreach ($unit as $k_unit => $v_unit): ?>
									<option value="<?php echo $v_unit['id']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Perusahaan</label>
						</td>
						<td class="col-md-9">
							<select class="col-sm-6 form-control perusahaan" data-required="1" disabled>
	                            <?php if ( count($perusahaan) > 0 ): ?>
	                                <?php foreach ($perusahaan as $k_prs => $v_prs): ?>
	                                    <option value="<?php echo $v_prs['kode']; ?>"><?php echo strtoupper($v_prs['nama']); ?></option>
	                                <?php endforeach ?>
	                            <?php endif ?>
	                        </select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-sm-12 no-padding">
			<hr>
			<button type="button" class="btn btn-primary" onclick="gudang.save()">
				<i class="fa fa-save"></i>
				Save
			</button>
		</div>
	</div>
</div>