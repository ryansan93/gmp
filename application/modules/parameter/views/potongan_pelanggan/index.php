<div class="row content-panel detailed" id="index">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Potongan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Potongan</a>
					</li>
				</ul>
			</div>

			<div class="panel-body" style="padding-top: 0px;">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active">
						<div class="col-md-12 no-padding">
							<button type="button" class="btn btn-success pull-right" onclick="pp.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
						</div>
						<div class="col-md-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
						<small>
							<table class="table table-bordered table-hover tbl_potongan">
								<thead>
									<tr>
										<th class="col-md-1">No</th>
										<th class="col-md-4">Pelanggan</th>
										<th class="col-md-2">Mulai</th>
										<th class="col-md-2">Berakhir</th>
										<th class="col-md-2">Potongan (%)</th>
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

					<div id="action" class="tab-pane fade">
						<?php if ( $akses['a_submit'] == 1 ): ?>
							<?php echo $add_form; ?>
						<?php else: ?>
							<h3>Add Form</h3>
						<?php endif ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>