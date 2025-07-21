<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $k_ln => $v_ln) { ?>
        <?php foreach ($v_ln['data'] as $k => $val) { ?>
            <?php
                $color_status = '';
                if ( $val['gstatus'] == getStatus('reject') ) {
                    $color_status = 'color: red;';
                }
    
                if ( $val['gstatus'] == getStatus('submit') ) {
                    $color_status = 'color: blue;';
                }
    
                $onclick_link = '';
                if ( isset($v_ln['link']) && !empty($v_ln['link']) ) {
                    $link = $v_ln['link'];
                    $onclick_link = $link;
                    if ( isset($val['key']) && !empty($val['key']) ) {
                        $key = $val['key'];
                        $link .= '/'.$key;
                        $onclick_link = $link;
                    }
                }
            ?>
            <small>
                <?php
                    $action = null;
                    if ( $v_ln['jenis'] == 'window.open' ) {
                        $action = "window.open('".$onclick_link."', '_self')";
                    }
                ?>
                <div class="col-xs-12 no-padding contain-notif cursor-p" onclick="<?php echo $action; ?>">
                    <div class="col-xs-12 no-padding"><b><?php echo strtoupper($v_ln['nama_fitur'].' | <span style="'.$color_status.'">'.$val['nama_status'].'</span>'); ?></b></div>
                    <div class="col-xs-12 no-padding"><?php echo strtoupper($val['keterangan']); ?></div>
                    <div class="col-xs-12 no-padding"><?php echo strtoupper($val['deskripsi'].' '.substr($val['waktu'], 0, 16)); ?></div>
                </div>
            </small>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    Tidak ada notifikasi.
<?php } ?>