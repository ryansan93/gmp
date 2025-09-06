var ps = {
	start_up: function() {
		ps.settingUp();
	}, // end - start_up

	settingUp: function() {
		$('[name=tanggal]').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		$('select.gudang').select2()
        $('select.barang').select2();
        $('select.jenis').select2().on("select2:select", function (e) {
			ps.getGudangAndBarang();
        });

		ps.getGudangAndBarang();
	}, // end - settingUp

	getGudangAndBarang: function() {
        var jenis = $('select.jenis').find('option:selected').val();

        $('select.gudang').find('option').removeAttr('disabled');
        $('select.barang').find('option').removeAttr('disabled');
        if ( jenis != 'all' ) {
            $('select.gudang').find('option:not([data-jenis="'+jenis+'"])').attr('disabled', 'disabled');
            $('select.gudang').find('option[value="all"]').removeAttr('disabled');

            $('select.barang').find('option:not([data-jenis="'+jenis+'"])').attr('disabled', 'disabled');
            $('select.barang').find('option[value="all"]').removeAttr('disabled');
        }

        $('select.gudang').select2();
        $('select.barang').select2();
    }, // end - getGudangAndBarang

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
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
				'gudang': $('.gudang').select2().val(),
				'barang': $('.barang').select2().val(),
				'jenis': $('.jenis').select2().val()
			};

			$.ajax({
                url : 'report/PosisiStok/getData',
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

ps.start_up();