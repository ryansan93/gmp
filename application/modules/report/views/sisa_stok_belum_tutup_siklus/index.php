<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <select class="jenis" name="jenis[]" multiple="multiple" width="100%" data-required="1">
                    <option value="all">All</option>
                    <?php if ( !empty($jenis) ): ?>
                        <?php foreach ($jenis as $k_jns => $v_jns): ?>
                            <option value="<?php echo $k_jns; ?>"><?php echo strtoupper($v_jns); ?></option>
                        <?php endforeach ?>
                    <?php endif ?>
                </select>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
                    <option value="all" > All </option>
                    <?php foreach ($unit as $key => $v_unit): ?>
                        <option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <select class="perusahaan" name="perusahaan[]" multiple="multiple" width="100%" data-required="1">
                    <?php foreach ($perusahaan as $key => $v_perusahaan): ?>
                        <option value="<?php echo $v_perusahaan['kode']; ?>" > <?php echo strtoupper($v_perusahaan['nama']); ?> </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding">
                    <label class="control-label">TANGGAL</label>
                </div>
                <div class="col-xs-12 no-padding">
                    <div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
                        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
                <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
                <div class="col-xs-12 no-padding">
                    <button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left col-xs-12" title="TAMPIL" onclick="st.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
                </div>
            </div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding">
                <small>
                    <table class="table table-bordered" style="margin-bottom: 0px;">
                        <thead>
                            <tr>
                                <td colspan="7"><b>TOTAL</b></td>
                                <td class="text-right hit_total" data-target="sak"><b>0</b></td>
                                <td class="text-right hit_total" data-target="tonase"><b>0</b></td>
                                <td colspan="2"></td>
                                <td class="text-right hit_total" data-target="tot_beli"><b>0</b></td>
                                <td class="text-right hit_total" data-target="tot_oa"><b>0</b></td>
                            </tr>
                            <tr>
                                <th style="width: 5%;">Transaksi</th>
                                <th style="width: 5%;">Tanggal</th>
                                <th style="width: 10%;">Perusahaan</th>
                                <th style="width: 10%;">Plasma</th>
                                <th style="width: 5%;">Periode</th>
                                <th style="width: 5%;">Unit</th>
                                <th style="width: 5%;">Jenis</th>
                                <th style="width: 5%;">Box / Zak</th>
                                <th style="width: 5%;">Ekor / Tonase / Pcs</th>
                                <th style="width: 5%;">Ongkos</th>
                                <th style="width: 5%;">Hrg Beli</th>
                                <th style="width: 5%;">Tot Beli</th>
                                <th style="width: 5%;">Tot OA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="13">Data tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </small>
			</div>
        </div>
    </div>
</div>