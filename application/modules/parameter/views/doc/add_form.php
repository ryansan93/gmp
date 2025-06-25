<div class="col-md-12 no-padding">
	<div class="col-sm-6 no-padding">
		<table class="table no-border tbl_add_doc">
			<tbody>
				<tr class="v-center">
					<td class="col-md-3">
						<label class="" >Dokumen</label>
					</td>
					<td class="col-md-9">
						<span class="file">.....................</span>
						<label class="pull-left">
	                    	<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="......" data-allowtypes="doc|pdf|docx" style="display: none;">
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
					        <input type="text" class="form-control text-center" data-required="1" />
					        <span class="input-group-addon">
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
							<input data-required="1" type="text" class="form-control text-right" name="harga_kontrak" data-tipe="integer" placeholder="Harga" maxlength="7">
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-sm-12 no-padding">
		<hr>
		<button type="button" class="btn btn-primary" onclick="doc.save()">
			<i class="fa fa-save"></i>
			SAVE
		</button>
	</div>
</div>