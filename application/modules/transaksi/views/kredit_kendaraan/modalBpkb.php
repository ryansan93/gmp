<div class="modal-header">
    <h4 class="modal-title">BPKB</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="panel-body no-padding">
        <?php
            $hide_div_not_view = null;
            $hide_div_dokumen_not_view = 'hide';
            $hide_div_view = 'hide';

            if ( !empty($data['no_bpkb']) ) {
                $hide_div_not_view = 'hide';
                $hide_div_dokumen_not_view = null;
                $hide_div_view = null;
            }
        ?>

        <div class="col-xs-12 no-padding not-view <?php echo $hide_div_not_view; ?>" style="margin-bottom: 10px;">
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <div class="col-xs-12 no-padding"><label class="control-label">No. BPKB</label></div>
                <div class="col-xs-12 no-padding">
                    <input type="text" class="form-control no_bpkb" placeholder="No. BPKB" data-required="1" value="<?php echo $data['no_bpkb']; ?>">
                </div>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding"><label class="control-label">Lampiran</label></div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-12 no-padding attachment">
                        <a name="dokumen" class="text-right <?php echo $hide_div_dokumen_not_view; ?>" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $data['bpkb']; ?>">
                            <?php echo $data['bpkb']; ?>
                        </a>
                        <label class="control-label" style="margin-bottom: 0px;">
                            <input style="display: none;" class="file_lampiran no-check lampiran_angsuran1" type="file" data-name="name" data-required="1" onchange="kk.showNameFile(this, 1)" data-key="bpkb" />
                            <i class="fa fa-paperclip cursor-p text-center" title="Lampiran" style="font-size: 20px;"></i> 
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 10px;"></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <button type="button" class="btn btn-primary" onclick="kk.saveBpkb(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </div>
        <div class="col-xs-12 no-padding view <?php echo $hide_div_view; ?>" style="margin-bottom: 10px;">
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <div class="col-xs-2 no-padding"><label class="control-label">No. BPKB</label></div>
                <div class="col-xs-10 no-padding">
                    : <?php echo $data['no_bpkb']; ?>
                </div>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-2 no-padding"><label class="control-label">Lampiran</label></div>
                <div class="col-xs-10 no-padding">
                    : <a name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $data['bpkb']; ?>"><?php echo $data['bpkb']; ?></a>
                </div>
            </div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 10px;"></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <button type="button" class="btn btn-primary" onclick="$('div.view').addClass('hide'); $('div.not-view').removeClass('hide'); $('div.not-view').find('input[type=file]').removeAttr('data-required')"><i class="fa fa-edit"></i> Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>