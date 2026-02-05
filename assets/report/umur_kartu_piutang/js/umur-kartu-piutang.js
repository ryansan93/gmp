var khl = {
    startUp: function() {
        khl.settingUp();
    }, // end - khl

    settingUp: function() {
        $('select.bulan').select2();
        $('select.tipe_pelanggan').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
    }, // end - khl

    getLists: function() {
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
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() ),
				'tipe_pelanggan': $('.tipe_pelanggan').select2().val()
			};

			$.ajax({
                url : 'report/UmurKartuPiutang/getLists',
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
    }, // end - getData

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
			var params = {
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'report/UmurKartuPiutang/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						khl.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/UmurKartuPiutang/exportExcel/'+params);
	}, // end - exportExcel
};

khl.startUp();