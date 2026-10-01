var nrc = {
	startUp: function () {
		nrc.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.perusahaan').select2();
        $('.bulan').select2();

		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
	}, // end - settingUp

	getData: function() {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi parameter terlebih dahulu.');
		} else {
			var params = {
				'perusahaan': $('.perusahaan').select2().val(),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
			};

			$.ajax({
                url : 'report/Neraca/getData',
                data : {
                    'params' : params
                },
                dataType : 'HTML',
                type : 'GET',
                beforeSend : function(){ showLoading(); },
                success : function(html){
                	$('.tbl_laporan tbody').html( html );

                    hideLoading();
                }
            });
		}
	}, // end - getData

	formDetail: function(elm) {
		var tr = $(elm).closest('tr');

		$('.neraca-item-row').removeClass('neraca-row-selected');
		$(tr).addClass('neraca-row-selected');

		var params = {
			'id_header': $(tr).attr('data-id-header'),
			'item_report_id': $(tr).attr('data-item-report-id'),
			'item_report_nama': $(tr).attr('data-item-report-nama'),
			'bulan': $('.bulan').select2().val(),
			'tahun': dateSQL($('#tahun').data('DateTimePicker').date()),
			'perusahaan': $('.perusahaan').select2().val()
		};

		showLoading();

		$.get('report/Neraca/formDetail', {
				'params': params
			}, function(data) {
			hideLoading();

			var _options = {
				className: 'veryWidth',
				message: data,
				size: 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function() {
				$(this).find('.modal-dialog').css({'max-width': '100%', 'width': '70%'});

				$(this).find('button.close').click(function() {
					$('div.modal.show').css({'overflow': 'auto'});
				});
			});
		}, 'html');
	}, // end - formDetail
};

nrc.startUp();