<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Perusahaan</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control perusahaan" data-required="1">
                    <option value="">-- Pilih Perusahaan --</option>
                    <?php foreach( $perusahaan as $key => $value ) : ?>
                        <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Unit</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control unit" data-required="1">
                    <option value="">-- Pilih Unit --</option>
                    <?php foreach( $unit as $key => $value ) : ?>
                        <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Jangka Waktu (Bulan)</label></div>
            <div class="col-xs-12 no-padding">
                <input type="text" class="form-control text-right jangka_waktu" data-required="1" placeholder="Jangka Waktu" data-tipe="integer">
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Mulai</label></div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date" id="StartDate" name="startDate">
                    <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Akhir</label></div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date" id="EndDate" name="endDate">
                    <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Nominal (Rp.)</label></div>
            <div class="col-xs-12 no-padding">
                <input type="text" class="form-control text-right nominal" data-required="1" placeholder="Nominal" data-tipe="integer">
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="sk.save()"><i class="fa fa-save"></i> Simpan</button>
    </div>
</div>