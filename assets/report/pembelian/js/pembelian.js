var beli = {
	startUp: function() {
		beli.settingUp();
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

            $('.unit').next('span.select2').css('width', '100%');
        });
        $('.unit').next('span.select2').css('width', '100%');

		$('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('.perusahaan').next('span.select2').css('width', '100%');
        });
        $('.perusahaan').next('span.select2').css('width', '100%');

        // $('.perusahaan').select2();
        $('.jenis').select2();
	}, // end - settingUp

	getLists: function() {
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
				'jenis': $('.jenis').select2('val'),
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('.unit').select2('val'),
				'perusahaan': $('.perusahaan').select2('val'),
			};

			$.ajax({
	            url: 'report/Pembelian/getLists',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                $('table.tbl_laporan tbody').html( data.html );

		                beli.hitTotal();
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - getLists

	hitTotal: function() {
		var total_jumlah = 0;
		var total_nilai = 0;

		$.map( $('table.tbl_laporan tbody').find('tr'), function(tr) {
			var jumlah = numeral.unformat( $(tr).find('td.jumlah').text() );
			var total = numeral.unformat( $(tr).find('td.total').text() );

			total_jumlah += parseFloat(jumlah  );
			total_nilai += parseFloat( total );
		});

		$('table.tbl_laporan').find('.total_ekor_tonase b').text( numeral.formatDec(total_jumlah) );
		$('table.tbl_laporan').find('.total_nilai b').text( numeral.formatDec(total_nilai) );
	}, // end - hitTotal

	excryptParams: function() {
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
				'jenis': $('.jenis').select2('val'),
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('.unit').select2('val'),
				'perusahaan': $('.perusahaan').select2('val'),
			};

			$.ajax({
	            url: 'report/Pembelian/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                beli.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/Pembelian/exportExcel/'+params);
	}, // end - exportExcel
};

beli.startUp();