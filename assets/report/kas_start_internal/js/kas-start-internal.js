var kk = {
	startUp: function () {
		kk.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('select.perusahaan').select2();
		// $('select.kas').select2();
		$('select.bulan').select2();

		$('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

		$('select.kas').select2().on('select2:select', function (e) {
            var data = e.params.data.element.dataset;

            kk.getNoreg( data.unit );
        });

        kk.getNoreg( $('select.kas').find('option:selected').attr('data-unit') );
	}, // end - settingUp

	getNoreg: function(unit) {
        showLoading('Ambil data noreg . . .');

        var params = {
            'unit': unit
        };

        $.ajax({
            url : 'report/KasStartInternal/getNoreg',
            data : {
                'params' : params
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                hideLoading();

                $('select.noreg').html( html );
                $('select.noreg').select2();
            },
        });
    }, // end - getNoreg

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
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() ),
				'noreg': $('.noreg').select2('val')
			};

			$.ajax({
                url : 'report/KasStartInternal/getLists',
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
	            url: 'report/KasStartInternal/excryptParams',
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
		goToURL('report/KasStartInternal/exportExcel/'+params);
	}, // end - exportExcel
};

kk.startUp();