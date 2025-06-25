<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="form-group">
				<div class="col-md-1">
					<label class="control-label">Unit</label>
				</div>
				<div class="col-md-2">
					<select class="form-control unit" data-required="1" onchange="real_sj.get_mitra(this)">
						<option value="">Pilih Unit</option>
						<?php if ( !empty($unit) ): ?>
            				<?php foreach ($unit as $k_unit => $v_unit): ?>
            					<option value="<?php echo $v_unit['id']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
            				<?php endforeach ?>
            			<?php endif ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-1" style="padding-right: 0px;">
					<label class="control-label">Tgl Panen</label>
				</div>
				<div class="col-sm-2">
	                <div class="input-group date" id="tgl_panen" name="tgl_panen">
	                    <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" onblur="real_sj.get_mitra(this)" />
	                    <span class="input-group-addon">
	                        <span class="glyphicon glyphicon-calendar"></span>
	                    </span>
	                </div>
	            </div>
			</div>
			<div class="form-group">
				<div class="col-md-1">
					<label class="control-label">Mitra</label>
				</div>
				<div class="col-md-3">
					<select class="form-control mitra" data-required="1" onchange="real_sj.get_data(this)">
						<option value="">Pilih Mitra</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-12">
					<hr style="margin-top: 0px; margin-bottom: 0px;">
				</div>
			</div>
			<div class="col-md-12 no-padding data_sj">
				<?php echo $add_form; ?>
			</div>
		</form>
	</div>
</div>