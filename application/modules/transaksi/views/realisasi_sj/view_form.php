<div class="form-group">
	<div class="col-md-12">
		<label class="control-label"><u>DATA SJ</u></label>
	</div>
</div>
<div class="form-group">
	<div class="col-md-12">
		<small>
			<table class="table table-bordered tbl_list_plg" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-md-2 text-center" rowspan="2">Nama Pelanggan</th>
						<th class="col-md-1 text-center" rowspan="2">No. DO</th>
						<th class="col-md-1 text-center" rowspan="2">No. SJ</th>
						<th class="text-center" colspan="4">Pengajuan</th>
						<th class="text-center" colspan="5">Realisasi</th>
						<th class="col-md-1 text-center" rowspan="2">No. Nota</th>
					</tr>
					<tr>
						<th class="col-md-1 text-center">Ekor</th>
						<th class="col-md-1 text-center">Tonase</th>
						<th class="col-md-1 text-center" style="width: 3%;">BB</th>
						<th class="col-md-1 text-center">Harga</th>
						<th class="col-md-1 text-center">Ekor</th>
						<th class="col-md-1 text-center">Tonase</th>
						<th class="col-md-1 text-center" style="width: 3%;">BB</th>
						<th class="col-md-1 text-center">Harga</th>
						<th class="col-md-1 text-center">Jenis Ayam</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($data_penjualan) ): ?>
						<?php foreach ($data_penjualan as $k_dp => $v_dp): ?>
							<?php foreach ($v_dp['det_rpah_real_sj'] as $k => $val): ?>
								<?php if ( $val['noreg'] == $noreg ): ?>
									<?php $idx_drs = 0; ?>
									<?php foreach ($val['data_real_sj'] as $k_drs => $v_drs): ?>
											<tr class="data" data-id="<?php echo $val['id']; ?>">
												<?php if ( $idx_drs == 0 ): ?>
													<td class="pelanggan" data-nomor="<?php echo $val['no_pelanggan']; ?>" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo $val['pelanggan']; ?></td>
													<td class="text-center no_do" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo $val['no_do']; ?></td>
													<td class="text-center no_sj" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo $val['no_sj']; ?></td>
													<td class="text-right" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo angkaRibuan($val['ekor']); ?></td>
													<td class="text-right" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo angkaDecimal($val['tonase']); ?></td>
													<td class="text-right" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo angkaDecimal($val['bb']); ?></td>
													<td class="text-right" rowspan="<?php echo count($val['data_real_sj']); ?>"><?php echo angkaDecimal($val['harga']); ?></td>
												<?php endif ?>
												<?php $idx_drs++; ?>
												<?php if ( $v_drs['id_header'] == $data_real_sj['id'] ): ?>
													<td class="text-right">
														<?php echo angkaRibuan($v_drs['ekor']); ?>
														<input type="text" class="form-control ekor text-right hide" data-tipe="integer" value="<?php echo angkaRibuan($v_drs['ekor']); ?>" onblur="real_sj.hit_bb(this)">
													</td>
												<td class="text-right">
														<?php echo angkaDecimal($v_drs['tonase']); ?>
														<input type="text" class="form-control tonase text-right hide" data-tipe="decimal" value="<?php echo angkaDecimal($v_drs['tonase']); ?>" onblur="real_sj.hit_bb(this)">
													</td>
													<td class="text-right">
														<?php echo angkaDecimal($v_drs['bb']); ?>
														<input type="text" class="form-control bb text-right hide" data-tipe="decimal" value="<?php echo angkaDecimal($v_drs['bb']); ?>" readonly>
													</td>
													<td class="text-right">
														<?php echo angkaDecimal($v_drs['harga']); ?>
														<input type="text" class="form-control harga text-right hide" data-tipe="decimal" value="<?php echo angkaDecimal($v_drs['harga']); ?>">
													</td>
													<td class="text-center">
														<?php 
															$ket = '-';
															foreach ($jenis_ayam as $k_ja => $v_ja) {
																if ( $k_ja == $v_drs['jenis_ayam'] ) {
																	$ket = $v_ja;
																}
															}
															echo $ket; 
														?>
													</td>
													<td class="text-left">
														<?php echo $v_drs['no_nota']; ?>
														<input type="text" class="form-control no_nota hide" maxlength="15" value="<?php echo $v_drs['no_nota']; ?>">
													</td>
												<?php endif ?>
											</tr>
									<?php endforeach ?>
								<?php endif ?>
							<?php endforeach ?>
						<?php endforeach ?>
					<?php else: ?>
						<tr>
							<td colspan="11">Data tidak ditemukan.</td>
						</tr>
					<?php endif ?>
				</tbody>
			</table>
		</small>
	</div>
