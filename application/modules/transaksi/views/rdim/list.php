<?php if (count($datas) > 0): ?>
    <?php foreach ($datas as $data): ?>
        <?php 
            $resubmit = null;
            if ( $data['g_status'] == 4 ) {
                $resubmit = $data['id'];
            }
        ?>

        <?php 
            $red = null;
            if ( $akses['a_ack'] == 1 ){
                $status = getStatus('submit');
                if ( $data['g_status'] == $status ) {
                    $red = 'red';
                }
            } else if ( $akses['a_approve'] == 1 ){
                $status = getStatus('ack');
                if ( $data['g_status'] == 2 ) {
                    $red = 'red';
                }
            } else {

            }
        ?>
        <tr class="search <?php echo $red; ?>">
            <td class="tanggal <?php echo $red ?>"><?php echo tglIndonesia($data->mulai, '-', ' ') .' - '. tglIndonesia($data->selesai, '-', ' ') ?></td>
            <td class="nomor"><?php echo $data->nomor ?></td>
            <td class="status col-sm-1"><?php echo getStatus($data->g_status) ?></td>
            <td class="keterangan">
                <div class="col-md-10 no-padding">
                    <?php
                        $last_log = $data->logs->last();
                        $keterangan = $last_log->deskripsi . ' pada ' . dateTimeFormat( $last_log->waktu );
                        echo $keterangan;
                    ?>
                </div>
                <?php if ( $akses['a_edit'] == 1 ){ ?>
                    <div class="col-md-1 no-padding pull-right">
                        <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="rdim.changeTabActive(this)" data-id="<?php echo $data->id; ?>" data-resubmit="<?php echo 'EDIT'; ?>"> 
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php } ?>
                <?php if ( $akses['a_view'] == 1 ){ ?>
                    <div class="col-md-1 no-padding pull-right">
                        <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="rdim.changeTabActive(this)" data-id="<?php echo $data->id; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php } ?>
                <!-- <span class="pull-right">
                <a onclick="Rdim.viewDetail(this)" data-toggle="tab" role="tab" href="#rencana_doc_in_mingguan" data-id="<?php echo $data->id ?>" > <i class="fa fa-search-plus"></i> <u>lihat</u> </a>
                </span> -->
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="4" class="text-center">Tidak ada ditemukan</td>
    </tr>
<?php endif; ?>
