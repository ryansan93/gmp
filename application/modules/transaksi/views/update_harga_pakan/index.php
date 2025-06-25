<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-12 no-padding">
                    <label class="control-label">Tanggal Order</label>
                </div>
                <div class="col-xs-12 no-padding">
                    <div class="input-group date datetimepicker" name="tglOrder" id="TglOrder">
                        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-8 no-padding" style="padding-right: 5px;">
                    <div class="col-xs-12 no-padding">
                        <label class="control-label">Supplier</label>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control supplier" data-required="1">
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($supplier as $key => $value) { ?>
                                <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nomor'].' | '.$value['nama']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-xs-4 no-padding" style="padding-left: 5px;">
                    <div class="col-xs-12 no-padding">
                        <label class="control-label">Pakan</label>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control pakan" data-required="1">
                            <option value="">-- Pilih Pakan --</option>
                            <?php foreach ($pakan as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-12 no-padding">
                    <label class="control-label">Harga Baru</label>
                </div>
                <div class="col-xs-12 no-padding">
                    <input type="text" class="form-control text-right harga" data-tipe="decimal" placeholder="Harga" data-required="1">
                </div>
            </div>
            <div class="col-xs-12 no-padding">
                <button type="button" class="col-xs-12 btn btn-primary" onclick="uhp.save()"><i class="fa fa-save"></i> Ubah Harga</button>
            </div>
            <div class="col-xs-12 no-padding">
                <hr style="margin-top: 5px; margin-bottom: 5px;">
            </div>
            <div class="col-xs-12 no-padding">
                <small>
                    <table class="table table-bordered" style="margin-bottom: 0px;">
                        <thead>
                            <tr>
                                <th class="col-xs-4">Keterangan</th>
                                <th class="col-xs-1">Tanggal Order</th>
                                <th class="col-xs-3">Supplier</th>
                                <th class="col-xs-2">Pakan</th>
                                <th class="col-xs-2">Harga Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5">Data tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </small>
            </div>
		</form>
	</div>
</div>