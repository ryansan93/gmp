var bank = {
    startUp: function () {
        bank.settingUp();
    }, // end - startUp

    settingUp: function () {
        $('select.bank').select2();
        $('select.akun_transaksi').select2({placeholder: '-- Pilih Akun --'}).on("select2:select", function (e) {
            var akun_transaksi = $('.akun_transaksi').select2().val();

            for (var i = 0; i < akun_transaksi.length; i++) {
                if ( akun_transaksi[i] == 'all' ) {
                    $('.akun_transaksi').select2().val('all').trigger('change');

                    i = akun_transaksi.length;
                }
            }

            $('.akun_transaksi').next('span.select2').css('width', '100%');
        });
        $('select.bulan').select2();

        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

        bank.cekTanggal();
    }, // end - settingUp

    getLists: function () {
        var dcontent = $('table').find('tbody');

		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
            var div_contain = $(ipt).closest('div.contain');

            var cek = 1;
            if ( $(div_contain).length > 0 ) {
                if ( $(div_contain).hasClass('hide') ) {
                    cek = 0;
                }
            }

            if ( cek == 1 ) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).parent().addClass('has-error');
                    err++;
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
            var jenis_tanggal = null;
            if ( $('#bulanan').is(':checked') ) {
                jenis_tanggal = 'bulanan';
            } else {
                jenis_tanggal = 'harian';
            }

			var params = {
				'bank': $('select.bank').select2().val(),
				'akun_transaksi': $('select.akun_transaksi').select2().val(),
				'jenis_tanggal': jenis_tanggal,
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/Bank/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

                    bank.hitTotal();
                }
            });
		}
	}, // end - getLists

    hitTotal: function() {
        var tot_debit = 0;
        var tot_kredit = 0;

        $.map( $('table tbody').find('tr'), function (tr) {
            var debit = parseFloat(numeral.unformat($(tr).find('td.debit').text()));
            var kredit = parseFloat(numeral.unformat($(tr).find('td.kredit').text()));
            
            tot_debit += debit;
            tot_kredit += kredit;
        });

        $('table thead').find('td.tot_debit b').text( numeral.formatDec( tot_debit ) );
        $('table thead').find('td.tot_kredit b').text( numeral.formatDec( tot_kredit ) );
    }, // end - hitTotal

    cekTanggal: function() {
        if ( $('#bulanan').is(':checked') ) {
            $('div.bulanan').removeClass('hide');
            $('div.harian').addClass('hide');
        } else {
            $('div.bulanan').addClass('hide');
            $('div.harian').removeClass('hide');
        }
    }, // end - cekTanggal

    excryptParams: function() {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
            var div_contain = $(ipt).closest('div.contain');

            var cek = 1;
            if ( $(div_contain).length > 0 ) {
                if ( $(div_contain).hasClass('hide') ) {
                    cek = 0;
                }
            }

            if ( cek == 1 ) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).parent().addClass('has-error');
                    err++;
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var jenis_tanggal = null;
            if ( $('#bulanan').is(':checked') ) {
                jenis_tanggal = 'bulanan';
            } else {
                jenis_tanggal = 'harian';
            }

			var params = {
				'bank': $('select.bank').select2().val(),
				'akun_transaksi': $('select.akun_transaksi').select2().val(),
				'jenis_tanggal': jenis_tanggal,
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'report/Bank/excryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
						bank.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

    exportExcel : function (params) {
		goToURL('report/Bank/exportExcel/'+params);
	}, // end - exportExcel
};

bank.startUp();