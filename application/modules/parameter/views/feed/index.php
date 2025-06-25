<div class="row content-panel detailed">
	<h4 class="mb">Master Feed</h4>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="active">
						<a data-toggle="tab" href="#history" data-tab="history">Riwayat Feed</a>
					</li>
					<li class="">
						<a data-toggle="tab" href="#action" data-tab="action">Feed</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_feed" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="feed.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php // } else if ( $akses['a_ack'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="feed.ack(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> ACK
								</button> -->
							<?php // } else if ( $akses['a_approve'] == 1 ) { ?>
								<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="feed.approve(this)"> 
									<i class="fa fa-check" aria-hidden="true"></i> APPROVE
								</button> -->
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered table-hover tbl_feed" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th rowspan="2" class="col-sm-2 text-center">Nomor Dokumen</th>
									<th rowspan="2" class="col-sm-1 text-center">Tgl. Berlaku</th>
									<th colspan="3" class="col-sm-2 text-center">Harga</th>
									<th rowspan="2" class="col-sm-1 text-center">Dokumen</th>
									<th rowspan="2" class="col-sm-4 text-center">Status</th>
									<?php if ( $akses['a_ack'] == 1 || $akses['a_approve'] == 1 ) { ?>
										<th rowspan="2" class="col-sm-1 text-center">Action</th>
									<?php } ?>
								</tr>
								<tr>
									<th class="text-center">Pakan 1</th>
									<th class="text-center">Pakan 2</th>
									<th class="text-center">Pakan 3</th>
								</tr>
							</thead>
							<tbody class="list">
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<?php if ( $akses['a_ack'] == 1 || $akses['a_approve'] == 1 ) { ?>
										<td></td>
									<?php } ?>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<div class="col-sm-6 no-padding">
								<table class="table no-border tbl_add_pakan" width="40%">
									<tbody>
										<tr class="v-center">
											<td class="col-md-3">
												<label class="" >Dokumen</label>
											</td>
											<td class="col-md-9">
												<span class="file">.....................</span>
												<label class="pull-left">
							                    	<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="......" data-allowtypes="doc|pdf|docx" style="display: none;">
							                    	<i class="glyphicon glyphicon-paperclip cursor-p"></i> 
							                  	</label>
											</td>
										</tr>
										<tr>
											<td class="col-md-3">				
												<label class="control-label">Tanggal Berlaku</label>
											</td>
											<td class="col-md-9">
												<div class="input-group date col-md-5" id="datetimepicker1" name="tanggal-berlaku">
											        <input type="text" class="form-control text-center" data-required="1" />
											        <span class="input-group-addon">
											            <span class="glyphicon glyphicon-calendar"></span>
											        </span>
											    </div>
											</td>
										</tr>
										<tr>
											<td class="col-md-3">
												<label class=""><u>Harga</u></label>
											</td>
										</tr>
										<tr>
											<td class="col-md-3">
												<label class="control-label input">Pakan 1</label>
											</td>
											<td class="col-md-9">
												<div class="col-md-4 no-padding">
													<input data-required="1" type="text" class="form-control text-right" name="pakan1" data-tipe="integer" placeholder="Harga" maxlength="7">
												</div>
											</td>
										</tr>
										<tr>
											<td class="col-md-3">
												<label class="control-label input">Pakan 2</label>
											</td>
											<td class="col-md-9">
												<div class="col-md-4 no-padding">
													<input data-required="1" type="text" class="form-control text-right" name="pakan2" data-tipe="integer" placeholder="Harga" maxlength="7">
												</div>
											</td>
										</tr>
										<tr>
											<td class="col-md-3">
												<label class="control-label input">Pakan 3</label>
											</td>
											<td class="col-md-9">
												<div class="col-md-4 no-padding">
													<input data-required="1" type="text" class="form-control text-right" name="pakan3" data-tipe="integer" placeholder="Harga" maxlength="7">
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-sm-12 no-padding">
								<hr>
								<button type="button" class="btn btn-primary" onclick="feed.save()">
									<i class="fa fa-save"></i>
									SAVE
								</button>
							</div>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>