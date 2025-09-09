var khl = {
    startUp: function() {
        khl.settingUp();
    }, // end - khl

    settingUp: function() {
        $('select.bulan').select2();
        $('select.jenis').select2().on("select2:select", function (e) {
            khl.getSupplierJenis();
        });;
        $('select.supplier').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
    }, // end - khl

    getSupplierJenis: function() {
        var jenis = $('select.jenis').select2().val();

        $('select.supplier').find('option').removeAttr('disabled');
        if ( jenis != 'all' ) {
            $('select.supplier').find('option:not([data-jenis="'+jenis+'"])').attr('disabled', 'disabled');
            $('select.supplier').find('option[value="all"]').removeAttr('disabled');
        }

        $('select.supplier').select2();
    }, // end - getSupplierJenis

    getData: function() {
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
				'jenis': $('.jenis').select2().val(),
				'supplier': $('.supplier').select2().val(),
			};

			$.ajax({
                url : 'report/KartuHutangLengkap/getData',
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

khl.startUp();