<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master FEED, DOC, VOADIP dan Peralatan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#feed" data-tab="feed" role="tab">FEED</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#doc" data-tab="doc" role="tab">DOC</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#voadip" data-tab="voadip" role="tab">VOADIP</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#peralatan" data-tab="peralatan" role="tab">PERALATAN</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="feed" class="tab-pane fade show active" role="tabpanel">
						<div class="col-md-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_feed" placeholder="Search" onkeyup="filter_all(this)">
							</div>
							<div class="col-lg-4 action no-padding">
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="feed" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="fdvp.row_add(this)"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
									<button id="btn-save" type="button" data-href="feed" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="fdvp.save_feed(this)"> 
										<i class="fa fa-save" aria-hidden="true"></i> SAVE
									</button>

									<?php if ( $akses['a_edit'] == 1 ) { ?>
										<button id="btn-edit" type="button" data-href="feed" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="fdvp.edit_feed(this)"> 
											<i class="fa fa-edit"></i> EDIT
										</button>
									<?php } ?>
								<?php } else { ?>
									<div class="col-lg-2 action no-padding pull-right">
										&nbsp
									</div>
								<?php } ?>
							</div>
							<table class="table table-bordered tbl_feed">
								<thead>
									<tr>
										<th>Kategori</th>
										<th class="col-sm-1">Kode</th>
										<th>Nama Pakan</th>
										<th>Kode<br>Item<br>Supplier</th>
										<th class="col-sm-2">Supplier</th>
										<th>Umur<br>(Hari)</th>
										<th>Berat<br>Pakan<br>(Kg)</th>
										<th>Bentuk Pakan</th>
										<th>Masa<br>Simpan<br>(Hari)</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="10"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div id="doc" class="tab-pane fade" role="tabpanel">
						<div class="col-md-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_doc" placeholder="Search" onkeyup="filter_all(this)">
							</div>
							<div class="col-lg-4 action no-padding">
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="fdvp.row_add(this)"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
									<button id="btn-save" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="fdvp.save_doc(this)"> 
										<i class="fa fa-save" aria-hidden="true"></i> SAVE
									</button>

									<?php if ( $akses['a_edit'] == 1 ) { ?>
										<button id="btn-edit" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="fdvp.edit_doc(this)"> 
											<i class="fa fa-edit"></i> EDIT
										</button>
									<?php } ?>
								<?php } else { ?>
									<div class="col-lg-2 action no-padding pull-right">
										&nbsp
									</div>
								<?php } ?>
							</div>
							<table class="table table-bordered tbl_doc">
								<thead>
									<tr>
										<th>Kategori</th>
										<th>Kode</th>
										<th>DOC</th>
										<th>Kode<br>Item<br>Supplier</th>
										<th>Supplier</th>
										<th>Berat (g)</th>
										<th>Isi<br>Kemasan<br>(Ekor)</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div id="voadip" class="tab-pane fade" role="tabpanel">
						<div class="col-md-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_voadip" placeholder="Search" onkeyup="filter_all(this)">
							</div>
							<div class="col-lg-4 action no-padding">
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="voadip" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="fdvp.row_add(this)"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
									<button id="btn-save" type="button" data-href="voadip" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="fdvp.save_voadip(this)"> 
										<i class="fa fa-save" aria-hidden="true"></i> SAVE
									</button>

									<?php if ( $akses['a_edit'] == 1 ) { ?>
										<button id="btn-edit" type="button" data-href="voadip" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="fdvp.edit_voadip(this)"> 
											<i class="fa fa-edit"></i> EDIT
										</button>
									<?php } ?>
								<?php } else { ?>
									<div class="col-lg-2 action no-padding pull-right">
										&nbsp
									</div>
								<?php } ?>
							</div>
							<table class="table table-bordered tbl_voadip">
								<thead>
									<tr>
										<th>Kategori</th>
										<th>Kode</th>
										<th>Nama VOADIP</th>
										<th>Kode<br>Item<br>Supplier</th>
										<th>Supplier</th>
										<th>Dosis /Ekor</th>
										<th>Isi Kemasan</th>
										<th>Satuan</th>
										<th>Bentuk</th>
										<th>Masa<br>Simpan<br>(Hari)</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="11"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div id="peralatan" class="tab-pane fade" role="tabpanel">
						<div class="col-md-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_peralatan" placeholder="Search" onkeyup="filter_all(this)">
							</div>
							<div class="col-lg-4 action no-padding">
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="fdvp.row_add(this)"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
									<button id="btn-save" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right hide" title="SAVE" onclick="fdvp.save_peralatan(this)"> 
										<i class="fa fa-save" aria-hidden="true"></i> SAVE
									</button>

									<?php if ( $akses['a_edit'] == 1 ) { ?>
										<button id="btn-edit" type="button" data-href="peralatan" class="btn btn-primary cursor-p pull-right hide" title="EDIT" onclick="fdvp.edit_peralatan(this)"> 
											<i class="fa fa-edit"></i> EDIT
										</button>
									<?php } ?>
								<?php } else { ?>
									<div class="col-lg-2 action no-padding pull-right">
										&nbsp
									</div>
								<?php } ?>
							</div>
							<table class="table table-bordered tbl_peralatan">
								<thead>
									<tr>
										<th>Kategori</th>
										<th>Kode</th>
										<th>Nama Peralatan</th>
										<th>Kode<br>Item<br>Supplier</th>
										<th>Supplier</th>
										<th>Isi Kemasan</th>
										<th>Satuan</th>
										<th>Masa<br>Simpan<br>(Hari)</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>