var lr = {
	startUp: function () {
		lr.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.perusahaan').select2();
		$('.unit').select2();
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
				'unit': $('.unit').select2().val(),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
			};

			$.ajax({
                url : 'report/LabaRugi/getData',
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

	viewForm: function (elm) {
        $('.modal').modal('hide');

        var params = {
            'perusahaan': $(elm).attr('data-perusahaan'),
            'unit': $(elm).attr('data-unit'),
            'bulan': $(elm).attr('data-bulan'),
            'tahun': $(elm).attr('data-tahun')
        };

        showLoading();
        $.get('report/LabaRugi/viewForm',{
            'params': params 
        },function(data){
            hideLoading();
            
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
                onEscape: true,
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $(this).find('.modal-header').css({'padding-top': '0px'});
                $(this).find('.modal-dialog').css({'width': '80%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    // $(this).priceFormat(Config[$(this).data('tipe')]);
                    priceFormat( $(this) );
                });

                $(this).find('.member_group').select2();
                $(this).removeAttr('tabindex');
            });
        },'html');
    }, // end - viewForm

    encryptParams: function(elm) {
        var params = {
            'perusahaan': $(elm).attr('data-perusahaan'),
            'unit': $(elm).attr('data-unit'),
            'bulan': $(elm).attr('data-bulan'),
            'tahun': $(elm).attr('data-tahun')
        };

        $.ajax({
            url: 'report/LabaRugi/encryptParams',
            data: {
                'params': params
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();

                if ( data.status == 1 ) {
                    lr.exportExcel(data.content);
                } else {
                    bootbox.alert( data.message );
                }
            }
        });
	}, // end - encryptParams

	exportExcel : function (params) {
		goToURL('report/LabaRugi/exportExcel/'+params);
	}, // end - exportExcel
};

lr.startUp();