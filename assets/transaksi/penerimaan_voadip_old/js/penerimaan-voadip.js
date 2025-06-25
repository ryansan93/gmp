var pv = {
	start_up : function () {
		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });
	}, // end - start_up

	set_data_order : function (elm) {
		var div = $(elm).closest('div#penerimaan-voadip');
		var table = $(div).find('table.tb_list_voadip');
		var supplier = $(elm).val();

		$('select.supplier').parent().removeClass('has-error');

		$.ajax({
			url: 'transaksi/PenerimaanVoadip/get_data_order_voadip_by_supplier',
			data: {
				'params': supplier
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					$(table).find('tbody').html(data.content);

					$('.datetimepicker').datetimepicker({
			            locale: 'id',
			            format: 'DD MMM Y HH:mm:ss',
			        	sideBySide: true
			        });

			        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			            $(this).priceFormat(Config[$(this).data('tipe')]);
			        });
				} else {
					bootbox.alert(data.message);
				};

			},
	    });
	}, // end - set_noreg

	save : function() {
		var err = 0;
		var err_per_tr = 0;

		var supplier = $('select.supplier').val();

		if ( empty(supplier) ) {
			bootbox.alert('Supplier belum di pilih.');
			$('select.supplier').parent().addClass('has-error');
		} else {
			var data = {};
			var data_detail = [];

			var no_order = null;
			var tgl_order = null;
			var index = 0;
			var data_terima_voadip = $.map( $('table.tb_list_voadip').find('tr.data'), function(tr) {
				if ( ($(tr).find('td.no_order').length > 0) && ($(tr).find('td.tgl_order').length > 0) ) {
					no_order = $(tr).find('td.no_order').text();
					tgl_order = $(tr).find('td.tgl_order').data('tanggal');

					index = 0;
					data_detail = [];
				};

				var no_sj = $(tr).find('input.no_sj').val();

				if ( !empty(no_sj) ) {
					$.map( $(tr).find('[data-required=1]'), function(ipt) {
						if ( empty($(ipt).val()) ) {
							$(ipt).parent().addClass('has-error');
							err_per_tr++;
						} else {
							$(ipt).parent().removeClass('has-error');
						};
					});

					data_detail[index] = {
						'kode_brg' : $(tr).find('td.barang').data('kode'),
						'nama_brg' : $(tr).find('td.barang').html(),
						'jumlah' : numeral.unformat( $(tr).find('input.jumlah').val() ),
						'no_sj' : $(tr).find('input.no_sj').val(),
						'tanggal' : dateTimeSQL( $(tr).find('[name=tgl_terima]').data('DateTimePicker').date() ),
						'id_tujuan_kirim' : $(tr).find('td.tujuan_kirim').data('id'),
						'alamat' : $(tr).find('input.alamat_terima').val(),
						'kondisi' : $(tr).find('select.kondisi').val(),
						'keterangan' : $(tr).find('input.ket').val(),
						'kirim_ke' : $(tr).find('td.tujuan_kirim').data('kirimke'),
					};

					if ( data_detail.length > 0 ) {
						data_header = {
							'no_order' : no_order,
							'tgl_order' : tgl_order,
							'supplier' : supplier,
							'detail' : []
						};

						data_header.detail = data_detail;

						data[no_order] = data_header;
					};
				};

				index++;
			});

			if ( Object.keys(data).length > 0 ) {
				if ( err_per_tr == 0 ) {
					bootbox.confirm('Apakah anda yakin ingin menyimpan data Penerimaan Voadip ?', function(result) {
						if ( result ) {
							pv.execute_save(data);
						};
					});
				} else {
					bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');	
				};
			} else {
				bootbox.alert('Belum ada data yang anda masukkan.');
			};
		};
	}, // end - save

	execute_save : function(params) {
		$.ajax({
			url: 'transaksi/PenerimaanVoadip/save',
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
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						var sel_supl = $('select.supplier');

						pv.set_data_order(sel_supl);
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - excecute_save
};

pv.start_up();