<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Unit</label></div>
    <div class="col-lg-12 no-padding">
        <select class="form-control unit" data-required="1">
            <option value="">-- Pilih Unit --</option>
            <?php if ( count($unit) > 0 ): ?>
                <?php foreach ($unit as $k => $val): ?>
                    <option value="<?php echo $val['kode'] ?>"><?php echo strtoupper($val['nama']); ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">No. SJ</label></div>
    <div class="col-lg-12 no-padding">
        <select class="form-control no_sj" data-required="1" disabled>
            <option value="">-- Pilih No. SJ --</option>
        </select>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Tgl Terima</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control tgl_terima" placeholder="Tanggal" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-right: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Asal</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control asal" placeholder="Asal" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-left: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Tujuan</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control tujuan" placeholder="Tujuan" disabled>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Ekspedisi</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-right: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">No. Polisi</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control no_polisi" placeholder="No. Polisi" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-left: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Sopir</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control sopir" placeholder="Sopir" disabled>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Ongkos Angkut (Rp.)</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control text-right ongkos_angkut" placeholder="Ongkos Angkut" data-required="1" data-tipe="decimal" maxlength="15" disabled>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-lg-12 no-padding">
    <button type="button" class="col-lg-12 btn btn-primary" onclick="oap.save()"> 
        <i class="fa fa-save" aria-hidden="true"></i> Simpan
    </button>
</div>