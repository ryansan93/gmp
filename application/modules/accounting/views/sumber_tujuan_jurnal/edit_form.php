<div class="modal-header header">
	<span class="modal-title">Edit Sumber / Tujuan Jurnal</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<table class="table no-border">
				<tbody>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Jurnal Trans</label>
						</td>
						<td class="col-md-9">
							<select id="jurnal_trans" class="form-control" type="text" data-required="1">
								<option value="">Pilih Jurnal Trans</option>
								<?php foreach ($jurnal_trans as $k => $val): ?>
									<?php
										$selected = null;
										if ( $val['id'] == $data['id_header'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $val['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Nama</label>
						</td>
						<td class="col-md-9">
							<input type="text" class="col-sm-12 form-control nama uppercase" placeholder="Nama Sumber / Tujuan" data-required="1" value="<?php echo $data['nama']; ?>" >
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-sm-12 no-padding">
			<hr style="margin-top: 0px;">
		</div>
		<div class="col-sm-12 no-padding" style="padding-right: 8px; padding-left: 8px;">
			<button type="button" class="btn btn-primary pull-right" onclick="stj.edit(this)" data-id="<?php echo $data['id']; ?>">
				<i class="fa fa-edit"></i>
				Simpan Perubahan
			</button>
		</div>
	</div>
</div>