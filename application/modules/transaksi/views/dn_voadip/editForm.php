<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control nomor" placeholder="No. DN" value="<?php echo $data['nomor']; ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal DN</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php foreach ($supplier as $key => $value): ?>
                    <?php
                        $selected = null;
                        if ( $value['nomor'] == $data['supplier'] ) {
                            $selected = 'selected';
                        }
                    ?>
					<option value="<?php echo $value['nomor']; ?>" <?php echo $selected; ?> ><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Keterangan DN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_dn" placeholder="Keterangan DN" data-required="1"><?php echo $data['ket_dn']; ?></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-3 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Total DN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right tot_dn" data-tipe="decimal" placeholder="Total DN" data-required="1" value="<?php echo angkaDecimal($data['tot_dn']); ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="overflow-x: auto;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
			<thead>
				<tr>
					<th class="col-xs-2">No. SJ</th>
					<th class="col-xs-7">Keterangan</th>
					<th class="col-xs-2">Nominal</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
                <?php if ( isset($data['detail']) && !empty($data['detail']) ) { ?>
                    <?php foreach ($data['detail'] as $k_det => $v_det) { ?>
                        <tr>
                            <td>
                                <select class="form-control no_sj">
                                    <option value="<?php echo $v_det['no_sj']; ?>" selected="selected"><?php echo $v_det['tgl_sj'].' | '.$v_det['no_sj']; ?></option>
                                </select>
                            </td>
                            <td>
                                <textarea class="form-control ket" data-required="1" placeholder="Keterangan"><?php echo $v_det['ket']; ?></textarea>
                            </td>
                            <td>
                                <input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" value="<?php echo angkaDecimal($v_det['nominal']); ?>" onblur="dn.hitTot()">
                            </td>
                            <td>
                                <div class="col-xs-12 no-padding">
                                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                        <button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRow(this)"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                        <button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRow(this)"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td>
                            <select class="form-control no_sj">
                            </select>
                        </td>
                        <td>
                            <textarea class="form-control ket" data-required="1" placeholder="Keterangan"></textarea>
                        </td>
                        <td>
                            <input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" onblur="dn.hitTot()">
                        </td>
                        <td>
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                    <button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRow(this)"><i class="fa fa-plus"></i></button>
                                </div>
                                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                    <button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRow(this)"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
        <button type="button" class="col-xs-12 btn btn-danger" onclick="dn.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="" data-href="action"><i class="fa fa-times"></i> Batal</button>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="dn.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
    </div>
</div>