<div class="modal-header">
	<span class="modal-title"><b>KONFIRMASI PEMBAYARAN PETERNAK</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 text-center no-padding"><h4><b>Jumlah</b></h4></div>
					<div class="col-xs-8 text-center no-padding total" data-val="<?php echo $data['total']; ?>"><h4><b><?php echo angkaDecimal($data['total']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Pembayaran</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control" placeholder="Nomor" value="<?php echo $data['nomor']; ?>" readonly />
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Tgl Bayar</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<div class="input-group date" id="tgl_bayar" data-val="<?php echo $data['tgl_bayar']; ?>">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date"/>
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Periode DOC In</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding periode_docin">
						<?php echo strtoupper(tglIndonesia($data['first_date'], '-', ' ').' - '.tglIndonesia($data['last_date'], '-', ' ')); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Perusahaan</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding perusahaan" data-val="<?php echo $data['no_perusahaan']; ?>">
						<?php echo strtoupper($data['perusahaan']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Mitra</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding mitra" data-val="<?php echo $data['no_mitra']; ?>">
						<?php echo strtoupper($data['mitra']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Rekening</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control text-center rekening" data-required="1" placeholder="No. Rekening" value="<?php echo $data['rekening_nomor'].' - '.$data['rekening_bank']; ?>" readonly />
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding">Lampiran</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<label class="col-xs-12 no-padding">
							<?php
								$hide = 'hide';
								$data_required = 1;
								if ( !empty($data['lampiran']) ) {
									$hide = '';
									$data_required = 0;
								}
							?>
							<a name="dokumen" class="text-right cursor-p <?php echo $hide; ?>" target="_blank" style="padding-right: 10px;"><?php echo $data['lampiran']; ?></a>
							<label class="" style="margin-bottom: 0px;">
								<input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="kpp.showNameFile(this)" data-name="name" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" data-required="<?php echo $data_required; ?>">
								<i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment"></i> 
							</label>
				      	</label>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<?php if ( !empty($data['nomor']) ): ?>
						<button type="button" class="btn btn-primary pull-right" onclick="kpp.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
					<?php else: ?>
						<button type="button" class="btn btn-primary pull-right" onclick="kpp.save()"><i class="fa fa-save"></i> Simpan</button>
					<?php endif ?>
				</div>
			</form>
		</div>
	</div>
</div>