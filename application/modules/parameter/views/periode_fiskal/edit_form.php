<div class="modal-header header">
	<span class="modal-title">Edit Biaya Operasional</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12" style="padding-bottom: 0px;">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">
						<label class="control-label" style="padding-top: 0px;">Periode</label>
					</div>
					<div class="col-lg-6">
						<div class="input-group date datetimepicker" name="periode" id="periode">
					        <input type="text" class="form-control text-center" placeholder="Periode" data-required="1" data-tgl="<?php echo $data['periode']; ?>" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">
						<label class="control-label" style="padding-top: 0px;">Start Date</label>
					</div>
					<div class="col-lg-6">
						<div class="input-group date datetimepicker" name="startDate" id="startDate">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" data-tgl="<?php echo $data['start_date']; ?>" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">
						<label class="control-label" style="padding-top: 0px;">End Date</label>
					</div>
					<div class="col-lg-6">
						<div class="input-group date datetimepicker" name="endDate" id="endDate">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" data-tgl="<?php echo $data['end_date']; ?>" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12">
						<label class="checkbox-inline">
							<input type="checkbox" value="1" class="status cursor-p" <?php echo ($data['status'] == 1) ? 'checked' : null; ?> ><label class="control-label" style="margin-top: 0xp; padding-top: 0px;">Aktif</label>
						</label>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-12 no-padding"><hr></div>
		<div class="col-lg-12">
			<form role="form" class="form-horizontal">
				<div class="form-group pull-right">
					<div class="col-lg-2">
						<button type="button" class="btn btn-primary cursor-p" onclick="pf.edit(this);" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Update</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>