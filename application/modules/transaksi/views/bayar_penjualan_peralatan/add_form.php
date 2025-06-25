<div class="modal-header">
	<span class="modal-title"><b>TAMBAH PEMBAYARAN PERALATAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding: 0px 15px 15px 15px;">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Tagihan</label>
					</div>
					<div class="col-xs-10 no-padding tagihan" data-val="<?php echo $data['tagihan']; ?>">
						<label class="control-label">: <?php echo angkaDecimal($data['tagihan']); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Saldo</label>
					</div>
					<div class="col-xs-10 no-padding saldo" data-val="<?php echo $data['saldo']; ?>">
						<label class="control-label">: <?php echo angkaDecimal($data['saldo']); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding"><hr style="margin-bottom: 10px; margin-top: 10px;"></div>

				<div class="col-xs-12 no-padding">
					<small>
						<table class="table table-bordered tbl_bayar" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="col-xs-3">Tgl Bayar</th>
									<th class="col-xs-4">Jumlah Bayar</th>
									<!-- <th class="col-xs-3">Lampiran</th> -->
								</tr>
							</thead>
							<tbody>
								<tr class="baru belum_lunas">
									<td>
										<div class="col-xs-12 no-padding" style="padding-right: 5px;">
											<div class="input-group date datetimepicker tgl_bayar">
										        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
										        <span class="input-group-addon">
										            <span class="glyphicon glyphicon-calendar"></span>
										        </span>
										    </div>
										</div>
									</td>
									<td>
										<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" data-required="1" placeholder="Jumlah">
									</td>
									<!-- <td></td> -->
								</tr>
							</tbody>
						</table>
					</small>
				</div>

				<div class="col-xs-12 no-padding"><hr style="margin-bottom: 10px; margin-top: 10px;"></div>

				<div class="col-xs-12 no-padding">
					<button type="button" class="btn btn-primary pull-right" onclick="bpp.save(this)" data-idjual="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>