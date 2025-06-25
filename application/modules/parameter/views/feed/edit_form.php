<?php // cetak_r($data->toArray()); ?>
<div class="col-md-12 no-padding">
	<div class="col-sm-6 no-padding">
		<input type="hidden" data-id="<?php echo $data['id']; ?>">
		<table class="table no-border tbl_add_pakan" width="40%">
			<tbody>
				<tr class="v-center">
					<td class="col-md-3">
						<label class="" >Dokumen</label>
					</td>
					<td class="col-md-9">
						<span class="file"><?php echo $data['lampiran']['filename']; ?></span>
						<label class="pull-left">
	                    	<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="<?php echo $data['lampiran']['path']; ?>" data-allowtypes="doc|pdf|docx" style="display: none;" data-old="<?php echo $data['lampiran']['path']; ?>">
	                    	<i class="glyphicon glyphicon-paperclip cursor-p"></i> 
	                  	</label>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">				
						<label class="control-label">Tanggal Berlaku</label>
					</td>
					<td class="col-md-9">
						<div class="input-group date col-md-5" id="datetimepicker1" name="tanggal-berlaku">
					        <input type="text" class="form-control text-center" data-required="1" value="<?php echo tglIndonesia($data['mulai'], '-', ' ') ?>" data-tanggal="<?php echo $data['mulai']; ?>" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">
						<label class=""><u>Harga</u></label>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">
						<label class="control-label input">Pakan 1</label>
					</td>
					<td class="col-md-9">
						<div class="col-md-4 no-padding">
							<input data-required="1" type="text" class="form-control text-right" name="pakan1" data-tipe="integer" placeholder="Harga" maxlength="7" value="<?php echo angkaRibuan($data['pakan1']); ?>">	
						</div>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">
						<label class="control-label input">Pakan 2</label>
					</td>
					<td class="col-md-9">
						<div class="col-md-4 no-padding">
							<input data-required="1" type="text" class="form-control text-right" name="pakan2" data-tipe="integer" placeholder="Harga" maxlength="7" value="<?php echo angkaRibuan($data['pakan2']); ?>">
						</div>
					</td>
				</tr>
				<tr>
					<td class="col-md-3">
						<label class="control-label input">Pakan 3</label>
					</td>
					<td class="col-md-9">
						<div class="col-md-4 no-padding">
							<input data-required="1" type="text" class="form-control text-right" name="pakan3" data-tipe="integer" placeholder="Harga" maxlength="7" value="<?php echo angkaRibuan($data['pakan3']); ?>">
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-sm-12 no-padding">
		<hr>
		<button type="button" class="btn btn-primary" onclick="feed.edit()">
			<i class="fa fa-edit"></i>
			EDIT
		</button>
	</div>
</div>