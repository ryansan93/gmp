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

                    stpp.hitSubTotal();

                    hideLoading();

                    $('.unit').next('span.select2').css('width', '100%');
                    $('.pelanggan').next('span.select2').css('width', '100%');
                }
            });
        }
	}, // end - get_lists

    hitSubTotal: function () {
        $.map( $('table').find('tbody tr.sub_total'), function(tr_subtotal) {
            var no_pelanggan = $(tr_subtotal).attr('data-nopelanggan');
            
            $.map( $(tr_subtotal).find('td.sub_total'), function(td_subtotal) {
                var target = $(td_subtotal).attr('data-target');
                var sub_total = 0;

                $.map( $('table').find('tbody tr.detail[data-nopelanggan="'+no_pelanggan+'"]'), function(tr_detail) {
                    var nilai = numeral.unformat($(tr_detail).find('td.'+target).text());
                    sub_total += nilai;
                });

                $(td_subtotal).find('b').text( numeral.formatDec(sub_total) );
            });
        });

        stpp.hitGrandTotal();
    }, // end - hitSubTotal

    hitGrandTotal: function () {
        $.map( $('table').find('thead tr.grandTotal'), function(tr_gtotal) {
            var target = $(tr_gtotal).attr('data-target');
            var g_total = 0;
            
            $.map( $(table).find('td.sub_total[target="'+target+'"]'), function(td_subtotal) {
                var nilai = numeral.unformat($(td_subtotal).text());
                g_total += nilai;
            });

            $(tr_gtotal).find('b').text( numeral.formatDec(g_total) );
        });
    }, // end - hitGrandTotal

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