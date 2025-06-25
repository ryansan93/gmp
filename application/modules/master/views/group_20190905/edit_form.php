<div class="modal-header">
	<span class="modal-title"><b>Edit Group</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<!-- <h4 class="mb">Edit Group</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<div class="col-lg-6 d-flex align-items-center">
						<div class="col-md-3 no-padding d-flex align-items-center">Nama Group</div>
						<div class="col-md-9 no-padding">
							<input type="text" placeholder="Nama Group" id="nama_group" class="form-control" data-id="<?php echo $data_group['id_group']; ?>" value="<?php echo $data_group['nama_group']; ?>" data-required="1">
						</div>
					</div>

					<div class="col-lg-3">
						<button id="btn-edit" type="button" class="pull-left btn btn-primary cursor-p" title="EDIT" onclick="group.edit(this)"> 
							<i class="fa fa-pencil-square-o" aria-hidden="true"></i> EDIT
						</button>
					</div>

					<div class="col-lg-3">
						<div class="left-inner-addon pull-right"><i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="detail" placeholder="Search" onkeyup="filter_all(this)"></div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<table class="table table-bordered detail" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th rowspan="2" class="col-sm-3">Parent Fitur</th>
									<th rowspan="2" class="col-sm-3">Nama Fitur</th>
									<th rowspan="2">Path Fitur</th>
									<th colspan="6" class="text-center">Akses</th>
									<th rowspan="2" class="col-sm-1 text-center">
										<?php 
											$checked_all = null;
											$len_grp = count($data_group['detail_group']);
											$len_ftr = 0;

											foreach ($data_fitur as $key => $val){
												$len_ftr += count($val['detail_fitur']);
											}

											if ( $len_grp == $len_ftr ) {
												$checked_all = 'checked';
											}
										?>
										<input <?php echo $checked_all; ?> type="checkbox" class="check-fitur-all" data-target="check-fitur" onclick="group.mark_view_all(this)">
									</th>
								</tr>
								<tr>
									<th>View</th>
									<th>Submit</th>
									<th>Update</th>
									<th>Delete</th>
									<th>Ack</th>
									<th>Approve</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data_fitur as $key => $val): ?>
									<?php foreach ($val['detail_fitur'] as $key => $d_val): ?>
										<?php
											$checked = null;
											$checked_view = null;
											$checked_submit = null;
											$checked_edit = null;
											$checked_delete = null;
											$checked_ack = null;
											$checked_approve = null;
											foreach ($data_group['detail_group'] as $key => $dg_val) {
												if ( $d_val['id_detfitur'] == $dg_val['id_detfitur'] ) {
													$checked = 'checked';
													$checked_view = ($dg_val['a_view'] == 1) ? 'checked' : null;
													$checked_submit = ($dg_val['a_submit'] == 1) ? 'checked' : null;
													$checked_edit = ($dg_val['a_edit'] == 1) ? 'checked' : null;
													$checked_delete = ($dg_val['a_delete'] == 1) ? 'checked' : null;
													$checked_ack = ($dg_val['a_ack'] == 1) ? 'checked' : null;
													$checked_approve = ($dg_val['a_approve'] == 1) ? 'checked' : null;
												}
											}
										?>
										<tr class="search">
											<td class="parent_fitur">
												<?php echo $val['nama_fitur'] ?>
											</td>
											<td class="nama_fitur">
												<?php echo $d_val['nama_detfitur'] ?>
											</td>
											<td class="path_fitur">
												<?php echo $d_val['path_detfitur'] ?>
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-view" <?php echo $checked_view; ?> onclick="group.mark_view(this)">
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-submit" <?php echo $checked_submit; ?> >
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-update" <?php echo $checked_edit; ?> >
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-delete" <?php echo $checked_delete; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-ack" <?php echo $checked_ack; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" class="check-approve" <?php echo $checked_approve; ?>>
											</td>
											<td class="text-center">
												<input <?php echo $checked?> data-idftr="<?php echo $d_val['id_detfitur']; ?>" type="checkbox" class="check-fitur" data-parent="check-fitur-all" onclick="group.mark_view(this)">
											</td>
										</tr>
									<?php endforeach ?>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>