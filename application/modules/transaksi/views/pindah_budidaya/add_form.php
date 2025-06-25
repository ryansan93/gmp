<div class="panel-body" style="padding-top: 0px;">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-sm-6 no-padding" style="padding-right: 5px;">
                        <div class="panel panel_asal" style="margin-bottom: 0px;">
                            <fieldset>
                                <legend>Asal</legend>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Tgl Docin</label>
                                    </div>
                                    <div class="col-md-6 no-padding">
                                        <div class="input-group date" id="tgl_docin" name="tgl_docin">
                                            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Unit</label>
                                    </div>
                                    <div class="col-md-5 no-padding">
                                        <select class="form-control unit" data-required="1" onchange="pb.get_data_asal(this)">
                                            <option value="">Pilih Unit</option>
                                            <?php if ( !empty($unit) ): ?>
                                                <?php foreach ($unit as $k_unit => $v_unit): ?>
                                                    <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                                                <?php endforeach ?>
                                            <?php endif ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Plasma</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control mitra" data-required="1">
                                            <option value="">Pilih Plasma</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Populasi</label>
                                    </div>
                                    <div class="col-md-5 no-padding">
                                        <input type="text" class="form-control text-right" name="populasi" placeholder="Populasi" data-tipe="integer" data-required="1" readonly>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="col-sm-6 no-padding" style="padding-left: 5px;">
                        <div class="panel panel_tujuan" style="margin-bottom: 0px;">
                            <fieldset>
                                <legend>Tujuan</legend>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Tgl Pindah</label>
                                    </div>
                                    <div class="col-md-6 no-padding">
                                        <div class="input-group date" id="tgl_pindah" name="tgl_pindah">
                                            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Unit</label>
                                    </div>
                                    <div class="col-md-5 no-padding">
                                        <select class="form-control unit" data-required="1" onchange="pb.get_data_tujuan(this)">
                                            <option value="">Pilih Unit</option>
                                            <?php if ( !empty($unit) ): ?>
                                                <?php foreach ($unit as $k_unit => $v_unit): ?>
                                                    <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                                                <?php endforeach ?>
                                            <?php endif ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Plasma</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control mitra" data-required="1">
                                            <option value="">Pilih Plasma</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Kandang</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control kandang" data-required="1">
                                            <option value="">Pilih Kandang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Populasi</label>
                                    </div>
                                    <div class="col-md-5 no-padding">
                                        <input type="text" class="form-control text-right" name="populasi" placeholder="Populasi" data-tipe="integer" data-required="1">
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">KaNit</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control kanit" data-required="1" readonly>
                                            <option value="">Pilih Kepala Unit</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">PPL</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control ppl" data-required="1" readonly>
                                            <option value="">Pilih PPL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Marketing</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control marketing" data-required="1" readonly>
                                            <option value="">Pilih Marketing</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">KoAr</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control koar" data-required="1" readonly>
                                            <option value="">Pilih Koordinator Area</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12" style="padding-bottom: 5px;">
                                    <div class="col-md-3 no-padding">
                                        <label class="control-label">Kontrak</label>
                                    </div>
                                    <div class="col-md-9 no-padding">
                                        <select class="form-control kontrak" data-required="1" readonly>
                                            <option value="">Pilih Kontrak</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-12 no-padding">
            <hr style="margin-top: 5px; margin-bottom: 5px;">
        </div>
        <div class="col-md-12 no-padding">
    		<button type="button" class="btn btn-primary pull-right" onclick="pb.save(this);"><i class="fa fa-save"></i> Simpan</button>
    	</div>
    </div>
</div>