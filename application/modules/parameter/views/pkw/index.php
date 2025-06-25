<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Perusahaan, Kota dan Wilayah</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#perusahaan" data-tab="perusahaan">PERUSAHAAN</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#wilayah" data-tab="wilayah">KOTA DAN WILAYAH</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#korwil" data-tab="korwil">KORWIL</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="perusahaan" class="tab-pane fade show active">
						<div class="col-md-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tgl_perusahaan" placeholder="Search" onkeyup="filter_all(this)">
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
									<tr>
										<td colspan="6"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div id="wilayah" class="tab-pane fade">
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
								<thead>
									<tr>
										<th>Provinsi</th>
										<td colspan="2">-</td>
									</tr>
									<tr>
										<th>Kota / Kab</th>
										<th>Kecamatan</th>
										<th>Kelurahan</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="3"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div id="korwil" class="tab-pane fade">
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
								<thead>
									<tr>
										<th>Negara</th>
									</tr>
									<tr>
										<th class="col-md-6">Korwil</th>
										<th class="col-md-6">Kota</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="2"></td>
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