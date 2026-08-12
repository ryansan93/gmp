<div class="row content-panel detailed dm-wrap">
	<div class="col-lg-12 no-padding detailed">
		<div class="dm-filter-bar">
			<div class="dm-filter-item">
				<label class="control-label">Unit</label>
				<select class="form-control dm-unit">
					<option value="all">SEMUA UNIT</option>
					<?php if ( !empty($unit) ): ?>
						<?php foreach ($unit as $v_unit): ?>
							<option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['kode']); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
			<div class="dm-filter-item dm-filter-btn">
				<button type="button" class="btn btn-primary" onclick="dm.reload()"><i class="fa fa-refresh"></i> Refresh</button>
			</div>
		</div>

		<!-- BAGIAN 1 - METRIK OPERASIONAL (KESIAPAN PANEN) -->
		<div class="dm-section">
			<div class="dm-section-title"><i class="fa fa-leaf"></i> Kesiapan Panen</div>
			<div class="dm-stat-row">
				<div class="dm-stat-card dm-stat-primary">
					<div class="dm-stat-label">Estimasi Tonase Siap Panen <span id="dm-umur-range"></span></div>
					<div class="dm-stat-value" id="dm-total-tonase">-</div>
					<div class="dm-stat-sub" id="dm-total-ekor">-</div>
					<div class="dm-stat-sub" id="dm-total-tonase-ton">-</div>
				</div>
				<div class="dm-stat-card">
					<div class="dm-stat-label">Jumlah Siklus Siap Panen</div>
					<div class="dm-stat-value" id="dm-jml-siklus">-</div>
					<div class="dm-stat-sub">siklus aktif</div>
				</div>
			</div>
			<div class="dm-bw-row">
				<div class="dm-bw-card dm-bw-kecil">
					<div class="dm-bw-title">UKURAN KECIL <span class="dm-bw-range">1.75 - 1.99 kg</span></div>
					<div class="dm-bw-val"><span id="dm-bw-kecil-ekor">-</span> ekor</div>
					<div class="dm-bw-sub"><span id="dm-bw-kecil-tonase">-</span> kg</div>
					<div class="dm-bw-sub" id="dm-bw-kecil-ton">-</div>
				</div>
				<div class="dm-bw-card dm-bw-besar">
					<div class="dm-bw-title">UKURAN BESAR <span class="dm-bw-range">2.00 - 2.50 kg</span></div>
					<div class="dm-bw-val"><span id="dm-bw-besar-ekor">-</span> ekor</div>
					<div class="dm-bw-sub"><span id="dm-bw-besar-tonase">-</span> kg</div>
					<div class="dm-bw-sub" id="dm-bw-besar-ton">-</div>
				</div>
				<div class="dm-bw-card dm-bw-jumbo">
					<div class="dm-bw-title">UKURAN JUMBO <span class="dm-bw-range">&gt;= 2.51 kg</span></div>
					<div class="dm-bw-val"><span id="dm-bw-jumbo-ekor">-</span> ekor</div>
					<div class="dm-bw-sub"><span id="dm-bw-jumbo-tonase">-</span> kg</div>
					<div class="dm-bw-sub" id="dm-bw-jumbo-ton">-</div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">
					Estimasi Tonase Siap Panen per Unit <span id="dm-umur-range-unit"></span>
					<small class="dm-card-note">klik salah satu bar utk lihat detail per plasma</small>
				</div>
				<div class="dm-card-body">
					<div class="dm-chart-wrap"><canvas id="dm-chart-kesiapan-unit"></canvas></div>
				</div>
			</div>
		</div>

		<!-- BAGIAN 2 - PRICING INTELLIGENCE -->
		<div class="dm-section">
			<div class="dm-section-title"><i class="fa fa-line-chart"></i> Pricing Intelligence</div>
			<div class="dm-card">
				<div class="dm-card-head">Tren Harga Jual Realisasi (14 hari terakhir)</div>
				<div class="dm-card-body">
					<div class="dm-chart-wrap"><canvas id="dm-chart-harga"></canvas></div>
				</div>
			</div>
		</div>

		<!-- BAGIAN 3 - SALES PERFORMANCE -->
		<div class="dm-section">
			<div class="dm-section-title"><i class="fa fa-bar-chart"></i> Sales Performance <small class="dm-section-note">30 hari terakhir</small></div>
			<div class="dm-stat-row">
				<div class="dm-stat-card dm-stat-success">
					<div class="dm-stat-label">Total Omset Penjualan <small class="dm-section-note">30 hari terakhir</small></div>
					<div class="dm-stat-value" id="dm-total-omset">-</div>
					<div class="dm-stat-sub" id="dm-total-omset-rp">-</div>
					<div class="dm-stat-sub" id="dm-total-omset-tonase">-</div>
					<div class="dm-stat-sub" id="dm-total-omset-ekor">-</div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">
					Volume Penjualan per Channel
					<small class="dm-card-note">per unit, membandingkan antar tipe pelanggan</small>
				</div>
				<div class="dm-card-body">
					<div class="dm-chart-wrap"><canvas id="dm-chart-channel"></canvas></div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">
					Volume &amp; Omset Penjualan per Unit
					<small class="dm-card-note">bar = tonase (kiri), garis = omset Rp (kanan)</small>
				</div>
				<div class="dm-card-body">
					<div class="dm-chart-wrap"><canvas id="dm-chart-unit"></canvas></div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">Top 10 Bakul - Ambilan Terbanyak</div>
				<div class="dm-card-body">
					<div class="dm-table-wrap">
						<table class="table table-condensed table-bordered dm-table">
							<thead>
								<tr>
									<th>#</th>
									<th>Bakul</th>
									<th class="text-right">Tonase</th>
									<th class="text-right">Nilai</th>
								</tr>
							</thead>
							<tbody id="dm-top-bakul-volume-body">
								<tr><td colspan="4" class="text-center text-muted">Memuat...</td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- BAGIAN 4 - HUTANG BAKUL -->
		<div class="dm-section">
			<div class="dm-section-title"><i class="fa fa-money"></i> Hutang Bakul</div>
			<div class="dm-stat-row">
				<div class="dm-stat-card dm-stat-danger">
					<div class="dm-stat-label">Total Hutang Bakul <small class="dm-section-note">per hari ini</small></div>
					<div class="dm-stat-value" id="dm-total-hutang-bakul">-</div>
					<div class="dm-stat-sub" id="dm-total-hutang-bakul-rp">-</div>
					<div class="dm-stat-sub" id="dm-total-hutang-bakul-jml">-</div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">
					Hutang Bakul per Unit
					<small class="dm-card-note">klik salah satu bar utk lihat detail per bakul</small>
				</div>
				<div class="dm-card-body">
					<div class="dm-chart-wrap"><canvas id="dm-chart-hutang-bakul"></canvas></div>
				</div>
			</div>
			<div class="dm-card">
				<div class="dm-card-head">
					<span id="dm-top-bakul-hutang-title">Top 10 Bakul - Hutang Terbanyak</span>
					<small class="dm-card-note">per hari ini, lintas semua unit</small>
				</div>
				<div class="dm-card-body">
					<div class="dm-table-wrap">
						<table class="table table-condensed table-bordered dm-table">
							<thead>
								<tr>
									<th>#</th>
									<th>Bakul</th>
									<th class="text-right">Umur Tertua</th>
									<th class="text-right">Hutang</th>
								</tr>
							</thead>
							<tbody id="dm-top-bakul-hutang-body">
								<tr><td colspan="4" class="text-center text-muted">Memuat...</td></tr>
							</tbody>
						</table>
					</div>
					<div class="dm-card-footer-btn">
						<button type="button" class="btn btn-default btn-sm" id="dm-top-bakul-hutang-toggle" onclick="dm.toggleTopBakulHutang()">Tampilkan Semua</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Detail Hutang Bakul -->
