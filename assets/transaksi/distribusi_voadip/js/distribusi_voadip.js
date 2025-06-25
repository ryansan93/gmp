var dv = {
	start_up: function(){
		dv.setting_up();
		dv.get_lists();

		$('select.filter').find('option[value=submit]').attr('selected', 'selected');
	}, // end - start_up

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });

        $('#datetimepicker2').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });

        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - setting_up

	get_lists: function() {
		var div_doc = $('div#voadip');
		var table = $(div_doc).find('table');
		var tbody = $(table).find('tbody');

		var start_date = $('[name=startDate]').data('DateTimePicker').date();
		var end_date = $('[name=endDate]').data('DateTimePicker').date();

		if ( !empty(start_date) && !empty(end_date) ) {
			var start_date = dateSQL( $('[name=startDate]').data('DateTimePicker').date() );
			var end_date = dateSQL( $('[name=endDate]').data('DateTimePicker').date() );
		} else {
			start_date = null;
			end_date = null;
		};

		var date = {
			'start_date': start_date,
			'end_date': end_date,
		};

		$.ajax({
			url: 'transaksi/DistribusiVoadip/get_lists',
			data: {
				'params': date
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				if ( data.status == 1 ) {
					$(tbody).html(data.content);
				};

				dv.setting_up();

				hideLoading();
			},
	    });
	}, // end - get_lists

	set_item_voadip: function(elm) {
		var tr = $(elm).closest('tr');
		var sel_brg = $(tr).find('select.barang');

		var val = $(elm).val();

		$.ajax({
			url: 'transaksi/ODVP/get_data_voadip',
			data: {
				'kategori': val
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				// showLoading();
			},
			success: function(data) {
				// hideLoading();
				if ( data.status == 1 ) {
					var opt = '<option value="">-- Pilih Barang --</option>';
					if ( data.content.length > 0 ) {
						for (var i = 0; i < data.content.length; i++) {
							opt += '<option value="'+data.content[i].kode+'" data-isi="'+data.content[i].berat+'"  data-bentuk="'+data.content[i].bentuk+'">'+data.content[i].nama+'</option>';
						};
					};

					sel_brg.html(opt);
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_item_voadip

	set_item_value: function (elm) {
		var tr = $(elm).closest('tr');
		var sel_barang = $(tr).find('select.barang');

		var isi = $(sel_barang).find('option:selected').data('isi');
		var bentuk = $(sel_barang).find('option:selected').data('bentuk');

		$(tr).find('td.isi').html( numeral.formatDec(isi) );
		$(tr).find('td.bentuk').html( bentuk.toUpperCase() );
	}, // end - set_item_value

	hit_do: function (elm) {
		var tr = $(elm).closest('tr');

		var jml_kemasan = numeral.unformat( $(tr).find('input.jml_kemasan').val() );
		var jml_isi = numeral.unformat( $(tr).find('input.jml_isi').val() );

		var total_do = jml_kemasan * jml_isi;

		$(tr).find('input.jml_do').val( numeral.formatDec(total_do) );
	}, // end - hit_do

	save: function() {
		var err_per_tr = 0;
		var table = $('table.tbl_voadip');

		var data = {};
		var data_detail = [];

		var peternak = null;
		var kandang = null;
		var populasi = null;
		var noreg = null;
		var umur = null;
		var index = 0;

		$.map( $(table).find('tr.data'), function(tr) {
			if ( ($(tr).find('td.nama').length > 0) ) {
				peternak = $(tr).find('td.nama').data('idpeternak');
				kandang = numeral.unformat( $(tr).find('td.kandang').html() );
				populasi = numeral.unformat( $(tr).find('td.populasi').html() );
				noreg = $(tr).find('td.noreg').html();
				umur = numeral.unformat( $(tr).find('td.umur').html() );

				index = 0;
				data_detail = [];
			};

			var kategori = $(tr).find('select.kategori').val();

			if ( !empty(kategori) ) {
				$.map( $(tr).find('[data-required=1]'), function(ipt) {
					if ( empty($(ipt).val()) ) {
						$(ipt).parent().addClass('has-error');
						err_per_tr++;
					} else {
						$(ipt).parent().removeClass('has-error');
					};
				});

				data_detail[index] = {
					'kategori' : kategori,
					'kode_barang' : $(tr).find('select.barang').val(),
					'supplier' : $(tr).find('select.supplier').val(),
					'tanggal' : dateTimeSQL( $(tr).find('[name=tgl_rcn_kirim]').data('DateTimePicker').date() ),
					'jml_kemasan' : numeral.unformat( $(tr).find('input.jml_kemasan').val() ),
					'jml_isi' : numeral.unformat( $(tr).find('input.jml_isi').val() ),
					'jml_do' : numeral.unformat( $(tr).find('input.jml_do').val() ),
				};

				if ( data_detail.length > 0 ) {
					data_header = {
						'peternak' : peternak,
						'kandang' : kandang,
						'populasi' : populasi,
						'noreg' : noreg,
						'umur' : umur,
						'detail' : []
					};

					data_header.detail = data_detail;

					data[noreg] = data_header;
				};
			}

			index++;
		});

		if ( Object.keys(data).length > 0 ) {
			if ( err_per_tr == 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data Distribusi Voadip ?', function(result) {
					if ( result ) {
						console.log(data);
						dv.execute_save(data);
					};
				});
			} else {
				bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');	
			};
		} else {
			bootbox.alert('Belum ada data yang anda masukkan.');
		};
	}, // end - save

	execute_save : function(params) {
		$.ajax({
			url: 'transaksi/DistribusiVoadip/save',
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
						dv.get_lists();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - excecute_save

	filter_by_status: function(elm) {
		var div = $(elm).closest('div#doc');
		var table = $(div).find('table.tbl_voadip');

		var val = $(elm).val();

		$(table).find('tbody tr').removeClass('hide');
		if ( val == 'submit' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter == 'ordered' || filter == 'terima' ) {
					$(tr).addClass('hide');
				};
			});
		} else if ( val == 'ordered' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter != 'ordered' || empty(filter) ) {
					$(tr).addClass('hide');
				};
			});
		} else if ( val == 'terima' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter != 'terima' || empty(filter) ) {
					$(tr).addClass('hide');
				};
			});
		} else {
			$(table).find('tbody tr').removeClass('hide');
		};
	}, // end - filter_by_status

	addRowChild: function(elm) {
        let row = $(elm).closest('tr');
        let noreg = $(row).data('noreg');
        let newRow = row.clone();
        // newRow.find('[name=tanggal]').val('');

        newRow.find('input, select').val('');
        row.find('.btn-ctrl').hide();
        row.after(newRow);
        
        let tbody = $(row).closest('tbody');
        if ( $(tbody).find('tr').length > 0 ) {
        	newRow.find('.btn_del_row_2x').removeClass('hide');
        };

        newRow.find('td.nama').remove();
        newRow.find('td.kandang').remove();
        newRow.find('td.populasi').remove();
        newRow.find('td.noreg').remove();
        newRow.find('td.umur').remove();

        var jml_row_per_noreg = $(tbody).find('[data-noreg='+noreg+']').length;
        $(tbody).find('tr[data-noreg='+noreg+']:first td.nama').attr('rowspan', jml_row_per_noreg);
        $(tbody).find('tr[data-noreg='+noreg+']:first td.kandang').attr('rowspan', jml_row_per_noreg);
        $(tbody).find('tr[data-noreg='+noreg+']:first td.populasi').attr('rowspan', jml_row_per_noreg);
        $(tbody).find('tr[data-noreg='+noreg+']:first td.noreg').attr('rowspan', jml_row_per_noreg);
        $(tbody).find('tr[data-noreg='+noreg+']:first td.umur').attr('rowspan', jml_row_per_noreg);

        var sel_kat = $(newRow).find('select.kategori');
        var sel_supl = $(newRow).find('select.supplier');

        var sel_brg = $(newRow).find('select.barang');
        var opt_barang = '<option value="">-- Pilih Barang --</option>';
		sel_brg.html(opt_barang);

		$(sel_kat).val($(sel_kat).find("option:first").val());
		$(sel_supl).val($(sel_supl).find("option:first").val());

        dv.setting_up();
        App.formatNumber();
    }, // end - addRowChild

    removeRowChild: function(elm) {
        let row = $(elm).closest('tr');
        let noreg = $(row).data('noreg');
        let tbody = $(row).closest('tbody');

        if ($(row).prev('tr.child').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();

            var jml_row_per_noreg = $(tbody).find('[data-noreg='+noreg+']').length;
	        $(tbody).find('tr[data-noreg='+noreg+']:first td.nama').attr('rowspan', jml_row_per_noreg);
	        $(tbody).find('tr[data-noreg='+noreg+']:first td.kandang').attr('rowspan', jml_row_per_noreg);
	        $(tbody).find('tr[data-noreg='+noreg+']:first td.populasi').attr('rowspan', jml_row_per_noreg);
	        $(tbody).find('tr[data-noreg='+noreg+']:first td.noreg').attr('rowspan', jml_row_per_noreg);
	        $(tbody).find('tr[data-noreg='+noreg+']:first td.umur').attr('rowspan', jml_row_per_noreg);
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).addClass('inactive');
        }
    }, // end - removeRowChild
};

dv.start_up();