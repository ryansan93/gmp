<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Unit</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['unit']); ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">No. SJ</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo $data['no_sj']; ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Tgl Terima</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_terima'], '-', ' ')); ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Asal</label></div>
    <div class="col-lg-9 no-padding">
        <label class="control-label">
            : <?php echo ($data['jenis_kirim'] == 'opkp') ? $data['nama_asal'].' ( KDG : '.(int)substr($data['id_asal'], -2).' )' : $data['nama_asal']; ?>
        </label>
    </div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Tujuan</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo $data['nama_tujuan'].' ( KDG : '.(int)substr($data['noreg_tujuan'], -2).' )'; ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Ekspedisi</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo $data['ekspedisi']; ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">No. Polisi</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo $data['no_polisi']; ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Sopir</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo $data['sopir']; ?></label></div>
</div>
<div class="col-lg-12 no-padding" style="padding-bottom: 5px;">
    <div class="col-lg-3 no-padding"><label class="control-label">Ongkos Angkut (Rp.)</label></div>
    <div class="col-lg-9 no-padding"><label class="control-label">: <?php echo angkaDecimal($data['ongkos_angkut']); ?></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-lg-12 no-padding">
    <div class="col-lg-6 no-padding" style="padding-right: 5px;">
        <button type="button" class="col-lg-12 btn btn-danger" onclick="oap.delete(this)" data-id="<?php echo $data['id']; ?>"> 
            <i class="fa fa-trash" aria-hidden="true"></i> Hapus
        </button>
    </div>
    <div class="col-lg-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-lg-12 btn btn-primary" onclick="oap.changeTabActive(this)" data-href="action" data-id="<?php echo $data['id']; ?>" data-edit="edit"> 
            <i class="fa fa-edit" aria-hidden="true"></i> Edit
        </button>
    </div>
</div>