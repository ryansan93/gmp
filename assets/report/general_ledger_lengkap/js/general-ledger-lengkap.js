var gl = {
    startUp: function () {
        gl.settingUp();
    }, // end - startUp

    settingUp: function () {
		$('.perusahaan').select2();
		$('.unit').select2();
		$('.coa_start').select2().on("select2:select", function (e) {
            gl.setCoaEnd();
        });
		$('.coa_end').select2().on("select2:select", function (e) {
            gl.setCoaStart();
        });

        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		gl.setCoaEnd();
		gl.setCoaStart();
    }, // end - settingUp

	setCoaEnd: function() {
		$('select.coa_end').select2('destroy');

        var coa_start = $('select.coa_start').select2().val();

        $('select.coa_end').find('option').removeAttr('disabled');
		$.map( $('select.coa_end').find('option'), function(opt) {
			var val = $(opt).val();

			if ( val < coa_start ) {
				$(opt).attr('disabled', 'disabled');
			}
		});

        $('select.coa_end').select2().on("select2:select", function (e) {
            gl.setCoaStart();
        });
    }, // end - setCoaEnd

	setCoaStart: function() {
		$('select.coa_start').select2('destroy');

        var coa_end = $('select.coa_end').select2().val();

        $('select.coa_start').find('option').removeAttr('disabled');
		$.map( $('select.coa_start').find('option'), function(opt) {
			var val = $(opt).val();

			if ( val > coa_end ) {
				$(opt).attr('disabled', 'disabled');
			}
		});

        $('select.coa_start').select2().on("select2:select", function (e) {
            gl.setCoaEnd();
        });
    }, // end - setCoaStart

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
				'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
				'unit': $('.unit').select2().val(),
				'coa_start': $('.coa_start').select2().val(),
				'coa_end': $('.coa_end').select2().val(),
				'perusahaan': $('.perusahaan').select2().val()
			};

			$.ajax({
                url : 'report/GeneralLedgerLengkap/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

					setTimeout(function() {
						// console.log("This message appears after 2 seconds.");
						var urut = 1;
						gl.getDetail( urut );
					}, 1000); // 2000 milliseconds = 2 seconds
                }
            });
		}
	}, // end - getLists

	getDetail: function(urut) {
		var tr = $('tr.get_data[data-urut="'+urut+'"]');
		var tr_detail = $('tr.detail[data-urut="'+urut+'"]');

		var params = {
			'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
			'end_date': dateSQL($('#EndDate').data('DateTimePicker').date()),
			'unit': $(tr).attr('data-unit'),
			'coa': $(tr).attr('data-coa'),
			'urut': urut
		};
		
		console.log( params );
		console.log( urut );
		console.log( $(tr_detail).length );
		console.log( $('tr.get_data[data-urut="'+urut+'"]').length );

		// urut = urut+1;
		// if ( $('tr.get_data[data-urut="'+urut+'"]').length > 0 ) {
		// 	gl.getDetail( urut );
		// }

		$.ajax({
			url : 'report/GeneralLedgerLengkap/getListsDetail',
			data : {
				'params' : params
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){ App.showLoaderInContent( $(tr_detail) ); },
			success : function(html){
				// App.hideLoaderInContent( $(dcontent), html );
				$(tr_detail).replaceWith( html );

				setTimeout(function() {
					urut = urut+1;
					if ( $('tr.get_data[data-urut="'+urut+'"]').length > 0 ) {
						gl.getDetail( urut );
					}
				}, 500); // 2000 milliseconds = 2 seconds
			}
		});
	}, // end - getDetail

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
		
		$.get('report/GeneralLedgerLengkap/formDetail',{
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
				'perusahaan': $('.perusahaan').select2().val()
			};

			$.ajax({
	            url: 'report/GeneralLedgerLengkap/encryptParams',
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
		goToURL('report/GeneralLedgerLengkap/exportExcel/'+params);
	}, // end - exportExcel
};

gl.startUp();