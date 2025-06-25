<div class="modal-header">
	<span class="modal-title"><b>KONFIRMASI PEMBAYARAN PAKAN</b></span>
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
						<div class="input-group date" id="tgl_bayar">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
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
					<div class="col-xs-4 no-padding">Nama Supplier</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding supplier" data-val="<?php echo $data['no_supplier']; ?>">
						<?php echo strtoupper($data['supplier']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Rekening</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<select class="form-control rekening" data-required="1">
							<option value="">Pilih Rekening</option>
							<?php if ( !empty($data['bank_supplier']) ): ?>
								<?php foreach ($data['bank_supplier'] as $k => $val): ?>
									<?php
										$cabang = null;
										if ( !empty($val['rekening_cabang_bank']) && $val['rekening_cabang_bank'] != '-' && $val['rekening_cabang_bank'] != 0 ) {
											if ( stristr('kcu ', $val['rekening_cabang_bank']) !== FALSE ) {
												$cabang = ' CAB.'.str_replace('KCU ', '', $val['rekening_cabang_bank']);
												$cabang = ' CAB.'.str_replace('Kcu ', '', $val['rekening_cabang_bank']);
												$cabang = ' CAB.'.str_replace('kcu ', '', $val['rekening_cabang_bank']);
											}

											if ( stristr('kcp ', $val['rekening_cabang_bank']) !== FALSE ) {
												$cabang = ' CAB.'.str_replace('KCP ', '', $val['rekening_cabang_bank']);
												$cabang = ' CAB.'.str_replace('Kcp ', '', $val['rekening_cabang_bank']);
												$cabang = ' CAB.'.str_replace('kcp ', '', $val['rekening_cabang_bank']);
											}
										}
									?>
									<option value="<?php echo $val['id'] ?>"><?php echo $val['rekening_nomor'].' - '.$val['bank'].$cabang; ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
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