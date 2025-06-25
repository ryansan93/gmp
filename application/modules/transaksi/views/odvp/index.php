<div class="row content-panel detailed">
	<!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#doc" data-tab="doc">Order DOC</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#voadip" data-tab="voadip">Order Voadip</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#pakan" data-tab="pakan">Order Pakan</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="doc" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding d-flex align-items-center">
							<div class="col-sm-1 no-padding">
								<span> Periode </label>
							</div>
							<div class="col-sm-3">
								<div class="input-group date datetimepicker" name="startDate" id="StartDate_ODVP">
							        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
							<div class="col-sm-3">
								<div class="input-group date datetimepicker" name="endDate" id="EndDate_ODVP">
							        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-2">
								<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="odvp.get_lists_doc()">Tampilkan</button>
							</div>
						</div>
						<div class="col-lg-4 action no-padding">
							<?php // if ( $akses['a_submit'] == 1 ) { ?>
								<div class="col-lg-4 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
									<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_odvp" placeholder="Search" onkeyup="filter_all(this)">
								</div>
								<select class="col-sm-4 form-control filter pull-right" onchange="odvp.filter_by_status(this)">
									<option value="all">All</option>
									<option value="submit">Submit</option>
									<option value="ordered">Ordered</option>
									<option value="terima">Terima</option>
								</select>
								<span class="col-sm-2 pull-right" style="padding-top: 8px;">Filter</span>
								<!-- <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="odvp.terima_doc_form()" style="margin-left: 10px;"> 
									<i class="fa fa-plus" aria-hidden="true"></i> Terima
								</button>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="odvp.order_doc_form()"> 
									<i class="fa fa-plus" aria-hidden="true"></i> Ordered
								</button> -->
								&nbsp
							<?php // } else { ?>
								<!-- <div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div> -->
							<?php // } ?>
						</div>
						<table class="table table-bordered table-hover tbl_odvp" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr class="v-center">
									<th class="col-sm-1 text-center" rowspan="2">Rencana<br>DOC In</th>
									<th class="col-sm-1 text-center" rowspan="2">Kota / Unit</th>
									<th class="col-sm-2 text-center" rowspan="2">Nama Peternak</th>
									<th class="text-center" rowspan="2">Kandang</th>
									<th class="text-center" rowspan="2">Populasi</th>
									<th class="text-center" colspan="3">Order DOC</th>
									<th class="text-center" colspan="4">Kirim dan Terima</th>
									<th class="text-center" colspan="2">Action</th>
								</tr>
								<tr class="v-center">
									<th class="col-sm-1 text-center">Tgl Order</th>
									<th class="col-sm-1 text-center">No Order</th>
									<th class="text-center">Jumlah</th>
									<th class="col-sm-1 text-center">Tgl Kirim</th>
									<th class="text-center">Jumlah</th>
									<th class="col-sm-1 text-center">Tgl Terima</th>
									<th class="text-center">Jumlah</th>
									<th>Order</th>
									<th>Terima</th>
								</tr>
							</thead>
							<tbody class="list">
								<tr>
									<td class="text-center" colspan="15">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id="voadip" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<div class="col-lg-8 search no-padding d-flex align-items-center">
								<div class="col-sm-1 no-padding">
									<span> Periode </label>
								</div>
								<div class="col-sm-3">
									<div class="input-group date datetimepicker" name="startDate" id="StartDate_VOADIP">
								        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
								<div class="col-sm-3">
									<div class="input-group date datetimepicker" name="endDate" id="EndDate_VOADIP">
								        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-2">
									<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="odvp.get_lists_voadip()">Tampilkan</button>
								</div>
								<div class="col-sm-2">
									<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.order_voadip_form()"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
								</div>
							</div>
							<div class="col-lg-4 action no-padding">
								<div class="col-lg-4 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
									<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_odvp" placeholder="Search" onkeyup="filter_all(this)">
								</div>
							</div>
							<table class="table table-bordered table-hover tbl_odvp" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr class="v-center">
										<th class="col-sm-1 text-center">Tanggal</th>
										<th class="col-sm-1 text-center">No. Order</th>
										<th class="col-sm-2 text-left">Supplier</th>
										<th class="col-sm-4 text-left">Perusahaan</th>
									</tr>
								</thead>
								<tbody class="list">
									<tr>
										<td class="text-left" colspan="4">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
					<div id="pakan" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<div class="col-sm-12 no-padding">
								<button id="btn-add" type="button" data-href="action" class="col-sm-12 btn btn-success cursor-p pull-left" title="ADD" onclick="odvp.order_pakan_form()"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
							</div>
							<div class="col-sm-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
							<div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
								<div class="col-sm-6 no-padding" style="padding-right: 5px;">
									<div class="col-sm-12 no-padding"><label class="control-label">Tgl Awal</label></div>
									<div class="col-sm-12 no-padding">
										<div class="input-group date datetimepicker" name="startDate" id="StartDate_PAKAN">
											<input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div>
								<div class="col-sm-6 no-padding" style="padding-left: 5px;">
									<div class="col-sm-12 no-padding"><label class="control-label">Tgl Akhir</label></div>
									<div class="col-sm-12 no-padding">
										<div class="input-group date datetimepicker" name="endDate" id="EndDate_PAKAN">
											<input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
								<div class="col-sm-12 no-padding"><label class="control-label">Perusahaan</label></div>
								<div class="col-sm-12 no-padding">
									<select class="form-control perusahaan" multiple="multiple" data-required="1">
										<option value="all">ALL</option>
										<?php if ( isset($perusahaan) && !empty($perusahaan) ) { ?>
											<?php foreach ($perusahaan as $k_prs => $v_prs) { ?>
												<option value="<?php echo $v_prs['kode']; ?>"><?php echo strtoupper($v_prs['perusahaan']); ?></option>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-12 no-padding">
								<button id="btn-tampil" type="button" data-href="action" class="col-sm-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="odvp.get_lists_pakan()">Tampilkan</button>
							</div>
							<div class="col-sm-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
							<div class="col-sm-12 action no-padding">
								<div class="col-sm-12 search left-inner-addon no-padding pull-right">
									<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_odvp" placeholder="Search" onkeyup="filter_all(this)">
								</div>
							</div>
							<table class="table table-bordered table-hover tbl_odvp" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr class="v-center">
										<th class="text-center col-sm-1">Tanggal</th>
										<th class="text-center col-sm-2">Supplier</th>
										<th class="text-center col-sm-2">Perusahaan</th>
										<th class="text-center col-sm-1">Rencana Kirim</th>
										<th class="text-center col-sm-1">No Order</th>
										<th class="text-center col-sm-1">Action</th>
									</tr>
								</thead>
								<tbody class="list">
									<tr>
										<td class="text-left" colspan="6">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>