</div>
<div class="form-group">
	<div class="col-md-1">
		<label class="control-label">Ekor</label>
	</div>
	<div class="col-md-2">
		<input type="text" class="form-control text-right" data-tipe="integer" placeholder="Ekor" data-required="1" value="<?php echo angkaRibuan($data_real_sj['ekor']); ?>" readonly>
	</div>
	<div class="col-md-1">
		<label class="control-label">Kg</label>
	</div>
	<div class="col-md-2">
		<input type="text" class="form-control text-right" data-tipe="decimal" placeholder="Kg" data-required="1" value="<?php echo angkaDecimal($data_real_sj['kg']); ?>" readonly>
	</div>
	<div class="col-md-1">
		<label class="control-label">BB</label>
	</div>
	<div class="col-md-1">
		<input type="text" class="form-control text-right" data-tipe="decimal" placeholder="BB" data-required="1" value="<?php echo angkaDecimal($data_real_sj['bb']); ?>" readonly>
	</div>
	<div class="col-md-1">
		<label class="control-label">Tara</label>
	</div>
	<div class="col-md-2">
		<input type="text" class="form-control text-right" data-tipe="decimal" placeholder="Tara Keranjang" data-required="1" value="<?php echo angkaDecimal($data_real_sj['tara']); ?>" readonly>
	</div>
</div>
<div class="form-group">
	<div class="col-md-12">
		<hr style="margin-top: 0px; margin-bottom: 0px;">
	</div>
</div>
<div class="form-group">
	<div class="col-md-1" style="padding-right: 0px;">
		<label class="control-label">Netto Ekor</label>
	</div>
	<div class="col-md-2">
		<input type="text" class="form-control text-right" data-tipe="integer" placeholder="Netto Ekor" data-required="1" value="<?php echo angkaRibuan($data_real_sj['netto_ekor']); ?>" readonly>
	</div>
	<div class="col-md-1">
		<label class="control-label">Netto Kg</label>
	</div>
	<div class="col-md-2">
		<input type="text" class="form-control text-right" data-tipe="decimal" placeholder="Netto Kg" data-required="1" value="<?php echo angkaDecimal($data_real_sj['netto_kg']); ?>" readonly>
	</div>
	<div class="col-md-1">
		<label class="control-label">BB Netto</label>
	</div>
	<div class="col-md-1">
		<input type="text" class="form-control text-right" data-tipe="decimal" placeholder="Netto BB" data-required="1" value="<?php echo angkaDecimal($data_real_sj['netto_bb']); ?>" readonly>
	</div>
</div>
<div class="form-group">
	<div class="col-md-12">
		<hr style="margin-top: 0px; margin-bottom: 0px;">
	</div>
</div>
<div class="form-group">
	<div class="col-md-6">
        <p>
            <b><u>Keterangan : </u></b>
            <?php
                if ( !empty($data_real_sj['logs']) ) {
                    foreach ($data_real_sj['logs'] as $key => $log) {
                        $temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
                    }
                    if ($temp) {
                        echo '<ul>' . implode("", $temp) . '</ul>';
                    }
                }
            ?>
        </p>
    </div>
	<div class="col-md-6">
	    <?php if ( $akses['a_delete'] == 1 ): ?>
	        <button type="button" class="btn btn-danger pull-right" onclick="real_sj.delete(this)" data-id="<?php echo $data_real_sj['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
	    <?php endif ?>
		<?php if ( $akses['a_edit'] == 1 ): ?>
	        <button type="button" class="btn btn-primary pull-right" onclick="real_sj.get_data(this)" data-resubmit="edit" style="margin-right: 10px;"><i class="fa fa-edit"></i> Update</button>
	    <?php endif ?>
	</div>
</div>