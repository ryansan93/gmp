<?php if ( !empty($siklus) ): ?>
	<?php $is_aktif = ($siklus['status'] === 'BELUM TUTUP SIKLUS'); ?>
	<div class="sk-siklus-card">
		<div class="sk-siklus-head">
			<div class="sk-siklus-avatar"><i class="fa fa-user"></i></div>
			<div class="sk-siklus-title">
				<div class="sk-siklus-nama"><?php echo strtoupper($siklus['nama_plasma']); ?></div>
				<div class="sk-siklus-sub"><i class="fa fa-home"></i> Kandang <?php echo $siklus['kandang']; ?></div>
			</div>
			<span class="sk-badge <?php echo $is_aktif ? 'sk-badge-aktif' : 'sk-badge-tutup'; ?>">
				<?php echo $is_aktif ? 'AKTIF' : 'TUTUP'; ?>
			</span>
		</div>
		<div class="sk-siklus-body">
			<div class="sk-siklus-row">
				<span class="sk-siklus-key"><i class="fa fa-barcode"></i> Noreg</span>
				<span class="sk-siklus-val"><?php echo $siklus['noreg']; ?></span>
			</div>
			<div class="sk-siklus-row">
				<span class="sk-siklus-key"><i class="fa fa-map-marker"></i> Unit</span>
				<span class="sk-siklus-val"><?php echo $siklus['unit']; ?></span>
			</div>
			<?php if ( !empty($siklus['tgl_docin']) ): ?>
			<div class="sk-siklus-row">
				<span class="sk-siklus-key"><i class="fa fa-calendar"></i> Chick-in</span>
				<span class="sk-siklus-val"><?php echo tglIndonesia($siklus['tgl_docin'], '-', ' ', true); ?></span>
			</div>
			<?php endif ?>
		</div>
		<?php if ( !empty($performa) && !empty($performa['umur']) ): ?>
			<?php $last = count($performa['umur']) - 1; ?>
			<div class="sk-siklus-stats">
				<div class="sk-siklus-stat">
					<div class="sk-siklus-stat-val sk-siklus-stat-fcr"><?php echo $performa['fcr'][$last]; ?></div>
					<div class="sk-siklus-stat-label">FCR</div>
				</div>
				<div class="sk-siklus-stat">
					<div class="sk-siklus-stat-val sk-siklus-stat-deplesi"><?php echo $performa['deplesi'][$last]; ?>%</div>
					<div class="sk-siklus-stat-label">DEPLESI</div>
				</div>
				<div class="sk-siklus-stat">
					<div class="sk-siklus-stat-val sk-siklus-stat-adg"><?php echo $performa['adg'][$last]; ?>g</div>
					<div class="sk-siklus-stat-label">ADG</div>
				</div>
				<div class="sk-siklus-stat">
					<div class="sk-siklus-stat-val sk-siklus-stat-ip"><?php echo $performa['ip'][$last]; ?></div>
					<div class="sk-siklus-stat-label">IP</div>
				</div>
			</div>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if ( !empty($ayam) ): ?>
	<div class="sk-ayam-card sk-collapsible sk-card-collapsed">
		<div class="sk-ayam-head sk-card-head-toggle" onclick="sk.toggleCard(this)">
			<span class="sk-card-head-title"><i class="fa fa-paw"></i> STOK AYAM</span>
			<?php if ( !empty($ayam['umur']) ): ?>
				<span class="sk-ayam-umur">UMUR <?php echo (int) $ayam['umur']; ?> HARI</span>
			<?php endif ?>
			<i class="fa fa-chevron-down sk-card-toggle"></i>
		</div>
		<div class="sk-ayam-hero sk-clickable sk-collapse-body" onclick="sk.getDetailAyam()">
			<div class="sk-ayam-hero-num"><?php echo angkaRibuan($ayam['sisa_ekor']); ?></div>
			<div class="sk-ayam-hero-label">SISA EKOR <i class="fa fa-chevron-right"></i></div>
		</div>
		<div class="sk-ayam-body sk-collapse-body">
			<div class="sk-ayam-row">
				<span class="sk-ayam-key"><i class="fa fa-sign-in"></i> Populasi</span>
				<span class="sk-ayam-val"><?php echo angkaRibuan($ayam['populasi']); ?> ekor</span>
			</div>
			<div class="sk-ayam-row">
				<span class="sk-ayam-key"><i class="fa fa-heartbeat"></i> Mati</span>
				<span class="sk-ayam-val"><?php echo angkaRibuan($ayam['ekor_mati_real']); ?> ekor</span>
			</div>
			<div class="sk-ayam-row">
				<span class="sk-ayam-key"><i class="fa fa-truck"></i> Terjual/Panen</span>
				<span class="sk-ayam-val"><?php echo angkaRibuan($ayam['ekor_jual']); ?> ekor</span>
			</div>
			<?php if ( !empty($ayam['bw_rata_lhk']) ): ?>
			<div class="sk-ayam-row">
				<span class="sk-ayam-key"><i class="fa fa-balance-scale"></i> BW Rata-rata</span>
				<span class="sk-ayam-val"><?php echo angkaDecimal($ayam['bw_rata_lhk']); ?> kg</span>
			</div>
			<?php endif ?>
			<?php // di-hide dulu, lihat riwayat commit kalau mau diaktifkan lagi ?>
			<?php if ( false ): ?>
			<div class="sk-ayam-row">
				<span class="sk-ayam-key"><i class="fa fa-cube"></i> Sisa Tonase</span>
				<span class="sk-ayam-val"><?php echo angkaDecimal($ayam['sisa_tonase']); ?> kg</span>
			</div>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>

