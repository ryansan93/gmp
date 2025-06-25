var myInterval;

var rdr = {
	startUp: function() {
		rdr.settingUp();
	}, // end - startUp

	settingUp: function() {
		$("#StartDate").datetimepicker({
			locale: 'id',
			format: 'DD MMM Y'
		});
		$("#EndDate").datetimepicker({
			locale: 'id',
			format: 'DD MMM Y'
		});

		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
			var unit = $('.unit').select2().val();

			for (var i = 0; i < unit.length; i++) {
				if ( unit[i] == 'all' ) {
					$('.unit').select2().val('all').trigger('change');

					i = unit.length;
				}
			}
		});

		$('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
			var perusahaan = $('.perusahaan').select2().val();

			for (var i = 0; i < perusahaan.length; i++) {
				if ( perusahaan[i] == 'all' ) {
					$('.perusahaan').select2().val('all').trigger('change');

					i = perusahaan.length;
				}
			}
		});

		$('.jenis').select2({placeholder: 'Pilih Jenis'}).on("select2:select", function (e) {
			var jenis = $('.jenis').select2().val();

			for (var i = 0; i < jenis.length; i++) {
				if ( jenis[i] == 'all' ) {
					$('.jenis').select2().val('all').trigger('change');

					i = jenis.length;
				}
			}
		});
	}, // end - settingUp

    getLists: function(elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
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
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'perusahaan': $('.perusahaan').select2('val'),
				'unit': $('.unit').select2('val'),
				'jenis': $('.jenis').select2('val')
			};

			$.ajax({
	            url: 'report/RekapDataRhpp/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'HTML',
	            beforeSend: function() { App.showLoaderInContent( $('table tbody') ); },
	            success: function(html) {
	                App.hideLoaderInContent( $('table tbody'), html );

					myInterval = setInterval(function () {
						if ( $('table tbody').length > 0 ) {
							rdr.hitTotal();
						}
					}, 250);
	            }
	        });
		}
	}, // end - getLists

	hitTotal: function () {
		clearInterval(myInterval);

		$.map( $('table thead td.total_hit'), function (td_total) {
			var target = $(td_total).attr('data-target');

			var total = 0;
			$.map( $('table tbody td.'+target), function (td) {
				var nilai = parseFloat($(td).attr('data-val'));
				total += nilai;
			});

			$(td_total).html( numeral.formatDec( total ) );
		});
	}, // end - angkaDecimal

    excryptParams: function(elm) {
		var err = 0;
		
		$.map( $('[data-required=1]'), function (ipt) {
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
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'perusahaan': $('.perusahaan').select2('val'),
				'unit': $('.unit').select2('val'),
				'jenis': $('.jenis').select2('val')
			};

			$.ajax({
	            url: 'report/RekapDataRhpp/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						rdr.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/RekapDataRhpp/exportExcel/'+params);
	}, // end - exportExcel
};

rdr.startUp();