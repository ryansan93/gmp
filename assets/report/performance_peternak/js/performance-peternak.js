var sk = {
	startUp: function() {
		sk.settingUp();
	}, // end - startUp

	charts: {},
	currentPerforma: null,
	chartPeriod: 'harian',

	toggleCard: function(headerEl) {
		var card = $(headerEl).closest('.sk-collapsible');
		card.toggleClass('sk-card-collapsed');

		if ( !card.hasClass('sk-card-collapsed') && card.hasClass('sk-chart-card') ) {
			// chart-nya baru bisa diukur & dibangun dgn benar begitu card ini kelihatan
			// (sebelum dibuka lebar container-nya 0), makanya dibuat lazy di sini
			window.requestAnimationFrame(function() {
				sk.buildCharts();
			});
		}
	}, // end - toggleCard

	settingUp: function() {
		$('select.siklus').select2({
			ajax: {
				url: 'report/PerformancePeternak/getPlasma',
				dataType: 'json',
				type: 'GET',
				data: function (params) {
					return {
						search: params.term,
					};
				},
				processResults: function (data) {
					return {
						results: !empty(data) ? data : []
					};
				},
				error: function (jqXHR, status, error) {
					return { results: [] };
				}
			},
			cache: true,
			placeholder: 'Cari nama plasma atau noreg ...',
			minimumInputLength: 2,
		});
	}, // end - settingUp

	getData: function() {
		var err = 0;
		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap pilih siklus terlebih dahulu.');
		} else {
			var params = {
				'noreg': $('select.siklus').val(),
			};

			$.ajax({
				url: 'report/PerformancePeternak/getData',
				data: {
					'params': params
				},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function(){ showLoading(); },
				success: function(data){
					hideLoading();

					if ( data.status == 1 ) {
						$('.hasil').html( data.html );

						sk.currentPerforma = data.performa;
						sk.chartPeriod = 'harian';
						sk.charts = {};
					} else {
						bootbox.alert( data.message );
					}
				}
			});
		}
	}, // end - getData

	setChartPeriod: function(btnEl, period) {
		sk.chartPeriod = period;
		$('.sk-chart-period-btn').removeClass('active');
		$(btnEl).addClass('active');
		sk.buildCharts();
	}, // end - setChartPeriod

	dayWindow: null,

	/**
	 * Deplesi/FCR/ADG/BW dibaca dari lhk per hari, jadi cukup 1x ambil data (mode harian) dari
	 * server; mode mingguan tinggal di-downsample di client (ambil baris umur kelipatan 7, alias
	 * akhir minggu) tanpa perlu ke server lagi, soalnya angkanya memang snapshot/kumulatif per
	 * hari, bukan yang perlu dijumlah/dirata-rata.
	 *
	 * Mode harian TIDAK dibuat lebar-lalu-di-scroll (itu cuma geser viewport di atas gambar
	 * statis) — chart-nya sendiri yang cuma nampilin jendela 7 hari, dan digeser (drag/swipe
	 * langsung di chart) lewat sk.bindChartPan()/sk.updateDayWindow() yang redraw ulang datanya.
	 */
	buildCharts: function() {
		var performa = sk.currentPerforma;
		if ( empty(performa) || empty(performa.umur) ) {
			return;
		}

		var src = (sk.chartPeriod === 'mingguan') ? sk.aggregateWeekly(performa) : performa;
		var lastIdx = src.umur.length - 1;

		$('#sk-val-bw').text( src.bw[lastIdx]+' kg' );
		$('#sk-val-fcr').text( src.fcr[lastIdx] );
		$('#sk-val-deplesi').text( src.deplesi[lastIdx]+'%' );

		var fullLabels = (sk.chartPeriod === 'mingguan')
			? $.map(src.umur, function(u){ return 'M'+Math.ceil(u/7); })
			: $.map(src.umur, function(u){ return 'H'+u; });

		$.each(sk.charts, function(key, chart) {
			if ( chart ) { chart.destroy(); }
		});
		sk.charts = {};

		var fullData = { deplesi: src.deplesi, fcr: src.fcr, adg: src.adg, bw: src.bw, fi: src.fi, ekor_mati: src.ekor_mati };
		var labels = fullLabels;
		var dataSet = fullData;

		if ( sk.chartPeriod === 'harian' ) {
			var total = fullLabels.length;
			var size = Math.min(7, total);
			var start = Math.max(0, total - size);

			sk.dayWindow = { total: total, size: size, start: start, labels: fullLabels, data: fullData };

			labels = fullLabels.slice(start, start + size);
			dataSet = {
				deplesi: fullData.deplesi.slice(start, start + size),
				fcr: fullData.fcr.slice(start, start + size),
				adg: fullData.adg.slice(start, start + size),
				bw: fullData.bw.slice(start, start + size),
				fi: fullData.fi.slice(start, start + size),
				ekor_mati: fullData.ekor_mati.slice(start, start + size),
			};

			$('.sk-chart-range').removeClass('hide');
			$('#sk-chart-range-text').text( labels[0]+' - '+labels[labels.length-1] );
		} else {
			sk.dayWindow = null;
			$('.sk-chart-range').addClass('hide');
		}

		sk.charts.deplesi = sk.buildOneChart('sk-chart-deplesi', dataSet.deplesi, labels, '#d9534f', 'rgba(217,83,79,0.15)', function(context) {
			var ekor = (context.chart.$ekorMati || [])[context.dataIndex];
			return (ekor !== undefined) ? (context.formattedValue+'%  ('+ekor+' ekor mati)') : (context.formattedValue+'%');
		});
		sk.charts.deplesi.$ekorMati = dataSet.ekor_mati;

		sk.charts.fcr = sk.buildOneChart('sk-chart-fcr', dataSet.fcr, labels, '#337ab7', 'rgba(51,122,183,0.15)');
		sk.charts.bw = sk.buildOneChart('sk-chart-bw', dataSet.bw, labels, '#5cb85c', 'rgba(92,184,92,0.15)');
		sk.charts.compare = sk.buildCompareChart(labels, dataSet.fi, dataSet.adg);

		if ( sk.chartPeriod === 'harian' ) {
			sk.bindChartPan();
		} else {
			sk.unbindChartPan();
		}
	}, // end - buildCharts

	buildOneChart: function(canvasId, dataArr, labels, color, bgColor, tooltipLabelFn) {
		return new Chart(canvasId, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [{
					data: dataArr,
					borderColor: color,
					backgroundColor: bgColor,
					fill: true,
					tension: 0.3,
					pointRadius: 3,
					pointHoverRadius: 5,
					pointHitRadius: 10,
					pointBackgroundColor: color,
					borderWidth: 2,
				}]
			},
			options: sk.miniChartOptions(tooltipLabelFn),
		});
	}, // end - buildOneChart

	buildCompareChart: function(labels, fiData, adgData) {
		return new Chart('sk-chart-compare', {
			data: {
				labels: labels,
				datasets: [
					{
						yAxisID: 'A',
						type: 'bar',
						label: 'FI (g)',
						data: fiData,
						backgroundColor: 'rgba(51,122,183,0.7)',
						borderColor: '#337ab7',
						borderWidth: 1,
						borderRadius: 3,
					},
					{
						yAxisID: 'B',
						type: 'bar',
						label: 'ADG (g)',
						data: adgData,
						backgroundColor: 'rgba(240,173,78,0.7)',
						borderColor: '#f0ad4e',
						borderWidth: 1,
						borderRadius: 3,
					},
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				interaction: { mode: 'nearest', intersect: true },
				scales: {
					x: {
						grid: { display: false },
						ticks: { autoSkip: false, font: { size: 10 } },
					},
					A: {
						type: 'linear',
						position: 'left',
						ticks: { color: '#337ab7', font: { size: 10 } },
						grid: { color: '#f0f0f0' },
					},
					B: {
						type: 'linear',
						position: 'right',
						ticks: { color: '#f0ad4e', font: { size: 10 } },
						grid: { display: false },
					},
				},
				plugins: {
					legend: {
						display: true,
						position: 'top',
						labels: { boxWidth: 10, font: { size: 10 } },
					},
					tooltip: { displayColors: true },
				},
			}
		});
	}, // end - buildCompareChart

	/**
	 * Drag/swipe langsung di canvas chart buat geser jendela 7 hari yang ditampilkan (bukan
	 * scroll viewport). Ke-4 mini chart (deplesi/fcr/adg/bw) digeser bareng krn window hari-nya
	 * sama, jadi cukup 1 state (sk.dayWindow) yang dipakai bareng.
	 */
	bindChartPan: function() {
		var $canvases = $('.sk-chart-canvas-wrap canvas');
		$canvases.off('.skpan');

		var dragging = false;
		var startX = 0;
		var baseStart = 0;
		var pxPerDay = 40;

		$canvases.on('pointerdown.skpan', function(e){
			if ( empty(sk.dayWindow) ) { return; }

			dragging = true;
			startX = e.originalEvent.clientX;
			baseStart = sk.dayWindow.start;
			pxPerDay = Math.max(20, $(this).width() / sk.dayWindow.size);

			if ( this.setPointerCapture ) {
				try { this.setPointerCapture(e.originalEvent.pointerId); } catch (err) {}
			}
		}).on('pointermove.skpan', function(e){
			if ( !dragging || empty(sk.dayWindow) ) { return; }

			var deltaX = e.originalEvent.clientX - startX;
			var shift = Math.round(-deltaX / pxPerDay);
			var maxStart = Math.max(0, sk.dayWindow.total - sk.dayWindow.size);
			var newStart = Math.min(maxStart, Math.max(0, baseStart + shift));

			if ( newStart !== sk.dayWindow.start ) {
				sk.dayWindow.start = newStart;
				sk.updateDayWindow();
			}
		}).on('pointerup.skpan pointercancel.skpan pointerleave.skpan', function(e){
			dragging = false;
		});
	}, // end - bindChartPan

	unbindChartPan: function() {
		$('.sk-chart-canvas-wrap canvas').off('.skpan');
	}, // end - unbindChartPan

	updateDayWindow: function() {
		var w = sk.dayWindow;
		if ( empty(w) ) { return; }

		var end = w.start + w.size;
		var labels = w.labels.slice(w.start, end);

		$('#sk-chart-range-text').text( labels[0]+' - '+labels[labels.length-1] );

		$.each(sk.charts, function(key, chart){
			if ( !chart ) { return; }

			if ( key === 'compare' ) {
				chart.data.labels = labels;
				chart.data.datasets[0].data = w.data.fi.slice(w.start, end);
				chart.data.datasets[1].data = w.data.adg.slice(w.start, end);
				chart.update('none');
			} else if ( !empty(w.data[key]) ) {
				chart.data.labels = labels;
				chart.data.datasets[0].data = w.data[key].slice(w.start, end);

				if ( key === 'deplesi' && !empty(w.data.ekor_mati) ) {
					chart.$ekorMati = w.data.ekor_mati.slice(w.start, end);
				}

				chart.update('none');
			}
		});
	}, // end - updateDayWindow

	aggregateWeekly: function(performa) {
		var out = { umur: [], bw: [], fcr: [], adg: [], fi: [], deplesi: [], ekor_mati: [] };
		var n = performa.umur.length;

		for (var i = 0; i < n; i++) {
			var isEndOfWeek = (performa.umur[i] % 7 === 0);
			var isLastRow = (i === n - 1);

			if ( isEndOfWeek || isLastRow ) {
				out.umur.push( performa.umur[i] );
				out.bw.push( performa.bw[i] );
				out.fcr.push( performa.fcr[i] );
				out.adg.push( performa.adg[i] );
				out.fi.push( performa.fi[i] );
				out.deplesi.push( performa.deplesi[i] );
				out.ekor_mati.push( performa.ekor_mati[i] );
			}
		}

		return out;
	}, // end - aggregateWeekly

	miniChartOptions: function(tooltipLabelFn) {
		var tooltipOpt = { displayColors: false };
		if ( tooltipLabelFn ) {
			tooltipOpt.callbacks = { label: tooltipLabelFn };
		}

		return {
			responsive: true,
			maintainAspectRatio: false,
			interaction: { mode: 'nearest', intersect: true },
			scales: {
				x: {
					grid: { display: false },
					ticks: { autoSkip: false, font: { size: 10 } },
				},
				y: {
					grid: { color: '#f0f0f0' },
					ticks: { font: { size: 10 } },
				},
			},
			plugins: {
				legend: { display: false },
				tooltip: tooltipOpt,
			},
		};
	}, // end - miniChartOptions

	getDetailAyam: function() {
		var noreg = $('select.siklus').val();

		$.ajax({
			url: 'report/PerformancePeternak/getDetailAyam',
			data: {
				'params': { 'noreg': noreg }
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function(){ showLoading(); },
			success: function(data){
				hideLoading();

				if ( data.status == 1 ) {
					bootbox.dialog({
						title: 'Riwayat Transaksi Ayam',
						message: data.html,
						closeButton: true,
						buttons: {
							tutup: {
								label: 'Tutup',
								className: 'btn-default btn-block',
								callback: function(){},
							},
						},
					});
				} else {
					bootbox.alert( data.message );
				}
			}
		});
	}, // end - getDetailAyam

	getDetailBarang: function(kode_barang, nama_barang) {
		var noreg = $('select.siklus').val();

		$.ajax({
			url: 'report/PerformancePeternak/getDetailBarang',
			data: {
				'params': {
					'noreg': noreg,
					'kode_barang': kode_barang,
					'nama_barang': nama_barang,
				}
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function(){ showLoading(); },
			success: function(data){
				hideLoading();

				if ( data.status == 1 ) {
					bootbox.dialog({
						title: nama_barang,
						message: data.html,
						closeButton: true,
						buttons: {
							tutup: {
								label: 'Tutup',
								className: 'btn-default btn-block',
								callback: function(){},
							},
						},
					});
				} else {
					bootbox.alert( data.message );
				}
			}
		});
	}, // end - getDetailBarang
};

sk.startUp();
