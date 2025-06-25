<div class="row content-panel detailed" id="index">
	<!-- <h4 class="mb">Master Peternak</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Kendaraan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Kendaraan</a>
					</li>
				</ul>
			</div>

			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 no-padding">
							<div class="col-lg-2" style="padding: 0px 5px 0px 0px;">
								<select class="form-control" id="search-by-pagination">
									<option value="">Search By</option>
									<option value="nama">Nama</option>
									<option value="unit">Unit</option>
								</select>
							</div>
							<div class="col-lg-6 search left-inner-addon" style="padding: 0px 5px 0px 0px;">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" id="search-val-pagination" type="search" placeholder="Search">
							</div>
							<div class="col-lg-2 no-padding">
								<button type="button" id="search-pagination" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
							</div>
						</div>
						<div class="col-lg-4 action no-padding">
							<?php // if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="kend.changeTabActive(this)" style="margin-left: 10px;"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php // } ?>
							<!-- <button id="btn-export" type="button" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="kend.form_export_excel(this)"> 
								<i class="fa fa-print" aria-hidden="true"></i> EXPORT EXCEL
							</button> -->
						</div>

						<div class="col-lg-12 no-padding">
                            <small>
                                <table class="table table-bordered table-hover tbl_peternak" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="col-lg-1">Perusahaan</th>
                                            <th class="col-lg-1">Jenis</th>
                                            <th class="col-lg-1">No. Polisi</th>
                                            <th class="col-lg-1">Unit</th>
                                            <th class="col-lg-2">Nama Pemegang</th>
                                            <th class="col-lg-1">Merk</th>
                                            <th class="col-lg-2">Type</th>
                                            <th class="col-lg-1">Warna</th>
                                            <th class="col-lg-1">Tahun</th>
                                            <th class="col-lg-1">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10">Data tidak ditemukan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </small>
						</div>
					</div>

					<div id="action" class="tab-pane fade">
						<?php // if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $addForm; ?>
						<?php // else: ?>
							<!-- <h3>Data Kosong.</h3> -->
						<?php // endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>