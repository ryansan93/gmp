<div class="col-xs-12">
    <form class="form-horizontal" role="form">
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">No. Order</label>
            </div>
            <div class="col-xs-5">
               <label class="control-label">: <?php echo $data['no_order']; ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Tgl Retur</label>
            </div>
            <div class="col-xs-5">
                <label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_retur'], '-', ' ')); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Asal</label>
            </div>
            <div class="col-xs-5">
               <label class="control-label">: <?php echo strtoupper($data['asal']); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Tujuan</label>
            </div>
            <div class="col-xs-5">
               <label class="control-label">: <?php echo strtoupper($data['tujuan']); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Keterangan</label>
            </div>
            <div class="col-xs-5">
               <label class="control-label">: <?php echo !empty($data['keterangan']) ? strtoupper($data['keterangan']) : '-'; ?></label>
            </div>
        </div>
        <!-- <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">OA</label>
            </div>
            <div class="col-xs-2">
               <label class="control-label">: <?php echo angkaDecimal($data['ongkos_angkut']); ?></label>
            </div>
        </div> -->
        <!-- <div class="form-group">
            <div class="col-xs-12 no-padding">
                <hr style="margin-top: 10px; margin-bottom: 10px;">
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Ekspedisi</label>
            </div>
            <div class="col-xs-2">
               <label class="control-label">: <?php echo strtoupper($data['ekspedisi']); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">No. Polisi</label>
            </div>
            <div class="col-xs-2">
               <label class="control-label">: <?php echo strtoupper($data['no_polisi']); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-1 no-padding">
                <label class="control-label">Sopir</label>
            </div>
            <div class="col-xs-2">
               <label class="control-label">: <?php echo strtoupper($data['sopir']); ?></label>
            </div>
        </div> -->
        <div class="form-group">
            <div class="col-xs-12 no-padding">
                <hr style="margin-top: 10px; margin-bottom: 10px;">
            </div>
        </div>
        <div class="form-group">
        	<small>
            	<table class="table table-bordered tbl_data_ov header">
            		<thead>
                        <tr>
                            <th class="col-xs-4 text-center">Nama Item</th>
                            <th class="col-xs-1 text-center">Jumlah</th>
                            <th class="col-xs-1 text-center">Jumlah Retur</th>
                            <th class="col-xs-3 text-center">Nilai Retur</th>
                            <th class="col-xs-3 text-center">Kondisi</th>
                        </tr>
            		</thead>
            		<tbody>
                        <?php if ( count($data['detail']) > 0) : ?>
                            <?php foreach ($data['detail'] as $k_det => $v_det): ?>
                                <tr class="v-center">
                                    <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['nama']); ?></td>
                                    <td class="text-right jml_op"><?php echo angkaDecimal($v_det['jumlah_ov']); ?></td>
                                    <td class="text-right"><?php echo angkaDecimal($v_det['jumlah_rv']); ?></td>
                                    <td class="text-right"><?php echo angkaDecimal($v_det['nilai_retur']); ?></td>
                                    <td class="text-left"><?php echo $v_det['kondisi']; ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif ?>
            		</tbody>
            	</table>
            </small>
        </div>
    </form>
</div>
<div class="col-xs-12 no-padding">
    <hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
    <?php if ( $akses['a_edit'] == 1 ): ?>
        <button type="button" class="btn btn-primary pull-right" onclick="rv.changeTabActive(this)" data-href="rv" data-resubmit="edit" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
    <?php endif ?>
    <?php if ( $akses['a_delete'] == 1 ): ?>
        <button type="button" class="btn btn-danger pull-right" onclick="rv.delete(this)" data-href="riwayat_rv" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
    <?php endif ?>
</div>