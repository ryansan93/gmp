var lhm = {
	startUp: function() {
		lhm.settingUp();
	}, // end - startUp

	settingUp: function() {
		$('[name=tanggal]').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - settingUp

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
			var params = {
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/LaporanHarianManajemen/getLists',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        $('div.data').html( data.html );
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
    }, // end - getLists
};

lhm.startUp();