<div class="col-md-12 no-padding">
	<div class="col-lg-8 search left-inner-addon no-padding">
		<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_wilayah" placeholder="Search" onkeyup="filter_all(this)">
	</div>
	<div class="col-lg-4 action no-padding">
		<?php if ( $akses['a_submit'] == 1 ) { ?>
			<button id="btn-add" type="button" data-href="wilayah" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pkw.add_form(this)"> 
				<i class="fa fa-plus" aria-hidden="true"></i> ADD
			</button>
			<!-- <button id="btn-save" type="button" data-href="wilayah" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="pkw.save_doc(this)"> 
				<i class="fa fa-save" aria-hidden="true"></i> SAVE
			</button>

			<?php if ( $akses['a_edit'] == 1 ) { ?>
				<button id="btn-edit" type="button" data-href="wilayah" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="pkw.edit_doc(this)"> 
					<i class="fa fa-edit"></i> EDIT
				</button>
			<?php } ?> -->
		<?php } else { ?>
			<div class="col-lg-2 action no-padding pull-right">
				&nbsp
			</div>
		<?php } ?>
	</div>
	<table class="table table-bordered tbl_wilayah">
		<?php foreach ($data as $k_prov => $v_prov): ?>
			<thead>
				<tr>
					<th>Provinsi</th>
					<td colspan="2"> <?php echo $v_prov['nama']; ?> </td>
				</tr>
				<tr>
					<th class="col-sm-3">Kota / Kabupaten</th>
					<th class="col-sm-3">Kecamatan</th>
					<th class="col-sm-6">Kelurahan</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($v_prov['kota_kab'] as $k_kota_kab => $v_kota_kab): ?>
					<?php $cetak_kota_kab = true; ?>

						<?php foreach ($v_kota_kab['kecamatan'] as $k_kec => $v_kec): ?>
							<?php $cetak_kec = true; ?>

								<?php foreach ($v_kec['kelurahan'] as $k_kel => $v_kel): ?>
									<tr class="v-center">
										<?php if ( $cetak_kota_kab ): ?>
											<!-- <td rowspan="<?php echo $rowspan_kota_kab; ?>"> <?php echo $v_kota_kab['nama']; ?> </td> -->
											<td rowspan="<?php echo $v_kota_kab['rowspan_kota_kab']; ?>"> <?php echo $v_kota_kab['nama']; ?> </td>
										<?php endif ?>

										<?php if ( $cetak_kec ): ?>
											<!-- <td rowspan="<?php echo $rowspan_kec; ?>"> <?php echo $v_kec['nama']; ?> </td> -->
											<td rowspan="<?php echo $v_kec['rowspan_kec']; ?>"> <?php echo $v_kec['nama']; ?> </td>
										<?php endif ?>

										<td> <?php echo $v_kel['nama']; ?> </td>
									</tr>

									<?php $cetak_kota_kab = false; ?>
									<?php $cetak_kec = false; ?>
								<?php endforeach ?>
						<?php endforeach ?>
				<?php endforeach ?>
			</tbody>
		<?php endforeach ?>
	</table>
</div>