<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Transaksi Jurnal</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jurnal_trans" data-required="1">
				<option value="">-- Pilih Transaksi Jurnal --</option>
				<?php foreach ($jurnal_trans as $key => $value): ?>
                    <?php
                        $selected = null;
                        if ( $value['kode'] == $data['kode_jurnal_trans'] ) {
                            $selected = 'selected';
                        }    
                    ?>
					<option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-8 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Transaksi</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control det_jurnal_trans" data-required="1">
                <option value="<?php echo $data['kode_det_jurnal_trans']; ?>" data-asal="<?php echo $data['asal']; ?>" data-coa_asal="<?php echo $data['coa_asal']; ?>" data-tujuan="<?php echo $data['tujuan']; ?>" data-coa_tujuan="<?php echo $data['coa_tujuan']; ?>" selected="selected"><?php echo $data['kode_det_jurnal_trans'].' | '.$data['nama_det_jurnal_trans']; ?></option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-1 no-padding"><label class="label-control">Asal</label></div>
		<div class="col-xs-1 no-padding" style="max-width: 2%;"><label class="label-control">:</label></div>
		<div class="col-xs-10 no-padding"><label class="label-control asal"><?php echo $data['coa_asal'].' | '.$data['asal']; ?></label></div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-1 no-padding"><label class="label-control">Tujuan</label></div>
		<div class="col-xs-1 no-padding" style="max-width: 2%;"><label class="label-control">:</label></div>
		<div class="col-xs-10 no-padding"><label class="label-control tujuan"><?php echo $data['coa_tujuan'].' | '.$data['tujuan']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control no_cn" placeholder="No. CN" value="<?php echo $data['nomor']; ?>" disabled>
		</div>
	</div>
    <div class="col-xs-7 no-padding">&nbsp;</div>
	<div class="col-xs-3 no-padding text-right" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Total CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right tot_cn" data-tipe="decimal" placeholder="Total CN" data-required="1"value="<?php echo angkaDecimal($data['tot_cn']); ?>" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal CN</label></div>
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
		<div class="col-xs-12 no-padding"><label class="label-control">Supplier</label></div>
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
		<div class="col-xs-12 no-padding"><label class="label-control">Keterangan CN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_cn" placeholder="Keterangan CN" data-required="1"><?php echo $data['ket_cn']; ?></textarea>
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
                                <input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" value="<?php echo angkaDecimal($v_det['nominal']); ?>" onblur="cn.hitTot()">
                            </td>
                            <td>
                                <div class="col-xs-12 no-padding">
                                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                        <button type="button" class="col-xs-12 btn btn-primary" onclick="cn.addRow(this)"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                        <button type="button" class="col-xs-12 btn btn-danger" onclick="cn.removeRow(this)"><i class="fa fa-trash"></i></button>
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
                            <input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" onblur="cn.hitTot()">
                        </td>
                        <td>
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                    <button type="button" class="col-xs-12 btn btn-primary" onclick="cn.addRow(this)"><i class="fa fa-plus"></i></button>
                                </div>
                                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                    <button type="button" class="col-xs-12 btn btn-danger" onclick="cn.removeRow(this)"><i class="fa fa-trash"></i></button>
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
        <button type="button" class="col-xs-12 btn btn-danger" onclick="cn.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="" data-href="action"><i class="fa fa-times"></i> Batal</button>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="cn.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
    </div>
</div>