var rpp = {
    startUp: function() {
        rpp.settingUp();
    }, // end - rpp

    settingUp: function() {
        $('select.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            $('select.mitra').find('option').removeAttr('disabled');

            var unit = $('select.unit').select2().val();

            var _attr = '';
            var all = 0;
            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.unit').select2().val('all').trigger('change');

                    i = unit.length;

                    $('select.mitra').find('option').removeAttr('disabled');
                    all = 1;
                } else {
                    if ( !empty(_attr) ) {
                        _attr += ', ';
                    }

                    _attr += '.'+unit[i];
                    
                }
            }
            
            if ( all == 0 ) {
                $('select.mitra').find('option:not(.all, '+_attr+')').attr('disabled', 'disabled');
            }

            $('select.unit').next('span.select2').css('width', '100%');

            $('select.mitra').select2({placeholder: 'Pilih Plasma'}).on("select2:select", function (e) {
                var unit = $('select.mitra').select2().val();
    
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] == 'all' ) {
                        $('select.mitra').select2().val('all').trigger('change');
    
                        i = unit.length;
                    }
                }
    
                $('select.mitra').next('span.select2').css('width', '100%');
            });
            $('select.mitra').next('span.select2').css('width', '100%');
        });
        $('select.unit').next('span.select2').css('width', '100%');

        $('select.mitra').select2({placeholder: 'Pilih Plasma'}).on("select2:select", function (e) {
            var unit = $('select.mitra').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.mitra').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('select.mitra').next('span.select2').css('width', '100%');
        });
        $('select.mitra').next('span.select2').css('width', '100%');
    }, // end - settingUp

    getLists: function() {
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
            var params = {
                'unit': $('select.unit').select2().val(),
                'mitra': $('select.mitra').select2().val()
            };

            $.ajax({
                url : 'report/RiwayatPerformancePlasma/getLists',
                data : {'params' : params},
                dataType : 'HTML',
                type : 'GET',
                beforeSend : function(){ showLoading(); },
                success : function(html){
                    hideLoading();

                    $('table tbody').html( html );
                    // if ( data.status == 1 ) {
                    // } else {
                    //     bootbox.alert(data.message);
                    // }
                }
            });
        }
    }, // end - getLists

    excryptParams: function() {
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
			var params = {
                'unit': $('select.unit').select2().val(),
                'mitra': $('select.mitra').select2().val()
            };

			$.ajax({
	            url: 'report/RiwayatPerformancePlasma/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                rpp.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/RiwayatPerformancePlasma/exportExcel/'+params);
	}, // end - exportExcel
};

rpp.startUp();