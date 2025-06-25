<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Kode</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper($data['kode']); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Tanggal</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ', true)); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Plasma</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper($data['nama_mitra']); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Perusahaan</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper($data['nama_perusahaan']); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Asal Piutang</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo ($data['tf_bank'] == 1) ? 'BANK' : 'NON BANK'; ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Nominal (Rp.)</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper(angkaDecimal($data['nominal'])); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-2 no-padding"><label class="label-control">Keterangan</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: <?php echo strtoupper($data['keterangan']); ?></label>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-2 no-padding"><label class="label-control">Lampiran</label></div>
        <div class="col-xs-10 no-padding">
            <label class="label-control">: 
                <?php if ( !empty($data['path']) ) { ?>
                    <a href="uploads/<?php echo $data['path']; ?>" target="_blank"><?php echo strtoupper($data['path']); ?></a>
                <?php } else { ?>
                    -
                <?php } ?>
            </label>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-xs-12 no-padding">
        <?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1 ) { ?>
            <?php if ( $akses['a_edit'] == 1 ) { ?>
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <button type="button" class="col-xs-12 btn btn-danger" data-id="<?php echo $data['id']; ?>" onclick="pm.delete(this)"><i class="fa fa-trash"></i> Hapus</button>
                </div>
            <?php } ?>
            <?php if ( $akses['a_delete'] == 1 ) { ?>
                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                    <button type="button" class="col-xs-12 btn btn-primary" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="action" onclick="pm.changeTabActive(this)"><i class="fa fa-edit"></i> Edit</button>
                </div>
            <?php } ?>
            <!-- <div class="col-xs-12 no-padding" style="padding-top: 5px;">
                <button type="button" class="col-xs-12 btn btn-default" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="pindah" onclick="pm.pindahPerusahaanForm(this)"><i class="fa fa-edit"></i> Pindah Perusahaan</button>
            </div> -->
        <?php } ?>
    </div>
</div>