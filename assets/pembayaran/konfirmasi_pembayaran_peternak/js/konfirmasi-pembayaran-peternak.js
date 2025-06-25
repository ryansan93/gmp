var formData = null;

var kpp = {
	start_up: function () {
		kpp.setting_up();

        formData = new FormData();
	}, // end - start_up

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
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

        kpp.compress_img( $(elm), _type);
    }, // end - showNameFile

    compress_img: function(elm, type) {
        showLoading();

        if ( type.indexOf("pdf") < 0 ) {
            var file_tmp = $(elm).get(0).files[0];

            ci.compress_img(file_tmp, file_tmp.name, 1080, function(data) {
                formData.append("files[0]", data);

                hideLoading();
            });
        } else {
            formData.append("files[0]", $(elm).get(0).files[0]);

            hideLoading();
        }
    }, // end - compress_img

	setting_up: function() {
        $('.check_all').change(function() {
            var data_target = $(this).data('target');

            if ( this.checked ) {
                $.map( $('.check[target='+data_target+']'), function(checkbox) {
                    $(checkbox).prop( 'checked', true );
                });
            } else {
                $.map( $('.check[target='+data_target+']'), function(checkbox) {
                    $(checkbox).prop( 'checked', false );
                });
            }

            kpp.hit_total_pilih(this);
        });

        $('.check').change(function() {
            var target = $(this).attr('target');

            var length = $('.check[target='+target+']').length;
            var length_checked = $('.check[target='+target+']:checked').length;

            if ( length == length_checked ) {
                $('.check_all').prop( 'checked', true );
            } else {
                $('.check_all').prop( 'checked', false );
            }

            kpp.hit_total_pilih(this);
        });

		// $('div#transaksi').find('#select_unit').selectpicker();
		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var div_tab_content = $(this).closest('div.tab-content');
            var div_active = $(div_tab_content).find('div.active');

            var option = $(e);
            var last_select = option[0].params.data.id;

			var unit = $(div_active).find('.unit').select2('val');

            if ( last_select == 'all' ) {
                $(div_active).find('.unit').select2().val(['all']).trigger('change');
            } else {
                var kode_unit = [];
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] != 'all' ) {
                        kode_unit.push( unit[i] );
                    }
                }

                $(div_active).find('.unit').select2().val(kode_unit).trigger('change');
            }

			$(div_active).find('.unit').next('span.select2').css('width', '100%');

            kpp.get_mitra(this);
		});
        $('.unit').next('span.select2').css('width', '100%');

        $('.select_peternak').select2({placeholder: 'Pilih Peternak'}).on("select2:select", function (e) {
            var div_tab_content = $(this).closest('div.tab-content');
            var div_active = $(div_tab_content).find('div.active');

            var option = $(e);
            var last_select = option[0].params.data.id;

            var unit = $(div_active).find('.select_peternak').select2('val');

            if ( last_select == 'all' ) {
                $(div_active).find('.select_peternak').select2().val(['all']).trigger('change');
            } else {
                var kode_unit = [];
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] != 'all' ) {
                        kode_unit.push( unit[i] );
                    }
                }

                $(div_active).find('.select_peternak').select2().val(kode_unit).trigger('change');
            }

            $(div_active).find('.select_peternak').next('span.select2').css('width', '100%');
        });
        $('.select_peternak').next('span.select2').css('width', '100%');

        // $('.select_peternak').select2();
        $('#select_perusahaan').select2();

        $('.perusahaan').select2();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('.date').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false, //Important! See issue #1075
            widgetPositioning: {
	            horizontal: "auto",
	            vertical: "auto"
	        }
		});

        $("[name=startDateTs]").on("dp.change", function (e) {
            $("[name=endDateTs]").data("DateTimePicker").minDate(e.date);
            // $("[name=endDateTs]").data("DateTimePicker").date(e.date);
        });
        $("[name=endDateTs]").on("dp.change", function (e) {
            $('[name=startDateTs]').data("DateTimePicker").maxDate(e.date);
        });

		// $.map( $('.date'), function(ipt) {
  //           var tgl = $(ipt).find('input').data('tgl');
  //           if ( !empty(tgl) ) {
  //               $(ipt).data("DateTimePicker").date(new Date(tgl));
  //           }
  //       });
	}, // end - setting_up

    changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+href).addClass('show');
        $('div#'+href).addClass('active');

        kpp.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'pembayaran/KonfirmasiPembayaranPeternak/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                kpp.setting_up(href, 'div#'+href);

                if ( !empty(edit) ) {
                    kpp.get_mitra( $(dcontent).find('.unit') );
                }
            },
        });
    }, // end - load_form

    get_lists: function() {
        var div = $('div#riwayat');
        let dcontent = $(div).find('table.tbl_riwayat tbody');

        var err = 0;
        $.map( $(div).find('[data-required=1]'), function(ipt) {
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
                'kode_unit': $(div).find('.unit').select2().val(),
                'mitra': $(div).find('.select_peternak').select2().val(),
                'perusahaan': $(div).find('.perusahaan').select2().val(),
                'start_date': dateSQL($(div).find('#start_date_bayar').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_bayar').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranPeternak/get_lists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    hideLoading();

                    $(div).find('.select_peternak').next('span.select2').css('width', '100%');
                    $(div).find('.perusahaan').next('span.select2').css('width', '100%');
                },
            });
        }
    }, // end - get_lists

    get_mitra: function(elm) {
        var div_tab_content = $(elm).closest('div.tab-content');
        var div_active = $(div_tab_content).find('div.active');

        var kode_unit = $(div_active).find('.unit').select2('val');
        var select_peternak = $(div_active).find('.select_peternak');

        if ( !empty(kode_unit) ) {
            var params = {
                'kode_unit': kode_unit
            };

            var nomor = $(select_peternak).data('val');

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranPeternak/get_mitra',
                data : { 'params': params },
                type : 'post',
                dataType : 'json',
                beforeSend : function(){ showLoading() },
                success : function(data){
                    hideLoading();

                    var option = '<option value="all">ALL</option>';

                    if ( data.content.length > 0 ) {
                        for (var i = 0; i < data.content.length; i++) {
                            var selected = null;
                            if ( !empty(nomor) ) {
                                if ( nomor == data.content[i].nomor ) {
                                    selected = 'selected';
                                }
                            }
                            option += '<option value="'+data.content[i].nomor+'" '+selected+' >'+data.content[i].unit+' | '+data.content[i].nama+'</option>';
                        }

                        $(select_peternak).removeAttr('disabled');
                    } else {
                        $(select_peternak).attr('disabled', 'disabled');
                    }

                    $(select_peternak).select2("destroy");

                    $(select_peternak).html( option );

                    // $(select_peternak).select2();
                    $(select_peternak).select2({placeholder: 'Pilih Peternak'}).on("select2:select", function (e) {
                        var div_tab_content = $(this).closest('div.tab-content');
                        var div_active = $(div_tab_content).find('div.active');

                        var option = $(e);
                        var last_select = option[0].params.data.id;

                        var unit = $(div_active).find(select_peternak).select2('val');

                        if ( last_select == 'all' ) {
                            $(div_active).find(select_peternak).select2().val(['all']).trigger('change');
                        } else {
                            var kode_unit = [];
                            for (var i = 0; i < unit.length; i++) {
                                if ( unit[i] != 'all' ) {
                                    kode_unit.push( unit[i] );
                                }
                            }

                            $(div_active).find(select_peternak).select2().val(kode_unit).trigger('change');
                        }

                        $(div_active).find(select_peternak).next('span.select2').css('width', '100%');
                    });
                    $(select_peternak).next('span.select2').css('width', '100%');
                },
            });
        } else {
            var option = '<option value="">Pilih Peternak</option>';

            $(select_peternak).attr('disabled', 'disabled');
            $(select_peternak).html( option );
            $(select_peternak).select2("destroy");
            $(select_peternak).select2();
        }
    }, // end - get_mitra

	get_data_rhpp: function() {
    	let div = $('div#transaksi');
        let dcontent = $(div).find('table tbody');

        var err = 0;
        $.map( $(div).find('[data-required=1]'), function(ipt) {
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
                'kode_unit': $(div).find('.unit').select2().val(),
                'nomor': $(div).find('.select_peternak').val(),
            	'perusahaan': $(div).find('#select_perusahaan').val(),
                'start_date': dateSQL($(div).find('#start_date_ts').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_ts').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranPeternak/get_data_rhpp',
                data : { 'params': params },
                type : 'post',
                dataType : 'json',
                beforeSend : function(){ showLoading() },
                success : function(data){
                    $(dcontent).html( data.html );
                    hideLoading();

                    $(div).find('.unit').next('span.select2').css('width', '100%');

                    $('.check').change(function() {
                        var target = $(this).attr('target');
            
                        var length = $('.check[target='+target+']').length;
                        var length_checked = $('.check[target='+target+']:checked').length;
            
                        if ( length == length_checked ) {
                            $('.check_all').prop( 'checked', true );
                        } else {
                            $('.check_all').prop( 'checked', false );
                        }
            
                        kpp.hit_total_pilih(this);
                    });
                },
            });
        }
    }, // end - get_data_rhpp

    hit_total_pilih: function(elm) {
    	var table = $(elm).closest('table');
    	var tbody = $(table).find('tbody');
    	var thead = $(table).find('thead');

    	var total = 0;
    	$.map( $(tbody).find('tr'), function(tr) {
    		var checkbox = $(tr).find('input[type=checkbox]');

    		if ( $(checkbox).prop('checked') ) {
    			var _total = parseInt($(tr).find('td.total').attr('data-val'));

    			total += _total;
    		}
    	});

    	$(thead).find('td.total b').html( numeral.formatDec(total) );
    }, // end - hit_total_pilih

    submit: function(elm) {
        var div = $('div#transaksi');

        var id = $(elm).data('id');

        var supplier = $(div).find('.select_peternak').val();
        var perusahaan = $(div).find('#select_perusahaan').val();

        var detail = [];
        $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
            if ( $(ipt).prop('checked') ) {
                var tr = $(ipt).closest('tr');

                var list_tgldocin = $(tr).find('td.tgl_docin').attr('data-val').split('<br>');
                var list_noreg = $(tr).find('td.noreg').attr('data-val').split('<br>');
                var list_kandang = $(tr).find('td.kandang').attr('data-val').split('<br>');
                var list_populasi = $(tr).find('td.populasi').attr('data-val').split('<br>');

                var detail2 = [];
                for (var i = 0; i < list_noreg.length; i++) {
                    var _detail2 = {
                        'tgl_docin': list_tgldocin[i],
                        'noreg': list_noreg[i],
                        'kandang': list_kandang[i],
                        'populasi': list_populasi[i]
                    };

                    detail2.push( _detail2 );
                }

                var _detail = {
                    'tipe_rhpp': $(tr).find('td.jenis').text(),
                    'sub_total': $(tr).find('td.total').attr('data-val'),
                    'detail2': detail2
                };

                detail.push( _detail );
            }
        });

        if ( detail.length == 0 ) {
            bootbox.alert('Tidak ada data yang akan anda submit.');
        } else {
            var params = {
                'id': id,
                'mitra': supplier,
                'perusahaan': perusahaan,
                'detail': detail
            };

            $.get('pembayaran/KonfirmasiPembayaranPeternak/konfirmasi_pembayaran',{
                'params': params
            },function(data){
                var _options = {
                    className : 'veryWidth',
                    message : data,
                    size : 'large',
                };
                bootbox.dialog(_options).bind('shown.bs.modal', function(){
                    var modal_dialog = $(this).find('.modal-dialog');
                    var modal_body = $(this).find('.modal-body');

                    $(modal_dialog).css({'max-width' : '35%'});
                    $(modal_dialog).css({'width' : '35%'});

                    var modal_header = $(this).find('.modal-header');
                    $(modal_header).css({'padding-top' : '0px'});

                    var tgl_bayar = $(modal_body).find('#tgl_bayar').data('val');
                    $(modal_body).find('#tgl_bayar').datetimepicker({
                        locale: 'id',
                        format: 'DD MMM Y'
                    });

                    if ( !empty(tgl_bayar) ) {
                        // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment(new Date(tgl_bayar)));
                        $(modal_body).find('#tgl_bayar').data("DateTimePicker").date(new Date(tgl_bayar));
                    } else {
                        // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment());
                    }

                    $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                        $(this).priceFormat(Config[$(this).data('tipe')]);
                    });
                });
            },'html');
        }
    }, // end - submit

    save: function() {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
                if ( result ) {
                    var detail = [];
                    $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
                        if ( $(ipt).prop('checked') ) {
                            var tr = $(ipt).closest('tr');

                            var list_tgldocin = $(tr).find('td.tgl_docin').attr('data-val').split('<br>');
                            var list_noreg = $(tr).find('td.noreg').attr('data-val').split('<br>');
                            var list_kandang = $(tr).find('td.kandang').attr('data-val').split('<br>');
                            var list_populasi = $(tr).find('td.populasi').attr('data-val').split('<br>');

                            var detail2 = [];
                            for (var i = 0; i < list_noreg.length; i++) {
                                var _detail2 = {
                                    'tgl_docin': list_tgldocin[i],
                                    'noreg': list_noreg[i],
                                    'kandang': list_kandang[i],
                                    'populasi': list_populasi[i]
                                };

                                detail2.push( _detail2 );
                            }

                            var _detail = {
                                'id_trans': $(tr).attr('data-id'),
                                'tipe_rhpp': $(tr).find('td.jenis').text(),
                                'invoice': $(tr).find('td.invoice').attr('data-val'),
                                'sub_total': $(tr).find('td.total').attr('data-val'),
                                'detail2': detail2
                            };

                            detail.push( _detail );
                        }
                    });

                    var _rekening = $(modal_body).find('.rekening').val().split(' - ');

                    var params = {
                        'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                        'periode_docin': $(modal_body).find('div.periode_docin').text(),
                        'mitra': $(modal_body).find('div.mitra').data('val'),
                        'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                        'rekening': _rekening[0],
                        'total': $(modal_body).find('div.total').attr('data-val'),
                        'detail': detail
                    };

                    formData.append("data", JSON.stringify(params));

                    $.ajax({
                        url : 'pembayaran/KonfirmasiPembayaranPeternak/save',
                        dataType: 'json',
                        type: 'post',
                        async:false,
                        processData: false,
                        contentType: false,
                        data: formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                                    kpp.load_form($(btn), null, 'transaksi');

                                    bootbox.hideAll();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - save

    edit: function(elm) {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

        var id = $(elm).data('id');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data pembayaran ?', function(result) {
                if ( result ) {
                    var detail = [];
                    $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
                        if ( $(ipt).prop('checked') ) {
                            var tr = $(ipt).closest('tr');

                            var list_tgldocin = $(tr).find('td.tgl_docin').attr('data-val').split('<br>');
                            var list_noreg = $(tr).find('td.noreg').attr('data-val').split('<br>');
                            var list_kandang = $(tr).find('td.kandang').attr('data-val').split('<br>');
                            var list_populasi = $(tr).find('td.populasi').attr('data-val').split('<br>');

                            var detail2 = [];
                            for (var i = 0; i < list_noreg.length; i++) {
                                var _detail2 = {
                                    'tgl_docin': list_tgldocin[i],
                                    'noreg': list_noreg[i],
                                    'kandang': list_kandang[i],
                                    'populasi': list_populasi[i]
                                };

                                detail2.push( _detail2 );
                            }

                            var _detail = {
                                'id_trans': $(tr).attr('data-id'),
                                'tipe_rhpp': $(tr).find('td.jenis').text(),
                                'sub_total': $(tr).find('td.total').attr('data-val'),
                                'detail2': detail2
                            };

                            detail.push( _detail );
                        }
                    });

                    var _rekening = $(modal_body).find('.rekening').val().split(' - ');

                    var params = {
                        'id': $(elm).data('id'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                        'periode_docin': $(modal_body).find('div.periode_docin').text(),
                        'mitra': $(modal_body).find('div.mitra').data('val'),
                        'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                        'rekening': _rekening[0],
                        'total': $(modal_body).find('div.total').attr('data-val'),
                        'detail': detail
                    };

                    formData.append("data", JSON.stringify(params));

                    $.ajax({
                        url : 'pembayaran/KonfirmasiPembayaranPeternak/edit',
                        dataType: 'json',
                        type: 'post',
                        async:false,
                        processData: false,
                        contentType: false,
                        data: formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                                    kpp.load_form($(btn), null, 'transaksi');

                                    bootbox.hideAll();
                                });
                            } else {
                                bootbox.alert(data.message);
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
                var params = {
                    'id': $(elm).data('id')
                };

                $.ajax({
                    url : 'pembayaran/KonfirmasiPembayaranPeternak/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                var btn = '<button type="button" data-href="transaksi"></button>';
                                kpp.load_form($(btn), null, 'transaksi');
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete
};

kpp.start_up();