var gl = {
    startUp: function () {
        gl.settingUp();
    }, // end - startUp

    settingUp: function () {
        $('select.bulan').select2();
		$('.perusahaan').select2();
		$('select.unit').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
    }, // end - settingUp

    getLists: function () {
        var dcontent = $('table').find('tbody');

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
			var params = {
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() ),
				'unit': $('.unit').select2().val(),
				'perusahaan': $('.perusahaan').select2().val()
			};

			$.ajax({
                url : 'report/GeneralLedger/getLists',
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

	formDetail: function(elm) {
		var tr = $(elm).closest('tr');

		showLoading();

		var periode = $(tr).attr('data-periode');
		var no_coa = $(tr).find('td.no_coa').text();
		var unit = $(tr).find('td.unit').text();
		var nama_coa = $(tr).find('td.nama_coa').text();

		var params = {
			'periode': periode,
			'no_coa': no_coa,
			'unit': unit,
			'nama_coa': nama_coa,
		};
		
		$.get('report/GeneralLedger/formDetail',{
				'params': params
			},function(data){
			hideLoading();
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				$(this).find('.modal-dialog').css({'max-width':'100%', 'width': '70%'});

				var modal_dialog = $(this).find('.modal-dialog');
				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
					$(this).find('tr #btn-remove').addClass('hide');
				};

				$(this).find('button.close').click(function() {
					$('div.modal.show').css({'overflow': 'auto'});
				});
			});
		},'html');
	}, // end - formDetail

    encryptParams: function() {
		var err = 0;
		
		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
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
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() ),
				'unit': $('.unit').select2().val(),
				'perusahaan': $('.perusahaan').select2().val()
			};

			$.ajax({
	            url: 'report/GeneralLedger/encryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                gl.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - encryptParams

	exportExcel : function (params) {
		goToURL('report/GeneralLedger/exportExcel/'+params);
	}, // end - exportExcel
};

gl.startUp();