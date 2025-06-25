<?php if ( !empty($data) && count($data) > 0 ) : ?>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <tbody>
                    <?php foreach ($data as $k_unit => $v_unit) : ?>
                        <tr class="head-unit">
                            <td class="col-xs-9 cursor-p" onclick="ar.openDetail(this)">
                                <div class="col-xs-12 no-padding text-left nama-unit"><b><?php echo $v_unit['nama_unit']; ?></b></div>
                                <div class="col-xs-12 no-padding text-left">&nbsp;</div>
                                <!-- <div class="col-xs-12 no-padding text-left"><hr style="margin-top: 5px; margin-bottom: 5px;"></div> -->
                                <div class="col-xs-12 no-padding text-left tgl-panen"><b><?php echo strtoupper(tglIndonesia($v_unit['tgl_panen'], '-', ' ', true)); ?></b></div>
                                <div class="col-xs-12 no-padding text-left"><b><?php echo 'HARGA : '.angkaRibuan($v_unit['harga']); ?></b></div>
                                <div class="col-xs-12 no-padding text-left"><b><?php echo strtoupper($v_unit['deskripsi'].' '.tglIndonesia(substr($v_unit['waktu'], 0, 10), '-', ' ', true).' '.substr($v_unit['waktu'], 11, 5)); ?></b></div>
                            </td>
                            <td class="col-xs-3">
                                <?php if ( $akses['a_approve'] == 1 ) { ?>
                                    <div class="col-xs-12 no-padding" style="height: 50%; padding-bottom: 3px;">
                                        <button type="button" class="col-xs-12 btn btn-primary" onclick="ar.approve(this)" data-id="<?php echo $v_unit['id']; ?>" style="height: 100%;"><i class="fa fa-check"></i> <b>APPROVE</b></button>
                                    </div>
                                    <div class="col-xs-12 no-padding" style="height: 50%; padding-top: 3px;">
                                        <button type="button" class="col-xs-12 btn btn-danger" onclick="ar.reject(this)" data-id="<?php echo $v_unit['id']; ?>" style="height: 100%;"><i class="fa fa-times"></i> <b>REJECT</b></button>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr class="detail-unit hide">
                            <td colspan="2">
                                <table class="table table-bordered" style="margin-bottom: 15px;">
                                    <tbody>
                                        <?php foreach ($v_unit['detail'] as $k_mitra => $v_mitra) : ?>
                                            <tr class="head">
                                                <td colspan="6">
                                                    <div class="col-xs-12 no-padding text-left"><b><?php echo $v_mitra['nama_mitra']; ?></b></div>
                                                    <div class="col-xs-12 no-padding text-left"><b><?php echo angkaRibuan($v_mitra['ekor_konfir']).' EKOR | '.angkaDecimal($v_mitra['kg_konfir']).' KG'; ?></b></div>
                                                </td>
                                            </tr>
                                            <tr class="detail-head">
                                                <td class="text-center col-xs-3"><b>Pelanggan</b></td>
                                                <td class="text-center col-xs-2"><b>Jml Nota</b></td>
                                                <td class="text-center col-xs-2"><b>Tempo</b></td>
                                                <td class="text-center col-xs-1"><b>BB</b></td>
                                                <td class="text-center col-xs-2"><b>Tonsae</b></td>
                                                <td class="text-center col-xs-2"><b>Harga</b></td>
                                            </tr>
                                            <?php $total_tonase = 0; ?>
                                            <?php foreach ($v_mitra['detail'] as $k_plg => $v_plg) : ?>
                                                <tr class="<?php echo $v_plg['bg_color']; ?>">
                                                    <td class="text-left" rowspan="2"><?php echo $v_plg['pelanggan']; ?></td>
                                                    <td class="text-right"><?php echo $v_plg['jml_do_hutang']; ?></td>
                                                    <td class="text-right"><?php echo $v_plg['selisih_hari']; ?></td>
                                                    <td class="text-right" rowspan="2"><?php echo angkaDecimal($v_plg['bb']); ?></td>
                                                    <td class="text-right" rowspan="2"><?php echo angkaDecimal($v_plg['tonase']); ?></td>
                                                    <td class="text-right" rowspan="2"><?php echo angkaRibuan($v_plg['harga']); ?></td>
                                                </tr>
                                                <tr class="<?php echo $v_plg['bg_color']; ?>">
                                                    <td class="text-right" colspan="2"><b><?php echo 'Rp. '.angkaDecimal($v_plg['total_do'] - $v_plg['total_bayar']); ?></b></td>
                                                </tr>
                                                <?php $total_tonase += $v_plg['tonase']; ?>
                                            <?php endforeach ?>
                                            <tr>
                                                <td colspan="4" class="text-right"><b>JUMLAH PENGAJUAN</b></td>
                                                <td class="text-right"><b><?php echo angkaDecimal($total_tonase); ?></b></td>
                                                <td></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </small>
    </div>
<?php else : ?>
    <div class="col-xs-12 no-padding">
        <label class="control-label">Tidak ada data yang akan di approve.</label>
    </div>
<?php endif ?>