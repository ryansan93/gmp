<div class="col-xs-12 no-padding dashboard_dirut">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding docin" style="border: 1px solid black;">
				<div class="data col-xs-12 no-padding">
					<div class="col-xs-12 no-padding">
						<label class="control-label col-xs-4 text-left">DOCIN</label>
						<label class="control-label col-xs-8 text-right"><?php echo strtoupper(substr(tglIndonesia($today, '-', ' '), 3, strlen(tglIndonesia($today, '-', ' ')))); ?></label>
					</div>
					<div class="col-xs-12 no-padding"><hr style="margin: 0px;"></div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-7 text-left">Populasi</label>
							<label class="control-label col-xs-5 text-right jml_ekor"><?php echo isset($data_summary['docin']['jml_ekor']) ? angkaRibuan($data_summary['docin']['jml_ekor'] / 100).' Box' : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-7 text-left">Plasma</label>
							<label class="control-label col-xs-5 text-right jml_kdg"><?php echo isset($data_summary['docin']['jml_kdg']) ? angkaRibuan($data_summary['docin']['jml_kdg']).' Kdg' : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<!-- <label class="control-label col-xs-12 text-left">&nbsp;</label> -->
							<label class="control-label col-xs-6 text-left">Harga DOC</label>
							<label class="control-label col-xs-6 text-right rata_harga_doc"><?php echo isset($data_summary['docin']['rata_harga_doc']) ? 'Rp. '.angkaDecimal($data_summary['docin']['rata_harga_doc']) : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-12 text-left">&nbsp;</label>
							<!-- <label class="control-label col-xs-6 text-left">Harga Pakan</label>
							<label class="control-label col-xs-6 text-right rata_harga_pakan"><?php echo isset($data_summary['docin']['rata_harga_pakan']) ? 'Rp. '.angkaDecimal($data_summary['docin']['rata_harga_pakan']) : '-'; ?></label> -->
						</div>
					</div>
				</div>
				<div class="loading col-xs-12 no-padding"></div>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding panen" style="border: 1px solid black;">
				<div class="data col-xs-12 no-padding">
					<div class="col-xs-12 no-padding">
						<label class="control-label col-xs-4 text-left">PANEN</label>
						<label class="control-label col-xs-8 text-right"><?php echo strtoupper(substr(tglIndonesia($today, '-', ' '), 3, strlen(tglIndonesia($today, '-', ' ')))); ?></label>
					</div>
					<div class="col-xs-12 no-padding"><hr style="margin: 0px;"></div>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-4 text-left">Tonase</label>
							<label class="control-label col-xs-8 text-right tonase"><?php echo isset($data_summary['panen']['tonase']) ? angkaDecimal($data_summary['panen']['tonase']).' Kg' : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-4 text-left">Ekor</label>
							<label class="control-label col-xs-8 text-right ekor"><?php echo isset($data_summary['panen']['ekor']) ? angkaRibuan($data_summary['panen']['ekor']).' Ekor' : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-6 text-left">Lama Panen</label>
							<label class="control-label col-xs-6 text-right rata_lama_panen"><?php echo isset($data_summary['panen']['rata_lama_panen']) ? angkaDecimal($data_summary['panen']['rata_lama_panen']).' Hari' : '-'; ?></label>
						</div>
						<div class="col-xs-12 no-padding">
							<label class="control-label col-xs-5 text-left">Hrg Rata2</label>
							<label class="control-label col-xs-7 text-right rata_harga"><?php echo isset($data_summary['panen']['rata_harga']) ? 'Rp. '.angkaDecimal($data_summary['panen']['rata_harga']) : '-'; ?></label>
						</div>
					</div>
				</div>
				<div class="loading col-xs-12 no-padding"></div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px; border-color: #dedede;"></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<canvas id="chart_penjualan_dan_harga" style="width:100%;"></canvas>
		</div>
	</div>
	<!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px; border-color: #dedede;"></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<canvas id="chart_plasma_merah" style="width:100%;"></canvas>
		</div>
	</div> -->
</div>