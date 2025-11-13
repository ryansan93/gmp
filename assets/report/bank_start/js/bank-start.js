var kk = {
	startUp: function () {
		kk.settingUp();
	}, // end - startUp

	settingUp: function () {
		// $('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
        //     var unit = $('.unit').select2('val');

        //     $('.btn-tutup-bulan').addClass('hide');
        // });

		// $('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
        //     var perusahaan = $('.perusahaan').select2('val');

        //     $('.btn-tutup-bulan').addClass('hide');
        // });

		$('select.perusahaan').select2();
		$('select.kas').select2();
		$('select.bulan').select2();

		$('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
	}, // end - settingUp

	getLists: function () {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
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
			var dcontent = $('table tbody');
			var params = {
				'kas': $('.kas').select2('val'),
				'perusahaan': $('.perusahaan').select2('val'),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/BankStart/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );
                }
            });
		}
	}, // end - getLists

	excryptParams: function() {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
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
			var dcontent = $('table tbody');
			var params = {
				'kas': $('.kas').select2('val'),
				'perusahaan': $('.perusahaan').select2('val'),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'report/BankStart/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						kk.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/BankStart/exportExcel/'+params);
	}, // end - exportExcel
};

kk.startUp();