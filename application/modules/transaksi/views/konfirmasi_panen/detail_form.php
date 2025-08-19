<div class="modal-header">
	<span class="modal-title"><b>Detail Konfirmasi Panen</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Tanggal DOC In</h5>
					</div>
					<div class="col-lg-4 no-padding action">
					    <div class="input-group date" id="tgl_docin" name="tanggal-docin" data-tgl="<?php echo substr($data['tgl_docin'], 0, 10); ?>">
					        <input type="text" class="form-control text-center" data-required="1" readonly />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Tanggal Panen</h5>
					</div>
					<div class="col-lg-4 no-padding action">
					    <div class="input-group date" id="tgl_panen" name="tanggal-panen" data-tgl="<?php echo substr($data['data_konfir']['tgl_panen'], 0, 10); ?>">
					        <input type="text" class="form-control text-center" data-required="1" readonly />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Noreg</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-left noreg" data-required="1" value="<?php echo $data['noreg']; ?>" readonly />
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Populasi</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right populasi" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($populasi); ?>" readonly />
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>BB Rata2</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right bb_rata2" data-tipe="decimal" value="<?php echo angkaDecimal($data['data_konfir']['bb_rata2']); ?>" data-required="1" readonly />
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Total Sekat</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right tot_sekat" data-tipe="decimal" value="<?php echo angkaDecimal($data['data_konfir']['total']); ?>" data-required="1" readonly />
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5><u>Data Sekat</u></h5>
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-4 no-padding">
					<table class="table table-bordered data_sekat detail">
						<thead>
							<tr>
								<th class="col-md-1">No</th>
								<th class="col-md-8">Jumlah</th>
								<th class="col-md-4">BB</th>
							</tr>
						</thead>
						<tbody>
							<?php $tot_jumlah = 0; $tot_bb = 0; $no_urut = 0; ?>
							<?php foreach ($data['data_konfir']['det_konfir'] as $k => $val): ?>
								<?php $tot_jumlah += $val['jumlah']; $tot_bb += $val['bb']; $no_urut++; ?>
								<tr class="v-center">
									<td class="text-center no"><?php echo $no_urut; ?></td>
									<td><input type="text" class="form-control text-right jumlah" data-tipe="integer" onblur="kp.hitung_total(this);" value="<?php echo angkaRibuan($val['jumlah']); ?>" data-required="1" readonly /></td>
									<td>
										<input type="text" class="form-control text-right bb" data-tipe="decimal" onblur="kp.hitung_total(this);" value="<?php echo angkaDecimal($val['bb']); ?>" data-required="1" readonly />
										<div class="btn-ctrl" style="display: none;">
											<span onclick="kp.removeRow(this)" class="btn_del_row_2x"></span>
											<span onclick="kp.addRow(this)" class="btn_add_row_2x"></span>
										</div>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
						<tfoot>
							<tr>
								<td><b>Total</b></td>
								<td class="text-right tot_jumlah"><b><?php echo angkaRibuan($tot_jumlah); ?></b></td>
								<td class="text-right tot_bb"><b><?php echo angkaDecimal($tot_bb); ?></b></td>
							</tr>
						</tfoot>
					</table>

					<table class="table table-bordered data_sekat edit hide">
						<thead>
							<tr>
								<th class="col-md-1">No</th>
								<th class="col-md-8">Jumlah</th>
								<th class="col-md-4">BB</th>
							</tr>
						</thead>
						<tbody>
							<?php $tot_jumlah = 0; $tot_bb = 0; $no_urut = 0; ?>
							<?php foreach ($data['data_konfir']['det_konfir'] as $k => $val): ?>
								<?php $tot_jumlah += $val['jumlah']; $tot_bb += $val['bb']; $no_urut++; ?>
								<tr class="v-center">
									<td class="text-center no"><?php echo $no_urut; ?></td>
									<td><input type="text" class="form-control text-right jumlah" data-tipe="integer" onblur="kp.hitung_total(this);" value="<?php echo angkaRibuan($val['jumlah']); ?>" data-required="1" readonly /></td>
									<td>
										<input type="text" class="form-control text-right bb" data-tipe="decimal" onblur="kp.hitung_total(this);" value="<?php echo angkaDecimal($val['bb']); ?>" data-required="1" readonly />
										<div class="btn-ctrl" style="display: none;">
											<span onclick="kp.removeRow(this)" class="btn_del_row_2x"></span>
											<span onclick="kp.addRow(this)" class="btn_add_row_2x"></span>
										</div>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
						<tfoot>
							<tr>
								<td><b>Total</b></td>
								<td class="text-right tot_jumlah"><b><?php echo angkaRibuan($tot_jumlah); ?></b></td>
								<td class="text-right tot_bb"><b><?php echo angkaDecimal($tot_bb); ?></b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed no-padding">
			<hr>
		</div>
		<div class="col-lg-12 detailed">
			<div class="col-lg-6 keterangan no-padding">
				<p>
	                <b><u>Keterangan : </u></b>
	                <?php
	                    if ( !empty($data['data_konfir']['logs']) ) {
	                        foreach ($data['data_konfir']['logs'] as $key => $log) {
	                            $temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
	                        }
	                        if ($temp) {
	                            echo '<ul>' . implode("", $temp) . '</ul>';
	                        }
	                    }
	                ?>
	            </p>
	            <!-- <?php if (! empty($data['alasan_tolak'])): ?>
	                <p>
	                    <b><u>Alasan Reject :</u></b>
	                    <ul>
	                        <li><?php echo $data['alasan_tolak'] ?></li>
	                    </ul>
	                </p>
	            <?php endif; ?> -->
			</div>
			<div class="col-lg-6 no-padding">
				<form role="form" class="form-horizontal">
					<div class="col-md-12 no-padding update hide">
						<button type="button" class="btn btn-primary pull-right" style="margin: 0px 5px;" onclick="kp.update(this)" data-id="<?php echo $data['data_konfir']['id']; ?>"><i class="fa fa-save"></i> Update</button>
						<button type="button" class="btn btn-danger pull-right" style="margin: 0px 5px;" onclick="kp.edit_batal(this)"data-jenis="batal"><i class="fa fa-times"></i> Batal</button>
					</div>
					<div class="col-md-12 no-padding action">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<button type="button" class="btn btn-success pull-right" style="margin: 0px 5px;" onclick="kp.load_form(this);" data-noreg="<?php echo $data['data_konfir']['noreg']; ?>"><i class="fa fa-plus"></i> Tambah Baru</button>
						<?php endif ?>
						<?php if ( $akses['a_edit'] == 1 ): ?>
							<button type="button" class="btn btn-primary pull-right" style="margin: 0px 5px;" onclick="kp.edit_batal(this)" data-jenis="edit"><i class="fa fa-edit"></i> Edit</button>
						<?php endif ?>
						<?php if ( $akses['a_delete'] == 1 ): ?>
							<button type="button" class="btn btn-danger pull-right" style="margin: 0px 5px;" onclick="kp.delete(this)" data-id="<?php echo $data['data_konfir']['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
						<?php endif ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>