<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $key_jenis = null; ?>
	<?php foreach ($data as $key => $value): ?>
		<?php if ( $key_jenis !== $value['jenis_barang'] ): ?>
			<?php $key_jenis = $value['jenis_barang']; ?>
			<?php $is_ovk = ($value['jenis_barang'] === 'voadip'); ?>
			<div class="sk-group-card sk-collapsible sk-card-collapsed">
				<div class="sk-group-head <?php echo $is_ovk ? 'sk-group-head-ovk' : 'sk-group-head-pakan'; ?> sk-card-head-toggle" onclick="sk.toggleCard(this)">
					<span class="sk-card-head-title"><i class="fa <?php echo $is_ovk ? 'fa-medkit' : 'fa-cubes'; ?>"></i> <?php echo strtoupper($is_ovk ? 'OVK' : $value['jenis_barang']); ?></span>
					<i class="fa fa-chevron-down sk-card-toggle"></i>
				</div>
				<div class="sk-group-body sk-collapse-body">
		<?php endif ?>
					<?php
						$qty = $value['jumlah'];
						$qty_display = ($qty >= 0) ? angkaDecimal($qty) : '('.angkaDecimal(abs($qty)).')';
						$zak_display = !$is_ovk ? number_format(abs($qty) / 50, 1, ',', '.') : null;
					?>
					<div class="sk-item-row sk-clickable" onclick="sk.getDetailBarang('<?php echo addslashes($value['kode_barang']); ?>', '<?php echo addslashes(strtoupper($value['nama_barang'])); ?>', '<?php echo addslashes($value['jenis_barang']); ?>')">
						<span class="sk-item-nama"><?php echo strtoupper($value['nama_barang']); ?></span>
						<span class="sk-item-qty-wrap">
							<span class="sk-item-jumlah <?php echo ($qty < 0) ? 'sk-item-minus' : ''; ?>"><?php echo $qty_display; ?><?php echo !$is_ovk ? ' kg' : ''; ?></span>
							<?php if ( !$is_ovk ): ?>
								<span class="sk-item-zak">&asymp; <?php echo $zak_display; ?> zak</span>
							<?php endif ?>
						</span>
						<i class="fa fa-chevron-right sk-item-chevron"></i>
					</div>
		<?php if ( !isset($data[$key+1]) || $data[$key+1]['jenis_barang'] !== $value['jenis_barang'] ): ?>
				</div>
			</div>
		<?php endif ?>
	<?php endforeach ?>
<?php else: ?>
	<div class="sk-empty-state">
		<i class="fa fa-inbox"></i>
		<div>Belum ada pergerakan stok pakan/OVK untuk siklus ini.</div>
	</div>
<?php endif ?>

<?php if ( !empty($performa) && !empty($performa['umur']) ): ?>
	<div class="sk-chart-card sk-collapsible sk-card-collapsed">
		<div class="sk-chart-head sk-card-head-toggle" onclick="sk.toggleCard(this)">
			<span class="sk-card-head-title"><i class="fa fa-line-chart"></i> PERFORMANCE PETERNAK</span>
			<i class="fa fa-chevron-down sk-card-toggle"></i>
		</div>
		<div class="sk-chart-body sk-collapse-body">
			<div class="sk-chart-period">
				<button type="button" class="sk-chart-period-btn active" onclick="sk.setChartPeriod(this, 'harian')">Harian</button>
				<button type="button" class="sk-chart-period-btn" onclick="sk.setChartPeriod(this, 'mingguan')">Mingguan</button>
			</div>
			<div class="sk-chart-range hide"><i class="fa fa-arrows-h"></i> Geser chart untuk lihat hari lain &middot; <span id="sk-chart-range-text"></span></div>
			<div class="sk-chart-item">
				<div class="sk-chart-label">DEPLESI (%) <span class="sk-chart-val" id="sk-val-deplesi"></span></div>
				<div class="sk-chart-canvas-wrap"><canvas id="sk-chart-deplesi"></canvas></div>
			</div>
			<div class="sk-chart-item">
				<div class="sk-chart-label">FCR <span class="sk-chart-val" id="sk-val-fcr"></span></div>
				<div class="sk-chart-canvas-wrap"><canvas id="sk-chart-fcr"></canvas></div>
			</div>
			<div class="sk-chart-item">
				<div class="sk-chart-label">BW (kg) <span class="sk-chart-val" id="sk-val-bw"></span></div>
				<div class="sk-chart-canvas-wrap"><canvas id="sk-chart-bw"></canvas></div>
			</div>
			<div class="sk-chart-item">
				<div class="sk-chart-label">PERBANDINGAN FI vs ADG</div>
				<div class="sk-chart-canvas-wrap sk-chart-canvas-wrap-lg"><canvas id="sk-chart-compare"></canvas></div>
			</div>
		</div>
	</div>
<?php endif ?>
