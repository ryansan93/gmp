var kpd = {
	start_up: function () {
		kpd.setting_up();
	}, // end - start_up

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

            kpd.hit_total_pilih(this);
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

            kpd.hit_total_pilih(this);
        });

		// $('div#transaksi').find('#select_unit').selectpicker();
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

        $('select.supplier').select2({placeholder: 'Pilih Supplier'}).on("select2:select", function (e) {
            var supplier = $('select.supplier').select2().val();

            for (var i = 0; i < supplier.length; i++) {
                if ( supplier[i] == 'all' ) {
                    $('select.supplier').select2().val('all').trigger('change');

                    i = supplier.length;
                }
            }

            $('select.supplier').next('span.select2').css('width', '100%');
        });
        $('select.supplier').next('span.select2').css('width', '100%');

        $('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('select.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('select.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('select.perusahaan').next('span.select2').css('width', '100%');
        });
        $('select.perusahaan').next('span.select2').css('width', '100%');

        $('#select_supplier').selectpicker();
        $('#select_perusahaan').selectpicker();

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

        kpd.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'pembayaran/KonfirmasiPembayaranDoc/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                kpd.setting_up(href, 'div#'+href);
            },
        });
    }, // end - load_form

    get_lists: function() {
        var div = $('div#riwayat');
        let dcontent = $(div).find('table.tbl_riwayat tbody');

        var err = 0;
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
                'supplier': $(div).find('.supplier').select2().val(),
                'perusahaan': $(div).find('.perusahaan').select2().val(),
                'start_date': dateSQL($(div).find('#start_date_bayar').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_bayar').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranDoc/get_lists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    hideLoading();

                    $(div).find('.supplier').next('span.select2').css('width', '100%');
                    $(div).find('.perusahaan').next('span.select2').css('width', '100%');
                },
            });
        }
    }, // end - get_lists

	get_data_doc: function() {
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
                'supplier': $(div).find('#select_supplier').val(),
            	'perusahaan': $(div).find('#select_perusahaan').val(),
                'start_date': dateSQL($(div).find('#start_date_order').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_order').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranDoc/get_data_doc',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
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
            
                        kpd.hit_total_pilih(this);
                    });
                },
            });
        }
    }, // end - get_data_doc

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

        var jml_supplier = 0;
        var supplier = null;
        var jml_perusahaan = 0;
        var perusahaan = null;

        var detail = [];
        $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
            if ( $(ipt).prop('checked') ) {
                var tr = $(ipt).closest('tr');

                var _detail = {
                    'tgl_order': $(tr).find('td.tgl_order').attr('data-val'),
                    'id_kab_kota': $(tr).find('td.kota_kab').attr('data-val'),
                    'kab_kota': $(tr).find('td.kota_kab').text(),
                    'id_perusahaan': $(tr).find('td.perusahaan').attr('data-val'),
                    'perusahaan': $(tr).find('td.perusahaan').text(),
                    'no_order': $(tr).find('td.no_order').attr('data-val'),
                    'no_peternak': $(tr).find('td.peternak').attr('data-val'),
                    'peternak': $(tr).find('td.peternak').text(),
                    'kandang': $(tr).find('td.kandang').attr('data-val'),
                    'populasi': $(tr).find('td.populasi').attr('data-val'),
                    'harga': $(tr).find('td.harga').attr('data-val'),
                    'total': $(tr).find('td.total').attr('data-val')
                };

                var _supplier = $(tr).attr('data-supplier');
                if ( supplier != _supplier ) {
                    supplier = _supplier;
                    jml_supplier++;
                }

                var _perusahaan = $(tr).find('td.perusahaan').attr('data-val');
                if ( perusahaan != _perusahaan ) {
                    perusahaan = _perusahaan;
                    jml_perusahaan++;
                }

                detail.push( _detail );
            }
        });

        if ( detail.length == 0 ) {
            bootbox.alert('Tidak ada data yang akan anda submit.');
        } else {
            if ( jml_supplier > 1 || jml_perusahaan > 1 ) {
                if ( jml_supplier > 1 && jml_perusahaan > 1 ) {
                    bootbox.alert('Data <b>perusahaan</b> dan <b>supplier</b> yang anda pilih lebih dari 1.');
                } else if ( jml_supplier > 1 ) {
                    bootbox.alert('Data <b>supplier</b> yang anda pilih lebih dari 1.');
                } else if ( jml_perusahaan > 1 ) {
                    bootbox.alert('Data <b>perusahaan</b> yang anda pilih lebih dari 1.');
                }
            } else {
                var params = {
                    'id': id,
                    'supplier': supplier,
                    'perusahaan': perusahaan,
                    'detail': detail
                };

                $.post('pembayaran/KonfirmasiPembayaranDoc/konfirmasi_pembayaran',{
                    'params': params
                },function(data){
                    // console.log( data );

                    var _options = {
                        className : 'veryWidth',
                        message : data.html,
                        size : 'large',
                    };
                    bootbox.dialog(_options).bind('shown.bs.modal', function(){
                        var modal_dialog = $(this).find('.modal-dialog');
                        var modal_body = $(this).find('.modal-body');

                        $(modal_dialog).css({'max-width' : '35%'});
                        $(modal_dialog).css({'width' : '35%'});

                        var modal_header = $(this).find('.modal-header');
                        $(modal_header).css({'padding-top' : '0px'});

                        $(modal_body).find('#tgl_bayar').datetimepicker({
                            locale: 'id',
                            format: 'DD MMM Y'
                        });

                        // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment());

                        $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                            $(this).priceFormat(Config[$(this).data('tipe')]);
                        });
                    });
                },'json');
            }
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

                            var _detail = {
                                'tgl_order': $(tr).find('td.tgl_order').attr('data-val'),
                                'id_kab_kota': $(tr).find('td.kota_kab').attr('data-val'),
                                'kab_kota': $(tr).find('td.kota_kab').text(),
                                'id_perusahaan': $(tr).find('td.perusahaan').attr('data-val'),
                                'perusahaan': $(tr).find('td.perusahaan').text(),
                                'no_order': $(tr).find('td.no_order').attr('data-val'),
                                'no_peternak': $(tr).find('td.peternak').attr('data-val'),
                                'peternak': $(tr).find('td.peternak').text(),
                                'kandang': $(tr).find('td.kandang').attr('data-val'),
                                'populasi': $(tr).find('td.populasi').attr('data-val'),
                                'harga': $(tr).find('td.harga').attr('data-val'),
                                'total': $(tr).find('td.total').attr('data-val')
                            };

                            detail.push( _detail );
                        }
                    });

                    var params = {
                        'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                        'periode_docin': $(modal_body).find('div.periode_docin').text(),
                        'supplier': $(modal_body).find('div.supplier').data('val'),
                        'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                        'rekening': $(modal_body).find('select.rekening').val(),
                        'total': $(modal_body).find('div.total').attr('data-val'),
                        'detail': detail
                    };

                    $.ajax({
                        url : 'pembayaran/KonfirmasiPembayaranDoc/save',
                        data : { 'params': params },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                                    kpd.load_form($(btn), null, 'transaksi');

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

                            var _detail = {
                                'tgl_order': $(tr).find('td.tgl_order').attr('data-val'),
                                'id_kab_kota': $(tr).find('td.kota_kab').attr('data-val'),
                                'kab_kota': $(tr).find('td.kota_kab').text(),
                                'id_perusahaan': $(tr).find('td.perusahaan').attr('data-val'),
                                'perusahaan': $(tr).find('td.perusahaan').text(),
                                'no_order': $(tr).find('td.no_order').attr('data-val'),
                                'no_peternak': $(tr).find('td.peternak').attr('data-val'),
                                'peternak': $(tr).find('td.peternak').text(),
                                'kandang': $(tr).find('td.kandang').attr('data-val'),
                                'populasi': $(tr).find('td.populasi').attr('data-val'),
                                'harga': $(tr).find('td.harga').attr('data-val'),
                                'total': $(tr).find('td.total').attr('data-val')
                            };

                            detail.push( _detail );
                        }
                    });

                    var params = {
                        'id': id,
                        'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                        'periode_docin': $(modal_body).find('div.periode_docin').text(),
                        'supplier': $(modal_body).find('div.supplier').data('val'),
                        'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                        'rekening': $(modal_body).find('select.rekening').val(),
                        'total': $(modal_body).find('div.total').attr('data-val'),
                        'detail': detail
                    };

                    $.ajax({
                        url : 'pembayaran/KonfirmasiPembayaranDoc/edit',
                        data : { 'params': params },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    var btn = '<button type="button" data-href="transaksi" data-id="'+id+'"></button>';
                                    kpd.load_form($(btn), null, 'transaksi');

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
                    url : 'pembayaran/KonfirmasiPembayaranDoc/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                var btn = '<button type="button" data-href="transaksi"></button>';
                                kpd.load_form($(btn), null, 'transaksi');
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

kpd.start_up();