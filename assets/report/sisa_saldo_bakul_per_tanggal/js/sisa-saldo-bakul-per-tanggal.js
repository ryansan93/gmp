var ss = {
    startUp: function () {
        ss.settingUp();
    }, // end - startUp

    settingUp: function () {
        $('.pelanggan').select2({placeholder: 'Pilih Pelanggan'}).on("select2:select", function (e) {
            var pelanggan = $('.pelanggan').select2().val();

            for (var i = 0; i < pelanggan.length; i++) {
                if ( pelanggan[i] == 'all' ) {
                    $('.pelanggan').select2().val('all').trigger('change');

                    i = pelanggan.length;
                }
            }

            $('.pelanggan').next('span.select2').css('width', '100%');
        });
        $('.pelanggan').next('span.select2').css('width', '100%');

        $('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('.perusahaan').next('span.select2').css('width', '100%');
        });
        $('.perusahaan').next('span.select2').css('width', '100%');

        $("#Tanggal").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - settingUp

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
            var dcontent = $('table').find('tbody');

			var params = {
                'pelanggan' : $('.pelanggan').select2().val(),
                'perusahaan' : $('.perusahaan').select2().val(),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
			};

			$.ajax({
                url : 'report/SisaSaldoBakulPerTanggal/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

                    ss.hitTotal();
                }
            });
		}
	}, // end - getLists

    hitTotal: function() {
        $.map( $('thead').find('td.hit_total'), function(td) {
            var target = $(td).attr('data-target');

            var total = 0;
            $.map( $('tbody').find('td[target="'+target+'"]'), function(td_target) {
                var nilai = numeral.unformat( $(td_target).text() );

                total += nilai;
            });

            $(td).find('b').text( numeral.formatDec( total ) );
        });
    }, // end - hitTotal

    encryptParams: function() {
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
            var dcontent = $('table').find('tbody');

			var params = {
                'pelanggan' : $('.pelanggan').select2().val(),
                'perusahaan' : $('.perusahaan').select2().val(),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
			};

			$.ajax({
                url : 'report/SisaSaldoBakulPerTanggal/encryptParams',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading() },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        ss.exportExcel(data.content);
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
	}, // end - encryptParams

    exportExcel : function (params) {
		goToURL('report/SisaSaldoBakulPerTanggal/exportExcel/'+params);
	}, // end - exportExcel
};

ss.startUp();