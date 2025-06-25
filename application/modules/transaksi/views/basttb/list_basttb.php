<small>
    <table class="table table-bordered custom_table tbl_basttb">
        <thead>
            <tr>
                <th class="text-center col-sm-2">Tanggal</th>
                <th class="text-center col-sm-2">Noreg</th>
                <th class="text-center col-sm-2">Nama Mitra</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($datas) > 0): ?>
                <?php // cetak_r($datas); ?>
                <?php foreach ($datas as $key => $val) { ?>
                    <tr>
                        <td class="tanggal"><?php echo tglIndonesia($val['tgl_trans'], '-', ' '); ?></td>
                        <td class="noreg"><?php echo $val['noreg']; ?></td>
                        <td class="nama_mitra"><?php echo $val['d_rdim_submit']['d_mitra_mapping']['d_mitra']['nama']; ?></td>
                        <td class="keterangan">
                            <div class="col-md-10">
                                <?php
                                    if ( !empty($val['logs']) ) {
                                        $last_log = $val['logs'][ count($val['logs']) - 1 ];
                                        $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
                                        echo $keterangan;
                                    } else {
                                        echo "-";
                                    }
                                ?>
                            </div>
                            <div class="col-md-2">
                                <!-- <span class="pull-right">
                                    <a onclick="basttb.changeTabActive(this)" class="cursor-p" data-toggle="tab" role="tab" data-href="action" data-id="<?php echo $val['id']; ?>" > <u>lihat</u> </a>
                                </span> -->
                                <!-- <a href="#" title="EDIT TERIMA"><i class="fa fa-edit"></i></a> -->
                                <span class="pull-right">
                                    <a class="cursor-p" title="DETAIL" onclick="basttb.changeTabActive(this)" data-href="action" data-id="<?php echo $val['id']; ?>"><i class="fa fa-file"></i></a>
                                    &nbsp
                                    <a class="cursor-p" title="EDIT" onclick="basttb.changeTabActive(this)" data-href="action" data-resubmit="edit" data-id="<?php echo $val['id']; ?>"><i class="fa fa-edit"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada ditemukan</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</small>