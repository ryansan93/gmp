var db = {
	startUp: function() {
		db.settingUp();
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

		$('select.jenis').select2().on('select2:select', function (e) {
			$('select.barang').select2().val('');
			$('select.barang').find('option').removeAttr('disabled');

			var jenis = $('select.jenis').select2().val();

			var _attr = '[data-tipe="'+jenis+'"]';
			if ( jenis == 'voadip' ) {
				_attr = '[data-tipe="obat"]';
			}

			$('select.barang').find('option:not(.all, '+_attr+')').attr('disabled', 'disabled');

			$('select.barang').select2({placeholder: 'Pilih Barang'}).on("select2:select", function (e) {
				var barang = $('select.barang').select2().val();
	
				for (var i = 0; i < barang.length; i++) {
					if ( barang[i] == 'all' ) {
						$('select.barang').select2().val('all').trigger('change');
	
						i = barang.length;
					}
				}
	
				$('select.barang').next('span.select2').css('width', '100%');
			});
			$('select.barang').next('span.select2').css('width', '100%');
		});

		$('select.barang').select2({placeholder: 'Pilih Barang'}).on("select2:select", function (e) {
            var barang = $('select.barang').select2().val();

            for (var i = 0; i < barang.length; i++) {
                if ( barang[i] == 'all' ) {
                    $('select.barang').select2().val('all').trigger('change');

                    i = barang.length;
                }
            }

            $('select.barang').next('span.select2').css('width', '100%');
        });
        $('select.barang').next('span.select2').css('width', '100%');

        $('select.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('select.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('select.unit').next('span.select2').css('width', '100%');
        });
        $('select.unit').next('span.select2').css('width', '100%');

		$('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('select.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('select.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('select.perusahaan').next('span.select2').css('width', '100%');
        });
        $('select.perusahaan').next('span.select2').css('width', '100%');

        // $('.perusahaan').select2();
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
				'jenis': $('select.jenis').select2('val'),
				'barang': $('select.barang').select2('val'),
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('select.unit').select2('val'),
				'perusahaan': $('select.perusahaan').select2('val'),
			};

			$.ajax({
	            url: 'report/DistribusiBarang/getLists',
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

		                db.hitTotal();
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - getLists

	hitTotal: function() {
		var total_beli = 0;
		var total_jual = 0;

		$.map( $('table.tbl_laporan tbody').find('tr'), function(tr) {
			var tot_beli = numeral.unformat( $(tr).find('td.tot_beli').text() );
			var tot_jual = numeral.unformat( $(tr).find('td.tot_jual').text() );

			total_beli += parseFloat( tot_beli );
			total_jual += parseFloat( tot_jual );
		});

		$('table.tbl_laporan').find('.tot_beli b').text( numeral.formatDec(total_beli) );
		$('table.tbl_laporan').find('.tot_jual b').text( numeral.formatDec(total_jual) );
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
				'jenis': $('select.jenis').select2('val'),
				'barang': $('select.barang').select2('val'),
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('select.unit').select2('val'),
				'perusahaan': $('select.perusahaan').select2('val'),
			};

			$.ajax({
	            url: 'report/DistribusiBarang/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                db.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/DistribusiBarang/exportExcel/'+params);
	}, // end - exportExcel
};

db.startUp();