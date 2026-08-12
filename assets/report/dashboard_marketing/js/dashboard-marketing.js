var dm = {
	charts: {},
	kesiapanDetail: null,
	topBakulHutangShowAll: false,
	hutangBakulDetail: {
		data: null,
		unit: null,
		sort: { field: 'hutang', dir: 'desc' },
	},

	startUp: function() {
		dm.settingUp();
		dm.reload();
	}, // end - startUp

	settingUp: function() {
		$('select.dm-unit').select2({
			placeholder: 'Pilih unit ...',
			minimumResultsForSearch: 8,
		});
		$('select.dm-unit').val('all').trigger('change');

		$('#dm-modal-hutang-bakul thead').on('click', '.dm-sortable', function() {
			dm.sortHutangBakulDetail( $(this).data('sort') );
		});

		$('#dm-modal-hutang-bakul-body').on('click', '.dm-invoice-link', function() {
			dm.exportInvoicePdf( $(this).data('pelanggan'), dm.hutangBakulDetail.unit );
		});

		$('#dm-top-bakul-hutang-body').on('click', '.dm-invoice-link', function() {
			dm.exportInvoicePdf( $(this).data('pelanggan'), $(this).data('unit') );
		});
	}, // end - settingUp

	reload: function() {
		var unit = $('select.dm-unit').val() || 'all';

		dm.loadKesiapanPanen( unit );
		dm.loadHargaRealisasi( unit );
		dm.loadVolumeChannel( unit );
		dm.loadVolumeUnit( unit );
		dm.loadHutangBakul( unit );
		dm.loadTopBakulVolume( unit );
		dm.loadTopBakulHutang( unit );
	}, // end - reload

	/**************************************************************************************
	 * BAGIAN 1 - KESIAPAN PANEN
	 **************************************************************************************/
	loadKesiapanPanen: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getKesiapanPanen',
			data: { 'params': { 'unit': unit, 'umur_min': 28, 'umur_max': 35 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				var c = data.content;

				$('#dm-umur-range').text( '(umur '+c.umur_min+'-'+c.umur_max+' hari)' );
				$('#dm-total-tonase').text( numeral.formatDec(c.total_tonase_siap)+' kg' );
				$('#dm-total-ekor').text( numeral.formatInt(c.total_ekor_siap)+' ekor' );
				$('#dm-total-tonase-ton').text( '≈ '+numeral.formatDec(c.total_tonase_siap / 1000)+' ton' );
				$('#dm-jml-siklus').text( numeral.formatInt(c.jml_siklus_siap) );

				$('#dm-bw-kecil-ekor').text( numeral.formatInt(c.bw_kecil.ekor) );
				$('#dm-bw-kecil-tonase').text( numeral.formatDec(c.bw_kecil.tonase) );
				$('#dm-bw-kecil-ton').text( '≈ '+numeral.formatDec(c.bw_kecil.tonase / 1000)+' ton' );
				$('#dm-bw-besar-ekor').text( numeral.formatInt(c.bw_besar.ekor) );
				$('#dm-bw-besar-tonase').text( numeral.formatDec(c.bw_besar.tonase) );
				$('#dm-bw-besar-ton').text( '≈ '+numeral.formatDec(c.bw_besar.tonase / 1000)+' ton' );
				$('#dm-bw-jumbo-ekor').text( numeral.formatInt(c.bw_jumbo.ekor) );
				$('#dm-bw-jumbo-tonase').text( numeral.formatDec(c.bw_jumbo.tonase) );
				$('#dm-bw-jumbo-ton').text( '≈ '+numeral.formatDec(c.bw_jumbo.tonase / 1000)+' ton' );

				$('#dm-umur-range-unit').text( '(umur '+c.umur_min+'-'+c.umur_max+' hari)' );
				dm.kesiapanDetail = c.detail;
				dm.renderKesiapanUnitChart( c.per_unit );
			}
		});
	}, // end - loadKesiapanPanen

	renderKesiapanUnitChart: function(perUnit) {
		if ( dm.charts.kesiapanUnit ) { dm.charts.kesiapanUnit.destroy(); }

		if ( empty(perUnit) ) {
			return;
		}

		var labels = $.map(perUnit, function(v) { return v.unit; });
		var values = $.map(perUnit, function(v) { return v.tonase; });

		dm.charts.kesiapanUnit = new Chart('dm-chart-kesiapan-unit', {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [{
					label: 'Tonase Siap Panen (kg)',
					data: values,
					backgroundColor: '#337ab7',
					borderRadius: 4,
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				onHover: function(event, elements) {
					event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
				},
				onClick: function(event, elements) {
					if ( empty(elements) ) { return; }

					var idx = elements[0].index;
					dm.showKesiapanUnitDetail( labels[idx] );
				},
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: function(context) {
								return numeral.formatDec(context.parsed.y)+' kg';
							}
						}
					},
				},
				scales: {
					x: { grid: { display: false } },
					y: { grid: { color: '#f0f0f0' }, beginAtZero: true },
				},
			},
		});
	}, // end - renderKesiapanUnitChart

	showKesiapanUnitDetail: function(unitKode) {
		$('#dm-modal-kesiapan-unit-unit').text( unitKode );

		var $body = $('#dm-modal-kesiapan-unit-body');
		$body.empty();

		var rows = $.grep(dm.kesiapanDetail || [], function(v) { return v.unit === unitKode; });

		var totalEkor = 0;
		var totalTonase = 0;

		if ( empty(rows) ) {
			$body.append('<tr><td colspan="5" class="text-center text-muted">Tidak ada siklus siap panen utk unit ini.</td></tr>');
		} else {
			$.each(rows, function(i, v) {
				totalEkor += v.sisa_ekor;
				totalTonase += v.tonase;

				$body.append(
					'<tr>' +
						'<td>'+(v.nama_plasma||'-').toUpperCase()+'</td>' +
						'<td class="text-right">'+numeral.formatInt(v.umur)+' hari</td>' +
						'<td class="text-right">'+numeral.formatDec(v.bb, 2)+'</td>' +
						'<td class="text-right">'+numeral.formatInt(v.sisa_ekor)+'</td>' +
						'<td class="text-right"><b>'+numeral.formatDec(v.tonase)+' kg</b></td>' +
					'</tr>'
				);
			});
		}

		$('#dm-modal-kesiapan-unit-total-ekor').text( numeral.formatInt(totalEkor) );
		$('#dm-modal-kesiapan-unit-total-tonase').text( numeral.formatDec(totalTonase)+' kg' );

		$('#dm-modal-kesiapan-unit').modal('show');
	}, // end - showKesiapanUnitDetail

	/**************************************************************************************
	 * BAGIAN 2 - PRICING
	 **************************************************************************************/
	loadHargaRealisasi: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getHargaRealisasi',
			data: { 'params': { 'unit': unit, 'hari': 14 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				var c = data.content;

				if ( dm.charts.harga ) { dm.charts.harga.destroy(); }

				if ( empty(c.tanggal) ) {
					return;
				}

				var labels = $.map(c.tanggal, function(t) {
					var parts = t.split('-');
					return parts[2]+'/'+parts[1];
				});

				dm.charts.harga = new Chart('dm-chart-harga', {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Harga Rata-rata (Rp/kg)',
							data: c.harga,
							borderColor: '#337ab7',
							backgroundColor: 'rgba(51,122,183,0.15)',
							fill: true,
							tension: 0.3,
							pointRadius: 3,
							pointHoverRadius: 5,
							borderWidth: 2,
						}]
					},
					options: dm.chartOptions('Rp'),
				});
			}
		});
	}, // end - loadHargaRealisasi

	/**************************************************************************************
	 * BAGIAN 3 - SALES PERFORMANCE
	 **************************************************************************************/
	loadVolumeChannel: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getVolumeChannel',
			data: { 'params': { 'unit': unit, 'hari': 30 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				dm.renderChannelChart( data.content );
			}
		});
	}, // end - loadVolumeChannel

	loadVolumeUnit: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getVolumeUnit',
			data: { 'params': { 'unit': unit, 'hari': 30 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				dm.renderVolumeUnitChart( data.content );
			}
		});
	}, // end - loadVolumeUnit

	renderVolumeUnitChart: function(content) {
		var totalOmset = 0;
		var totalTonase = 0;
		var totalEkor = 0;
		$.each((content && content.nilai) || [], function(i, v) { totalOmset += parseFloat(v) || 0; });
		$.each((content && content.volume_ton) || [], function(i, v) { totalTonase += parseFloat(v) || 0; });
		$.each((content && content.ekor) || [], function(i, v) { totalEkor += parseInt(v) || 0; });

		$('#dm-total-omset').text( dm.formatRupiahSingkat(totalOmset) );
		$('#dm-total-omset-rp').text( 'Rp '+numeral.formatInt(totalOmset) );
		$('#dm-total-omset-tonase').text( numeral.formatDec(totalTonase)+' ton' );
		$('#dm-total-omset-ekor').text( numeral.formatInt(totalEkor)+' ekor' );

		if ( dm.charts.unit ) { dm.charts.unit.destroy(); }

		if ( empty(content) || empty(content.unit) ) {
			return;
		}

		dm.charts.unit = new Chart('dm-chart-unit', {
			data: {
				labels: content.unit,
				datasets: [
					{
						type: 'bar',
						label: 'Volume (ton)',
						data: content.volume_ton,
						backgroundColor: '#f0ad4e',
						borderRadius: 4,
						yAxisID: 'y',
						order: 2,
					},
					{
						type: 'line',
						label: 'Omset (Rp)',
						data: content.nilai,
						borderColor: '#5cb85c',
						backgroundColor: '#5cb85c',
						fill: false,
						tension: 0.3,
						pointRadius: 4,
						pointHoverRadius: 6,
						borderWidth: 2,
						yAxisID: 'y1',
						order: 1,
					},
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: true, position: 'bottom' },
					tooltip: {
						callbacks: {
							label: function(context) {
								if ( context.dataset.yAxisID === 'y1' ) {
									return 'Omset: Rp '+numeral.formatInt(context.parsed.y);
								}

								var ekor = content.ekor[ context.dataIndex ];
								return [
									'Volume: '+numeral.formatDec(context.parsed.y)+' ton',
									numeral.formatInt(ekor)+' ekor',
								];
							}
						}
					},
				},
				scales: {
					x: { grid: { display: false } },
					y: {
						position: 'left',
						grid: { color: '#f0f0f0' },
						beginAtZero: true,
						title: { display: true, text: 'Ton' },
					},
					y1: {
						position: 'right',
						grid: { display: false },
						beginAtZero: true,
						title: { display: true, text: 'Rp' },
						ticks: {
							callback: function(value) { return dm.formatRupiahSingkat(value); }
						}
					},
				},
			},
		});
	}, // end - renderVolumeUnitChart

	loadHutangBakul: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getHutangBakul',
			data: { 'params': { 'unit': unit } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				dm.renderHutangBakulChart( data.content );
			}
		});
	}, // end - loadHutangBakul

	renderHutangBakulChart: function(content) {
		var totalHutang = 0;
		$.each((content && content.hutang) || [], function(i, v) { totalHutang += parseFloat(v) || 0; });

		$('#dm-total-hutang-bakul').text( dm.formatRupiahSingkat(totalHutang) );
		$('#dm-total-hutang-bakul-rp').text( 'Rp '+numeral.formatInt(totalHutang) );
		$('#dm-total-hutang-bakul-jml').text( numeral.formatInt((content && content.jml_bakul) || 0)+' bakul msh hutang' );

		if ( dm.charts.hutangBakul ) { dm.charts.hutangBakul.destroy(); }

		if ( empty(content) || empty(content.unit) ) {
			return;
		}

		dm.charts.hutangBakul = new Chart('dm-chart-hutang-bakul', {
			type: 'bar',
			data: {
				labels: content.unit,
				datasets: [{
					label: 'Hutang Bakul (Rp)',
					data: content.hutang,
					backgroundColor: '#d9534f',
					borderRadius: 4,
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				onHover: function(event, elements) {
					event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
				},
				onClick: function(event, elements) {
					if ( empty(elements) ) { return; }

					var idx = elements[0].index;
					var unitKode = content.unit[idx];

					dm.loadHutangBakulDetail( unitKode );
				},
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Rp '+numeral.formatInt(context.parsed.y);
							}
						}
					},
				},
				scales: {
					x: { grid: { display: false } },
					y: {
						grid: { color: '#f0f0f0' },
						beginAtZero: true,
						ticks: {
							callback: function(value) { return dm.formatRupiahSingkat(value); }
						}
					},
				},
			},
		});
	}, // end - renderHutangBakulChart

	loadHutangBakulDetail: function(unitKode) {
		$('#dm-modal-hutang-bakul-unit').text( unitKode );
		$('#dm-modal-hutang-bakul-body').html('<tr><td colspan="3" class="text-center text-muted">Memuat...</td></tr>');
		$('#dm-modal-hutang-bakul').modal('show');

		dm.hutangBakulDetail.data = null;
		dm.hutangBakulDetail.unit = unitKode;
		dm.hutangBakulDetail.sort = { field: 'hutang', dir: 'desc' };

		$.ajax({
			url: 'report/DashboardMarketing/getHutangBakulDetail',
			data: { 'params': { 'unit': unitKode } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				dm.hutangBakulDetail.data = ( data.status == 1 ) ? data.content : null;
				dm.renderHutangBakulDetailTable();
			}
		});
	}, // end - loadHutangBakulDetail

	sortHutangBakulDetail: function(field) {
		var sort = dm.hutangBakulDetail.sort;

		if ( sort.field === field ) {
			sort.dir = (sort.dir === 'asc') ? 'desc' : 'asc';
		} else {
			sort.field = field;
			sort.dir = (field === 'nama_pelanggan') ? 'asc' : 'desc';
		}

		dm.renderHutangBakulDetailTable();
	}, // end - sortHutangBakulDetail

	renderHutangBakulDetailTable: function() {
		var $body = $('#dm-modal-hutang-bakul-body');
		$body.empty();

		var data = dm.hutangBakulDetail.data;
		var sort = dm.hutangBakulDetail.sort;

		var totalInvoice = 0;
		var totalHutang = 0;

		if ( empty(data) ) {
			$body.append('<tr><td colspan="3" class="text-center text-muted">Tidak ada hutang bakul utk unit ini.</td></tr>');
		} else {
			var mult = (sort.dir === 'asc') ? 1 : -1;

			var rows = data.slice().sort(function(a, b) {
				var va = a[ sort.field ];
				var vb = b[ sort.field ];

				if ( sort.field === 'nama_pelanggan' ) {
					va = (va || '').toUpperCase();
					vb = (vb || '').toUpperCase();
					return mult * va.localeCompare(vb);
				}

				return mult * ((parseFloat(va) || 0) - (parseFloat(vb) || 0));
			});

			$.each(rows, function(i, v) {
				totalInvoice += parseInt(v.jml_invoice) || 0;
				totalHutang += parseFloat(v.hutang) || 0;

				$body.append(
					'<tr>' +
						'<td>'+(v.nama_pelanggan||'-').toUpperCase()+'</td>' +
						'<td class="text-right">'+numeral.formatInt(v.umur_tertua)+' hari<br><small class="dm-invoice-link" data-pelanggan="'+v.no_pelanggan+'" title="Cetak PDF detail invoice">'+numeral.formatInt(v.jml_invoice)+' invoice <i class="fa fa-file-pdf-o"></i></small></td>' +
						'<td class="text-right"><b>Rp '+numeral.formatInt(v.hutang)+'</b></td>' +
					'</tr>'
				);
			});
		}

		$('#dm-modal-hutang-bakul-total-invoice').text( numeral.formatInt(totalInvoice) );
		$('#dm-modal-hutang-bakul-total-hutang').text( 'Rp '+numeral.formatInt(totalHutang) );

		var $sortables = $('#dm-modal-hutang-bakul thead .dm-sortable');
		$sortables.removeClass('dm-sort-active').find('i').attr('class', 'fa fa-sort');

		var $active = $sortables.filter('[data-sort="'+sort.field+'"]');
		$active.addClass('dm-sort-active').find('i').attr('class', 'fa fa-sort-'+sort.dir);
	}, // end - renderHutangBakulDetailTable

	exportInvoicePdf: function(noPelanggan, unitKode) {
		$.ajax({
			url: 'report/DashboardMarketing/cekExportInvoicePdf',
			data: { 'params': { 'no_pelanggan': noPelanggan, 'unit': unitKode } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				if ( data.status != 1 ) { return; }

				goToURL( 'report/DashboardMarketing/exportInvoicePdf/'+data.content );
			}
		});
	}, // end - exportInvoicePdf

	loadTopBakulVolume: function(unit) {
		$.ajax({
			url: 'report/DashboardMarketing/getTopBakulVolume',
			data: { 'params': { 'unit': unit, 'hari': 30 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				var $body = $('#dm-top-bakul-volume-body');
				$body.empty();

				if ( data.status != 1 || empty(data.content) ) {
					$body.append('<tr><td colspan="4" class="text-center text-muted">Belum ada penjualan utk periode ini.</td></tr>');
					return;
				}

				$.each(data.content, function(i, v) {
					$body.append(
						'<tr>' +
							'<td>'+(i+1)+'</td>' +
							'<td>'+(v.nama_pelanggan||'-').toUpperCase()+'</td>' +
							'<td class="text-right">'+numeral.formatDec(v.volume_ton)+' ton</td>' +
							'<td class="text-right">Rp '+numeral.formatInt(v.nilai)+'</td>' +
						'</tr>'
					);
				});
			}
		});
	}, // end - loadTopBakulVolume

	loadTopBakulHutang: function(unit) {
		var showAll = dm.topBakulHutangShowAll;

		$('#dm-top-bakul-hutang-title').text( showAll ? 'Semua Bakul - Hutang Terbanyak' : 'Top 10 Bakul - Hutang Terbanyak' );
		$('#dm-top-bakul-hutang-toggle').text( showAll ? 'Tampilkan Top 10 Saja' : 'Tampilkan Semua' );

		$.ajax({
			url: 'report/DashboardMarketing/getTopBakulHutang',
			data: { 'params': { 'unit': unit, 'show_all': showAll ? 1 : 0 } },
			type: 'POST',
			dataType: 'JSON',
			success: function(data) {
				var $body = $('#dm-top-bakul-hutang-body');
				$body.empty();

				if ( data.status != 1 || empty(data.content) ) {
					$body.append('<tr><td colspan="4" class="text-center text-muted">Tidak ada hutang bakul saat ini.</td></tr>');
					return;
				}

				$.each(data.content, function(i, v) {
					$body.append(
						'<tr>' +
							'<td>'+(i+1)+'</td>' +
							'<td>'+(v.nama_pelanggan||'-').toUpperCase()+'</td>' +
							'<td class="text-right">'+numeral.formatInt(v.umur_tertua)+' hari<br><small class="dm-invoice-link" data-pelanggan="'+v.no_pelanggan+'" data-unit="'+unit+'" title="Cetak PDF detail invoice">'+numeral.formatInt(v.jml_invoice)+' invoice <i class="fa fa-file-pdf-o"></i></small></td>' +
							'<td class="text-right"><b>Rp '+numeral.formatInt(v.hutang)+'</b></td>' +
						'</tr>'
					);
				});
			}
		});
	}, // end - loadTopBakulHutang

	toggleTopBakulHutang: function() {
		dm.topBakulHutangShowAll = !dm.topBakulHutangShowAll;

		var unit = $('select.dm-unit').val() || 'all';
		dm.loadTopBakulHutang( unit );
	}, // end - toggleTopBakulHutang

	renderChannelChart: function(content) {
		if ( dm.charts.channel ) { dm.charts.channel.destroy(); }

		if ( empty(content) || empty(content.channel) ) {
			return;
		}

		var unitList = [];
		var channelList = [];
		var matrix = {};

		$.each(content.unit, function(i, u) {
			var c = content.channel[i];
			var val = content.volume_kg[i];

			if ( unitList.indexOf(u) === -1 ) { unitList.push(u); }
			if ( channelList.indexOf(c) === -1 ) { channelList.push(c); }

			if ( !matrix[c] ) { matrix[c] = {}; }
			matrix[c][u] = (matrix[c][u] || 0) + val;
		});

		var palette = ['#5cb85c', '#337ab7', '#f0ad4e', '#d9534f', '#5bc0de', '#9b59b6', '#34495e', '#e67e22'];

		var datasets = $.map(channelList, function(c, idx) {
			return {
				label: c,
				data: $.map(unitList, function(u) { return matrix[c][u] || 0; }),
				backgroundColor: palette[idx % palette.length],
				borderRadius: 4,
			};
		});

		dm.charts.channel = new Chart('dm-chart-channel', {
			type: 'bar',
			data: {
				labels: unitList,
				datasets: datasets,
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: true, position: 'bottom' },
					tooltip: {
						callbacks: {
							label: function(context) {
								return context.dataset.label+': '+numeral.formatDec(context.parsed.y)+' kg';
							}
						}
					},
				},
				scales: {
					x: { grid: { display: false } },
					y: { grid: { color: '#f0f0f0' }, beginAtZero: true },
				},
			},
		});
	}, // end - renderChannelChart

	formatRupiahSingkat: function(value) {
		var abs = Math.abs(value);

		if ( abs >= 1000000000 ) {
			return numeral.formatDec(value / 1000000000)+' M';
		}

		if ( abs >= 1000000 ) {
			return numeral.formatDec(value / 1000000)+' Jt';
		}

		return numeral.formatInt(value);
	}, // end - formatRupiahSingkat

	chartOptions: function(unitLabel) {
		return {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: function(context) {
							return numeral.formatDec(context.parsed.y)+' '+unitLabel;
						}
					}
				},
			},
			scales: {
				x: { grid: { display: false } },
				y: { grid: { color: '#f0f0f0' }, beginAtZero: true },
			},
		};
	}, // end - chartOptions
};

dm.startUp();
