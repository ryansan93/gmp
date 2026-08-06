var db = {
	state: {
		page: 1,
		total_pages: 1,
	},

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

		$('select.jenis_transaksi').select2({placeholder: 'Pilih Jenis Transaksi'}).on("select2:select", function (e) {
            var jenis = $('select.jenis_transaksi').select2().val();

            for (var i = 0; i < jenis.length; i++) {
                if ( jenis[i] == 'all' ) {
                    $('select.jenis_transaksi').select2().val('all').trigger('change');

                    i = jenis.length;
                }
            }

            $('select.jenis_transaksi').next('span.select2').css('width', '100%');
        });
        $('select.jenis_transaksi').next('span.select2').css('width', '100%');

        // $('.perusahaan').select2();
	}, // end - settingUp

	getLists: function(resetPage) {
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
			if ( resetPage !== false ) {
				db.state.page = 1;
			}

			var params = {
				'jenis': $('select.jenis').select2('val'),
				'barang': $('select.barang').select2('val'),
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('select.unit').select2('val'),
				'perusahaan': $('select.perusahaan').select2('val'),
				'jenis_transaksi': $('select.jenis_transaksi').select2('val'),
				'page': db.state.page,
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

		                db.state.page = data.page;
		                db.state.total_pages = data.total_pages;

		                db.renderPagination(data.total_rows);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - getLists

	gotoPage: function(page) {
		if ( page < 1 || page > db.state.total_pages ) {
			return;
		}

		db.state.page = page;
		db.getLists(false);
	}, // end - gotoPage

	renderPagination: function(total_rows) {
		var page = db.state.page;
		var total_pages = db.state.total_pages;

		$('.pagination-db .page-info').text('Halaman ' + page + ' dari ' + total_pages + ' (total ' + numeral.formatInt(total_rows) + ' baris)');
		$('.pagination-db .btn-prev').prop('disabled', page <= 1);
		$('.pagination-db .btn-next').prop('disabled', page >= total_pages);
	}, // end - renderPagination

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
				'jenis_transaksi': $('select.jenis_transaksi').select2('val'),
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