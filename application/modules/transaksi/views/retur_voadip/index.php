<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat_rv" data-tab="riwayat_rv">Riwayat Retur Voadip</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#rv" data-tab="rv">Retur Voadip</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="riwayat_rv" class="tab-pane fade show active">
						<div class="col-lg-10 search no-padding d-flex align-items-center">
                            <div class="col-sm-1 no-padding">
                                <span> Periode </label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                                    <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
                            <div class="col-sm-2">
                                <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                                    <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-1 text-center no-padding" style="max-width: 4%;">Unit</div>
                            <div class="col-sm-2">
                                <select class="form-control unit">
                                    <option value="all">All</option>
                                    <?php if ( !empty($unit) ): ?>
                                        <?php foreach ($unit as $k_unit => $v_unit): ?>
                                            <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="rv.get_lists()" style="margin-right: 10px;">Tampilkan</button>
                                <button id="btn-add" type="button" data-href="rv" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="rv.changeTabActive(this)"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="col-lg-2 action no-padding">
                            <div class="col-lg-12 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
                                <i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rv" placeholder="Search" onkeyup="filter_all(this)">
                            </div>
                        </div>
						<!-- <div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rv" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="rv" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="rv.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div> -->
						<small>
							<table class="table table-bordered tbl_rv" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th class="col-md-1 text-center">Tanggal</th>
										<th class="col-md-1 text-center">No. Order</th>
										<th class="col-md-1 text-center">No. Retur</th>
										<th class="col-md-2 text-center">Asal</th>
										<th class="col-md-2 text-center">Tujuan</th>
										<th class="text-center" style="width: 5%;">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="6">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						</small>
					</div>
					<div id="rv" class="tab-pane fade">
						<?php echo $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>