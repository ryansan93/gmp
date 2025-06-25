<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-3 no-padding"><label class="control-label text-left">Transaksi Jurnal</label></div>
	<div class="col-xs-9 no-padding">
		<label class="control-label">: <?php echo $data['det_jurnal_trans']['nama']; ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-3 no-padding"><label class="control-label text-left">Report Jurnal</label></div>
	<div class="col-xs-9 no-padding">
		<label class="control-label">: <?php echo $data['jurnal_report']['nama']; ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-3 no-padding"><label class="control-label text-left">Posisi</label></div>
	<div class="col-xs-9 no-padding">
		<label class="control-label">: <?php echo ($data['posisi'] == 'cr') ? 'KREDIT' : 'DEBET'; ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="mj.changeTabActive(this)" data-href="action" data-edit="edit" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
	<button type="button" class="btn btn-danger pull-right" onclick="mj.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
</div>