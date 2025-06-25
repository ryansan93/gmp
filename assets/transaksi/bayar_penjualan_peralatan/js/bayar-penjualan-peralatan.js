var bpp = {
	start_up: function() {
		bpp.setting_up('riwayat', 'div#riwayat');
		bpp.setting_up('transaksi', 'div#transaksi');
	}, // end - start_up

	get_lists: function(elm) {
        var div_riwayat = $(elm).closest('div#riwayat');
		var div_filter = $(elm).closest('div.filter');

        var err = 0;
        $.map( $(div_filter).find('[data-required=1]'), function(ipt) {
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
                'mitra': $(div_filter).find('#select_mitra').val(),
                'start_date': dateSQL( $(div_filter).find('#StartDateJual').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(div_filter).find('#EndDateJual').data('DateTimePicker').date() ),
                'filter': $(div_filter).find('select.filter').val()
            };

    		$.ajax({
                url: 'transaksi/BayarPenjualanPeralatan/get_lists',
                data: { 'params': params },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(){ showLoading() },
                success: function(data){
                    hideLoading();

                    $('table.tbl_penjualan').find('tbody').html( data.html );
                }
            });
        }

	}, // end - get_lists

    add_form: function(elm) {
        var params = {
            'id': $(elm).data('id')
        };

        $.get('transaksi/BayarPenjualanPeralatan/add_form',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_body = $(this).find('.modal-body:first');
                $(modal_body).css({'padding': '0px'});

                var table = $(modal_body).find('table');
                var tbody = $(table).find('tbody');
                if ( $(tbody).find('.modal-body tr').length <= 1 ) {
                    $(this).find('tr #btn-remove').addClass('hide');
                };

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $(modal_body).find('.tgl_bayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $.map( $(modal_body).find('.date'), function(ipt) {
                    var tgl = $(ipt).find('input').data('tgl');
                    if ( !empty(tgl) ) {
                        $(ipt).data("DateTimePicker").date(new Date(tgl));
                        // $(ipt).data("DateTimePicker").minDate(moment(tgl));
                    }
                });
            });
        },'html');
    }, // end - add_form

    edit_form: function(elm) {
        var params = {
            'id': $(elm).data('id')
        };

        $.get('transaksi/BayarPenjualanPeralatan/edit_form',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_body = $(this).find('.modal-body:first');
                $(modal_body).css({'padding': '0px'});

                var table = $(modal_body).find('table');
                var tbody = $(table).find('tbody');
                if ( $(tbody).find('.modal-body tr').length <= 1 ) {
                    $(this).find('tr #btn-remove').addClass('hide');
                };

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $(modal_body).find('.tgl_bayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $.map( $(modal_body).find('.date'), function(ipt) {
                    var tgl = $(ipt).find('input').data('tgl');
                    if ( !empty(tgl) ) {
                        $(ipt).data("DateTimePicker").date(new Date(tgl));
                        // $(ipt).data("DateTimePicker").minDate(moment(tgl));
                    }
                });
            });
        },'html');
    }, // end - add_form

    detail_form: function(elm) {
        var params = {
            'id': $(elm).data('id')
        };

        $.get('transaksi/BayarPenjualanPeralatan/detail_form',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                // $(this).find('.modal-dialog').css(
                //  'max-width','80%'
                // );

                var modal_body = $(this).find('.modal-body:first');
                $(modal_body).css({'padding': '0px'});

                var table = $(modal_body).find('table');
                var tbody = $(table).find('tbody');
                if ( $(tbody).find('.modal-body tr').length <= 1 ) {
                    $(this).find('tr #btn-remove').addClass('hide');
                };

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $(modal_body).find('.tgl_bayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $.map( $(modal_body).find('.date'), function(ipt) {
                    var tgl = $(ipt).find('input').data('tgl');
                    if ( !empty(tgl) ) {
                        $(ipt).data("DateTimePicker").date(new Date(tgl));
                        // $(ipt).data("DateTimePicker").minDate(moment(tgl));
                    }
                });
            });
        },'html');
    }, // end - detail_form

	setting_up: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$("#StartDateJual").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDateJual").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            // useCurrent: false //Important! See issue #1075
        });
        $("#StartDateJual").on("dp.change", function (e) {
            $("#EndDateJual").data("DateTimePicker").minDate(e.date);
        });
        $("#EndDateJual").on("dp.change", function (e) {
            $('#StartDateJual').data("DateTimePicker").maxDate(e.date);
        });
	}, // end - setting_up

	showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

    hit_tot_bayar: function(elm) {
        var modal_body = $(elm).closest('.modal-body');

        var table = $(modal_body).find('table.tbl_bayar');

        var total_jual = $(modal_body).find('div.tot_jual').data('val');
        var total_bayar = 0;
        $.map( $(table).find('tbody tr'), function(tr) {
            var jml_bayar = 0;
            if ( $(tr).find('input.jml_bayar').length > 0 ) {
                jml_bayar = numeral.unformat($(tr).find('input.jml_bayar').val());
            } else {
                jml_bayar = $(tr).find('td.jml_bayar').data('val');
            }

            total_bayar += jml_bayar;
        });

        var sisa_bayar = (total_bayar > total_jual) ? 0 : total_jual - total_bayar;

        $(modal_body).find('div.tot_bayar').attr('data-val', total_bayar);
        $(modal_body).find('div.tot_bayar label').text(': '+numeral.formatDec(total_bayar));

        $(modal_body).find('div.sisa_bayar').attr('data-val', sisa_bayar);
        $(modal_body).find('div.sisa_bayar label').text(': '+numeral.formatDec(sisa_bayar));

        if ( sisa_bayar == 0 ) {
            $(modal_body).find('div.status').attr('data-val', 'SUDAH');
            $(modal_body).find('div.status label span').text('SUDAH');
            $(modal_body).find('div.status label span').css({'color': 'blue'});
        } else {
            $(modal_body).find('div.status').attr('data-val', 'BELUM');
            $(modal_body).find('div.status label span').text('BELUM');
            $(modal_body).find('div.status label span').css({'color': 'red'});

            $(modal_body).find('.belum_lunas').removeClass('hide');
        }
    }, // end - hit_tot_bayar

	save: function(elm) {
        let err = 0;
        let div = $(elm).closest('.modal-body');

        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan pakan.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let data = {
                        'id_jual': $(elm).data('idjual'),
                        'tagihan': $(div).find('.tagihan').data('val'),
                        'saldo': $(div).find('.saldo').data('val'),
                        'tgl_bayar': dateSQL( $(div).find('.tgl_bayar').data('DateTimePicker').date() ),
                        'jumlah': numeral.unformat( $(div).find('input.jml_bayar').val() )
                    };

                    $.ajax({
                        url : 'transaksi/BayarPenjualanPeralatan/save',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {'params': data},
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    bootbox.hideAll();

                                    bpp.get_lists( $('button#btn-tampil') );
                                });
                            } else {
                                bootbox.alert( data.message );
                            }
                        },
                    });
                }
            });
        }
    }, // end - save

	edit: function(elm) {
        let err = 0;
        let div = $(elm).closest('.modal-body');

        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan pakan.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let data = {
                        'id_bayar': $(elm).data('idbayar'),
                        'tagihan': $(div).find('.tagihan').data('val'),
                        'saldo': $(div).find('.saldo').data('val'),
                        'tgl_bayar': dateSQL( $(div).find('.tgl_bayar').data('DateTimePicker').date() ),
                        'jumlah': numeral.unformat( $(div).find('input.jml_bayar').val() )
                    };

                    $.ajax({
                        url : 'transaksi/BayarPenjualanPeralatan/edit',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {'params': data},
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    bootbox.hideAll();

                                    bpp.get_lists( $('button#btn-tampil') );
                                });
                            } else {
                                bootbox.alert( data.message );
                            }
                        },
                    });
                }
            });
        }
	}, // end - edit

	delete: function(elm) {
    	var div = $('div#transaksi');

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
			if ( result ) {
				var data = {
					'id': $(elm).data('id')
				};

				$.ajax({
		            url: 'transaksi/BayarPenjualanPeralatan/delete',
		            data: { 'params': data },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		bootbox.hideAll();

                                bpp.get_lists( $('button#btn-tampil') );
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            }
		        });
			}
		});
    }, // end - delete
};

bpp.start_up();