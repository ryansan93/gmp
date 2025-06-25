<div class="modal-header">
	<span class="modal-title"><b>EDIT DETAIL TRANSAKSI</b></span>
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
                    <div class="col-xs-12 no-padding"><label class="control-label">Detail Transaksi</label></div>
					<div class="col-xs-12 no-padding">
                        <select class="form-control det_jurnal_trans">
                            <option value="">-- Pilih Detail Transaksi --</option>
                            <?php foreach ($det_jurnal_trans as $key => $value) { ?>
                                <?php
                                    $selected = null;
                                    if ( $value['sumber_coa'] == $data['coa_asal'] && $value['tujuan_coa'] == $data['coa_tujuan'] ) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?php echo $value['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($value['nama']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Asal</label></div>
					<div class="col-xs-9 no-padding sumber_coa" data-coa="<?php echo $data['coa_asal']; ?>"><label class="control-label">: <?php echo strtoupper($data['asal']); ?></label></div>
				</div>
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-3 no-padding"><label class="control-label">Tujuan</label></div>
					<div class="col-xs-9 no-padding tujuan_coa" data-coa="<?php echo $data['coa_tujuan']; ?>"><label class="control-label">: <?php echo strtoupper($data['tujuan']); ?></label></div>
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
				<div class="col-xs-12 no-padding">
                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                        <button type="button" class="col-xs-12 btn btn-danger" onclick="kk.detailForm(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-times"></i> Batal</button>
                    </div>
                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                        <button type="button" class="col-xs-12 btn btn-primary" onclick="kk.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
                    </div>
				</div>
			</form>
		</div>
	</div>
</div>