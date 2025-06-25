var hs = {
	start_up: function () {
		hs.setting_up();
	}, // end - start_up

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $("[name=startDate]").datetimepicker({
			locale: 'id',
            format: 'MMM YYYY',
            useCurrent: false //Important! See issue #1075
		});
		$("[name=endDate]").datetimepicker({
			locale: 'id',
            format: 'MMM YYYY',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			var start_date = dateSQL($("[name=startDate]").data("DateTimePicker").date());
			$("[name=endDate]").data("DateTimePicker").minDate(new Date(start_date.substr(0, 7)+'-01 00:00:00'));
		});
		$("[name=endDate]").on("dp.change", function (e) {
			var end_date = dateSQL($("[name=endDate]").data("DateTimePicker").date());
			$('[name=startDate]').data("DateTimePicker").maxDate(new Date(end_date.substr(0, 7)+'-01 23:59:59'));
		});
	}, // end - setting_up

	hitung_stok: function() {
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
			bootbox.alert('Harap isi periode terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin proses perhitungan stok ?', function(result) {
				if ( result ) {
					var startDate = dateSQL( $('[name=startDate]').data('DateTimePicker').date() );
					var endDate = dateSQL( $('[name=endDate]').data('DateTimePicker').date() );
					
					hs.exec_hitung_stok(startDate, endDate, endDate, $('[name=startDate]').find('input').val());
				}
			});
		}
	}, // end - hitung_stok

	exec_hitung_stok: function(startDate, endDate, target, text_target) {
		$.ajax({
			url: 'transaksi/HitungStok/hitung_stok',
			// url: 'transaksi/HitungStok/tes',
			data: {
				'startDate': startDate,
				'endDate': endDate,
				'target': target
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				console.log( text_target );
				showLoading('Proses hitung bulan <b>'+text_target.toUpperCase()+'<b>');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					if ( data.lanjut == 1 ) {
						var params = data.params;

						hs.exec_hitung_stok(params.start_date, params.end_date, params.target, params.text_target);
					} else {
						bootbox.alert(data.message, function() {
							location.reload();
						});
					}
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_hitung_stok
};

hs.start_up();