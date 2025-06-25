<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Unit</label></div>
    <div class="col-lg-12 no-padding">
        <select class="form-control unit" data-required="1">
            <option value="">-- Pilih Unit --</option>
            <?php if ( count($unit) > 0 ): ?>
                <?php foreach ($unit as $k => $val): ?>
                    <?php
                        $selected = null;
                        if ( $val['kode'] == $data['kode_unit'] ) {
                            $selected = 'selected';
                        }
                    ?>
                    <option value="<?php echo $val['kode'] ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
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
            <option value="<?php echo $data['no_sj']; ?>" data-jk="<?php echo $data['jenis_kirim']; ?>" data-namaasal="<?php echo $data['nama_asal']; ?>" data-namatujuan="<?php echo $data['nama_tujuan']; ?>" data-idasal="<?php echo $data['id_asal']; ?>" data-noregtujuan="<?php echo $data['noreg_tujuan']; ?>" data-tglterima="<?php echo $data['tgl_terima']; ?>" data-tglterimatext="<?php echo $data['tgl_terima_text']; ?>" data-ekspedisi="<?php echo $data['ekspedisi']; ?>" data-nopolisi="<?php echo $data['no_polisi']; ?>" data-sopir="<?php echo $data['sopir']; ?>" selected="selected"><?php echo strtoupper($data['jenis_kirim'].' | '.$data['tgl_terima_text'].' | '.$data['no_sj']); ?></option>
        </select>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Tgl Terima</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control tgl_terima" placeholder="Tanggal" value="<?php echo strtoupper(tglIndonesia($data['tgl_terima'], '-', ' ')); ?>" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-right: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Asal</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control asal" placeholder="Asal" value="<?php echo ($data['nama_asal'] == 'opkp') ? $data['nama_asal'].' ( KDG : '.(int)substr($data['id_asal'], -2).' )' : $data['nama_asal']; ?>" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-left: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Tujuan</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control tujuan" placeholder="Tujuan" value="<?php echo $data['nama_tujuan'].' ( KDG : '.(int)substr($data['noreg_tujuan'], -2).' )'; ?>" disabled>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Ekspedisi</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" value="<?php echo $data['ekspedisi']; ?>" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-right: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">No. Polisi</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control no_polisi" placeholder="No. Polisi" value="<?php echo $data['no_polisi']; ?>" disabled>
    </div>
</div>
<div class="col-lg-6 no-padding" style="padding-bottom: 5px; padding-left: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Sopir</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control sopir" placeholder="Sopir" value="<?php echo $data['sopir']; ?>" disabled>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-12 no-padding"><label class="control-label">Ongkos Angkut (Rp.)</label></div>
    <div class="col-lg-12 no-padding">
        <input type="text" class="form-control text-right ongkos_angkut" placeholder="Ongkos Angkut" data-required="1" data-tipe="decimal" maxlength="15" value="<?php echo angkaDecimal($data['ongkos_angkut']); ?>" disabled>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-lg-12 no-padding">
    <div class="col-lg-6 no-padding" style="padding-right: 5px;">
        <button type="button" class="col-lg-12 btn btn-danger" onclick="oap.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action"> 
            <i class="fa fa-times" aria-hidden="true"></i> Batal
        </button>
    </div>
    <div class="col-lg-6 no-padding" style="padding-left: 5px;">
         <button type="button" class="col-lg-12 btn btn-primary" onclick="oap.edit(this)" data-id="<?php echo $data['id']; ?>"> 
            <i class="fa fa-save" aria-hidden="true"></i> Simpan Perubahan
        </button>
    </div>
</div>