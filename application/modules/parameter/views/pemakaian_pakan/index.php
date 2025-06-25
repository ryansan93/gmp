<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Pemakaian Pakan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Pemakaian Pakan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Pemakaian Pakan</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_pemakaian_pakan" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ): ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="pp.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php else: ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php endif ?>
						</div>
						<table class="table table-bordered table-hover tbl_pemakaian_pakan" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-2">Tanggal Berlaku</th>
									<th class="col-sm-3">Nomor</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<!-- UNTUK ISI DARI LIST PEMAKAIAN PAKAN -->
								<?php if ( count($list) > 0 ): ?>
									<?php foreach ($list as $key => $val) { ?>
										<?php 
											$resubmit = null;
											if ( $val['status'] == 4 ) {
												$resubmit = $val['id'];
											}
										?>

										<?php 
											$red = null;
											if ( $akses['a_ack'] == 1 ){
												$status = getStatus('submit');
												if ( $val['status'] == $status ) {
													$red = 'red';
												}
											} else if ( $akses['a_approve'] == 1 ){
												$status = getStatus('ack');
												if ( $val['status'] == 2 ) {
													$red = 'red';
												}
											} else {

											}
										?>

										<tr class="search <?php echo $red; ?>">
											<td><?php echo tglIndonesia($val['mulai'], '-', ' '); ?></td>
											<td><?php echo $val['nomor']; ?></td>
											<td>
												<div class="col-sm-10 no-padding">
													<?php 

														if ( !empty($val['logs']) ) {
															$last_log = $val['logs'][ count($val['logs']) - 1 ];
															$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
														} else {
															$keterangan = '-';
														}

														echo $keterangan;
													?>
												</div>
												<div class="col-sm-1 no-padding">
													<?php if ( $akses['a_edit'] == 1 ){ ?>
														<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
															<i class="fa fa-edit" aria-hidden="true"></i>
														</button>
													<?php } ?>
													<!-- <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
														<i class="fa fa-file" aria-hidden="true"></i>
													</button> -->
													<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" ><u>Lihat</u></a> -->
													<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" ><u>Edit</u></a> -->
												</div>
												<div class="col-sm-1 no-padding">
													<!-- <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>"> 
														<i class="fa fa-edit" aria-hidden="true"></i>
													</button> -->
													<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
														<i class="fa fa-file" aria-hidden="true"></i>
													</button>
													<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" ><u>Lihat</u></a> -->
													<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" ><u>Edit</u></a> -->
												</div>
											</td>
									   </tr>
									<?php } ?>
								<?php else : ?>
									<tr>
										<td colspan="3">Data tidak ditemukan. </td>
								   </tr>
								<?php endif ?>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<div class="col-lg-1 no-padding pull-left">
								<h6>Tgl Berlaku : </h6>
							</div>
							<div class="col-lg-2 no-padding action">
			                    <!-- <input class="form-control text-center" type="text" value="" data-tipe="date"> -->
			                    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
				                    <input type="text" class="form-control text-center" data-required="1" />
				                    <span class="input-group-addon">
				                        <span class="glyphicon glyphicon-calendar"></span>
				                    </span>
				                </div>
							</div>
							<div class="col-lg-6">
								<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="SAVE" onclick="pp.save(this)"> 
									<i class="fa fa-save" aria-hidden="true"></i> SAVE
								</button>
							</div>
							<table class="table table-bordered table-hover" id="tb_input_standar_performa" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th class="text-center">Umur (hari)</th>
										<th class="text-center">Daya Hidup (%)</th>
										<th class="text-center">Mortalitas Harian (%)</th>
										<th class="text-center">Konsumsi Pakan (g)</th>
										<th class="text-center">Konsumsi Pakan Perhari (g)</th>
										<th class="text-center">Berat Badan</th>
										<th class="text-center">ADG</th>
										<th class="text-center">FCR</th>
										<th class="text-center">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="col-sm-1">
											<input class="form-control text-center" type="text" name="umur" value="0" data-tipe="integer" disabled data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="daya_hidup" value="<?php echo angkaDecimal(100) ?>" data-tipe="decimal" disabled data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="mortalitas" value="0" data-tipe="decimal" disabled isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="kons_pakan" value="0" data-tipe="integer" disabled data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="kons_pakan_harian" value="0" data-tipe="integer" disabled isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="bb" value="0" data-tipe="integer" onchange="pp.calcRowValue(this)" data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="adg" value="0" data-tipe="integer" disabled isedit="1" onchange="pp.calcRowValue(this)" data-required="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="fcr" value="0" data-tipe="decimal3" disabled data-required="1">
										</td>
										<td class="action text-center col-sm-1">
											<button id="btn-add" type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="pp.addRowTable(this)"><i class="fa fa-plus"></i></button>
											<button id="btn-remove" type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="pp.removeRowTable(this)"><i class="fa fa-minus"></i></button>
										</td>
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