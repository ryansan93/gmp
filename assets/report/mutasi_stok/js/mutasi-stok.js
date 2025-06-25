var ms = {
	start_up: function() {
		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - start_up

	get_gudang_dan_barang: function(elm) {
		var params = $(elm).val();

		if ( !empty(params) ) {
			$.ajax({
				url: 'report/MutasiStok/get_gudang_dan_barang',
				data: {
					'params': params
				},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function() {
					showLoading();
				},
				success: function(data) {
					hideLoading();

					var opt_gudang = '<option value="all">All</option>';
					if ( !empty(data.list_data.gudang) ) {
						var data_gudang = data.list_data.gudang;
						for (var i = 0; i < data_gudang.length; i++) {
							opt_gudang += '<option value="'+data_gudang[i].id+'">'+data_gudang[i].nama.toUpperCase()+'</option>';
						}
					}

					$('select.gudang').html( opt_gudang );

					var opt_brg = '<option value="all">All</option>';
					if ( !empty(data.list_data.barang) ) {
						var data_brg = data.list_data.barang;
						for (var i = 0; i < data_brg.length; i++) {
							opt_brg += '<option value="'+data_brg[i].kode+'">'+data_brg[i].nama.toUpperCase()+'</option>';
						}
					}

					$('select.barang').html( opt_brg );
				},
		    });
		} else {
			$('select.gudang').html( '<option value="">Pilih Gudang</option>' );
			$('select.barang').html( '<option value="">Pilih Barang</option>' );
		}
	}, // end - get_gudang

	get_data: function() {
		var err = 0;
		$.map( $('input, select'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
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
				'start_date': dateSQL( $('[name=startDate]').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('[name=endDate]').data('DateTimePicker').date() ),
				'jenis': $('select.jns_barang').val(),
				'kode_gudang': $('select.gudang').val(),
				'kode_brg': $('select.barang').val()
			};

			$.ajax({
				url: 'report/MutasiStok/get_data',
				data: {
					'params': params
				},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function() {
					showLoading();
				},
				success: function(data) {
					hideLoading();

					$('table.tbl_list tbody').remove();
	                $('table.tbl_list thead').after(data.html);
				},
		    });
		}
	}, // end - get_data
};

ms.start_up();