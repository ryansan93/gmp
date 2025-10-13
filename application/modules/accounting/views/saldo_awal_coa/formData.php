<?php
    $hide_submit = '';
    $hide_update_delete = 'hide';
    $disabled = 'null';
    if ( !empty($data) ) {
        $hide_submit = 'hide';
        $hide_update_delete = '';
        $disabled = 'disabled';
    }
?>

<div class="col-xs-12 no-padding">
    <small>
        <table class="table table-bordered" style="margin-bottom: 0px;">
            <thead>
                <tr>
                    <th class="col-xs-2">No. COA</th>
                    <th class="col-xs-5">Nama COA</th>
                    <th class="col-xs-2">Unit</th>
                    <th class="col-xs-2">Nominal</th>
                    <th class="col-xs-1">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) ) { ?>
                    <?php foreach ($data as $k_data => $v_data) { ?>
                        <?php
                            $edit = 'edit';
                            $_unit = null;
                            foreach ($coa as $k_coa => $v_coa) {
                                if ( $v_coa['no_coa'] == $v_data['no_coa'] ) {
                                    if ( !empty($v_coa['unit']) ) {
                                        $edit = null;
                                        $_unit = $v_coa['unit'];
                                    }
                                }
                            }
                        ?>

                        <tr>
                            <td><input type="text" class="form-control no_coa" value="<?php echo $v_data['no_coa']; ?>" disabled></td>
                            <td><input type="text" class="form-control nama_coa" value="<?php echo $v_data['nama_coa']; ?>" disabled></td>
                            <td>
                                <?php // cetak_r( $_unit ); ?>
                                <select class="form-control unit <?php echo $edit; ?>" <?php echo (!empty($_unit)) ? 'disabled' : null; ?> >
                                    <?php foreach ($unit as $k_unit => $v_unit) { ?>
                                        <?php
                                            $selected = '';
                                            if ( $v_unit['kode'] == $_unit || $v_unit['kode'] == $v_data['unit'] ) {
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['nama']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="text" class="form-control nominal text-right edit" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($v_data['debet']); ?>" <?php echo $disabled; ?>></td>
                            <td>
                                <?php if ( empty($_unit) ) { ?>
                                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                        <button type="button" class="btn btn-primary col-xs-12 <?php echo $edit; ?>" onclick="sa.addRow(this)" disabled><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                        <button type="button" class="btn btn-danger col-xs-12 <?php echo $edit; ?>" onclick="sa.removeRow(this)" disabled><i class="fa fa-minus"></i></button>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <?php foreach ($coa as $k_coa => $v_coa) { ?>
                        <tr>
                            <td><input type="text" class="form-control no_coa" value="<?php echo $v_coa['no_coa']; ?>" disabled></td>
                            <td><input type="text" class="form-control nama_coa" value="<?php echo $v_coa['nama_coa']; ?>" disabled></td>
                            <td>
                                <?php $edit = ( empty($v_coa['unit']) ) ? 'edit' : null; ?>
                                <?php $sel_disabled = ( !empty($v_coa['unit']) ) ? 'disabled' : null; ?>
                                <select class="form-control unit <?php echo $edit; ?>" <?php echo $sel_disabled; ?>>
                                    <?php foreach ($unit as $k_unit => $v_unit) { ?>
                                        <?php
                                            $selected = '';
                                            if ( $v_unit['kode'] == $v_coa['unit'] ) {
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['nama']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="text" class="form-control nominal text-right edit" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal(0); ?>" <?php echo $disabled; ?>></td>
                            <td>
                                <?php if ( empty($v_coa['unit']) ) { ?>
                                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                        <button type="button" class="btn btn-primary col-xs-12 edit" onclick="sa.addRow(this)"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                        <button type="button" class="btn btn-danger col-xs-12 edit" onclick="sa.removeRow(this)"><i class="fa fa-minus"></i></button>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </small>
</div>
<div class="col-xs-12 no-padding"><hr></div>
<div class="col-xs-12 no-padding submit <?php echo $hide_submit; ?>">
    <?php if ( $akses['a_submit'] == 1 ) { ?>
        <button type="button" class="btn btn-primary pull-right" onclick="sa.save()"><i class="fa fa-save"></i> Simpan</button>
    <?php } ?>
</div>
<div class="col-xs-12 no-padding update_delete <?php echo $hide_update_delete; ?>">
    <?php if ( $akses['a_delete'] == 1 ) { ?>
        <button type="button" class="btn btn-danger pull-right" onclick="sa.delete(this)" data-kode="<?php echo $data[0]['periode']; ?>" style="margin-left: 5px;">
            <i class="fa fa-trash"></i>
            Hapus
        </button>
    <?php } ?>
    <?php if ( $akses['a_edit'] == 1 ) { ?>
        <button type="button" class="btn btn-primary pull-right" onclick="sa.editForm()" style="margin-left: 5px;">
            <i class="fa fa-edit"></i>
            Edit
        </button>
    <?php } ?>
</div>
<div class="col-xs-12 no-padding edit hide">
    <button type="button" class="btn btn-primary pull-right" onclick="sa.edit(this)" data-kode="<?php echo $data[0]['periode']; ?>" style="margin-left: 5px;">
        <i class="fa fa-save"></i>
        Update
    </button>
    <button type="button" class="btn btn-danger pull-right" onclick="sa.batalEditForm()" style="margin-right: 5px;">
        <i class="fa fa-times"></i>
        Batal
    </button>
</div>