<div class="modal-header">
	<span class="modal-title"><b>VIEW DATA</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding">No. Pembayaran</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo strtoupper($data['kode']); ?></b>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding">Tanggal</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></b>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding">Perusahaan</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo strtoupper($data['nama_perusahaan']); ?></b>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding">Bakul</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo (isset($data['nama_pelanggan']) && !empty($data['nama_pelanggan'])) ? strtoupper($data['nama_pelanggan']) : '-'; ?></b>
					</div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding">Jumlah Transfer</div>
                    <div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo strtoupper(angkaRibuan($data['jml_transfer'])); ?></b>
                    </div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">Keterangan</div>
                    <div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-8 no-padding">
                        <b><?php echo strtoupper($data['ket']); ?></b>
                    </div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1 ) { ?>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-6" style="padding: 0px 5px 0px 0px;">
							<?php if ( $akses['a_delete'] == 1 ) { ?>
								<button type="button" class="col-xs-12 btn btn-danger pull-right" onclick="rm.delete(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-trash"></i> Hapus</button>
							<?php } ?>
						</div>
						<div class="col-xs-6" style="padding: 0px 0px 0px 5px;">
							<?php if ( $akses['a_edit'] == 1 ) { ?>
								<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="rm.editForm(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-edit"></i> Edit</button>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</form>
		</div>
	</div>
</div>