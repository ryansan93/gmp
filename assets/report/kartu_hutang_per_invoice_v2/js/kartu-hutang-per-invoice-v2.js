var khl2 = {
    startUp: function() {
        khl2.settingUp();
    }, // end - khl2

    settingUp: function() {
        $('select.unit').select2();
        $('select.jenis').select2().on("select2:select", function (e) {
            khl2.getSupplierJenis();
        });;
        $('select.supplier').select2();
        $('select.jenis_hutang').select2({
            placeholder: '-- Semua Jenis Hutang --',
            allowClear: true,
        });

        $('#TanggalAwal').datetimepicker({
            locale: 'id',
            format: 'YYYY-MM-DD'
        });
        $('#TanggalAkhir').datetimepicker({
            locale: 'id',
            format: 'YYYY-MM-DD'
        });
    }, // end - khl2

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
				'start_date': dateSQL( $('#TanggalAwal').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#TanggalAkhir').data('DateTimePicker').date() ),
                'unit': $('.unit').select2().val(),
                'jenis': $('.jenis').select2().val(),
				'supplier': $('.supplier').select2().val(),
				'jenis_hutang': $('.jenis_hutang').select2().val(),
			};

			$.ajax({
                url : 'report/KartuHutangPerInvoiceV2/getData',
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

khl2.startUp();
