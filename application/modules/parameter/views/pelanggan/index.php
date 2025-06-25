<div class="row content-panel">
	<div class="col-lg-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Daftar Pelanggan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Master Pelanggan</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active" role="tabpanel" data-ismobile="<?php echo $isMobile; ?>">
						<?php if ( $isMobile ) { ?>
							<div class="row">
								<div class="col-xs-12">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
											<div class="col-xs-12 no-padding">
												<label class="control-label">Kecamatan</label>
											</div>
											<div class="col-xs-12 no-padding">
												<select class="form-control kecamatan" type="text" data-required="1">
													<option value="">Pilih Kecamatan</option>
													<?php foreach ($kecamatan as $k_kecamatan => $v_kecamatan): ?>
														<option value="<?php echo $v_kecamatan['id']; ?>"><?php echo strtoupper($v_kecamatan['nama_header'].' | '.$v_kecamatan['nama']); ?></option>
													<?php endforeach ?>
												</select>
											</div>
										</div>

										<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
											<div class="col-xs-12 no-padding">
												<label class="control-label">Nama Pelanggan</label>
											</div>
											<div class="col-xs-12 no-padding">
												<select class="form-control pelanggan" type="text" data-required="1">
													<option value="">Pilih Pelanggan</option>
													<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
														<option value="<?php echo $v_plg['nomor']; ?>"><?php echo strtoupper($v_plg['nama'].' ('.$v_plg['kab_kota'].')'); ?></option>
													<?php endforeach ?>
												</select>
											</div>
										</div>

										<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
											<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="plg.getListMobile()"><i class="fa fa-search"></i> Tampilkan</button>
										</div>
									</div>
								</div>
								<div class="col-xs-12"><hr style="padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 5px;"></div>
								<div class="col-xs-12">
									<small>
										<span>Klik pada baris untuk melihat detail.</span>
										<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
											<thead>
												<tr>
													<th class="col-xs-1">#</th>
													<th class="col-xs-2">NIP</th>
													<th class="col-xs-5">Pelanggan</th>
													<th class="col-xs-3">Ambil Terakhir</th>
													<th class="col-xs-1"></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td colspan="5">Data tidak ditemukan.</td>
												</tr>
											</tbody>
										</table>
									</small>
								</div>
							</div>
						<?php } else { ?>
							<div class="col-lg-8 no-padding">
								<div class="col-lg-2" style="padding: 0px 5px 0px 0px;">
									<select class="form-control" id="search-by-pagination">
										<option value="">Search By</option>
										<option value="nama">Nama</option>
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
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="plg.changeTabActive(this)" style="margin-left: 10px;"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
								<?php // } else if ( $akses['a_ack'] == 1 ) { ?>
									<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="doc.ack(this)"> 
										<i class="fa fa-check" aria-hidden="true"></i> ACK
									</button> -->
								<?php // } else if ( $akses['a_approve'] == 1 ) { ?>
									<!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="doc.approve(this)"> 
										<i class="fa fa-check" aria-hidden="true"></i> APPROVE
									</button> -->
								<?php } ?>
								<button id="btn-export" type="button" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="plg.form_export_excel(this)"> 
									<i class="fa fa-print" aria-hidden="true"></i> EXPORT EXCEL
								</button>
							</div>
							<div class="col-sm-12 no-padding">
								<table class="table table-bordered tbl_plg">
									<thead>
										<tr>
											<th>NIP</th>
											<th>Nama Pelanggan</th>
											<th>NIK</th>
											<th>Alamat</th>
											<th>Status</th>
											<th>Saldo Awal (Rp)</th>
											<th>Keterangan</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="8"></td>
										</tr>
									</tbody>
								</table>
							</div>
							
							<div class="col-lg-12 no-padding">
								<div id="pagination-ry"></div>
							</div>
						<?php } ?>
					</div>

					<div id="action" class="tab-pane fade" role="tabpanel">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
						<?php else: ?>
							<h3>Master Pelanggan.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>