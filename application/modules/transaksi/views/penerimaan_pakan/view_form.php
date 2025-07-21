<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. BBM</div>
        <div class="col-lg-6">
            <label class="control-label">: <?php echo $data['no_bbm']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. SJ</div>
        <div class="col-lg-6">
            <label class="control-label">: <?php echo $data_kp[0]['no_sj']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Jenis Pengiriman</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo strtoupper($data_kp[0]['jenis_kirim']); ?></label>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. Order</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo $data_kp[0]['no_order']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Tgl Kirim</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo strtoupper(tglIndonesia($data_kp[0]['tgl_kirim'], '-', ' ', true)); ?></label>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Tgl Tiba</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_terima'], '-', ' ', true)); ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Asal</div>
        <div class="col-lg-9">
            <label class="control-label">: <?php echo $asal; ?></label>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Tujuan</div>
        <div class="col-lg-9">
            <label class="control-label">: <?php echo $tujuan; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center no-padding">
        <div class="col-lg-12">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. Polisi</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo $data_kp[0]['no_polisi']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Ekspedisi</div>
        <div class="col-lg-8">
            <label class="control-label">: <?php echo $data_kp[0]['ekspedisi']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Sopir</div>
        <div class="col-lg-4">
            <label class="control-label">: <?php echo $data_kp[0]['sopir']; ?></label>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center no-padding">
        <div class="col-lg-12">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center">
        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
            <thead>
                <tr>
                    <th class="col-lg-2 text-center" rowspan="2">Jenis Pakan</th>
                    <th class="col-lg-2 text-center" colspan="2">Kirim</th>
                    <th class="col-lg-2 text-center" colspan="2">Terima</th>
                </tr>
                <tr>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Kondisi</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['detail'] as $k_det => $v_det): ?>
                    <?php
                        $jml_kirim = 0;
                        $kondisi_kirim = '';
                        foreach ($data_kp as $kp_det => $vp_det) {
                            if ( $vp_det['item'] == $v_det['item'] ) {
                                $jml_kirim = $vp_det['jumlah'];
                                $kondisi_kirim = $vp_det['kondisi'];
                            }
                        }
                    ?>
                    <tr>
                        <td class="barang" data-kode="<?php echo $v_det['d_barang']['kode'] ?>"><?php echo $v_det['d_barang']['nama'] ?></td>
                        <td class="text-right"><?php echo angkaRibuan($jml_kirim) ?></td>
                        <td><?php echo $kondisi_kirim ?></td>
                        <td class="text-right"><?php echo angkaRibuan($v_det['jumlah']) ?></td>
                        <td><?php echo $v_det['kondisi'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ( $retur == 0 && $pp == 0 ): ?>
    <div class="form-group d-flex align-items-center">
        <div class="col-lg-12 d-flex align-items-center no-padding">
            <div class="col-lg-12">
                <hr style="margin-top: 10px; margin-bottom: 10px;">
            </div>
        </div>
    </div>
    <div class="form-group lock_btn_fiskal" data-date="<?php echo $data['tgl_terima']; ?>">
        <div class="col-lg-12">
            <?php if ( $akses['a_edit'] == 1 ): ?>
                <button type="button" data-href="penerimaan" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pp.changeTabActive(this)" style="margin-left: 10px;" data-id="<?php echo $data['id']; ?>" data-edit="edit"> 
                    <i class="fa fa-edit" aria-hidden="true"></i> Edit
                </button>
            <?php endif ?>
            <?php if ( $akses['a_delete'] == 1 ): ?>
                <button type="button" data-href="penerimaan" class="btn btn-danger cursor-p pull-right" title="DELETE" onclick="pp.delete(this)" style="margin-left: 10px;" data-id="<?php echo $data['id']; ?>"> 
                    <i class="fa fa-trash" aria-hidden="true"></i> Hapus
                </button>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>