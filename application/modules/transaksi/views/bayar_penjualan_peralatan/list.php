<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p header" onclick="bpp.detail_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td class="text-center"><?php echo tglIndonesia($v_data['tanggal'], '-', ' '); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['sisa']); ?></td>
			<td class="text-center">
				<?php
					$hide = 'hide';
					$red = 'blue';
					if ( stristr($v_data['status'], 'belum') !== FALSE ) {
						$red = 'red';
						$hide = '';
					}
				?>
				<label class="control-label" style="padding-top: 0px; color: <?php echo $red; ?>"><?php echo strtoupper($v_data['status']); ?></label>
			</td>
		</tr>
		<tr class="detail">
			<td colspan="4" style="background-color: #dedede;">
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-2" style="background-color: #b8cdff;">Tgl Bayar</th>
							<th class="col-xs-2" style="background-color: #b8cdff;">Jenis Bayar</th>
							<th class="col-xs-3" style="background-color: #b8cdff;">Saldo</th>
							<th class="col-xs-3" style="background-color: #b8cdff;">Jml Bayar</th>
							<th class="col-xs-2" style="background-color: #b8cdff;"></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($v_data['d_bayar']) ): ?>
							<?php foreach ($v_data['d_bayar'] as $k_db => $v_db): ?>
								<tr>
									<td class="text-center"><?php echo tglIndonesia($v_db['tanggal'], '-', ' '); ?></td>
									<td class="text-left"><?php echo strtoupper($v_db['jenis_bayar']); ?></td>
									<td class="text-right"><?php echo angkaDecimal($v_db['saldo']); ?></td>
									<td class="text-right"><?php echo angkaDecimal($v_db['bayar']); ?></td>
									<td class="text-center">
										<?php if ( !empty($v_db['id']) ) { ?>
											<button type="button" class="btn btn-sm btn-danger cursor-p" title="HAPUS" onclick="bpp.delete(this)" data-id="<?php echo $v_db['id']; ?>">
												<i class="fa fa-trash" aria-hidden="true"></i> 
											</button>
										  <button type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="bpp.edit_form(this)" data-id="<?php echo $v_db['id']; ?>"> 
											  <i class="fa fa-edit" aria-hidden="true"></i> 
										  </button>
										<?php } ?>
									</td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="4">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
						<tr class="<?php echo $hide; ?>">
							<td colspan="4">
								<button type="button" class="btn btn-primary" onclick="bpp.add_form(this)" data-id="<?php echo $v_data['id']; ?>"><i class="fa fa-plus"></i> Tambah Pembayaran</button>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>