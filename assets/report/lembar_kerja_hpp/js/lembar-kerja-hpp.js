var lkh = {
	startUp: function () {
		lkh.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('select.unit').select2();

		$('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

		lkh.set_table_page('#tbl_lkh');
	}, // end - settingUp

	set_table_page : function(tbl_id){
        let _t_rdim = TUPageTable;
        _t_rdim.destroy();
        _t_rdim.setTableTarget(tbl_id);
        _t_rdim.setPages(['page1', 'page2', 'page3', 'page4', 'page5', 'page6']);
        _t_rdim.setHideButton(true);
        _t_rdim.onClickNext(function(){
            // console.log('Log onClickNext');
        });
        _t_rdim.start();
    }, // end - set_table_page

    getPeriodeParams: function () {
    	var bulan = $('.bulan').val();
    	var angkaBulan = (bulan.length == 1) ? '0'+bulan : bulan;
    	var tahun = $('#Tahun').data('DateTimePicker').date().format('YYYY');

    	var start_date = tahun+'-'+angkaBulan+'-01';
    	var end_date = moment(start_date).endOf('month').format('YYYY-MM-DD');

    	return {
    		'start_date': start_date,
    		'end_date': end_date
    	};
    }, // end - getPeriodeParams

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
			var periode = lkh.getPeriodeParams();
			var params = {
				'unit': $('.unit').select2('val'),
				'start_date': periode.start_date,
				'end_date': periode.end_date
			};

			$.ajax({
                url : 'report/LembarKerjaHpp/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

					lkh.set_table_page('#tbl_lkh');
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
			var periode = lkh.getPeriodeParams();
			var params = {
				'unit': $('.unit').select2('val'),
				'start_date': periode.start_date,
				'end_date': periode.end_date
			};

			$.ajax({
	            url: 'report/LembarKerjaHpp/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						lkh.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/LembarKerjaHpp/exportExcel/'+params);
	}, // end - exportExcel
};

lkh.startUp();