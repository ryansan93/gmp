<div class="panel-body" style="padding-top: 0px;">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-md-2 no-padding">
                        <label class="control-label">Tgl Panen</label>
                    </div>
                    <div class="col-md-2 no-padding">
                        <div class="input-group date" id="tgl_panen" name="tgl_panen" data-tgl="<?php echo $data['tgl_panen']; ?>">
                            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" value="<?php echo tglIndonesia( $data['tgl_panen'], '-', ' ', TRUE); ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Unit</label>
                	</div>
                	<div class="col-md-3 no-padding">
                		<select class="form-control unit" data-required="1" onchange="rpah.get_data(this)">
                			<option value="">Pilih Unit</option>
                			<?php if ( !empty($unit) ): ?>
                				<?php foreach ($unit as $k_unit => $v_unit): ?>
                                    <?php
                                        $selected = null;
                                        if ( $data['id_unit'] == $v_unit['id'] ) {
                                            $selected = 'selected';
                                        }
                                    ?>
                					<option value="<?php echo $v_unit['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_unit['nama']); ?></option>
                				<?php endforeach ?>
                			<?php endif ?>
                		</select>
                	</div>
                	<!-- <div class="col-md-7 no-padding text-right">
                		<label class="control-label tgl_panen" data-tgl="<?php echo $data['tgl_panen']; ?>">Tanggal Panen : <?php echo tglIndonesia( $data['tgl_panen'], '-', ' ', TRUE); ?></label>
                	</div> -->
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Bottom Price</label>
                	</div>
                	<div class="col-md-2 no-padding">
                		<input type="text" class="form-control text-right bottom_price" placeholder="BOTTOM PRICE" value="<?php echo angkaRibuan($data['bottom_price']); ?>" data-tipe="integer" data-required="1" />
                	</div>
                </div>
                <div class="form-group">
                	<small>
	                	<table class="table table-bordered tbl_data_konfir header">
	                		<thead>
	                			<tr>
	                				<th class="col-md-4">Nama Peternak</th>
	                				<th class="col-md-2">Noreg</th>
	                				<th class="col-md-1">Kandang</th>
	                				<th class="col-md-1">Ekor</th>
                                    <th class="col-md-1">Tonase</th>
	                			</tr>
	                		</thead>
	                		<tbody>
	                			<?php if ( !empty($data['konfir']) ): ?>
                                    <?php foreach ($data['konfir'] as $k_konfir => $v_konfir): ?>
                                        <tr class="head" data-idkonfir="<?php echo $v_konfir['id']; ?>">
                                            <td><?php echo strtoupper($v_konfir['mitra']); ?></td>
                                            <td class="noreg"><?php echo $v_konfir['noreg']; ?></td>
                                            <td class="text-center kandang" data-unit="<?php echo strtoupper($v_konfir['unit']); ?>"><?php echo $v_konfir['kandang']; ?></td>
                                            <td class="text-right head_tot_ekor"><?php echo angkaDecimal($v_konfir['ekor']); ?></td>
                                            <td class="text-right head_tot_kg"><?php echo angkaDecimal($v_konfir['tonase']); ?></td>
                                        </tr>
                                        <tr class="detail">
                                            <td colspan="6" style="padding-right: 40px;">
                                                <table class="table table-bordered detail">
                                                    <thead>
                                                        <tr>
                                                            <th class="col-md-1">No. DO</th>
                                                            <th class="col-md-1">No. SJ</th>
                                                            <th class="col-md-3">Nama Pelanggan</th>
                                                            <th class="col-md-1">Outstanding</th>
                                                            <th class="col-md-1">Ekor</th>
                                                            <th class="col-md-1">Tonase</th>
                                                            <th class="col-md-1">BB</th>
                                                            <th class="col-md-1">Harga</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $idx = 0; $tot_ekor = 0; $tot_kg = 0;$tot_bb = 0; ?>
                                                        <?php foreach ($v_konfir['det_rpah'] as $k_drpah => $v_drpah): ?>
                                                            <?php $idx++; ?>
                                                            <tr>
                                                                <td>
                                                                    <input type="text" class="form-control no_do" value="<?php echo $v_drpah['no_do'] ?>" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control no_sj" value="<?php echo $v_drpah['no_sj'] ?>" readonly>
                                                                </td>
                                                                <td>
                                                                    <select class="form-control pelanggan" data-required="1">
                                                                        <option value="">Pilih Pelanggan</option>
                                                                        <?php if ( !empty($data_pelanggan) ): ?>
                                                                            <?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
                                                                                <?php
                                                                                    $selected = null;
                                                                                    if ( $v_drpah['no_plg'] == $v_dp['nomor'] ) {
                                                                                        $selected = 'selected';
                                                                                    }
                                                                                ?>
                                                                                <option value="<?php echo $v_dp['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dp['nama']); ?></option>
                                                                            <?php endforeach ?>
                                                                        <?php endif ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right outstanding" placeholder="OUTSTANDING" value="<?php echo $v_drpah['outstanding']; ?>" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right ekor" placeholder="EKOR" value="<?php echo angkaRibuan($v_drpah['ekor']); ?>" data-tipe="integer" data-required="1" onblur="rpah.hit_bb(this)">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right tonase" placeholder="TONASE" value="<?php echo angkaDecimal($v_drpah['tonase']); ?>" data-tipe="decimal" data-required="1" onblur="rpah.hit_bb(this)">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right bb" placeholder="BB" value="<?php echo angkaDecimal($v_drpah['bb']); ?>" data-tipe="decimal" data-required="1" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right harga" placeholder="HARGA" value="<?php echo angkaRibuan($v_drpah['harga']); ?>" data-tipe="integer" data-required="1">
                                                                    <div class="btn-ctrl" style="<?php echo (count($v_konfir['det_rpah']) != $idx) ? 'display: none': ''; ?>">
                                                                        <span onclick="rpah.removeRow(this)" class="btn_del_row_2x"></span>
                                                                        <span onclick="rpah.addRow(this)" class="btn_add_row_2x"></span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php $tot_ekor += $v_drpah['ekor']; $tot_kg += $v_drpah['tonase']; ?>
                                                        <?php endforeach ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td class="text-right" colspan="4"><b>Total</b></td>
                                                            <td class="text-right detail_tot_ekor <?php echo ($v_konfir['ekor'] < $tot_ekor) ? 'lebih' : ''; ?>"><b><?php echo angkaRibuan($tot_ekor); ?></b></td>
                                                            <td class="text-right detail_tot_kg <?php echo ($v_konfir['tonase'] < $tot_kg) ? 'lebih' : ''; ?>""><b><?php echo angkaDecimal($tot_kg); ?></b></td>
                                                            <td class="text-right detail_tot_bb"><b><?php echo ($tot_ekor > 0 && $tot_kg > 0) ? angkaDecimal($tot_kg/$tot_ekor) : '0'; ?></b></td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Data tidak ditemukan.</td>
                                    </tr>
                                <?php endif ?>
	                		</tbody>
	                	</table>
	                </small>
                </div>
            </form>
        </div>
        <div class="col-md-12 no-padding">
    		<button type="button" class="btn btn-primary pull-right" onclick="rpah.edit(this);" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Edit</button>
            <button type="button" class="btn btn-danger pull-right" onclick="rpah.changeTabActive(this)" data-href="rpah" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-times"></i> Batal</button>
    	</div>
    </div>
</div>