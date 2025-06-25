<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Kode</label></div>
        <div class="col-xs-12 no-padding">
            <input type="text" class="form-control text-center kode" placeholder="Kode" value="<?php echo $data['kode']; ?>" disabled>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tanggal Bayar</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" >
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Karyawan</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control karyawan" data-required="1">
                    <option>-- Pilih Karyawan --</option>
                    <?php foreach( $karyawan as $key => $value ) : ?>
                        <?php
                            $selected = null;
                            if ( $value['nik'] == $data['karyawan'] ) {
                                $selected = 'selected';
                            }
                        ?>
                        <option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nik'].' | '.$value['nama'].' ('.$value['jabatan'].')'); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Perusahaan</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control perusahaan" data-required="1">
                    <option>-- Pilih Perusahaan --</option>
                    <?php foreach( $perusahaan as $key => $value ) : ?>
                        <?php
                            $selected = null;
                            if ( $value['nomor'] == $data['perusahaan'] ) {
                                $selected = 'selected';
                            }
                        ?>
                        <option value="<?php echo $value['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Kode Piutang</label></div>
        <div class="col-xs-12 no-padding">
            <select class="form-control piutang_kode" data-required="1" data-sisapiutang="<?php echo $data['sisa_piutang']; ?>" data-kode="<?php echo $data['piutang_kode']; ?>">
                <option value="">-- Pilih Kode Piutang --</option>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Sisa Piutang (Rp.)</label></div>
            <div class="col-xs-12 no-padding">
                <input type="text" class="form-control text-right sisa_piutang" data-tipe="decimal" placeholder="Sisa Piutang (Rp.)" data-required="1" disabled="disabled" value="<?php echo angkaDecimal($data['sisa_piutang']); ?>">
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Nominal Bayar (Rp.)</label></div>
            <div class="col-xs-12 no-padding">
                <input type="text" class="form-control text-right nominal" data-tipe="decimal" placeholder="Nominal (Rp.)" data-required="1" value="<?php echo angkaDecimal($data['nominal']); ?>">
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Jenis Bayar</label></div>
        <div class="col-xs-12 no-padding">
            <select class="form-control jns_bayar" data-required="1">
                <option value="gaji" <?php echo ($data['jns_bayar'] == 'gaji') ? 'selected' : null; ?>>GAJI</option>
                <option value="non_gaji" <?php echo ($data['jns_bayar'] == 'non_gaji') ? 'selected' : null; ?>>NON GAJI</option>
                <option value="bonus" <?php echo ($data['jns_bayar'] == 'bonus') ? 'selected' : null; ?>>BONUS</option>
                <!-- <option value="perusahaan" <?php echo ($data['jns_bayar'] == 'perusahaan') ? 'selected' : null; ?>>PERUSAHAAN</option> -->
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Keterangan</label></div>
        <div class="col-xs-12 no-padding">
            <textarea class="form-control keterangan" data-required="1" placeholder="Keterangan"><?php echo $data['keterangan']; ?></textarea>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding"><label class="label-control">Lampiran</label></div>
        <div class="col-xs-12 no-padding attachment" style="padding-right: 5px;">
            <label class="cursor-p" style="padding-right: 10px;">
                <input style="display: none;" class="file_lampiran no-check" type="file" data-name="name" onchange="showNameFile(this)" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" />
                <i class="fa fa-paperclip cursor-p" title="Lampiran"></i> 
            </label>
            <a name="dokumen" class="text-right cursor-p" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $data['path']; ?>"><?php echo strtoupper($data['path']); ?></a>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <button type="button" class="col-xs-12 btn btn-danger" data-id="<?php echo $data['id']; ?>" data-edit="" data-href="action" onclick="ppk.changeTabActive(this)"><i class="fa fa-times"></i> Batal</button>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <button type="button" class="col-xs-12 btn btn-primary" data-id="<?php echo $data['id']; ?>" onclick="ppk.edit(this)"><i class="fa fa-save"></i> Simpan Perubahan</button>
        </div>
    </div>
</div>