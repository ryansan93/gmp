<div class="modal-header">
	<span class="modal-title"><b>GL LENGKAP</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
                <div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding"><label class="control-label">COA</label></div>
					<div class="col-xs-10 no-padding"><label class="control-label">: <?php echo strtoupper($data['no_coa'].' | '.$data['nama_coa']); ?></label></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding"><label class="control-label">Unit</label></div>
					<div class="col-xs-10 no-padding"><label class="control-label">: <?php echo strtoupper($data['unit']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-2 no-padding"><label class="control-label">Periode</label></div>
                    <div class="col-xs-10 no-padding"><label class="control-label">: <?php echo substr(strtoupper(tglIndonesia($data['periode'], '-', ' ', true)), 3); ?></label></div>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr style="margin-top: 10px; margin-bottom: 10px;">
                </div>
                <?php
                    $tot_saldo_awal = 0;
                    $tot_debet = 0;
                    $tot_kredit = 0;
                    $tot_saldo_akhir = 0;
                ?>
                <div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">Tgl</th>
                                    <th class="col-xs-2">No. Dokumen</th>
                                    <th class="col-xs-1">Unit</th>
                                    <th class="col-xs-4">Keterangan</th>
                                    <th class="col-xs-2">Debet</th>
                                    <th class="col-xs-2">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( !empty($detail) ) { ?>
                                    <?php foreach ($detail as $key => $value) { ?>
                                        <?php
                                            if ( stristr($value['keterangan'], 'saldo awal') !== false ) {
                                                if ( $value['debet'] <> 0 ) {
                                                    $tot_saldo_awal += $value['debet'];
                                                }

                                                if ( $value['kredit'] <> 0 ) {
                                                    $tot_saldo_awal += $value['kredit'];
                                                }
                                            } else {
                                                $tot_debet += $value['debet'];
                                                $tot_kredit += $value['kredit'];
                                            }
                                        ?>

                                        <tr>
                                            <td><?php echo ($value['tanggal'] < '2025-01-01') ? '' : tglIndonesia($value['tanggal'], '-', ' '); ?></td>
                                            <td><?php echo $value['kode_trans']; ?></td>
                                            <td><?php echo $value['unit']; ?></td>
                                            <td><?php echo $value['keterangan']; ?></td>
                                            <td class="text-right"><?php echo ($value['debet'] >= 0) ? angkaDecimal($value['debet']) : '('.angkaDecimal(abs($value['debet'])).')'; ?></td>
                                            <td class="text-right"><?php echo ($value['kredit'] >= 0) ? angkaDecimal($value['kredit']) : '('.angkaDecimal(abs($value['kredit'])).')'; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6">Data tidak ditemukan.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </small>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr style="margin-top: 10px; margin-bottom: 10px;">
                </div>
                <div class="col-xs-12 no-padding">
                    <?php $tot_saldo_akhir = $tot_saldo_awal + $tot_debet + $tot_kredit; ?>
                    <small>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-xs-3">Saldo Awal</th>
                                    <th class="col-xs-3">Debet</th>
                                    <th class="col-xs-3">Kredit</th>
                                    <th class="col-xs-3">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-right"><?php echo ($tot_saldo_awal >= 0) ? angkaDecimal($tot_saldo_awal) : '('.angkaDecimal(abs($tot_saldo_awal)).')'; ?></td>
                                    <td class="text-right"><?php echo ($tot_debet >= 0) ? angkaDecimal($tot_debet) : '('.angkaDecimal(abs($tot_debet)).')'; ?></td>
                                    <td class="text-right"><?php echo ($tot_kredit >= 0) ? angkaDecimal($tot_kredit) : '('.angkaDecimal(abs($tot_kredit)).')'; ?></td>
                                    <td class="text-right"><?php echo ($tot_saldo_akhir >= 0) ? angkaDecimal($tot_saldo_akhir) : '('.angkaDecimal(abs($tot_saldo_akhir)).')'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </small>
                </div>
			</form>
		</div>
	</div>
</div>