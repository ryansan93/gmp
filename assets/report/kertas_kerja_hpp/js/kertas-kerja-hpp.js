var kkh = {
	startUp: function () {
		kkh.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('select.unit').select2();
		// $('select.bulan').select2();

		// $('#Tahun').datetimepicker({
        //     locale: 'id',
        //     format: 'Y'
        // });

		$("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		kkh.set_table_page('#tbl_kkh');
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
				// 'bulan': $('.bulan').select2().val(),
				// 'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/KertasKerjaHpp/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

					kkh.set_table_page('#tbl_kkh');
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
				// 'bulan': $('.bulan').select2().val(),
				// 'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'report/KertasKerjaHpp/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						kkh.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/KertasKerjaHpp/exportExcel/'+params);
	}, // end - exportExcel
};

kkh.startUp();