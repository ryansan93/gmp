<?php // cetak_r($data->toArray()); ?>
<div class="col-md-12 no-padding">
	<div class="col-sm-6 no-padding">
		<table class="table no-border tbl_add_pakan">
			<tbody>
				<tr class="v-center">
					<td class="col-md-3">
						<label class="" >Dokumen</label>
					</td>
					<td class="col-md-9">
						<a href="uploads/<?php echo $data['lampiran']['path']; ?>"><?php echo $data['lampiran']['filename']; ?></a>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">				
						<label class="control-label">Tanggal Berlaku</label>
					</td>
					<td class="col-md-9">
						<div class="input-group date col-md-5" id="datetimepicker1" name="tanggal-berlaku">
					        <input type="text" class="form-control text-center" data-required="1" value="<?php echo tglIndonesia($data['mulai'], '-', ' '); ?>" readonly />
					        <span class="input-group-addon" readonly>
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">
						<label class="control-label">Harga Kontrak</label>
					</td>
					<td class="col-md-9">
						<div class="col-md-4 no-padding">
							<input data-required="1" type="text" class="form-control text-right" name="harga_kontrak" data-tipe="integer" placeholder="Harga" maxlength="7" value="<?php echo angkaRibuan($data['doc']); ?>" readonly />
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>