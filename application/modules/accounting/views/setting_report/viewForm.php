<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Laporan : <?php echo $data['nama']; ?></label></div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<tbody>
					<?php foreach ($data['group'] as $k_group => $v_group): ?>
						<tr class="group">
							<td class="col-xs-12">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Group : <?php echo $v_group['nama']; ?></label></div>
								</div>
							</td>
						</tr>
						<tr class="item-group">
							<td style="background-color: #ededed;">
								<small>
									<table class="table table-bordered" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th class="col-xs-1">Urutan</th>
												<th class="col-xs-2">Item</th>
												<th class="col-xs-2">Nama COA</th>
												<th class="col-xs-1">No. COA</th>
												<th class="col-xs-2">Posisi Laporan</th>
												<th class="col-xs-2">Posisi Jurnal</th>
												<th class="col-xs-1">Posisi Data</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($v_group['urut'] as $k_urut => $v_urut): ?>
												<?php foreach ($v_urut['item'] as $k_item => $v_item): ?>
													<tr>
														<td class="text-center">
															<?php echo $k_urut; ?>
														</td>
														<td>
															<?php echo $v_item['nama_item']; ?>
														</td>
														<td>
															<?php echo $v_item['nama_coa']; ?>
														</td>
														<td class="coa text-center">
															<?php echo $v_item['coa']; ?>
														</td>
														<td>
															<?php echo strtoupper($v_item['posisi']); ?>
														</td>
														<td>
															<?php echo !empty($v_item['posisi_jurnal']) ? strtoupper($v_item['posisi_jurnal']) : '-'; ?>
														</td>
														<td>
															<?php echo !empty($v_item['posisi_data']) ? strtoupper($v_item['posisi_data']) : '-'; ?>
														</td>
													</tr>
												<?php endforeach ?>
											<?php endforeach ?>
										</tbody>
									</table>
								</small>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<button type="button" class="col-xs-12 btn btn-danger" onclick="sr.delete(this)" data-id="<?php echo $data['id'] ?>"><i class="fa fa-trash"></i> Hapus</button>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.changeTabActive(this)" data-edit="edit" data-href="action" data-id="<?php echo $data['id'] ?>"><i class="fa fa-edit"></i> Edit</button>
		</div>
	</div>
</div>