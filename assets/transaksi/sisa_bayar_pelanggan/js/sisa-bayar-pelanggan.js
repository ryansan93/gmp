var sbp = {
	start_up: function () {
		sbp.setting_up();
	}, // end - start_up

	setting_up: function() {
		$("[name=startDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		$("[name=endDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			$("[name=endDate]").data("DateTimePicker").minDate(e.date);
			$("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});

		$('select.pelanggan').select2();
		$('select.perusahaan').select2();
	}, // end - setting_up

	changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

        sbp.load_form();
    }, // end - changeTabActive

    load_form: function() {
        var dcontent = $('div#sbp');

        $.ajax({
            url : 'transaksi/SisaBayarPelanggan/load_form',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                sbp.setting_up();
            },
        });
    }, // end - load_form

	get_lists: function(elm) {
		var div = $(elm).closest('div#riwayat_sbp');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
				'start_date': dateSQL($(div).find('[name=startDate]').data("DateTimePicker").date()),
				'end_date': dateSQL($(div).find('[name=endDate]').data("DateTimePicker").date())
			};

			$.ajax({
	            url : 'transaksi/SisaBayarPelanggan/get_lists',
	            data : {
	                'params': params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	$(div).find('table.tbl_sbp tbody').html( html );

	                hideLoading();
	            },
	        });
		}
	}, // end - get_lists

	get_saldo: function(elm) {
		var div = $(elm).closest('div#sbp');

		if ( empty($(div).find('select.pelanggan').val()) || empty($(div).find('select.perusahaan').val()) ) {
			bootbox.alert( 'Harap pilih pelanggan dan perusahaan terlebih dahulu.' );
		} else {
			var pelanggan = $(div).find('select.pelanggan').val();
			var perusahaan = $(div).find('select.perusahaan').val();

			var params = {
				'pelanggan': pelanggan,
				'perusahaan': perusahaan
			};

			$.ajax({
	            url : 'transaksi/SisaBayarPelanggan/get_saldo',
	            data : {
	                'params': params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	            	$('input.sisa_saldo').val( numeral.formatDec(data.content) );

	                hideLoading();
	            },
	        });
		}
	}, // end - get_saldo

	save: function(elm) {
		var div = $(elm).closest('div#sbp');

		var pelanggan = $(div).find('select.pelanggan').select2('val');
		var perusahaan = $(div).find('select.perusahaan').select2('val');
		var sisa_saldo = $(div).find('input.sisa_saldo').val();

		if ( empty(pelanggan) && empty(perusahaan) && empty(sisa_saldo) ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
		} else {
			if ( sisa_saldo == 0 ) {
				bootbox.alert( 'Sisa saldo 0.' );
			} else {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
					if ( result ) {
						var params = {
							'pelanggan': pelanggan,
							'perusahaan': perusahaan,
							'sisa_saldo': numeral.unformat( $(div).find('input.sisa_saldo').val() )
						};

						$.ajax({
				            url : 'transaksi/SisaBayarPelanggan/save',
				            data : {
				                'params': params
				            },
				            type : 'POST',
				            dataType : 'JSON',
				            beforeSend : function(){ showLoading(); },
				            success : function(data){
				            	hideLoading();
				            	if ( data.status == 1 ) {
				            		bootbox.alert( data.message, function() {
				            			sbp.load_form();
				            		});
				            	} else {
				            		bootbox.alert( data.message );
				            	}
				            },
				        });
					}
				});
			}
		}
	}, // end - save

	delete: function(elm) {
		var tr = $(elm).closest('tr');

		var id = $(tr).data('id');
		
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
			if ( result ) {
				var params = {
					'id': id,
				};

				$.ajax({
		            url : 'transaksi/SisaBayarPelanggan/delete',
		            data : {
		                'params': params
		            },
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		            	hideLoading();
		            	if ( data.status == 1 ) {
		            		bootbox.alert( data.message, function() {
		            			$('button#btn-tampilkan').click();
		            		});
		            	} else {
		            		bootbox.alert( data.message );
		            	}
		            },
		        });
			}
		});
	}, // end - save
};

sbp.start_up();