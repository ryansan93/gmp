var lj = {
    startUp: function () {
        lj.settingUp();
    }, // end - startUp

    settingUp: function () {
        $('select.bulan').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
    }, // end - settingUp

    getLists: function () {
        var dcontent = $('table').find('tbody');

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
                url : 'bantuan/TerimaOvkJurnal/getLists',
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
};

lj.startUp();