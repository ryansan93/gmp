<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="head cursor-p" onclick="pk.collapseRow(this)">
            <td><?php echo strtoupper($value['kode']); ?></td>
            <td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
            <td><?php echo strtoupper($value['perusahaan']); ?></td>
            <td><?php echo strtoupper($value['nama']); ?></td>
            <td class="text-right piutang"><?php echo angkaDecimal($value['piutang']); ?></td>
            <td class="text-right bayar"><?php echo angkaDecimal($value['tot_bayar']); ?></td>
            <td class="text-right sisa"><?php echo angkaDecimal($value['sisa']); ?></td>
        </tr>
        <tr class="detail hide">
            <td colspan="7" style="background-color: #ededed;">
                <div class="col-xs-12 no-padding"><label class="label-control">DETAIL PEMBAYARAN</label></div>
                <div class="col-xs-12 no-padding">
                    <table class="table table-bordered" style="margin-bottom: 0px;">
                        <thead>
                            <tr>
                                <td class="col-xs-3"><b>Tanggal</b></td>
                                <td class="col-xs-6"><b>Nominal (Rp.)</b></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( !empty($value['det_bayar']) && count($value['det_bayar']) > 0 ) { ?>
                                <?php foreach ($value['det_bayar'] as $k_det => $v_det) { ?>
                                    <tr>
                                        <td><?php echo strtoupper(tglIndonesia($v_det['tanggal'], '-', ' ')); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($v_det['nominal']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="2">Data tidak ditemukan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="7">Data tidak ditemukan.</td>
    </tr>
<?php } ?>