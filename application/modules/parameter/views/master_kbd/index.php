<div class="row content-panel detailed">
	<!-- <h4 class="mb">Master Kontrak, Bonus Dan Denda</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Kontrak, Bonus Dan Denda</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Kontrak, Bonus Dan Denda</a>
					</li>
				</ul>
			</div>
			<?php // cetak_r($list); ?>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active" role="tabpanel">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_sapronak_kesepakatan" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ): ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="kbd.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php else: ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php endif ?>
						</div>
						<table class="table table-bordered table-hover tbl_sapronak_kesepakatan" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-1">Tanggal Berlaku</th>
									<th class="col-sm-1">Nomor</th>
									<th class="col-sm-1">Pola Budidaya</th>
									<th class="col-sm-1">Item Pola</th>
									<th class="col-sm-4">Status</th>
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
											<td><?php echo strtoupper($val['nama_pola']); ?></td>
											<td><?php echo $val['item_pola']; ?></td>
											<td>
												<div class="col-sm-10 no-padding">
													<?php
														$keterangan = $val['deskripsi'] . ' pada ' . dateTimeFormat( $val['waktu'] );

														echo $keterangan;
													?>
													<!-- <?php 
														if ( isset($val['logs'][ count($val['logs']) - 1 ]) ) {
															$last_log = $val['logs'][ count($val['logs']) - 1 ];
															$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
														} else {
															$keterangan = '-';
														}

														echo $keterangan;
													?> -->
												</div>
												<div class="col-sm-1 no-padding">
													<?php if ( $akses['a_edit'] == 1 ){ ?>
														<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="kbd.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
															<i class="fa fa-edit" aria-hidden="true"></i>
														</button>
													<?php } ?>
												</div>
												<div class="col-sm-1 no-padding">
													<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="kbd.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
														<i class="fa fa-file" aria-hidden="true"></i>
													</button>
												</div>
											</td>
									   </tr>
									<?php } ?>
								<?php else : ?>
									<tr>
										<td colspan="5">Data tidak ditemukan. </td>
								   </tr>
								<?php endif ?>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade" role="tabpanel">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>