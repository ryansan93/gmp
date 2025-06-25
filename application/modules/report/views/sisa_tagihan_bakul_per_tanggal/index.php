<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding">
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <select class="pelanggan" name="pelanggan[]" multiple="multiple" width="100%" data-required="1">
                    <option value="all">All</option>
                    <?php if ( !empty($pelanggan) ): ?>
                        <?php foreach ($pelanggan as $k_plg => $v_plg): ?>
                            <option value="<?php echo $v_plg['nomor'] ?>"><?php echo strtoupper($v_plg['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_plg['nama_unit'])).')'; ?></option>
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
                <div class="col-xs-12 no-padding" style="padding-right: 10px;">
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
                                <td class="text-right hit_total" data-target="total"><b>0</b></td>
                                <td class="text-right hit_total" data-target="bayar"><b>0</b></td>
                                <td class="text-right hit_total" data-target="sisa"><b>0</b></td>
                            </tr>
                            <tr>
                                <th class="col-xs-1">Perusahaan</th>
                                <th class="col-xs-2">Bakul</th>
                                <th class="col-xs-2">Plasma</th>
                                <th class="col-xs-1">Tgl Tutup Siklus</th>
                                <th class="col-xs-1">Tanggal</th>
                                <th class="col-xs-1">No. DO</th>
                                <th class="col-xs-1">No. Nota</th>
                                <th class="col-xs-1">Total</th>
                                <th class="col-xs-1">Bayar</th>
                                <th class="col-xs-1">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10">Data tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </small>
			</div>
        </div>
    </div>
</div>