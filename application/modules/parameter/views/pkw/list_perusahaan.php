<div class="col-md-12 no-padding">
	<div class="col-lg-8 search left-inner-addon no-padding">
		<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_perusahaan" placeholder="Search" onkeyup="filter_all(this)">
	</div>
	<div class="col-lg-4 action no-padding">
		<?php if ( $akses['a_submit'] == 1 ) { ?>
			<button id="btn-add" type="button" data-href="perusahaan" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pkw.add_form(this)"> 
				<i class="fa fa-plus" aria-hidden="true"></i> ADD
			</button>
			<!-- <button id="btn-save" type="button" data-href="perusahaan" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="pkw.save_feed(this)"> 
				<i class="fa fa-save" aria-hidden="true"></i> SAVE
			</button>

			<?php if ( $akses['a_edit'] == 1 ) { ?>
				<button id="btn-edit" type="button" data-href="perusahaan" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="pkw.edit_feed(this)"> 
					<i class="fa fa-edit"></i> EDIT
				</button>
			<?php } ?> -->
		<?php } else { ?>
			<div class="col-lg-2 action no-padding pull-right">
				&nbsp
			</div>
		<?php } ?>
	</div>
	<table class="table table-bordered tbl_perusahaan">
		<thead>
			<tr>
				<th class="col-sm-1">Kode</th>
				<th class="col-sm-2">Perusahaan</th>
				<th class="col-sm-2">Alamat</th>
				<th class="col-sm-1">Kota</th>
				<th class="col-sm-2">NPWP</th>
				<th class="col-sm-4">Status</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( !empty($data)) : ?>
				<?php foreach ($data as $k => $v_perusahaan): ?>
					<?php 
						$resubmit = null;
						if ( $v_perusahaan['status'] == 4 ) {
							$resubmit = $v_perusahaan['id'];
						}
					?>

					<?php 
						$red = null;
						if ( $akses['a_ack'] == 1 ){
							$status = getStatus(1);
							if ( $v_perusahaan['status'] == $status ) {
								$red = 'red';
							}
						} else if ( $akses['a_approve'] == 1 ){
							$status = getStatus(2);
							if ( $v_perusahaan['status'] == $status ) {
								$red = 'red';
							}
						} else {

						}
					?>
					<tr class="search">
						<td><?php echo $v_perusahaan['kode']; ?></td>
						<td><?php echo $v_perusahaan['perusahaan'] ?></td>
						<td><?php echo $v_perusahaan['alamat'] ?></td>
						<td><?php echo $v_perusahaan['d_kota']['nama'] ?></td>
						<td><?php echo $v_perusahaan['npwp'] ?></td>
						<td>
							<div class="col-sm-11 no-padding">
								<?php 
									$last_log = $v_perusahaan['logs'][ count($v_perusahaan['logs']) - 1 ];
									$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
									echo $keterangan;
								?>
							</div>
							<div class="col-sm-1 no-padding">
								<?php if ( $akses['a_edit'] == 1 ){ ?>
									<button id="btn-add" type="button" data-href="perusahaan" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pkw.edit_form(this)" data-id="<?php echo $v_perusahaan['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>"> 
										<i class="fa fa-edit" aria-hidden="true"></i>
									</button>
								<?php } ?>
							</div>
						</td>
					</tr>
				<?php endforeach ?>
			<?php else: ?>
					<tr>
						<td class="text-center" colspan="6">Data tidak ditemukan.</td>
					</tr>
			<?php endif ?>
		</tbody>
	</table>
</div>