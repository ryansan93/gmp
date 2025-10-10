<div class="modal-header">
	<span class="modal-title"><b>PEMBAYARAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">No. Bayar / No. Invoice</th>
                                    <th class="col-xs-1">Bruto</th>
                                    <th class="col-xs-1">Potongan PPH</th>
                                    <th class="col-xs-1">Netto</th>
                                    <th class="col-xs-1">Pengajuan Transfer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $key => $value) { ?>
                                    <tr>
                                        <td><?php echo $value['no_inv']; ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['bruto']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['pph']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['netto']); ?></td>
                                        <td class="text-right"><?php echo angkaDecimal($value['transfer']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </small>
				</div>
			</form>
		</div>
	</div>
</div>