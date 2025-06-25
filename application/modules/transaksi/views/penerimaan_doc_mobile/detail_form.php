<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['mitra']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['noreg']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding no_order" data-val="<?php echo $data['no_order']; ?>" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Order</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['no_order']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. SJ</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: 
						<?php if ( !empty($data['lampiran_sj']) ): ?>
							<a href="uploads/<?php echo $data['lampiran_sj']; ?>" name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;"><?php echo strtoupper($data['no_sj']); ?></a>
						<?php else: ?>
							<?php echo strtoupper($data['no_sj']); ?>
						<?php endif ?>
					</label>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Kirim</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control tanggal_kirim" placeholder="Kirim" data-required="1" value="<?php echo strtoupper(tglIndonesia($data['kirim'], '-', ' ')); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Tiba</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control tanggal_kirim" placeholder="Kirim" data-required="1" value="<?php echo strtoupper(tglIndonesia(substr($data['tiba'], 0, 10), '-', ' ').' '.substr($data['tiba'], 11, 5)); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Polisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control nopol uppercase" placeholder="No. Polisi" data-required="1" value="<?php echo strtoupper($data['nopol']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Kondisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control kondisi uppercase" placeholder="Kondisi" data-required="1" value="<?php echo strtoupper($data['kondisi']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Jumlah Ekor</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right ekor" placeholder="Ekor" data-tipe="integer" data-required="1"  value="<?php echo angkaRibuan($data['ekor']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-4 no-padding" style="margin-bottom: 5px; padding-right: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Jumlah Box</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right box" placeholder="Box" data-tipe="integer" data-required="1"  value="<?php echo angkaRibuan($data['box']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-2 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">BB</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bb" placeholder="BB" data-tipe="decimal3" data-required="1"  value="<?php echo angkaDecimalFormat($data['bb'], 3); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Uniformity (%)</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right uniformity" placeholder="Uniformity" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['uniformity']); ?>" disabled />
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Keterangan DOC</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered ket_doc" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-9">Keterangan</th>
							<th class="col-xs-3">Lampiran</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data['data_ket']) ): ?>
							<?php foreach ($data['data_ket'] as $k_dk => $v_dk): ?>
								<tr class="v-center">
									<td class="text-left">
										<?php echo strtoupper($v_dk['keterangan']); ?>
									</td>
									<td class="text-center" style="vertical-align: top;">
										<?php if ( !empty( $v_dk['lampiran'] ) ): ?>
								        	<a href="uploads/<?php echo $v_dk['lampiran']; ?>" name="dokumen" class="text-right" target="_blank"><i class="fa fa-file"></i></a>
								        <?php else: ?>
								        	-
										<?php endif ?>
									</td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="2">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="btn btn-primary col-xs-12" onclick="pdm.change_tab(this)" data-id="<?php echo $data['no_order']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger col-xs-12" onclick="pdm.delete()"><i class="fa fa-trash"></i> Hapus</button>
			</div>
		</form>
	</div>
</div>
