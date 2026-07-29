var pphUnifikasi = {
	pageSize: 100,

	state: {
		rows: [],
		page: 1,
	},

	startUp: function() {
		pphUnifikasi.settingUp();
	}, // end - startUp

	settingUp: function() {
		var form = $('form.pph-unifikasi');

		form.find("#Tahun").datetimepicker({
			locale: 'id',
			format: 'Y'
		});

		form.find('.bulan').select2({placeholder: 'Pilih Bulan'});

		form.find('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
			var perusahaan = form.find('.perusahaan').select2().val();

			for (var i = 0; i < perusahaan.length; i++) {
				if ( perusahaan[i] == 'all' ) {
					form.find('.perusahaan').select2().val('all').trigger('change');

					i = perusahaan.length;
				}
			}
		});

		form.find('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
			var unit = form.find('.unit').select2().val();

			for (var i = 0; i < unit.length; i++) {
				if ( unit[i] == 'all' ) {
					form.find('.unit').select2().val('all').trigger('change');

					i = unit.length;
				}
			}
		});

		form.on('click', '.pagination-pph .btn-prev', function() { pphUnifikasi.gotoPage(pphUnifikasi.state.page - 1); });
		form.on('click', '.pagination-pph .btn-next', function() { pphUnifikasi.gotoPage(pphUnifikasi.state.page + 1); });
	}, // end - settingUp

	getParams: function() {
		var form = $('form.pph-unifikasi');

		return {
			'bulan': form.find('.bulan').select2().val(),
			'tahun': dateSQL(form.find('#Tahun').data('DateTimePicker').date()).substring(0, 4),
			'perusahaan': form.find('.perusahaan').select2('val'),
			'unit': form.find('.unit').select2('val'),
		};
	}, // end - getParams

	getLists: function(elm) {
		var form = $('form.pph-unifikasi');

		var err = 0;

		$.map( form.find('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var params = pphUnifikasi.getParams();

			$.ajax({
	            url: 'report/PphUnifikasi/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                pphUnifikasi.state.rows = data.data;
	                pphUnifikasi.state.page = 1;

	                pphUnifikasi.renderPage();
	                pphUnifikasi.setTotals(data.total);
	            }
	        });
		}
	}, // end - getLists

	gotoPage: function(page) {
		var state = pphUnifikasi.state;
		var totalPages = Math.max(1, Math.ceil(state.rows.length / pphUnifikasi.pageSize));

		page = Math.max(1, Math.min(page, totalPages));
		state.page = page;

		pphUnifikasi.renderPage();
	}, // end - gotoPage

	renderPage: function() {
		var state = pphUnifikasi.state;
		var start = (state.page - 1) * pphUnifikasi.pageSize;
		var pageRows = state.rows.slice(start, start + pphUnifikasi.pageSize);

		var html = '';
		if ( pageRows.length == 0 ) {
			html = '<tr><td colspan="15">Data tidak ditemukan.</td></tr>';
		} else {
			$.map( pageRows, function(row) { html += pphUnifikasi.buildRow(row); } );
		}

		$('table.tbl_pph tbody').html( html );
		pphUnifikasi.renderPagination();
	}, // end - renderPage

	renderPagination: function() {
		var state = pphUnifikasi.state;
		var totalRows = state.rows.length;
		var totalPages = Math.max(1, Math.ceil(totalRows / pphUnifikasi.pageSize));
		var bar = $('.pagination-pph');

		bar.find('.page-info').text('Halaman ' + state.page + ' dari ' + totalPages + ' (total ' + numeral.formatInt(totalRows) + ' baris)');
		bar.find('.btn-prev').prop('disabled', state.page <= 1);
		bar.find('.btn-next').prop('disabled', state.page >= totalPages);
	}, // end - renderPagination

	setTotals: function(total) {
		$('table.tbl_pph thead td.total[data-target=bruto] b').html( numeral.formatInt(total.bruto) );
	}, // end - setTotals

	buildRow: function(row) {
		return '<tr class="data">'
			+ '<td class="text-center">'+row[0]+'</td>'
			+ '<td class="text-center">'+row[1]+'</td>'
			+ '<td class="text-center">'+row[2]+'</td>'
			+ '<td class="text-center">'+row[3]+'</td>'
			+ '<td>'+row[4]+'</td>'
			+ '<td>'+row[5]+'</td>'
			+ '<td>'+row[6]+'</td>'
			+ '<td>'+row[7]+'</td>'
			+ '<td class="text-center">'+row[8]+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[9])+'</td>'
			+ '<td class="text-center">'+row[10]+'</td>'
			+ '<td>'+row[11]+'</td>'
			+ '<td>'+row[12]+'</td>'
			+ '<td>'+row[13]+'</td>'
			+ '<td class="text-center">'+row[14]+'</td>'
			+ '</tr>';
	}, // end - buildRow

	excryptParams: function(elm) {
		var form = $('form.pph-unifikasi');

		var err = 0;

		$.map( form.find('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var params = pphUnifikasi.getParams();

			$.ajax({
	            url: 'report/PphUnifikasi/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						pphUnifikasi.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/PphUnifikasi/exportExcel/'+params);
	}, // end - exportExcel
};

pphUnifikasi.startUp();
