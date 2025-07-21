<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding filter">
        <div class="col-xs-3 text-left">Filter SJ</div>
        <div class="col-xs-3">
            <select class="form-control unit" data-required="1">
                <option value="">-- Pilih Unit --</option>
                <?php if ( count($unit) > 0 ): ?>
                    <?php foreach ($unit as $k => $val): ?>
                        <?php 
                            $true = false;
                            if ( stristr($data_kp[0]['no_order'], $val['kode']) !== FALSE ) { 
                                $true = true;
                            }
                        ?>
                        <option value="<?php echo $val['kode'] ?>" <?php echo ($true) ? 'selected' : null; ?> ><?php echo strtoupper($val['nama']); ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
        <div class="col-xs-3" style="padding-left: 0px;">
            <div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
                <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" data-tgl="<?php echo $data_kp[0]['tgl_kirim']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div class="col-xs-2" style="padding-left: 0px;">
            <button type="button" class="btn btn-primary get_sj_not_terima" onclick="pp.get_sj_not_terima(this)">Ambil SJ</button>
        </div>
    </div>
</div>
<hr style="margin-top: 10px; margin-bottom: 10px;">
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">No. BBM</div>
        <div class="col-xs-4">
            <input type="text" class="form-control no_bbm" placeholder="No. BBM" value="<?php echo $data['no_bbm'] ?>" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">No. SJ</div>
        <div class="col-xs-6">
            <select class="form-control no_sj" data-required="1" onchange="pp.get_data_by_sj(this)" data-id="<?php echo $data_kp[0]['id']; ?>" data-nosj="<?php echo $data_kp[0]['no_sj']; ?>">
                <option value="">-- Pilih No. SJ --</option>
                <!-- <?php if ( count($get_sj_not_terima) > 0 ): ?>
                	<?php foreach ($get_sj_not_terima as $k => $val): ?>
                        <?php
                            $selected = null;
                            if ( $val['id'] == $data_kp[0]['id'] ) {
                                $selected = 'selected';
                            }
                        ?>
                		<option value="<?php echo $val['id'] ?>" ><?php echo $val['no_sj']; ?></option>
                	<?php endforeach ?>
                <?php endif ?>
                <option value="<?php echo $data_kp[0]['id'] ?>" selected ><?php echo $data_kp[0]['no_sj']; ?></option> -->
            </select>
        </div>
        <!-- <div class="col-xs-2" style="padding-top: 2px;">
            <a name="dokumen" class="text-right sj" href="<?php echo 'uploads/'.$data['path']; ?>" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
            <label class="" style="margin-bottom: 0px;">
                <input style="display: none;" placeholder="Dokumen" class="file_lampiran_sj no-check" type="file" onchange="pp.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx|jpg|jpeg|png">
                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
            </label>
        </div> -->
    </div>
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-2 text-left">No. Polisi</div>
        <div class="col-xs-4">
            <input type="text" class="form-control no_pol" placeholder="No. Polisi" data-required="1" value="<?php echo $data_kp[0]['no_polisi'] ?>" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">Ekspedisi</div>
        <div class="col-xs-8">
            <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1" value="<?php echo $data_kp[0]['ekspedisi'] ?>" readonly>
        </div>
    </div>
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-2 text-left">Sopir</div>
        <div class="col-xs-4">
            <input type="text" class="form-control sopir" placeholder="Sopir" data-required="1" value="<?php echo $data_kp[0]['sopir'] ?>" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">Jenis Pengiriman</div>
        <div class="col-xs-4">
            <input type="text" class="form-control jenis_kirim" placeholder="Jenis" data-required="1" value="<?php echo $data_kp[0]['jenis_kirim'] ?>" readonly>
        </div>
    </div>
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-2 text-left">No. Order</div>
        <div class="col-xs-4">
            <input type="text" class="form-control no_order" placeholder="No. Order" data-required="1" value="<?php echo $data_kp[0]['no_order'] ?>" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">Tgl Kirim</div>
        <div class="col-xs-4">
            <input type="text" class="form-control tgl_kirim" placeholder="Tanggal" data-required="1" value="<?php echo tglIndonesia($data_kp[0]['tgl_kirim'], '-', ' ') ?>" readonly>
        </div>
    </div>
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-2 text-left">Tgl Tiba</div>
        <div class="col-xs-4">
            <div class="input-group date datetimepicker" name="tgl_terima" id="tgl_terima">
		        <input type="text" class="form-control text-center" placeholder="Tanggal Terima" data-required="1" data-tgl="<?php echo $data['tgl_terima']; ?>" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-3 text-left">Asal</div>
        <div class="col-xs-6">
            <input type="text" class="form-control asal" placeholder="Asal" data-required="1" value="<?php echo $asal ?>" readonly>
        </div>
    </div>
    <div class="col-xs-6 d-flex align-items-center no-padding">
        <div class="col-xs-2 text-left">Tujuan</div>
        <div class="col-xs-6">
            <input type="text" class="form-control tujuan" placeholder="Tujuan" data-required="1" value="<?php echo $tujuan ?>" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-xs-12 d-flex align-items-center">
        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
            <thead>
                <tr>
                    <th class="col-xs-2 text-center" rowspan="2">Jenis Pakan</th>
                    <th class="col-xs-2 text-center" colspan="2">Kirim</th>
                    <th class="col-xs-2 text-center" colspan="2">Terima</th>
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
                        <td class="text-center"><?php echo $kondisi_kirim ?></td>
                        <td><input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($v_det['jumlah']) ?>"></td>
                        <td><input type="text" class="form-control kondisi" placeholder="Kondisi" value="<?php echo $v_det['kondisi'] ?>" data-required="1"></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<div class="form-group">
    <div class="col-xs-12">
        <div class="col-xs-12 no-padding">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
        <div class="col-xs-12 no-padding">
            <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pp.edit_terima_pakan(this)" data-id="<?php echo $data['id']; ?>"> 
                <i class="fa fa-edit" aria-hidden="true"></i> Update
            </button>
        </div>
    </div>
</div>