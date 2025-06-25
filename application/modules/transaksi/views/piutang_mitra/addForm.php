<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Kode</label></div>
        <div class="col-xs-12 no-padding">
            <input type="text" class="form-control text-center kode" data-tipe="decimal" placeholder="Kode" disabled>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tanggal</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <div class="col-xs-12 no-padding"><label class="label-control">Plasma</label></div>
            <div class="col-xs-12 no-padding">
                <select class="form-control mitra" data-required="1">
                    <option>-- Pilih Plasma --</option>
                    <?php foreach( $mitra as $key => $value ) : ?>
                        <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama'].' ('.$value['kode_perusahaan'].')'); ?></option>
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
                        <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Asal Piutang</label></div>
        <div class="col-xs-12 no-padding">
            <select class="form-control tf_bank" data-required="1">
                <option>-- Pilih Asal Piutang --</option>
                <option value="1"><?php echo 'BANK'; ?></option>
                <option value="0"><?php echo 'NON BANK'; ?></option>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Nominal (Rp.)</label></div>
        <div class="col-xs-12 no-padding">
            <input type="text" class="form-control text-right nominal" data-tipe="decimal" placeholder="Nominal (Rp.)" data-required="1">
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="label-control">Keterangan</label></div>
        <div class="col-xs-12 no-padding">
            <textarea class="form-control keterangan" data-required="1" placeholder="Keterangan"></textarea>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding"><label class="label-control">Lampiran</label></div>
        <div class="col-xs-12 no-padding attachment" style="padding-right: 5px;">
            <label class="cursor-p" style="padding-right: 10px;">
                <input style="display: none;" class="file_lampiran no-check" type="file" data-name="name" onchange="showNameFile(this)" data-allowtypes="doc|DOC|pdf|PDF|docx|DOCX|jpg|JPG|jpeg|JPEG|png|PNG" />
                <i class="fa fa-paperclip cursor-p" title="Lampiran"></i> 
            </label>
            <a name="dokumen" class="text-right cursor-p hide" target="_blank" style="padding-right: 10px;"></a>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="pm.save()"><i class="fa fa-save"></i> Simpan</button>
    </div>
</div>