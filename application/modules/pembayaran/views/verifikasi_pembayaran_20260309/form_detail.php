<div class="modal-header">
	<span class="modal-title"><b>PEMBAYARAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-3 no-padding">ATAS NAMA</div>
                        <div class="col-xs-9 no-padding">: <span class="atasnama"><?php echo strtoupper($atas_nama); ?></span></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-3 no-padding">BANK</div>
                        <div class="col-xs-9 no-padding">: <span class="bank"><?php echo strtoupper($bank); ?></span></div>
                    </div>
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-3 no-padding">NO. REKENING</div>
                        <div class="col-xs-9 no-padding">: <span class="norek"><?php echo strtoupper($no_rek); ?></span></div>
                    </div>
                </div>
                <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">Tanggal</th>
                                    <th class="col-xs-1">No. Bayar / No. Invoice</th>
                                    <th class="col-xs-1">Bruto</th>
                                    <th class="col-xs-1">Potongan PPH</th>
                                    <th class="col-xs-1">Netto</th>
                                    <th class="col-xs-1">Pengajuan Transfer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $t_bruto = 0;
                                    $t_pph = 0;
                                    $t_netto = 0;
                                    $t_pengajuan_transafer = 0;
                                ?>
                                <?php foreach ($data as $key => $value) { ?>
                                    <tr>
                                        <td>
                                            <?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?>
                                        </td>
                                        <td>
                                            <?php if ( isset($value['lampiran']) && !empty($value['lampiran']) ) { ?>
                                                <a href="uploads/<?php echo $value['lampiran']; ?>" target="_blank"><?php echo $value['no_inv']; ?></a>
                                            <?php } else { ?>
                                                <?php echo $value['no_inv']; ?>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right"><?php echo angkaDecimal($value['bruto']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['pph']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['netto']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['transfer']); ?></td>

                                        <?php
                                            $t_bruto += $value['bruto'];
                                            $t_pph += $value['pph'];
                                            $t_netto += $value['netto'];
                                            $t_pengajuan_transafer += $value['transfer'];
                                        ?>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="2"><b>TOTAL</b></td>
                                    <td class="text-right"><b><?php echo angkaDecimal($t_bruto); ?></b></td>
                                    <td class="text-right"><b><?php echo angkaDecimal($t_pph); ?></b></td>
                                    <td class="text-right"><b><?php echo angkaDecimal($t_netto); ?></b></td>
                                    <td class="text-right"><b><?php echo angkaDecimal($t_pengajuan_transafer); ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </small>
				</div>
                <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
                <div class="col-xs-12 no-padding">
                    <button type="button" class="col-xs-12 btn btn-default" onclick="vp.encryptParams(this)" data-id="<?php echo $id; ?>"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                </div>
			</form>
		</div>
	</div>
</div>