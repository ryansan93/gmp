var khl = {
    startUp: function() {
        khl.settingUp();
    }, // end - khl

    settingUp: function() {
        $.map( $('div.tab-pane'), function (div) {
            $(div).find('select.bulan').select2();

            $(div).find('#Tahun').datetimepicker({
                locale: 'id',
                format: 'Y'
            });
        });
    }, // end - khl

    getData: function(elm) {
        var div = $(elm).closest('div.tab-pane');

        var err = 0;
        $.map( $(div).find('[data-required=1]'), function (ipt) {
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
            var dcontent = $(div).find('table.tbl_laporan tbody');
			var params = {
				'bulan': $(div).find('select.bulan').select2().val(),
				'tahun': dateSQL( $(div).find('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/KartuPiutangRingkas/getData',
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

    getDataPerUnit: function(elm) {
        var div = $(elm).closest('div.tab-pane');

        var err = 0;
        $.map( $(div).find('[data-required=1]'), function (ipt) {
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
            var dcontent = $(div).find('table.tbl_laporan tbody');
			var params = {
				'bulan': $(div).find('select.bulan').select2().val(),
				'tahun': dateSQL( $(div).find('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/KartuPiutangRingkas/getDataPerUnit',
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
    }, // end - getDataPerUnit
};

khl.startUp();