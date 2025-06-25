<div class="modal-header">
	<span class="modal-title"><b>Edit Fitur</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<!-- <h4 class="mb">Edit Fitur</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Nama Parent Fitur</div>
					<div class="col-lg-4">
						<input type="text" placeholder="Nama Judul Menu" id="nama_parent" class="form-control" data-id="<?php echo $data['id_fitur']; ?>" value="<?php echo $data['nama_fitur']; ?>" data-required="1">
					</div>

					<div class="col-lg-6">
						<button id="btn-add" type="button" class="pull-right btn btn-primary cursor-p" title="EDIT" onclick="fitur.edit(this)"> 
							<i class="fa fa-pencil-square-o" aria-hidden="true"></i> EDIT
						</button>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<table class="table table-bordered detail" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-4">Nama Fitur</th>
									<th>Path Fitur</th>
									<th class="col-sm-2">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data['detail_fitur'] as $key => $d_val): ?>
									<tr data-iddet="<?php echo $d_val['id_detfitur']; ?>">
										<td>
											<input type="text" placeholder="Nama Fitur" id="nama_fitur" class="form-control" value="<?php echo $d_val['nama_detfitur']; ?>" data-required="1">
										</td>
										<td>
											<input type="text" placeholder="Path Fitur" id="path_fitur" class="form-control" value="<?php echo $d_val['path_detfitur']; ?>" data-required="1">
										</td>
										<td class="text-center">
											<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="ADD ROW" onclick="fitur.add_row(this)"> 
												<i class="fa fa-plus" aria-hidden="true"></i> 
											</button>
			          						<button id="btn-remove" type="button" class="btn btn-danger cursor-p" title="REMOVE ROW" onclick="remove_row(this)"> 
			          							<i class="fa fa-minus" aria-hidden="true"></i> 
			          						</button>
										</td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>