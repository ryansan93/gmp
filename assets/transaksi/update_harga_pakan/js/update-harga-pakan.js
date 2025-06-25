var uhp = {
	startUp: function() {
        uhp.settingUp();
		uhp.getRiwayat();
	}, // end - startUp

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $('.pakan').select2();
		$('.supplier').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    getRiwayat: function() {
		$.ajax({
			url: 'transaksi/UpdateHargaPakan/getRiwayat',
			data: {},
			type: 'GET',
			dataType: 'HTML',
			beforeSend: function(){ App.showLoaderInContent( $('table tbody') ); },
			success: function(html){
				App.hideLoaderInContent( $('table tbody'), $(html) );
			}
		});
    }, // end - getLists

	save: function() {
		var err = 0;
		$.map( $('[data-required="1"]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data harga ?', function (result) {
				if ( result ) {
					var params = {
						'tgl_order': dateSQL( $('#TglOrder').data('DateTimePicker').date() ),
						'pakan': $('.pakan').select2('val'),
						'supplier': $('.supplier').select2('val'),
						'harga': numeral.unformat( $('.harga').val() )
					};

					$.ajax({
			            url: 'transaksi/UpdateHargaPakan/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
							hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
			            			uhp.getRiwayat();
			            		});
			            	} else{
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
		}
	}, // end - save
};

uhp.startUp();