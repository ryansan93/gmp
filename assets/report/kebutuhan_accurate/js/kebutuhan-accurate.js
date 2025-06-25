var myInterval;

var ka = {
	startUp: function() {
		ka.settingUp();
	}, // end - startUp

	settingUp: function() {
		$.map( $('div.tab-pane'), function (div) {
			$(div).find("#StartDate").datetimepicker({
				locale: 'id',
				format: 'DD MMM Y'
			});
			$(div).find("#EndDate").datetimepicker({
				locale: 'id',
				format: 'DD MMM Y'
			});

			$(div).find('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
				var unit = $(div).find('.unit').select2().val();

				for (var i = 0; i < unit.length; i++) {
					if ( unit[i] == 'all' ) {
						$(div).find('.unit').select2().val('all').trigger('change');

						i = unit.length;
					}
				}
			});

			$(div).find('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
				var perusahaan = $(div).find('.perusahaan').select2().val();

				for (var i = 0; i < perusahaan.length; i++) {
					if ( perusahaan[i] == 'all' ) {
						$(div).find('.perusahaan').select2().val('all').trigger('change');

						i = perusahaan.length;
					}
				}
			});

			$(div).find('.jenis').select2({placeholder: 'Pilih Jenis'}).on("select2:select", function (e) {
				var jenis = $(div).find('.jenis').select2().val();

				for (var i = 0; i < jenis.length; i++) {
					if ( jenis[i] == 'all' ) {
						$(div).find('.jenis').select2().val('all').trigger('change');

						i = jenis.length;
					}
				}
			});

			$(div).find('.tutup_siklus').select2();
		});
	}, // end - settingUp

    getLists: function(elm) {
		var div = $(elm).closest('div.tab-pane');

		var err = 0;

		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
			var params = {
				'jenis': $(elm).attr('data-jenis'),
				'start_date': dateSQL($(div).find('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($(div).find('#EndDate').data('DateTimePicker').date()),
				'perusahaan': ($(div).find('.perusahaan').length > 0) ? $(div).find('.perusahaan').select2('val') : null,
				'unit': ($(div).find('.unit').length > 0) ? $(div).find('.unit').select2('val') : null,
				'tutup_siklus': ($(div).find('.tutup_siklus').length > 0) ? $(div).find('.tutup_siklus').select2('val') : null,
				'kode_jenis':  ($(div).find('.jenis').length > 0) ? $(div).find('.jenis').select2('val') : null,
			};

			$.ajax({
	            url: 'report/KebutuhanAccurate/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'HTML',
	            beforeSend: function() { App.showLoaderInContent( $(div).find('table.tbl_laporan tbody') ); },
	            success: function(html) {
	                App.hideLoaderInContent( $(div).find('table.tbl_laporan tbody'), html );

					myInterval = setInterval(function () {
						if ( $(div).find('table.tbl_laporan tbody tr.data').length > 0 ) {
							ka.hitTotal( div );
						}
					}, 250);
	            }
	        });
		}
	}, // end - getLists

	hitTotal: function ( div ) {
		clearInterval(myInterval);

		$.map( $(div).find('table.tbl_laporan thead td.total'), function (td_total) {
			var target = $(td_total).attr('data-target');
			var jenis = $(td_total).attr('data-jenis');

			var total = 0;
			$.map( $(div).find('table.tbl_laporan tbody td.'+target), function (td) {
				var nilai = 0;
				if ( jenis == 'decimal' ) {
					nilai = parseFloat($(td).attr('data-val'));
				} else {
					nilai = parseInt($(td).attr('data-val'));
				}
				total += nilai;
			});

			// console.log( target );
			// console.log( jenis );
			// console.log( $(div).find('table.tbl_laporan tbody td.'+target).length );

			if ( jenis == 'decimal' ) {
				$(td_total).find('b').html( numeral.formatDec( total ) );
			} else {
				$(td_total).find('b').html( numeral.formatInt( total ) );
			}

		});
	}, // end - angkaDecimal

    excryptParams: function(elm) {
		var div = $(elm).closest('div.tab-pane');

		var err = 0;
		
		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
			var params = {
				'jenis': $(elm).attr('data-jenis'),
				'start_date': dateSQL($(div).find('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($(div).find('#EndDate').data('DateTimePicker').date()),
				'perusahaan': ($(div).find('.perusahaan').length > 0) ? $(div).find('.perusahaan').select2('val') : null,
				'unit': ($(div).find('.unit').length > 0) ? $(div).find('.unit').select2('val') : null,
				'tutup_siklus': ($(div).find('.tutup_siklus').length > 0) ? $(div).find('.tutup_siklus').select2('val') : null,
				'kode_jenis':  ($(div).find('.jenis').length > 0) ? $(div).find('.jenis').select2('val') : null,
			};

			$.ajax({
	            url: 'report/KebutuhanAccurate/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						if ( $(elm).attr('data-tipe') == 'excel' ) {
							ka.exportExcel(data.content);
						} else {
							ka.exportXml(data.content);
						}
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/KebutuhanAccurate/exportExcel/'+params);
	}, // end - exportExcel

	exportXml : function (params) {
		console.log('xml');

		goToURL('report/KebutuhanAccurate/exportXml/'+params);
	}, // end - exportXml
};

ka.startUp();