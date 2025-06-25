<div class="modal-header">
    <h4 class="modal-title">Pilih Piutang</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="panel-body no-padding">
        <?php // cetak_r( $data ); ?>
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-sm-2">Plasma</th>
                        <th class="col-sm-2">Perusahaan</th>
                        <th class="col-sm-1">Tanggal</th>
                        <th class="col-sm-1">Kode</th>
                        <th class="col-sm-3">Keterangan</th>
                        <th class="col-sm-2">Sisa Hutang (Rp.)</th>
                        <th class="col-sm-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $k_data => $v_data) { ?>
                        <tr>
                            <td class="nama_mitra"><?php echo strtoupper($v_data['nama_mitra']); ?></td>
                            <td class="nama_perusahaan"><?php echo strtoupper($v_data['nama_perusahaan']); ?></td>
                            <td class="tanggal"><?php echo strtoupper(tglIndonesia($v_data['tanggal'], '-', ' ')); ?></td>
                            <td class="kode"><?php echo strtoupper($v_data['kode']); ?></td>
                            <td class="keterangan"><?php echo strtoupper($v_data['keterangan']); ?></td>
                            <td class="text-right sisa_piutang"><?php echo angkaDecimal($v_data['sisa_piutang']); ?></td>
                            <td>
                                <button type="button" class="btn btn-primary col-xs-12" onclick="rg.addPiutang(this)"><i class="fa fa-plus"></i></button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
</div>