var fpOrtax = {
	pageSize: 100,

	state: {
		faktur: { rows: [], page: 1 },
		detail: { rows: [], page: 1 },
	},

	startUp: function() {
		fpOrtax.settingUp();
	}, // end - startUp

	settingUp: function() {
		var form = $('form.fp-ortax');

		form.find("#StartDate").datetimepicker({
			locale: 'id',
			format: 'DD MMM Y'
		});
		form.find("#EndDate").datetimepicker({
			locale: 'id',
			format: 'DD MMM Y'
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

		form.find('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
			var perusahaan = form.find('.perusahaan').select2().val();

			for (var i = 0; i < perusahaan.length; i++) {
				if ( perusahaan[i] == 'all' ) {
					form.find('.perusahaan').select2().val('all').trigger('change');

					i = perusahaan.length;
				}
			}
		});

		form.find('.tutup_siklus').select2();

		form.on('click', '.pagination-faktur .btn-prev', function() { fpOrtax.gotoPage('faktur', fpOrtax.state.faktur.page - 1); });
		form.on('click', '.pagination-faktur .btn-next', function() { fpOrtax.gotoPage('faktur', fpOrtax.state.faktur.page + 1); });
		form.on('click', '.pagination-detail .btn-prev', function() { fpOrtax.gotoPage('detail', fpOrtax.state.detail.page - 1); });
		form.on('click', '.pagination-detail .btn-next', function() { fpOrtax.gotoPage('detail', fpOrtax.state.detail.page + 1); });
	}, // end - settingUp

	getParams: function() {
		var form = $('form.fp-ortax');

		return {
			'start_date': dateSQL(form.find('#StartDate').data('DateTimePicker').date()),
			'end_date': dateSQL(form.find('#EndDate').data('DateTimePicker').date()),
			'perusahaan': form.find('.perusahaan').select2('val'),
			'unit': form.find('.unit').select2('val'),
			'tutup_siklus': form.find('.tutup_siklus').select2('val'),
		};
	}, // end - getParams

    getLists: function(elm) {
		var form = $('form.fp-ortax');

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
			var params = fpOrtax.getParams();

			$.ajax({
	            url: 'report/FpOrtax/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                fpOrtax.state.faktur.rows = data.faktur;
	                fpOrtax.state.faktur.page = 1;
	                fpOrtax.state.detail.rows = data.detail;
	                fpOrtax.state.detail.page = 1;

	                fpOrtax.renderPage('faktur');
	                fpOrtax.renderPage('detail');
	                fpOrtax.setTotals(data.totals);
	            }
	        });
		}
	}, // end - getLists

	gotoPage: function(tableKey, page) {
		var state = fpOrtax.state[tableKey];
		var totalPages = Math.max(1, Math.ceil(state.rows.length / fpOrtax.pageSize));

		page = Math.max(1, Math.min(page, totalPages));
		state.page = page;

		fpOrtax.renderPage(tableKey);
	}, // end - gotoPage

	renderPage: function(tableKey) {
		var state = fpOrtax.state[tableKey];
		var start = (state.page - 1) * fpOrtax.pageSize;
		var pageRows = state.rows.slice(start, start + fpOrtax.pageSize);
		var builder = (tableKey == 'faktur') ? fpOrtax.buildFakturRow : fpOrtax.buildDetailRow;
		var colspan = (tableKey == 'faktur') ? 17 : 14;

		var html = '';
		if ( pageRows.length == 0 ) {
			html = '<tr><td colspan="'+colspan+'">Data tidak ditemukan.</td></tr>';
		} else {
			$.map( pageRows, function(row) { html += builder(row); } );
		}

		$('table.tbl_'+tableKey+' tbody').html( html );
		fpOrtax.renderPagination( tableKey );
	}, // end - renderPage

	renderPagination: function(tableKey) {
		var state = fpOrtax.state[tableKey];
		var totalRows = state.rows.length;
		var totalPages = Math.max(1, Math.ceil(totalRows / fpOrtax.pageSize));
		var bar = $('.pagination-'+tableKey);

		bar.find('.page-info').text('Halaman ' + state.page + ' dari ' + totalPages + ' (total ' + numeral.formatInt(totalRows) + ' baris)');
		bar.find('.btn-prev').prop('disabled', state.page <= 1);
		bar.find('.btn-next').prop('disabled', state.page >= totalPages);
	}, // end - renderPagination

	setTotals: function(totals) {
		$('table.tbl_detail thead td.total[data-target=jumlah] b').html( numeral.formatDec(totals.jumlah) );
		$('table.tbl_detail thead td.total[data-target=dpp] b').html( numeral.formatDec(totals.dpp) );
		$('table.tbl_detail thead td.total[data-target=dppnl] b').html( numeral.formatDec(totals.dppnl) );
		$('table.tbl_detail thead td.total[data-target=ppn] b').html( numeral.formatDec(totals.ppn) );
	}, // end - setTotals

	buildFakturRow: function(row) {
		return '<tr class="data">'
			+ '<td class="text-center">'+row[0]+'</td>'
			+ '<td class="text-center">'+row[1]+'</td>'
			+ '<td>'+row[2]+'</td>'
			+ '<td class="text-center">'+row[3]+'</td>'
			+ '<td class="text-center">'+row[4]+'</td>'
			+ '<td>'+row[5]+'</td>'
			+ '<td>'+row[6]+'</td>'
			+ '<td class="text-center">'+row[7]+'</td>'
			+ '<td>'+row[8]+'</td>'
			+ '<td>'+row[9]+'</td>'
			+ '<td class="text-center">'+row[10]+'</td>'
			+ '<td class="text-center">'+row[11]+'</td>'
			+ '<td>'+row[12]+'</td>'
			+ '<td>'+row[13]+'</td>'
			+ '<td>'+row[14]+'</td>'
			+ '<td>'+row[15]+'</td>'
			+ '<td>'+row[16]+'</td>'
			+ '</tr>';
	}, // end - buildFakturRow

	buildDetailRow: function(row) {
		return '<tr class="data">'
			+ '<td class="text-center">'+row[0]+'</td>'
			+ '<td class="text-center">'+row[1]+'</td>'
			+ '<td class="text-center">'+row[2]+'</td>'
			+ '<td>'+row[3]+'</td>'
			+ '<td class="text-center">'+row[4]+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[5])+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatDec(row[6])+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[7])+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[8])+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[9])+'</td>'
			+ '<td class="text-center">'+row[10]+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[11])+'</td>'
			+ '<td class="text-center">'+row[12]+'</td>'
			+ '<td class="text-right number_format">'+numeral.formatInt(row[13])+'</td>'
			+ '</tr>';
	}, // end - buildDetailRow

    excryptParams: function(elm) {
		var form = $('form.fp-ortax');

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
			var params = fpOrtax.getParams();

			$.ajax({
	            url: 'report/FpOrtax/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						fpOrtax.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/FpOrtax/exportExcel/'+params);
	}, // end - exportExcel
};

fpOrtax.startUp();
