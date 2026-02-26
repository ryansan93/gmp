var kk = {
	startUp: function () {
		kk.settingUp();
	}, // end - startUp

	settingUp: function () {
		$("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });

		$('select.unit').select2({placeholder: '-- Pilih Unit --'}).on("select2:select", function (e) {
            var unit = $('select.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }
        });
        $('select.pelanggan').select2({placeholder: '-- Pilih Bakul --'}).on("select2:select", function (e) {
            var pelanggan = $('select.pelanggan').select2().val();

            for (var i = 0; i < pelanggan.length; i++) {
                if ( pelanggan[i] == 'all' ) {
                    $('select.pelanggan').select2().val('all').trigger('change');

                    i = pelanggan.length;
                }
            }
        });
	}, // end - settingUp

	getLists: function () {
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
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
				'unit': $('select.unit').select2('val'),
				'pelanggan': $('.pelanggan').select2('val')
			};

			$.ajax({
                url : 'report/LaporanPenjualanAyam/getLists',
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
			var dcontent = $('table tbody');
			var params = {
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
				'unit': $('select.unit').select2('val'),
				'pelanggan': $('.pelanggan').select2('val')
			};

			$.ajax({
	            url: 'report/LaporanPenjualanAyam/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						kk.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/LaporanPenjualanAyam/exportExcel/'+params);
	}, // end - exportExcel
};

kk.startUp();