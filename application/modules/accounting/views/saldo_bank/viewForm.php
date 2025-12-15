<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Saldo Bank</label></div>
	<div class="col-xs-9 no-padding">
		<label class="control-label">: <?php echo strtoupper(tglIndonesia($data[0]['tanggal'], '-', ' ')); ?></label>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-3 no-padding"><label class="control-label">Total</label></div>
	<div class="col-xs-9 no-padding">
		<label class="control-label">: <?php echo angkaDecimal($data[0]['total']); ?></label>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="overflow-x: auto;">
		<small>
			<table class="table table-bordered tbl_detail" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
				<thead>
					<tr>
						<th class="col-xs-2">COA</th>
						<th class="col-xs-3">Nama COA</th>
						<th class="col-xs-2">Saldo</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($data) ) { ?>
						<?php foreach ($data as $k_det => $v_det) { ?>
							<tr class="data" data-urut="">
								<td><?php echo strtoupper($v_det['coa']); ?></td>
								<td><?php echo strtoupper($v_det['nama_coa']); ?></td>
								<td class="text-right">
									<?php echo angkaDecimal($v_det['saldo_akhir']); ?>
								</td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="3">Data tidak ditemukan.</td>
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
		&nbsp;
		<!-- <div class="col-xs-12 no-padding"><label class="control-label"><u>Keterangan</u></label></div>
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
		</div> -->
	</div>
	<div class="col-xs-6 no-padding lock_btn_fiskal" data-date="<?php echo substr($data[0]['tanggal'], 0, 10); ?>">
		<?php if ( $akses['a_edit'] == 1 ) { ?>
			<button type="button" class="btn btn-primary pull-right btn_tutup_bulan" onclick="sb.changeTabActive(this)" data-href="action" data-edit="edit" data-tanggal="<?php echo $data[0]['tanggal']; ?>" style="margin-left: 5px;">
				<i class="fa fa-edit"></i>
				Edit
			</button>
		<?php } ?>
		<?php if ( $akses['a_delete'] == 1 ) { ?>
			<button type="button" class="btn btn-danger pull-right btn_tutup_bulan" onclick="sb.delete(this)" data-tanggal="<?php echo $data[0]['tanggal']; ?>" style="margin-right: 5px;">
				<i class="fa fa-trash"></i>
				Delete
			</button>
		<?php } ?>
	</div>
</div>