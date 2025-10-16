<div class="modal-header">
	<span class="modal-title"><b>GL LENGKAP</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding"><label class="control-label">COA</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['nama_unit']); ?></label></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding"><label class="control-label">Unit</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['nama_unit']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Periode</label></div>
                    <div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ', true)); ?></label></div>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr style="margin-top: 10px; margin-bottom: 10px;">
                </div>
                <div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th>Tgl</th>
                                    <th>No. Dokumen</th>
                                    <th>Unit</th>
                                    <th>Keterangan</th>
                                    <th>Debet</th>
                                    <th>Kredit</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </small>
                </div>
			</form>
		</div>
	</div>
</div>