<div id="dm-modal-hutang-bakul" class="modal fade my-style" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Detail Hutang Bakul - Unit <span id="dm-modal-hutang-bakul-unit"></span></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="dm-table-wrap">
					<table class="table table-condensed table-bordered dm-table">
						<thead>
							<tr>
								<th class="dm-sortable" data-sort="nama_pelanggan">Bakul <i class="fa fa-sort"></i></th>
								<th class="text-right dm-sortable" data-sort="umur_tertua">Umur Tertua <i class="fa fa-sort"></i></th>
								<th class="text-right dm-sortable" data-sort="hutang">Hutang <i class="fa fa-sort"></i></th>
							</tr>
						</thead>
						<tbody id="dm-modal-hutang-bakul-body">
							<tr><td colspan="3" class="text-center text-muted">Memuat...</td></tr>
						</tbody>
						<tfoot>
							<tr>
								<td><b>TOTAL</b></td>
								<td class="text-right"><b id="dm-modal-hutang-bakul-total-invoice">-</b> invoice</td>
								<td class="text-right"><b id="dm-modal-hutang-bakul-total-hutang">-</b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Detail Kesiapan Panen per Plasma -->
<div id="dm-modal-kesiapan-unit" class="modal fade my-style" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Detail Siap Panen - Unit <span id="dm-modal-kesiapan-unit-unit"></span></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="dm-table-wrap">
					<table class="table table-condensed table-bordered dm-table">
						<thead>
							<tr>
								<th>Plasma</th>
								<th class="text-right">Umur</th>
								<th class="text-right">BW (Kg)</th>
								<th class="text-right">Ekor</th>
								<th class="text-right">Tonase</th>
							</tr>
						</thead>
						<tbody id="dm-modal-kesiapan-unit-body">
							<tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr>
						</tbody>
						<tfoot>
							<tr>
								<td><b>TOTAL</b></td>
								<td class="text-right">-</td>
								<td class="text-right">-</td>
								<td class="text-right"><b id="dm-modal-kesiapan-unit-total-ekor">-</b></td>
								<td class="text-right"><b id="dm-modal-kesiapan-unit-total-tonase">-</b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
