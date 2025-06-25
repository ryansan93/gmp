<div class="row content-panel detailed">
	<!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Kartu Pakan Peternak</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Kartu Pakan Peternak</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_kpm" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="kpm.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered table-hover tbl_kpm" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-1 text-center">Tanggal</th>
									<th class="col-sm-2 text-center">Noreg</th>
									<th class="col-sm-3 text-center">Nama Peternak</th>
									<th class="col-sm-6 text-center">Status</th>
								</tr>
							</thead>
							<tbody class="list">
								<tr>
									<td colspan="4" class="text-center">Tidak ada ditemukan</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade" data-id="">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
							<!-- <div class="col-sm-12 row">
								<div class="col-sm-6 no-padding d-flex align-items-center">
									<div class="col-sm-3 text-left no-padding">
										<span>Periode</span>
									</div>
									<div class="col-sm-8 d-flex align-items-center">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-6 d-flex align-items-center">
											<div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
										        <input type="text" class="form-control text-center" data-required="1" onblur="kpm.get_noreg()" placeholder="Tanggal" />
										        <span class="input-group-addon">
										            <span class="glyphicon glyphicon-calendar"></span>
										        </span>
										    </div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 d-flex align-items-center row">
								<div class="col-sm-6 no-padding d-flex align-items-center">
									<div class="col-sm-3 text-left no-padding">
										<span>No. Siklus</span>
									</div>
									<div class="col-sm-8 d-flex align-items-center">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-6 d-flex align-items-center">
											<select class="form-control noreg" onchange="kpm.set_data_rdim(this)" data-required="1">
												<option value="">-- Pilih No. Siklus --</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-sm-6 no-padding">
									<div class="col-sm-2 text-left no-padding">
										<span>Populasi</span>
									</div>
									<div class="col-sm-3">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 populasi text-right"><span>-</span></div>
									</div>
									<div class="col-md-1 no-padding">
										<span>Ekor</span>
									</div>
								</div>
							</div>
							<div class="col-md-12 d-flex align-items-center row">
								<div class="col-md-6 no-padding">
									<div class="col-md-3 text-left no-padding">
										<span>Peternak</span>
									</div>
									<div class="col-md-8">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 mitra"><span>-</span></div>
									</div>
								</div>
								<div class="col-md-6 no-padding d-flex align-items-center">
									<div class="col-md-2 text-left no-padding">
										<span>Jenis Pakan</span>
									</div>
									<div class="col-md-7 d-flex align-items-center">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 text-right">
											<select class="form-control jenis_pakan" onchange="kpm.set_header_pakan()" data-required="1">
												<option value="">-- Pilih Jenis Pakan --</option>
												<?php foreach ($jenis_pakan as $key => $v_jp): ?>
													<?php
														$selected = '';
														if ( trim($v_jp['nama']) == 'BR1FC' ) {
															$selected = 'selected';
														}
													?>
													<option value="<?php echo $v_jp['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_jp['nama']; ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 row">
								<div class="col-sm-6 no-padding">
									<div class="col-sm-3 text-left no-padding">
										<span>Tanggal DOC In</span>
									</div>
									<div class="col-sm-8">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 tgl_docin"><span>-</span></div>
									</div>
								</div>
								<div class="col-md-6 no-padding">
									<div class="col-md-2 text-left no-padding">
										<span>Kebutuhan</span>
									</div>
									<div class="col-md-3">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 kebutuhan_kg text-right"><span>-</span></div>
									</div>
									<div class="col-md-1 no-padding">
										<span>Kg</span>
									</div>
									<div class="col-md-3">
										<div class="col-md-1"><span>:</span></div>
										<div class="col-md-8 kebutuhan_zak text-right"><span>-</span></div>
									</div>
									<div class="col-md-1 no-padding">
										<span>Zak</span>
									</div>
								</div>
							</div>
							<div class="col-sm-12 no-padding" style="padding-top: 10px;">
								<table class="table table-bordered list_kpm">
									<thead>
										<tr class="v-center">
											<th class="text-center" rowspan="2">Tanggal</th>
											<th class="text-center" rowspan="2">Umur</th>
											<th class="text-center" colspan="6">Pakan <span class="nama_pakan"></span></th>
											<th class="text-center" rowspan="2">Tanggal</th>
											<th class="text-center" rowspan="2">Umur</th>
											<th class="text-center" colspan="6">Pakan <span class="nama_pakan"></span></th>
										</tr>
										<tr class="v-center">
											<th class="text-center">STD (Gram)</th>
											<th class="text-center">Setting (Gram)</th>
											<th class="text-center">Rcn Kirim (Zak)</th>
											<th class="text-center">Tgl Kirim</th>
											<th class="text-center">Terima</th>
											<th class="text-center">Jns Pakan</th>
											<th class="text-center">STD (Gram)</th>
											<th class="text-center">Setting (Gram)</th>
											<th class="text-center">Rcn Kirim (Zak)</th>
											<th class="text-center">Tgl Kirim</th>
											<th class="text-center">Terima</th>
											<th class="text-center">Jns Pakan</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="text-center" colspan="16">Data Kosong.</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-sm-12 no-padding">
								<hr>
								<button type="button" class="btn btn-primary save" href='#kpm' onclick="kpm.save_kpm(this)">Simpan</button>
							</div> -->
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>