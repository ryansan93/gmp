var lr = {
	_xhr: {}, // jqXHR pending requests milik fitur ini, dilacak per key (getData/viewForm/encryptParams)

	startUp: function () {
		lr.settingUp();
		lr.bindStopRequests();
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

	// NOTE: query laporan ini berat (banyak join, bisa >1 menit di server). Supaya
	// request lama yg masih jalan tidak numpuk/balapan sama request baru saat filter
	// diganti-ganti cepat, ATAU nyangkut percuma saat halaman ditutup/refresh/pindah,
	// semua request AJAX fitur ini dilacak di lr._xhr lalu di-abort() di titik-titik
	// itu. CATATAN: abort() cuma menghentikan sisi BROWSER (berhenti menunggu &
	// membatalkan render hasil) - query yg sudah terlanjur dikirim ke SQL Server tetap
	// jalan sampai selesai di server (tidak ada cara membatalkan query SQL yg sedang
	// berjalan cuma dari JS), tapi ini tetap mencegah penumpukan request BARU dan
	// mencegah hasil basi (response request lama) menimpa tampilan yg lebih baru.
	stopAllRequests: function() {
		$.each(lr._xhr, function(key, xhr){
			if ( xhr && typeof xhr.abort === 'function' && xhr.state() === 'pending' ) {
				xhr.abort();
			}
		});
		lr._xhr = {};
	}, // end - stopAllRequests

	bindStopRequests: function() {
		// halaman di-refresh / ditutup / pindah url - lihat catatan stopAllRequests()
		$(window).off('beforeunload.lr pagehide.lr').on('beforeunload.lr pagehide.lr', function(){
			lr.stopAllRequests();
		});
	}, // end - bindStopRequests

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

			lr.stopAllRequests();
			lr._xhr.getData = $.ajax({
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
        lr.stopAllRequests();
        lr._xhr.viewForm = $.get('report/LabaRugi/viewForm',{
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

        lr.stopAllRequests();
        lr._xhr.encryptParams = $.ajax({
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