<div class="modal-header">
	<span class="modal-title"><b>KONFIRMASI PEMBAYARAN PAKAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-5 text-left no-padding"><h4><b>Sub Total</b></h4></div>
					<div class="col-xs-7 text-right no-padding sub_total" data-val="<?php echo $data['total']; ?>"><h4><b><?php echo angkaDecimal($data['total']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-5 text-left no-padding"><h4><b>Potongan PPH 23</b></h4></div>
					<div class="col-xs-7 text-right no-padding potongan_pph_23" data-val="<?php echo $data['total_pph']; ?>"><h4><b><?php echo angkaDecimal($data['total_pph']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-5 text-left no-padding"><h4><b>Biaya Materai</b></h4></div>
					<div class="col-xs-7 text-right no-padding biaya_materai" data-val="<?php echo $data['biaya_materai']; ?>"><h4><b><?php echo angkaDecimal($data['biaya_materai']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-5 text-left no-padding"></div>
					<div class="col-xs-7 text-left no-padding">
						<hr style="margin-top: 10px; margin-bottom: 10px;">
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-5 text-left no-padding"><h4><b>Grand Total</b></h4></div>
					<div class="col-xs-7 text-right no-padding total" data-val="<?php echo ($data['total']-$data['total_pph']); ?>"><h4><b><?php echo angkaDecimal(($data['total']-$data['total_pph'])); ?></b></h4></div>
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
						<div class="input-group date" id="tgl_bayar">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" data-tgl="<?php echo $data['tgl_bayar']; ?>" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Biaya Materai</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control biaya_materai text-right" placeholder="Biaya Materai" data-tipe="decimal" data-required="1" onblur="kpoap.hitGrandTotal(this)" value="<?php echo $data['biaya_materai']; ?>" />
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Periode Mutasi</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding periode_mutasi">
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
					<div class="col-xs-4 no-padding">Ekspedisi</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding ekspedisi" data-val="<?php echo $data['ekspedisi']; ?>" data-id="<?php echo $data['ekspedisi_id']; ?>">
						<?php echo $data['ekspedisi']; ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Bank | No. Rekening</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<select class="form-control bank" data-required="1">
							<option value="">-- Pilih Bank dan No. Rekening --</option>
							<?php foreach ($bank_ekspedisi as $k_be => $v_be): ?>
								<?php
									$selected = null;
									if ( $v_be['bank'] == $data['bank'] && $v_be['rekening_nomor'] == $data['rekening'] ) {
										$selected = 'selected';
									}
								?>
								<option value="<?php echo $v_be['id']; ?>" data-bank="<?php echo $v_be['bank']; ?>" data-norek="<?php echo $v_be['rekening_nomor']; ?>" <?php echo $selected; ?> ><?php echo $v_be['bank'].' | '.$v_be['rekening_nomor']; ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Bank</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control bank" placeholder="Bank" data-required="1" value="<?php echo $data['bank'] ?>" maxlength="25">
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Rekening</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control rekening" placeholder="No. Rekening" data-required="1" value="<?php echo $data['rekening'] ?>" maxlength="50">
					</div>
				</div> -->
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Invoice</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control invoice" placeholder="No. Invoice" data-required="1" value="<?php echo $data['invoice']; ?>" maxlength="50">
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Lampiran</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<div class="col-lg-12" style="padding: 7px 0px 0px 0px;">
							<a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo $data['lampiran']; ?></a>
							<label class="">
								<?php 
									$data_required = 'data-required="1"';
									if ( !empty($data['nomor']) ) {
										$data_required = null;
									}
								?>
								<input type="file" onchange="showNameFile(this)" class="file_lampiran" <?php echo $data_required; ?> name="" placeholder="Bukti Transfer" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" data-old="<?php echo $data['lampiran']; ?>" style="display: none;">
								<i class="glyphicon glyphicon-paperclip cursor-p"></i>
							</label>
						</div>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<?php if ( !empty($data['nomor']) ): ?>
						<button type="button" class="btn btn-primary pull-right" onclick="kpoap.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
					<?php else: ?>
						<button type="button" class="btn btn-primary pull-right" onclick="kpoap.save()"><i class="fa fa-save"></i> Simpan</button>
					<?php endif ?>
				</div>
			</form>
		</div>
	</div>
</div>