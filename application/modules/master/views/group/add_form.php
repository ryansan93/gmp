<div class="modal-header">
	<span class="modal-title"><b>Add Group</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<!-- <h4 class="mb">Add Group</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<div class="col-lg-6 d-flex align-items-center">
						<div class="col-md-3 no-padding d-flex align-items-center">Nama Group</div>
						<div class="col-md-9 no-padding">
							<input type="text" placeholder="Nama Group" id="nama_group" class="form-control" data-required="1">
						</div>
					</div>

					<div class="col-lg-3">
						<button id="btn-add" type="button" class="pull-left btn btn-primary cursor-p" title="SAVE" onclick="group.save(this)"> 
							<i class="fa fa-save" aria-hidden="true"></i> SAVE
						</button>
					</div>

					<div class="col-lg-3">
						<div class="left-inner-addon pull-right"><i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="detail" placeholder="Search" onkeyup="filter_all(this)"></div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<small>
							<table class="table table-bordered detail" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th rowspan="2" class="col-sm-3">Parent Fitur</th>
										<th rowspan="2" class="col-sm-3">Nama Fitur</th>
										<th rowspan="2">Path Fitur</th>
										<th colspan="6" class="text-center">Akses</th>
										<th rowspan="2" class="col-sm-1 text-center"><input type="checkbox" class="check-fitur-all" data-target="check-fitur" onclick="group.mark_view_all(this)"></th>
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
													<input type="checkbox" class="check-view" onclick="group.mark_view(this)">
												</td>
												<td class="text-center">
													<input type="checkbox" class="check-submit">
												</td>
												<td class="text-center">
													<input type="checkbox" class="check-update">
												</td>
												<td class="text-center">
													<input type="checkbox" class="check-delete">
												</td>
												<td class="text-center">
													<input type="checkbox" class="check-ack">
												</td>
												<td class="text-center">
													<input type="checkbox" class="check-approve">
												</td>
												<td class="text-center">
													<input data-idftr="<?php echo $d_val['id_detfitur']; ?>" type="checkbox" class="check-fitur" data-parent="check-fitur-all" onclick="group.mark_view(this)">
												</td>
											</tr>
										<?php endforeach ?>
									<?php endforeach ?>
								</tbody>
							</table>
						</small>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>