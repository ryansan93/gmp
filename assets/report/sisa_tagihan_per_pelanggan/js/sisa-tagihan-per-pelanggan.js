var stpp = {
	start_up: function () {
        stpp.setting_up();
	}, // end -start_up

    setting_up: function () {
        $("#tanggal").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $("#tanggal").find('input').attr('data-tgl');
        if ( !empty(tgl) ) {
            $("#tanggal").data('DateTimePicker').date(new Date(tgl));
        }

        $('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('.unit').next('span.select2').css('width', '100%');
        });
        $('.unit').next('span.select2').css('width', '100%');

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

        // $('.pelanggan').selectpicker();
    }, // end - setting_up

	get_lists: function (elm) {
		var form = $(elm).closest('form');

        var err = 0;
        $.map( $(form).find('[data-required=1]'), function(ipt) {
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
    		var pelanggan = $(form).find('.pelanggan').select2().val();
            var kode_unit = $(form).find('.unit').select2().val();
            var kode_perusahaan = $(form).find('.perusahaan').select2().val();
            var minimal_lama_bayar = numeral.unformat($(form).find('.minimal_lama_bayar').val());
            var tanggal = dateSQL($(form).find('#tanggal').data('DateTimePicker').date());

            var params = {
                'pelanggan': pelanggan,
                'kode_unit': kode_unit,
                'kode_perusahaan': kode_perusahaan,
                'minimal_lama_bayar': minimal_lama_bayar,
                'tanggal': tanggal
            };

    		$.ajax({
                url : 'report/SisaTagihanPerPelanggan/get_lists',
                data : {
                    'params' : params
                },
                dataType : 'JSON',
                type : 'POST',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                    $('table').find('tbody').html(data.list);

                    var tot_tonase = 0;
                    var tot_tagihan = 0
                    var tot_sisa_tagihan= 0;

                    $.map( $('table').find('tbody tr'), function(tr) {
                        tot_tonase += numeral.unformat( $(tr).find('td.tonase').text() );
                        tot_tagihan += numeral.unformat( $(tr).find('td.tagihan').text() );
                        tot_sisa_tagihan += numeral.unformat( $(tr).find('td.sisa_tagihan').text() );
                    });

                    $('table').find('td.total_tonase b').text( numeral.formatDec(tot_tonase) );
                    $('table').find('td.total_tagihan b').text( numeral.formatDec(tot_tagihan) );
                    $('table').find('td.total_sisa_tagiahn b').text( numeral.formatDec(tot_sisa_tagihan) );

                    hideLoading();

                    $('.unit').next('span.select2').css('width', '100%');
                    $('.pelanggan').next('span.select2').css('width', '100%');
                }
            });
        }
	}, // end - get_lists

    cekExportExcel: function (elm) {
        var form = $(elm).closest('form');

        var err = 0;
        $.map( $(form).find('[data-required=1]'), function(ipt) {
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
            var pelanggan = $(form).find('.pelanggan').select2().val();
            var kode_unit = $(form).find('.unit').select2().val();
            var kode_perusahaan = $(form).find('.perusahaan').select2().val();
            var minimal_lama_bayar = numeral.unformat($(form).find('.minimal_lama_bayar').val());
            var tanggal = dateSQL($(form).find('#tanggal').data('DateTimePicker').date());

            var params = {
                'pelanggan': pelanggan,
                'kode_unit': kode_unit,
                'kode_perusahaan': kode_perusahaan,
                'minimal_lama_bayar': minimal_lama_bayar,
                'tanggal': tanggal
            };

            $.ajax({
                url : 'report/SisaTagihanPerPelanggan/cekExportExcel',
                data : {
                    'params' : params
                },
                dataType : 'JSON',
                type : 'POST',
                beforeSend : function(){},
                success : function(data){
                    window.open('report/SisaTagihanPerPelanggan/exportExcel/'+data.content, '_blank');
                }
            });
        }
    }, // end - cekExportExcel
};

stpp.start_up();