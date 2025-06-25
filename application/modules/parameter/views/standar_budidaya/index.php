<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Standar Budidaya</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Standar Budidaya</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Standar Budidaya</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_standar_budidaya" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ): ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="sb.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php else: ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php endif ?>
						</div>
						<table class="table table-bordered table-hover tbl_standar_budidaya" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-2">Tanggal Berlaku</th>
									<th class="col-sm-3">Nomor</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<!-- UNTUK ISI DARI LIST STANDAR BUDIDAYA -->
								<?php if ( count($list) > 0 ): ?>
									<?php foreach ($list as $key => $val) { ?>
										<?php 
											$resubmit = null;
											if ( $val['g_status'] == 4 ) {
												$resubmit = $val['id'];
											}
										?>

										<?php 
											$red = null;
											if ( $akses['a_ack'] == 1 ){
												$status = getStatus('submit');
												if ( $val['g_status'] == $status ) {
													$red = 'red';
												}
											} else if ( $akses['a_approve'] == 1 ){
												$status = getStatus('ack');
												if ( $val['g_status'] == 2 ) {
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
														$last_log = $val['logs'][ count($val['logs']) - 1 ];
														$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
														echo $keterangan;
													?>
												</div>
												<div class="col-sm-1 no-padding">
													<?php if ( $akses['a_edit'] == 1 ){ ?>
														<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="sb.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
															<i class="fa fa-edit" aria-hidden="true"></i>
														</button>
													<?php } ?>
												</div>
												<div class="col-sm-1 no-padding">
													<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="sb.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
														<i class="fa fa-file" aria-hidden="true"></i>
													</button>
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
			                    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
				                    <input type="text" class="form-control text-center" data-required="1" />
				                    <span class="input-group-addon">
				                        <span class="glyphicon glyphicon-calendar"></span>
				                    </span>
				                </div>
							</div>
							<div class="col-lg-6">
								<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="SAVE" onclick="sb.save(this)"> 
									<i class="fa fa-save" aria-hidden="true"></i> SAVE
								</button>
							</div>
							<table class="table table-bordered table-hover" id="tb_input_standar_budidaya" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th class="text-center">Umur (hari)</th>
										<th class="text-center">Berat Badan (g)</th>
										<th class="text-center">FCR</th>
										<th class="text-center">Daya Hidup (%)</th>
										<th class="text-center">IP</th>
										<th class="text-center">Konsumsi Pakan Perhari (g)</th>
										<th class="text-center">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="umur" value="" data-tipe="integer" isedit="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="bb" value="" data-tipe="integer" onchange="" isedit="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="fcr" value="" data-tipe="decimal3" isedit="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="daya_hidup" value="" data-tipe="decimal" isedit="1">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="ip" value="" data-tipe="integer" isedit="1" onchange="">
										</td>
										<td class="col-sm-1">
											<input class="form-control text-right" type="text" name="kons_pakan_harian" value="" data-tipe="integer" isedit="1" onchange="">
										</td>
										<td class="action text-center col-sm-1">
											<button type="button" class="btn btn-sm btn-danger" onclick="sb.removeRowTable(this)"><i class="fa fa-minus"></i></button>
											<button type="button" class="btn btn-sm btn-default" onclick="sb.addRowTable(this)"><i class="fa fa-plus"></i></button>
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