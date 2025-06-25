<div class="modal-header">
	<span class="modal-title"><b>Add Fitur</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<!-- <h4 class="mb">Add Fitur</h4> -->
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Nama Parent Fitur</div>
					<div class="col-lg-4">
						<input type="text" placeholder="Nama Judul Menu" id="nama_parent" class="form-control" data-required="1">
					</div>

					<div class="col-lg-6">
						<button id="btn-add" type="button" class="pull-right btn btn-primary cursor-p" title="SAVE" onclick="fitur.save(this)"> 
							<i class="fa fa-save" aria-hidden="true"></i> SAVE
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
								<tr>
									<td>
										<input type="text" placeholder="Nama Fitur" id="nama_fitur" class="form-control" data-required="1">
									</td>
									<td>
										<input type="text" placeholder="Path Fitur" id="path_fitur" class="form-control" data-required="1">
									</td>
									<td class="text-center">
										<button id="btn-add" type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="add_row(this)"> 
											<i class="fa fa-plus" aria-hidden="true"></i> 
										</button>
		          						<button id="btn-remove" type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="remove_row(this)"> 
		          							<i class="fa fa-minus" aria-hidden="true"></i> 
		          						</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>