<div class="row content-panel detailed">
	<!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Rencana Chick In Mingguan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Rencana Chick In Mingguan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#batal" data-tab="batal">Pembatalan Chick In</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-lg-8 search left-inner-addon no-padding">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rdim" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<div class="col-lg-4 action no-padding">
							<?php if ( $akses['a_submit'] == 1 ) { ?>
								<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="rdim.changeTabActive(this)"> 
									<i class="fa fa-plus" aria-hidden="true"></i> ADD
								</button>
							<?php } else { ?>
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp;
								</div>
							<?php } ?>
						</div>
						<table class="table table-bordered table-hover tbl_rdim" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-2 text-center">Periode</th>
									<th class="col-sm-3 text-center">Nomor</th>
									<th class="col-sm-1 text-center">Status</th>
									<th class="col-sm-6 text-center">Keterangan</th>
								</tr>
							</thead>
							<tbody class="list">
								<tr>
									<td class="text-center" colspan="4">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
						<?php else: ?>
							<h3>Data Kosong.</h3>
						<?php endif ?>
					</div>

					<div id="batal" class="tab-pane fade">
						<?php if ( !$akses['a_approve'] == 1 ): ?>
							<div class="col-sm-12 no-padding">
								<form class="form-horizontal">
									<div class="col-sm-1 no-padding" style="width: inherit;">
										<label class="control-label"> Periode </label>
									</div>
									<div class="col-sm-3">
										<select class="form-control " name="periode" onchange="rdim.getDataPembatalanRdim(this)">
											<option value="">-- pilih periode --</option>
											<?php foreach ($periodes as $periode): ?>
												<option value="<?php echo $periode->id ?>"><?php echo tglIndonesia($periode->mulai, '-', ' ') .' s.d '. tglIndonesia($periode->selesai, '-', ' ') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</form>
							</div>
							<div class="col-sm-12 no-padding" style="margin-top: 12px;">
								<table id="tb_pembatalan_rdim" class="table table-hover table-bordered custom_table table-form">
									<thead>
										<tr>
											<th class="col-sm-3">Mitra</th>
											<th class="col-sm-1">Noreg</th>
											<th class="col-sm-2">Document</th>
											<th>Alasan batal</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="4">pilih periode untuk menampilkan data</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-sm-12 no-padding">
								<button type="button" class="btn btn-primary pull-right" onclick="rdim.savePembatalanRdim()"><i class="fa fa-save"></i> Save</button>
							</div>
						<?php else: ?>
							<h4 class="text-center">Pembatalan Rencana DOC in Mingguan</h4>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>