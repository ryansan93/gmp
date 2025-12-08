<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Kas Masuk</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['no_km']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Voucher</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['jurnal_trans_nama']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['unit']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Kas Masuk</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_km'], '-', ' ')); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Asal</label></div>
		<div class="col-xs-7 no-padding">
			<label class="control-label">: <?php echo !empty($data['pelanggan']) ? $data['pelanggan'] : '-'; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Keterangan</label></div>
		<div class="col-xs-9 no-padding">
			<label class="control-label">: <?php echo !empty($data['keterangan']) ? $data['keterangan'] : '-'; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Nama Kas</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo !empty($data['nama_bank']) ? $data['nama_bank'] : '-'; ?></label>
		</div>
	</div>
	<!-- <div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Giro</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo !empty($data['no_giro']) ? $data['no_giro'] : '-'; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Tempo</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_tempo'], '-', ' ')); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Cair</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_cair'], '-', ' ')); ?></label>
		</div>
	</div> -->
</div>
<div class="col-xs-5 no-padding" style="padding-left: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total</label></div>
		<div class="col-xs-6 no-padding nilai">
			<div class="col-xs-1 no-padding"><label class="control-label">:</label></div>
			<div class="col-xs-11 no-padding text-right">
				<label class="control-label"><?php echo angkaDecimal($data['nilai']); ?></label>
			</div>
		</div>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="overflow-x: auto;">
		<small>
			<table class="table table-bordered tbl_detail" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
				<thead>
					<tr>
						<th class="col-xs-2">Transaksi</th>
						<th class="col-xs-2">COA</th>
						<th class="col-xs-3">Keterangan</th>
						<th class="col-xs-1">No. Invoice</th>
						<!-- <th>Nilai Invoice</th> -->
						<th class="col-xs-2">Nilai</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($detail) ) { ?>
						<?php foreach ($detail as $k_det => $v_det) { ?>
							<tr class="data" data-urut="">
								<td><?php echo strtoupper($v_det['det_jurnal_trans_nama']); ?></td>
								<td><?php echo strtoupper($v_det['coa_asal'].' | '.$v_det['coa_asal_nama']); ?></td>
								<td><?php echo !empty($v_det['keterangan']) ? strtoupper($v_det['keterangan']) : '-'; ?></td>
								<td><?php echo strtoupper($v_det['no_invoice']); ?></td>
								<td class="text-right">
									<?php echo angkaDecimal($v_det['nilai']); ?>
								</td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="6">Data tidak ditemukan.</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</small>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr></div>

<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label"><u>Keterangan</u></label></div>
		<div class="col-xs-12 no-padding list_ket">
			<ul>
				<?php if ( !empty($log) ) { ?>
					<?php foreach ($log as $k_lt => $v_lt) { ?>
						<li>
							<?php
								$ket = $v_lt['deskripsi'].' '.substr($v_lt['waktu'], 0, 10).' '.substr($v_lt['waktu'], 11, 5);
								echo $ket;
							?>
						</li>
					<?php } ?>
				<?php } else { ?>
					<li>-</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="col-xs-6 no-padding lock_btn_fiskal" data-date="<?php echo substr($data['tgl_km'], 0, 10); ?>">
		<?php if ( $akses['a_edit'] == 1 ) { ?>
			<button type="button" class="btn btn-primary pull-right btn_tutup_bulan" onclick="km.changeTabActive(this)" data-href="action" data-edit="edit" data-kode="<?php echo $data['no_km']; ?>" style="margin-left: 5px;">
				<i class="fa fa-edit"></i>
				Edit
			</button>
		<?php } ?>
		<?php if ( $akses['a_delete'] == 1 ) { ?>
			<button type="button" class="btn btn-danger pull-right btn_tutup_bulan" onclick="km.delete(this)" data-kode="<?php echo $data['no_km']; ?>" style="margin-right: 5px;">
				<i class="fa fa-trash"></i>
				Delete
			</button>
		<?php } ?>
		<?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1) { ?>
			<label class="control-label pull-right btn_tutup_bulan" style="padding-left: 10px; padding-right: 10px;">|</label>
			<!-- <div style="width: 1%; border: 1px solid black;"></div> -->
			<button type="button" class="btn btn-default pull-right cetak" onclick="km.printPreview(this)" data-kode="<?php echo exEncrypt($data['no_km']); ?>"><i class="fa fa-print"></i> Cetak</button>
			<!-- <button type="button" class="btn btn-default pull-right" onclick="km.exportPdf(this)" data-kode="<?php echo exEncrypt($data['no_km']); ?>"><i class="fa fa-print"></i> Cetak</button> -->
		<?php } ?>
	</div>
</div>