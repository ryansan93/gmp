<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="head" data-idkonfir="<?php echo $val['data_konfir']['id']; ?>">
			<td class="text-center">
				<input type="checkbox" name="pilih" class="cursor-p">
			</td>
			<td><?php echo strtoupper($val['mitra']); ?></td>
			<td class="noreg"><?php echo $val['data_konfir']['noreg']; ?></td>
			<td class="text-center kandang" data-unit="<?php echo strtoupper($val['unit']); ?>"><?php echo $val['kandang']; ?></td>
			<td class="text-right head_tot_ekor">
				<?php
					$tot_ekor = 0;
					foreach ($val['data_konfir']['det_konfir'] as $k_dk => $v_dk) {
						$tot_ekor += $v_dk['jumlah'];
					}
					echo angkaDecimal($tot_ekor); 
				?>
			</td>
			<td class="text-right head_tot_kg"><?php echo angkaDecimal($val['data_konfir']['total']); ?></td>
		</tr>
		<tr class="detail">
			<td colspan="6" style="padding-right: 40px;">
				<table class="table table-bordered detail">
					<thead>
						<tr>
							<th class="col-md-1">No. DO</th>
							<th class="col-md-1">No. SJ</th>
							<th class="col-md-3">Nama Pelanggan</th>
							<th class="col-md-1">Outstanding</th>
							<th class="col-md-1">Ekor</th>
							<th class="col-md-1">Tonase</th>
							<th class="col-md-1">BB</th>
							<th class="col-md-1">Harga</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="text" class="form-control no_do" readonly>
							</td>
							<td>
								<input type="text" class="form-control no_sj" readonly>
							</td>
							<td>
								<select class="form-control pelanggan" data-required="1">
									<option value="">Pilih Pelanggan</option>
									<?php if ( !empty($data_pelanggan) ): ?>
										<?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
											<option value="<?php echo $v_dp['nomor']; ?>">
												<?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?>
											</option>
										<?php endforeach ?>
									<?php endif ?>
								</select>
							</td>
							<td>
								<input type="text" class="form-control text-right outstanding" placeholder="OUTSTANDING" value="0" readonly>
							</td>
							<td>
								<input type="text" class="form-control text-right ekor" placeholder="EKOR" data-tipe="integer" data-required="1" onblur="rpah.hit_bb(this)">
							</td>
							<td>
								<input type="text" class="form-control text-right tonase" placeholder="TONASE" data-tipe="decimal" data-required="1" onblur="rpah.hit_bb(this)">
							</td>
							<td>
								<input type="text" class="form-control text-right bb" placeholder="BB" data-tipe="decimal" data-required="1" readonly>
							</td>
							<td>
								<input type="text" class="form-control text-right harga" placeholder="HARGA" data-tipe="integer" data-required="1">
								<div class="btn-ctrl">
									<span onclick="rpah.removeRow(this)" class="btn_del_row_2x"></span>
									<span onclick="rpah.addRow(this)" class="btn_add_row_2x"></span>
								</div>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="4"><b>Total</b></td>
							<td class="text-right detail_tot_ekor"><b>0</b></td>
							<td class="text-right detail_tot_kg"><b>0</b></td>
							<td class="text-right detail_tot_bb"><b>0</b></td>
							<td colspan="2"></td>
						</tr>
					</tfoot>
				</table>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Belum ada data konfirmasi panen.</td>
	</tr>
<?php endif ?>
