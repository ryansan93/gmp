<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Memo</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['no_mm']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Memo</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_mm'], '-', ' ')); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Customer</label></div>
		<div class="col-xs-7 no-padding">
			<label class="control-label">: <?php echo !empty($data['kode_cust']) ? $data['nama_cust'] : '-'; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Supplier</label></div>
		<div class="col-xs-7 no-padding">
			<label class="control-label">: <?php echo !empty($data['kode_supl']) ? $data['nama_supl'] : '-'; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Keterangan</label></div>
		<div class="col-xs-9 no-padding">
			<label class="control-label">: <?php echo !empty($data['keterangan']) ? $data['keterangan'] : '-'; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-5 no-padding" style="padding-left: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total Debet</label></div>
		<div class="col-xs-6 no-padding nilai">
			<div class="col-xs-1 no-padding"><label class="control-label">:</label></div>
			<div class="col-xs-11 no-padding text-right">
				<label class="control-label"><?php echo formatAngka($data['tot_debet']); ?></label>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total Kredit</label></div>
		<div class="col-xs-6 no-padding nilai">
			<div class="col-xs-1 no-padding"><label class="control-label">:</label></div>
			<div class="col-xs-11 no-padding text-right">
				<label class="control-label"><?php echo formatAngka($data['tot_kredit']); ?></label>
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
						<th style="width: 10%;">No. COA</th>
						<th style="width: 20%;">Nama. COA</th>
						<th style="width: 20%;">Keterangan</th>
						<th style="width: 13%;">No. Faktur</th>
						<th style="width: 13%;">No. Invoice</th>
						<th style="width: 9%;">Debet</th>
						<th style="width: 9%;">Kredit</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($detail) ) { ?>
						<?php foreach ($detail as $k_det => $v_det) { ?>
							<tr class="data" data-urut="">
								<td>
									<?php echo strtoupper($v_det['no_coa']); ?>
								</td>
								<td>
									<?php echo strtoupper($v_det['nama_coa']); ?>
								</td>
								<td>
									<?php echo !empty($v_det['keterangan']) ? strtoupper($v_det['keterangan']) : '-'; ?>
								</td>
								<td>
									<?php echo !empty($v_det['no_faktur']) ? strtoupper($v_det['no_faktur']) : '-'; ?>
								</td>
								<td>
									<?php echo !empty($v_det['no_inv']) ? strtoupper($v_det['no_inv']) : '-'; ?>
								</td>
								<td class="text-right">
									<?php echo formatAngka($v_det['debet']); ?>
								</td>
								<td class="text-right">
									<?php echo formatAngka($v_det['kredit']); ?>
								</td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="7">Data tidak ditemukan.</td>
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
	<div class="col-xs-6 no-padding div_tutup_bulan" data-val="<?php echo substr($data['tgl_mm'], 0, 10); ?>">
		<?php if ( $akses['a_edit'] == 1 ) { ?>
			<button type="button" class="btn btn-primary pull-right btn_tutup_bulan" onclick="mm.changeTabActive(this)" data-href="action" data-edit="edit" data-kode="<?php echo $data['no_mm']; ?>" style="margin-left: 5px;">
				<i class="fa fa-edit"></i>
				Edit
			</button>
		<?php } ?>
		<?php if ( $akses['a_delete'] == 1 ) { ?>
			<button type="button" class="btn btn-danger pull-right btn_tutup_bulan" onclick="mm.delete(this)" data-kode="<?php echo $data['no_mm']; ?>" style="margin-right: 5px;">
				<i class="fa fa-trash"></i>
				Delete
			</button>
		<?php } ?>
		<?php if ( $akses['a_edit'] == 1 || $akses['a_delete'] == 1) { ?>
			<label class="control-label pull-right btn_tutup_bulan" style="padding-left: 10px; padding-right: 10px;">|</label>
			<!-- <div style="width: 1%; border: 1px solid black;"></div> -->
			<button type="button" class="btn btn-default pull-right" onclick="mm.printPreview(this)" data-kode="<?php echo exEncrypt($data['no_mm']); ?>"><i class="fa fa-print"></i> Cetak</button>
			<!-- <button type="button" class="btn btn-default pull-right" onclick="mm.exportPdf(this)" data-kode="<?php echo exEncrypt($data['no_mm']); ?>"><i class="fa fa-print"></i> Cetak</button> -->
		<?php } ?>
	</div>
</div>