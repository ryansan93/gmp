var fpOrtax = {
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

		form.find('a[data-toggle=tab]').on('shown.bs.tab', function (e) {
			if ( $(e.target).attr('data-tab') == 'tabDetail' ) {
				fpOrtax.renderDetailTab( form );
			}
		});
	}, // end - settingUp

	renderDetailTab: function ( form ) {
		if ( form.data('detailRendered') || !form.data('detailHtml') ) {
			return;
		}

		form.find('table.tbl_detail tbody').html( form.data('detailHtml') );
		form.data('detailRendered', true);
		fpOrtax.hitTotal( form );
	}, // end - renderDetailTab

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

			// Tabel DetailFaktur bisa berisi puluhan ribu baris; kalau langsung dimasukkan ke DOM
			// bareng tabel Faktur, pindah tab jadi berat terus-menerus. Jadi cuma disimpan dulu,
			// baru benar-benar dirender pas tab DetailFaktur pertama kali dibuka.
			form.find('table.tbl_detail tbody').empty();
			form.data('detailHtml', null);
			form.data('detailRendered', false);

			$.ajax({
	            url: 'report/FpOrtax/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'JSON',
	            beforeSend: function() {
	            	App.showLoaderInContent( form.find('table.tbl_faktur tbody') );
	            },
	            success: function(data) {
	                App.hideLoaderInContent( form.find('table.tbl_faktur tbody'), data.faktur );

	                form.data('detailHtml', data.detail);

	                if ( form.find('a[data-tab=tabDetail]').hasClass('active') ) {
	                	fpOrtax.renderDetailTab( form );
	                }
	            }
	        });
		}
	}, // end - getLists

	hitTotal: function ( form ) {
		$.map( form.find('table.tbl_laporan thead td.total'), function (td_total) {
			var target = $(td_total).attr('data-target');
			var jenis = $(td_total).attr('data-jenis');

			var total = 0;
			$.map( form.find('table.tbl_laporan tbody td.'+target), function (td) {
				var nilai = 0;
				if ( jenis == 'decimal' ) {
					nilai = parseFloat($(td).attr('data-val'));
				} else {
					nilai = parseInt($(td).attr('data-val'));
				}
				total += nilai;
			});

			if ( jenis == 'decimal' ) {
				$(td_total).find('b').html( numeral.formatDec( total ) );
			} else {
				$(td_total).find('b').html( numeral.formatInt( total ) );
			}
		});
	}, // end - hitTotal

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
