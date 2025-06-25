<div class="modal-header">
	<span class="modal-title"><b>DETAIL TRANSAKSI</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding"><label class="control-label">Unit</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['nama_unit']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding"><label class="control-label">Perusahaan</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['nama_perusahaan']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Tanggal</label></div>
                    <div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ', true)); ?></label></div>
                </div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Transaksi Jurnal</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['transaksi_jurnal']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Detail Transaksi</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['detail_transaksi_jurnal']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Asal</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['asal']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Tujuan</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['tujuan']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">PiC</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['pic']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Keterangan</label></div>
					<div class="col-xs-9 no-padding"><label class="control-label">: <?php echo strtoupper($data['keterangan']); ?></label></div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

                <?php if ( $akses['a_edit'] == 1 && $g_status != getStatus('ack') ) { ?>
                    <div class="col-xs-12 no-padding">
                        <button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="kk.editForm(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit Detail Transaksi</button>
                    </div>
                <?php } ?>
			</form>
		</div>
	</div>
</div>