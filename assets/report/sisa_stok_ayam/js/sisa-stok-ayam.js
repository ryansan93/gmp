var ssa = {
	startUp: function () {
		ssa.settingUp();
	}, // end - startUp

	settingUp: function () {
		$("[name=tanggal]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});

		$('select.unit').select2();
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
				'unit': $('.unit').select2('val'),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/SisaStokAyam/getLists',
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
				'unit': $('.unit').select2('val'),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'report/SisaStokAyam/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						ssa.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/SisaStokAyam/exportExcel/'+params);
	}, // end - exportExcel
};

ssa.startUp();