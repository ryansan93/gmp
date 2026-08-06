<?php foreach ($list as $key => $val) { ?>
	<tr class="head cursor-p search" title="Klik untuk melihat detail" data-val="0">
		<td class="col-sm-2 id_fitur"><?php echo $val['id_fitur']; ?></td>
		<td><?php echo $val['nama_fitur']; ?></td>
		<td class="col-sm-2 text-center">
			<?php if ( $akses['a_edit'] == 1 ): ?>
				<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="fitur.edit_form(this)">
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="fitur.delete(this)">
					<i class="fa fa-trash" aria-hidden="true"></i>
				</button>
			<?php endif ?>

		</td>
	</tr>
	<tr class="det hide">
		<td colspan="3">
			<?php if ( !empty($val['detail_fitur']) ): ?>
				<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th class="col-sm-2">Kode</th>
							<th class="col-sm-2">Nama</th>
							<th>Path Fitur</th>
							<th class="col-sm-1 text-center">Status</th>
							<?php if ( $akses['a_edit'] == 1 ): ?><th class="col-sm-1 text-center">Action</th><?php endif ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($val['detail_fitur'] as $key => $d_val): ?>
							<tr>
								<td><?php echo $d_val['id_detfitur']; ?></td>
								<td><?php echo $d_val['nama_detfitur']; ?></td>
								<td><?php echo $d_val['path_detfitur']; ?></td>
								<td class="text-center"><?php echo $d_val['status'] == 1 ? 'AKTIF' : 'NON AKTIF'; ?></td>
								<?php if ( $akses['a_edit'] == 1 ): ?>
									<td class="text-center">
										<button type="button" class="btn btn-sm <?php echo $d_val['status'] == 1 ? 'btn-warning' : 'btn-success'; ?> cursor-p" title="<?php echo $d_val['status'] == 1 ? 'NON-AKTIFKAN' : 'AKTIFKAN'; ?>" data-iddet="<?php echo $d_val['id_detfitur']; ?>" onclick="fitur.toggle_status_detail(this)">
											<i class="fa <?php echo $d_val['status'] == 1 ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" aria-hidden="true"></i>
										</button>
									</td>
								<?php endif ?>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			<?php endif ?>
			<?php if ( !empty($val['sub_fitur']) ): ?>
				<?php foreach ($val['sub_fitur'] as $s_key => $s_val): ?>
					<div style="padding: 8px 0 4px 0;" class="clearfix">
						<span class="id_fitur hide"><?php echo $s_val['id_fitur']; ?></span>
						<b><i class="fa fa-level-up fa-rotate-90"></i> <?php echo $s_val['nama_fitur']; ?></b> <small class="text-muted">(<?php echo $s_val['id_fitur']; ?>)</small>
						<span class="pull-right">
							<?php if ( $akses['a_edit'] == 1 ): ?>
								<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="fitur.edit_form(this)">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</button>
							<?php endif ?>
							<?php if ( $akses['a_delete'] == 1 ): ?>
								<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="fitur.delete(this)">
									<i class="fa fa-trash" aria-hidden="true"></i>
								</button>
							<?php endif ?>
						</span>
					</div>
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th class="col-sm-2">Kode</th>
								<th class="col-sm-2">Nama</th>
								<th>Path Fitur</th>
								<th class="col-sm-1 text-center">Status</th>
								<?php if ( $akses['a_edit'] == 1 ): ?><th class="col-sm-1 text-center">Action</th><?php endif ?>
							</tr>
						</thead>
						<tbody>
							<?php if ( !empty($s_val['detail_fitur']) ): ?>
								<?php foreach ($s_val['detail_fitur'] as $d_key => $d_val): ?>
									<tr>
										<td><?php echo $d_val['id_detfitur']; ?></td>
										<td><?php echo $d_val['nama_detfitur']; ?></td>
										<td><?php echo $d_val['path_detfitur']; ?></td>
										<td class="text-center"><?php echo $d_val['status'] == 1 ? 'AKTIF' : 'NON AKTIF'; ?></td>
										<?php if ( $akses['a_edit'] == 1 ): ?>
											<td class="text-center">
												<button type="button" class="btn btn-sm <?php echo $d_val['status'] == 1 ? 'btn-warning' : 'btn-success'; ?> cursor-p" title="<?php echo $d_val['status'] == 1 ? 'NON-AKTIFKAN' : 'AKTIFKAN'; ?>" data-iddet="<?php echo $d_val['id_detfitur']; ?>" onclick="fitur.toggle_status_detail(this)">
													<i class="fa <?php echo $d_val['status'] == 1 ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" aria-hidden="true"></i>
												</button>
											</td>
										<?php endif ?>
									</tr>
								<?php endforeach ?>
							<?php else: ?>
								<tr>
									<td colspan="5">Belum ada item.</td>
								</tr>
							<?php endif ?>
						</tbody>
					</table>
				<?php endforeach ?>
			<?php endif ?>
		</td>
	</tr>
<?php } ?>
