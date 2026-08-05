var rgu = {
    startUp: function() {
        rgu.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('select.bulan').select2();
        $('select.perusahaan').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
    }, // end - settingUp

    getData: function() {
        var err = 0;
        $.map( $('[data-required=1]'), function (ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
            var dcontent = $('table.tbl_laporan tbody');
			var params = {
				'bulan': $('select.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() ),
				'perusahaan': $('select.perusahaan').select2().val()
			};

			$.ajax({
                url : 'report/RingkasanGlPerUnit/getData',
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
};

rgu.startUp();