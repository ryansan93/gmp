<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-2 no-padding"><label class="control-label">Bank</label></div>
            <div class="col-xs-10 no-padding"><label class="control-label">: <?php echo strtoupper($data['nama_coa']); ?></label></div>
		</div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-2 no-padding"><label class="control-label">Tanggal</label></div>
            <div class="col-xs-10 no-padding"><label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
		</div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-2 no-padding"><label class="control-label">Saldo Awal (Rp.)</label></div>
            <div class="col-xs-10 no-padding"><label class="control-label">: <?php echo angkaDecimal($data['saldo_awal']); ?></label></div>
		</div>
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-2 no-padding"><label class="control-label">Saldo Akhir (Rp.)</label></div>
            <div class="col-xs-10 no-padding"><label class="control-label">: <?php echo angkaDecimal($data['saldo_akhir']); ?></label></div>
		</div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px"></div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <td colspan="3" class="text-right"><b>Total</b></td>
                        <td class="tot_debit text-right"><b>0</b></td>
                        <td class="tot_kredit text-right"><b>0</b></td>
                    </tr>
                    <tr>
                        <th class="col-xs-1">Tanggal</th>
                        <th class="col-xs-2">Akun Transaksi</th>
                        <th class="col-xs-5">Keterangan</th>
                        <th class="col-xs-2">Debit</th>
                        <th class="col-xs-2">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( !empty($detail) && count($detail) > 0 ) { ?>
                    <?php foreach ($detail as $key => $value) { ?>
                        <tr>
                            <td><?php echo tglIndonesia($value['tanggal'], '-', ' '); ?></td>
                            <td><?php echo $value['nama_jurnal_trans']; ?></td>
                            <td><?php echo trim($value['keterangan']); ?></td>
                            <td class="text-right debit"><?php echo angkaDecimal($value['debit']); ?></td>
                            <td class="text-right kredit"><?php echo angkaDecimal($value['kredit']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else {?>
                    <tr>
                        <td colspan="5">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
</div>