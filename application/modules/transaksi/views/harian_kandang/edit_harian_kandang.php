<div class="panel-body">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-sm-1" >
                        <label class="control-label">Periode</label>
                    </div>
                    <div class="col-sm-3">
                        <select class="form-control small " name="periode" onchange="Hk.getNoregMitraByRdim(this)">
                            <option value="">-- pilih periode --</option>
                            <?php foreach ($periodes as $periode): ?>
                                <?php
                                    $selected = '';
                                    $periode_val = tglIndonesia($periode->mulai, '-', ' ') . ' s.d ' . tglIndonesia($periode->selesai, '-', ' ');
                                    $periode_edit = tglIndonesia($rdim['mulai'], '-', ' ') . ' s.d ' . tglIndonesia($rdim['selesai'], '-', ' ');
                                    if ( $periode_edit == $periode_val ) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?php echo $periode->id ?>" <?php echo $selected; ?> ><?php echo tglIndonesia($periode->mulai, '-', ' ') . ' s.d ' . tglIndonesia($periode->selesai, '-', ' ') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <label class="control-label">Noreg</label>
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control small" name="noreg" data-idrdimsubmit="<?php echo $data['d_rdim_submit']['id']; ?>">
                            <option value="">-- pilih periode --</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <label class="control-label">Mitra</label>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nama-mitra" value="" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <label class="control-label">Populasi</label>
                    </div>
                    <div class="col-sm-1">
                        <input type="text" class="form-control text-right" name="populasi" value="" data-tipe="integer" readonly>
                    </div>
                </div>
                <hr>
                <div class="row new-line">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <label class="control-label">Tanggal timbang</label>
                            </div>
                            <div class="col-sm-5">
                                <!-- <div class="input-group">
                                    <input value="" type="text" class="form-control text-center date" placeholder="Start Date" name="timbangDate" data-tipe="date" readonly data-required="1">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div> -->
                                <div class="input-group date" id="tgl_timbang" data-tgl="<?php echo $data['tgl_timbang']; ?>">
                                    <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal Timbang" value="<?php echo tglIndonesia($data['tgl_timbang'], '-', ' '); ?>" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <label class="control-label">Umur</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control text-right" name="umur" data-tipe="integer" value="<?php echo angkaRibuan($data['umur']); ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <label class="control-label">Jumlah Kematian</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control text-right" name="jml-kematian" data-tipe="integer" value="<?php echo angkaRibuan($data['mati']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <label class="control-label">BB Rata2</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control text-right" name="bb-average" data-tipe="decimal" value="<?php echo angkaDecimal($data['bb']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <label class="control-label">Terima Pakan</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control text-right" name="terima-pakan" data-tipe="integer" value="<?php echo angkaRibuan($data['terima_pakan']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <label class="control-label">Sisa Pakan di Kandang</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control text-right" name="sisa-pakan" data-tipe="integer" value="<?php echo angkaRibuan($data['sisa_pakan']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <label class="control-label">Komentar PIC</label>
                            </div>
                            <div class="col-sm-8">
                                <textarea name="komentar" rows="2" class="form-control"><?php echo $data['ket']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row new-line">
                    <div class="col-sm-4">
                        <table id="tb_sekat" class="table table-hover table-bordered custom_table table-form small">
                            <thead>
                                <tr>
                                    <th class="col-sm-1">Jml sekat</th>
                                    <th class="col-sm-1">BB</th>
                                    <th class="col-sm-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detail'] as $k_detail => $v_detail): ?>
                                    <tr>
                                        <td><input class="form-control text-right" type="text" name="sekat" data-tipe="integer" value="<?php echo $v_detail['jml_sekat']; ?>"></td>
                                        <td><input class="form-control text-right" type="text" name="bb" data-tipe="decimal" value="<?php echo angkaDecimal($v_detail['bb']); ?>"></td>
                                        <td class="text-center action">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="Hk.removeRowTable(this)"> <i class="fa fa-minus"></i> </button>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="Hk.addRowTable(this)"> <i class="fa fa-plus"></i> </button>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary pull-right edit" data-id="<?php echo $data['id']; ?>" onclick="Hk.edit()"> <i class="fa fa-edit"></i> | Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>