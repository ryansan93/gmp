<div class="panel-body" style="padding-top: 0px;">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group" style="margin-bottom: 5px;">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Unit</label>
                	</div>
                	<div class="col-md-3 no-padding">
                        <label class="control-label">: <?php echo $data['unit']; ?></label>
                	</div>
                	<div class="col-md-7 no-padding text-right">
                		<label class="control-label tgl_panen">Tanggal Panen : <?php echo tglIndonesia( $data['tgl_panen'], '-', ' ', TRUE); ?></label>
                	</div>
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Bottom Price</label>
                	</div>
                	<div class="col-md-2 no-padding">
                        <label class="control-label">: <?php echo angkaRibuan($data['bottom_price']); ?></label>
                	</div>
                </div>
                <div class="form-group">
                	<small>
	                	<table class="table table-bordered tbl_data_konfir header">
	                		<thead>
	                			<tr>
	                				<th class="col-md-4">Nama Peternak</th>
	                				<th class="col-md-2">Noreg</th>
	                				<th class="col-md-1">Kandang</th>
	                				<th class="col-md-1">Ekor</th>
                                    <th class="col-md-1">Tonase</th>
	                			</tr>
	                		</thead>
	                		<tbody>
                                <?php if ( !empty($data['konfir']) ): ?>
                                    <?php foreach ($data['konfir'] as $k_konfir => $v_konfir): ?>
        	                			<tr class="head">
                                            <td><?php echo strtoupper($v_konfir['mitra']); ?></td>
                                            <td class="noreg"><?php echo $v_konfir['noreg']; ?></td>
                                            <td class="text-center"><?php echo $v_konfir['kandang']; ?></td>
                                            <td class="text-right"><?php echo angkaRibuan($v_konfir['ekor']); ?></td>
                                            <td class="text-right"><?php echo angkaDecimal($v_konfir['tonase']); ?></td>
        	                			</tr>
                                        <tr class="detail">
                                            <td colspan="5">
                                                <table class="table table-bordered detail">
                                                    <thead>
                                                        <tr>
                                                            <th class="col-md-1">No. DO</th>
                                                            <th class="col-md-1">No. SJ</th>
                                                            <th class="col-md-3">Nama Pelanggan</th>
                                                            <th class="col-md-1">Outstanding</th>
                                                            <th class="col-md-1">Ekor</th>
                                                            <th class="col-md-1">Tonase</th>
                                                            <th class="col-md-1">BB</th>
                                                            <th class="col-md-1">Harga</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $tot_ekor = 0; $tot_kg = 0;$tot_bb = 0; ?>
                                                        <?php foreach ($v_konfir['det_rpah'] as $k_drpah => $v_drpah): ?>
                                                            <tr>
                                                                <td><?php echo $v_drpah['no_do']; ?></td>
                                                                <td><?php echo $v_drpah['no_sj']; ?></td>
                                                                <td><?php echo $v_drpah['plg']; ?></td>
                                                                <td><?php echo $v_drpah['outstanding']; ?></td>
                                                                <td class="text-right"><?php echo angkaRibuan($v_drpah['ekor']); ?></td>
                                                                <td class="text-right"><?php echo angkaDecimal($v_drpah['tonase']); ?></td>
                                                                <td class="text-right"><?php echo angkaDecimal($v_drpah['bb']); ?></td>
                                                                <td class="text-right"><?php echo angkaRibuan($v_drpah['harga']); ?></td>
                                                            </tr>
                                                            <?php $tot_ekor += $v_drpah['ekor']; $tot_kg += $v_drpah['tonase']; ?>
                                                        <?php endforeach ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td class="text-right" colspan="4"><b>Total</b></td>
                                                            <td class="text-right detail_tot_ekor <?php echo ($v_konfir['ekor'] < $tot_ekor) ? 'lebih' : ''; ?>"><b><?php echo angkaRibuan($tot_ekor); ?></b></td>
                                                            <td class="text-right detail_tot_kg <?php echo ($v_konfir['tonase'] < $tot_kg) ? 'lebih' : ''; ?>""><b><?php echo angkaDecimal($tot_kg); ?></b></td>
                                                            <td class="text-right detail_tot_bb"><b><?php echo ($tot_ekor > 0 && $tot_kg > 0) ? angkaDecimal($tot_kg/$tot_ekor) : '0'; ?></b></td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
	                		</tbody>
	                	</table>
	                </small>
                </div>
            </form>
        </div>
        <div class="col-md-6 no-padding">
            <p>
                <b><u>Keterangan : </u></b>
                <?php
                    if ( !empty($data['logs']) ) {
                        foreach ($data['logs'] as $key => $log) {
                            $temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
                        }
                        if ($temp) {
                            echo '<ul>' . implode("", $temp) . '</ul>';
                        }
                    }
                ?>
            </p>
            <!-- <?php if (! empty($data['alasan_tolak'])): ?>
                <p>
                    <b><u>Alasan Reject :</u></b>
                    <ul>
                        <li><?php echo $data['alasan_tolak'] ?></li>
                    </ul>
                </p>
            <?php endif; ?> -->
        </div>
        <div class="col-md-6 no-padding">
            <?php if ( $data['edit'] == 1 ): ?>
                <?php if ( $akses['a_edit'] == 1 ): ?>
                    <button type="button" class="btn btn-primary pull-right" onclick="rpah.changeTabActive(this)" data-href="rpah" data-resubmit="edit" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Update</button>
                <?php endif ?>
                <?php if ( $akses['a_delete'] == 1 ): ?>
                    <button type="button" class="btn btn-danger pull-right" onclick="rpah.delete(this)" data-href="riwayat_rpah" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
                <?php endif ?>
            <?php endif ?>
            <?php if ( $akses['a_ack'] == 1 ): ?>
                <?php if ( $data['g_status'] == getStatus('submit') ): ?>
                    <button type="button" class="btn btn-primary pull-right" onclick="rpah.approve(this)" data-href="rpah" data-id="<?php echo $data['id']; ?>"><i class="fa fa-check"></i> Approve</button>
                    <!-- <button type="button" class="btn btn-danger pull-right" onclick="rpah.reject(this)" data-href="rpah" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-times"></i> Reject</button> -->
                <?php endif ?>
            <?php endif ?>
    	</div>
    </div>
</div>