<div class="modal-header">
	<span class="modal-title"><b>REALISASI PEMBAYARAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Tagihan</b></h4></div>
					<div class="col-xs-8 text-right no-padding total" data-val="<?php echo $data['total']; ?>"><h4><b><?php echo angkaDecimal($data['total']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Debit Note</b></h4></div>
					<div class="col-xs-8 text-right no-padding total_dn" data-val="<?php echo $data['total_dn']; ?>"><h4><b><?php echo angkaDecimal($data['total_dn']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding">&nbsp;</div>
					<div class="col-xs-8 no-padding">
						<hr style="margin-top: 10px; margin-bottom: 10px;">
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Credit Note</b></h4></div>
					<div class="col-xs-8 text-right no-padding total_cn" data-val="<?php echo $data['total_cn']; ?>"><h4><b><?php echo angkaDecimal($data['total_cn']); ?></b></h4></div>
				</div>
				<!-- <?php if ( $data['jenis_pembayaran'] == 'supplier' ): ?>
				<?php endif ?> -->
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Potongan</b></h4></div>
					<div class="col-xs-8 text-right no-padding total_potongan" data-val="<?php echo $data['total_potongan']; ?>"><h4><b><?php echo angkaDecimal($data['total_potongan']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Bayar</b></h4></div>
					<div class="col-xs-8 text-right no-padding total_bayar" data-val="<?php echo $data['total_bayar']; ?>"><h4><b><?php echo angkaDecimal($data['total_bayar']); ?></b></h4></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding">&nbsp;</div>
					<div class="col-xs-8 no-padding">
						<hr style="margin-top: 10px; margin-bottom: 10px;">
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding"><h4><b>Kurang Bayar</b></h4></div>
					<div class="col-xs-8 text-right no-padding kurang_bayar"><h4><b><?php echo angkaDecimal($data['total']); ?></b></h4></div>
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
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Perusahaan</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding perusahaan" data-val="<?php echo $data['no_perusahaan']; ?>">
						<?php echo strtoupper($data['perusahaan']); ?>
					</div>
				</div>
				<?php 
					$hide_supplier = 'hide';
					$hide_peternak = 'hide';
					$hide_ekspedisi = 'hide';
					if ( stristr($data['jenis_pembayaran'], 'supplier') !== FALSE ) {
						$hide_supplier = null;
					} else if ( stristr($data['jenis_pembayaran'], 'plasma') !== FALSE ) {
						$hide_peternak = null;
					} else if ( stristr($data['jenis_pembayaran'], 'ekspedisi') !== FALSE ) {
						$hide_ekspedisi = null;
					}
				?>
				<div class="col-xs-12 no-padding <?php echo $hide_supplier; ?>" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Supplier</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding supplier" data-val="<?php echo $data['no_supplier']; ?>">
						<?php echo strtoupper($data['supplier']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding <?php echo $hide_peternak; ?>" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Peternak</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding peternak" data-val="<?php echo $data['no_peternak']; ?>">
						<?php echo strtoupper($data['peternak']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding <?php echo $hide_ekspedisi; ?>" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Nama Ekspedisi</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding ekspedisi" data-val="<?php echo $data['no_ekspedisi']; ?>">
						<?php echo strtoupper($data['ekspedisi']); ?>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Rekening</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<?php if ( stristr($data['jenis_pembayaran'], 'supplier') !== FALSE ) { ?>
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

											$selected = null;
											if ( $data['rekening'] == $val['id'] ) {
												$selected = 'selected';
											}
										?>
										<option value="<?php echo $val['id'] ?>" <?php echo $selected; ?> ><?php echo $val['rekening_nomor'].' - '.$val['bank'].$cabang; ?></option>
									<?php endforeach ?>
								<?php endif ?>
							</select>
						<?php } else if ( stristr($data['jenis_pembayaran'], 'supplier') !== FALSE ) { ?>
							<input type="text" class="form-control rekening" data-required="1" value="<?php echo $data['rekening']; ?>" readonly>
						<?php } else if ( stristr($data['jenis_pembayaran'], 'ekspedisi') !== FALSE ) { ?>
							<select class="form-control rekening" data-required="1">
								<option value="">-- Pilih Bank dan No. Rekening --</option>
								<?php foreach ($data['bank_ekspedisi'] as $k_be => $v_be): ?>
									<?php
										$selected = null;
										if ( $v_be['bank'] == $data['bank'] && $v_be['rekening_nomor'] == $data['rekening'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $v_be['id']; ?>" data-bank="<?php echo $v_be['bank']; ?>" data-norek="<?php echo $v_be['rekening_nomor']; ?>" <?php echo $selected; ?> ><?php echo $v_be['bank'].' | '.$v_be['rekening_nomor']; ?></option>
								<?php endforeach ?>
							</select>
						<?php } ?>
					</div>
				</div>
				<?php if ( $data['jenis_pembayaran'] == 'supplier' ): ?>
					<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
						<div class="col-xs-4 no-padding">Debit Note</div>
						<div class="col-xs-1 no-padding text-center">:</div>
						<div class="col-xs-7 no-padding">
							<button type="button" class="btn btn-default" onclick="rp.modalPilihDN(this)">Pilih DN yang akan di gunakan</button>
						</div>
					</div>
					<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
						<div class="col-xs-4 no-padding">Credit Note</div>
						<div class="col-xs-1 no-padding text-center">:</div>
						<div class="col-xs-7 no-padding">
							<button type="button" class="btn btn-default" onclick="rp.modalPilihCN(this)">Pilih CN yang akan di gunakan</button>
						</div>
					</div>
				<?php endif ?>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Potongan</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<button type="button" class="btn btn-default" onclick="rp.modalPotongan(this)">Isi potongan di sini</button>
					</div>
				</div>
				<div class="col-xs-12 no-padding <?php echo ($data['form_uang_muka'] == 0) ? 'hide' : null; ?>" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Uang Muka</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control text-right uang_muka" data-tipe="decimal" data-required="1" value="<?php echo $data['uang_muka']; ?>" onblur="rp.hit_jml_bayar()">
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Jml Transfer</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control text-right jml_transfer" data-tipe="decimal" data-required="1" value="<?php echo $data['jml_transfer']; ?>" onblur="rp.hit_jml_bayar()">
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Bukti</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control no_bukti" placeholder="Nomor" data-required="1" value="<?php echo $data['no_bukti']; ?>" />
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Lampiran Bukti</div>
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
								<input type="file" onchange="showNameFile(this)" class="file_lampiran" <?php echo $data_required; ?> name="" placeholder="Bukti Transfer" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
								<i class="glyphicon glyphicon-paperclip cursor-p"></i>
							</label>
						</div>
					</div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<small>
						<table class="table table-bordered tbl_tagihan" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="col-xs-2 text-center">Transaksi</th>
									<th class="col-xs-2 text-center">No. Bayar</th>
									<th class="col-xs-4 text-center">Tagihan</th>
									<th class="col-xs-4 text-center">Bayar</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data['detail'] as $k_det => $v_det): ?>
									<tr>
										<td class="text-center transaksi" data-val="<?php echo $v_det['transaksi']; ?>"><?php echo $v_det['transaksi']; ?></td>
										<td class="text-center no_bayar" data-val="<?php echo $v_det['no_bayar']; ?>"><?php echo $v_det['no_bayar']; ?></td>
										<td class="text-right tagihan" data-val="<?php echo $v_det['tagihan']; ?>"><?php echo angkaDecimal($v_det['tagihan']); ?></td>
										<td class="text-right bayar" data-val="<?php echo $v_det['bayar']; ?>">
											<?php echo angkaDecimal($v_det['bayar']); ?>
											<!-- <input type="text" class="bayar form-control text-right" data-tipe="decimal" placeholder="Nilai" data-required="1" onblur="rp.hit_jml_bayar(this)" value="<?php echo angkaDecimal($v_det['bayar']); ?>"> -->
										</td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</small>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<?php if ( !empty($data['nomor']) ): ?>
						<button type="button" class="btn btn-primary pull-right" onclick="rp.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
					<?php else: ?>
						<button type="button" class="btn btn-primary pull-right" onclick="rp.save()"><i class="fa fa-save"></i> Simpan</button>
					<?php endif ?>
				</div>
			</form>
		</div>
	</div>
</div>