<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $key > 0 ) { ?>
            <div class="col-xs-12 no-padding"><br></div>
        <?php } ?>
        <div class="col-xs-12 no-padding">
            <table class="table table-bordered" style="margin-bottom: 0px;;">
                <tbody>
                    <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
                        <tr>
                            <td class="col-xs-4"><label class="control-label"><?php echo $v_det['keterangan']; ?></label></td>
                            <td class="col-xs-8 text-right"><?php echo ($v_det['nilai'] >= 0) ? angkaDecimal($v_det['nilai']) : '('.angkaDecimal(abs($v_det['nilai'])).')'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!-- <div class="col-xs-8 no-padding"><label class="control-label"><?php echo $v_det['keterangan']; ?></label></div>
            <div class="col-xs-4 no-padding text-right"><?php echo ($v_det['nilai'] >= 0) ? angkaDecimal($v_det['nilai']) : '('.angkaDecimal(abs($v_det['nilai'])).')'; ?></div> -->
        </div>
    <?php } ?>
<?php } ?>