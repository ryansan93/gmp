<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-2 no-padding">
                        <label class="control-label">Periode Saldo Awal</label>
                    </div>
                    <div class="col-xs-3 no-padding">
                        <div class="input-group date datetimepicker" name="tglSa" id="TglSa">
                            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $periode; ?>" <?php echo !empty($periode) ? 'disabled' : ''; ?> />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 no-padding"><hr></div>
                <div class="col-xs-12 no-padding form_data">
                    <?php echo $formData; ?>
                </div>
            </div>
        </form>
    </div>
</div>