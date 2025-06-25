<?php // cetak_r($data); ?>

<div class="col-md-12 no-padding">
	<div class="col-lg-8 search left-inner-addon no-padding">
		<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_korwil" placeholder="Search" onkeyup="filter_all(this)">
	</div>
	<div class="col-lg-4 action no-padding">
		<?php if ( $akses['a_submit'] == 1 ) { ?>
			<button id="btn-add" type="button" data-href="korwil" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pkw.add_form(this)"> 
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
	<table class="table table-bordered tbl_korwil">
		<tbody>
			<?php foreach ($data as $k_data => $v_data): ?>
				<tr>
					<th>Negara</th>
					<td colspan="2"><?php echo $v_data['nama']; ?></td>
				</tr>
				<tr>
					<th class="col-md-3">Korwil</th>
					<th class="col-md-7">Kota</th>
					<th class="col-md-2">Kode</th>
				</tr>

				<?php if ( count($v_data['perwakilan']) > 0 ): ?>
					<?php foreach ($v_data['perwakilan'] as $k_pwk => $v_pwk): ?>
						<?php $cetak_pwk = true; ?>
						<?php if ( count($v_pwk['unit']) > 0 ) { ?>
							<?php foreach ($v_pwk['unit'] as $k_unit => $v_unit): ?>
								<tr class="v-center">
									<?php if ( $cetak_pwk ): ?>
										<td rowspan="<?php echo $v_pwk['rowspan_pwk']; ?>" ><?php echo $v_pwk['nama']; ?></td>
									<?php endif ?>
									<td><?php echo $v_unit['nama']; ?></td>
									<td><?php echo $v_unit['kode']; ?></td>
								</tr>
								<?php $cetak_pwk = false; ?>
							<?php endforeach ?>
						<?php } else { ?>
							<tr class="v-center">
								<td><?php echo $v_pwk['nama']; ?></td>
								<td>-</td>
								<td>-</td>
							</tr>
							<?php $cetak_pwk = false; ?>
						<?php } ?>
					<?php endforeach ?>
				<?php endif ?>
			<?php endforeach ?>
		</tbody>
	</table>
</div>