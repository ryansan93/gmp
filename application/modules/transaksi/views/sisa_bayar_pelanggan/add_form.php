<div class="panel-body" style="padding-top: 0px;">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-md-2 no-padding">
                        <label class="control-label">Pelanggan</label>
                    </div>
                    <div class="col-md-4 no-padding">
                        <select class="form-control pelanggan" data-live-search="true" data-required="1">
                            <option value="">Pilih Pelanggan</option>
                            <?php if ( count($pelanggan) > 0 ): ?>
                                <?php foreach ($pelanggan as $k_plg => $v_plg): ?>
                                    <option data-tokens="<?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['nama_unit']).')'; ?>" value="<?php echo $v_plg['nomor']; ?>"><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['nama_unit']).')'; ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary" onclick="sbp.get_saldo(this)"><i class="fa fa-search"></i> Ambil Saldo</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-2 no-padding">
                        <label class="control-label">Perusahaan</label>
                    </div>
                    <div class="col-md-4 no-padding">
                        <select class="form-control perusahaan" data-live-search="true" data-required="1">
                            <option value="">Pilih Perusahaan</option>
                            <?php if ( count($perusahaan) > 0 ): ?>
                                <?php foreach ($perusahaan as $k_prs => $v_prs): ?>
                                    <option data-tokens="<?php echo strtoupper($v_prs['nama']); ?>" value="<?php echo $v_prs['kode']; ?>"><?php echo strtoupper($v_prs['nama']); ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Sisa Saldo</label>
                	</div>
                	<div class="col-md-3 no-padding">
                		<input type="text" class="form-control sisa_saldo text-right" data-tipe="decimal" placeholder="Sisa Saldo Pelanggan" disabled>
                	</div>
                </div>
            </form>
        </div>
        <div class="col-md-12 no-padding">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
        <div class="col-md-12 no-padding">
    		<button type="button" class="btn btn-primary pull-right" onclick="sbp.save(this);"><i class="fa fa-save"></i> Simpan</button>
    	</div>
    </div>
</div>