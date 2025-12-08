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

	set_table_page : function(tbl_id){
        let _t_rdim = TUPageTable;
        _t_rdim.destroy();
        _t_rdim.setTableTarget(tbl_id);
        _t_rdim.setPages(['page1', 'page2', 'page3', 'page4']);
        _t_rdim.setHideButton(true);
        _t_rdim.onClickNext(function(){
            // console.log('Log onClickNext');
        });
        _t_rdim.start();
    }, // end - set_table_page

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
                url : 'report/SisaStokAyamMinMax/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

					ssa.set_table_page('#tbl_data');
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
	            url: 'report/SisaStokAyamMinMax/excryptParams',
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
		goToURL('report/SisaStokAyamMinMax/exportExcel/'+params);
	}, // end - exportExcel
};

ssa.startUp();