<div class="modal-header header">
	<span class="modal-title">Add SKP Peternak</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-1">
						<span>Peternak</span>
					</div>
					<div class="col-lg-2">
						<select class="form-control nomor_mitra" onchange="skp.set_data_mitra(this)" data-required="1">
							<option value="">Pilih Peternak</option>
							<?php if ( count($mitra) > 0 ): ?>
								<?php foreach ($mitra as $k_mitra => $v_mitra): ?>
									<option value="<?php echo $v_mitra['nomor']; ?>" data-nama="<?php echo $v_mitra['nama']; ?>"><?php echo $v_mitra['nomor'] . ' | ' . $v_mitra['nama']; ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</div>
					<div class="col-lg-4">
						<input type="text" class="form-control nama_mitra" placeholder="Nama Peternak" data-required="1" readonly>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-1">
						<span>Berlaku</span>
					</div>
					<div class="col-lg-2">
						<div class="input-group date" id="start_date" name="start_date">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Mulai" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-lg-1 text-center">
						<label class="control-label">s/d</label>
					</div>
					<div class="col-lg-2">
						<div class="input-group date" id="end_date" name="end_date">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Berakhir" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-1">
						<span>Lampiran</span>
					</div>
					<div class="col-lg-10">
						<label class="control-label" style="margin-right: 5px;">
                        	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="skp.showNameFile(this)" data-name="no-name" data-required="1" data-allowtypes="doc|pdf|docx|jpg|jpeg|png">
                        	<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SKP"></i> 
                      	</label>
						<span name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;">
							.........................
						</span>
						<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right" style="margin-bottom: 0px;">
					<div class="col-lg-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="skp.save(this)"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>