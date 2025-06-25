var kpoap = {
	startUp: function () {
		kpoap.settingUp();
	}, // end - startUp

	settingUp: function() {
        $('.filter').select2();
		$('.jenis_kirim').select2();
		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
			var unit = $(this).select2('val');

			for (var i = 0; i < unit.length; i++) {
				if ( unit[i] == 'all' ) {
					$('.unit').select2().val('all').trigger('change');

					i = unit.length;
				}
			}
		});

        $('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $(this).select2('val');

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('select.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }
        });

        $('select.ekspedisi').select2({placeholder: 'Pilih Ekspedisi'}).on("select2:select", function (e) {
            var ekspedisi = $(this).select2('val');

            for (var i = 0; i < ekspedisi.length; i++) {
                if ( ekspedisi[i] == 'all' ) {
                    $('select.ekspedisi').select2().val('all').trigger('change');

                    i = ekspedisi.length;
                }
            }
        });

        $('#select_perusahaan').select2();
        $('#select_ekspedisi').select2();

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

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');

            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });

        $('.checkAll').change(function() {
            var data_target = $(this).data('target');

            if ( this.checked ) {
                $.map( $('.checkSelf[target='+data_target+']'), function(checkbox) {
                    $(checkbox).prop( 'checked', true );
                });
            } else {
                $.map( $('.checkSelf[target='+data_target+']'), function(checkbox) {
                    $(checkbox).prop( 'checked', false );
                });
            }

            kpoap.hit_total_pilih();
        });

        $('.checkSelf').change(function() {
            var target = $(this).attr('target');

            var length = $('.checkSelf[target='+target+']').length;
            var length_checked = $('.checkSelf[target='+target+']:checked').length;

            if ( length == length_checked ) {
                $('.checkAll').prop( 'checked', true );
            } else {
                $('.checkAll').prop( 'checked', false );
            }

            kpoap.hit_total_pilih();
        });
	}, // end - settingUp

    hitGrandTotal: function(elm) {
        var form = $(elm).closest('form');

        var biaya_materai = numeral.unformat($(elm).val());

        $(form).find('div.biaya_materai').attr('data-val', biaya_materai);
        $(form).find('div.biaya_materai h4 b').text( numeral.formatDec(biaya_materai) );

        var sub_total = $(form).find('div.sub_total').attr('data-val');
        var potongan_pph_23 = $(form).find('div.potongan_pph_23').attr('data-val');

        var grand_total = sub_total - biaya_materai - potongan_pph_23;

        $(form).find('div.total').attr('data-val', grand_total);
        $(form).find('div.total h4 b').text( numeral.formatDec(grand_total) );
    }, // end - hitGrandTotal

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

        kpoap.loadForm($(elm), edit, href);
    }, // end - changeTabActive

    loadForm: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'pembayaran/KonfirmasiPembayaranOaPakan/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                kpoap.settingUp();
            },
        });
    }, // end - loadForm

    getLists: function() {
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
                'perusahaan': $(div).find('.perusahaan').select2().val(),
                'ekspedisi': $(div).find('.ekspedisi').select2().val(),
                'unit': $(div).find('.unit').select2().val(),
                'start_date': dateSQL($(div).find('#start_date_bayar').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_bayar').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranOaPakan/getLists',
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
    }, // end - getLists

    getDataOa: function() {
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
                'kode_unit': $(div).find('.unit').select2('val'),
                'perusahaan': $(div).find('#select_perusahaan').select2('val'),
                'ekspedisi': $(div).find('#select_ekspedisi').select2('val'),
                'filter': $(div).find('.filter').select2('val'),
                'jenis_kirim': $(div).find('.jenis_kirim').select2('val'),
                'start_date': dateSQL($(div).find('#start_date_order').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_order').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/KonfirmasiPembayaranOaPakan/getDataOa',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    $(div).find('.unit').next('span.select2').css('width', '100%');

                    kpoap.settingUp();

                    hideLoading();

                },
            });
        }
    }, // end - getDataOa

    hit_total_pilih: function() {
        var table = $('table.tbl_list');
        var tbody = $(table).find('tbody');
        var thead = $(table).find('thead');

        var total = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            var checkbox = $(tr).find('input[type=checkbox]');

            if ( $(checkbox).prop('checked') ) {
                var _total = parseFloat($(tr).find('td.sub_total').attr('data-val'));

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
        var ekspedisi = null;

        if ( $(div).find('tbody input[type=checkbox]:checked').length == 0 ) {
            bootbox.alert('Tidak ada data yang akan anda submit.');
        } else {
            var tr_first = $(div).find('tbody input[type=checkbox]:checked:first').closest('tr');
            var tr_last = $(div).find('tbody input[type=checkbox]:checked:last').closest('tr');

            var params = {
                'id': id,
                'perusahaan': $(div).find('select#select_perusahaan').select2('val'),
                'ekspedisi': $(div).find('select#select_ekspedisi option:selected').text(),
                'ekspedisi_id': $(div).find('select#select_ekspedisi').select2('val'),
                'total': numeral.unformat( $(div).find('td.total b').html() ),
                'first_date': $(tr_first).find('td.tgl_mutasi').attr('data-val'),
                'last_date': $(tr_last).find('td.tgl_mutasi').attr('data-val')
            };

            $.get('pembayaran/KonfirmasiPembayaranOaPakan/konfirmasiPembayaran',{
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

                    $(modal_body).find('#tgl_bayar').datetimepicker({
                        locale: 'id',
                        format: 'DD MMM Y'
                    });

                    // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment());

                    $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                        $(this).priceFormat(Config[$(this).data('tipe')]);
                    });

                    $.map( $(modal_body).find('.date'), function(ipt) {
                        var tgl = $(ipt).find('input').data('tgl');

                        if ( !empty(tgl) ) {
                            $(ipt).data("DateTimePicker").date(new Date(tgl));
                        }
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
            var _file = $('.file_lampiran').get(0).files[0];

            if ( !_file ) {
                bootbox.alert('Harap isi lampiran terlebih dahulu.');
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
                    if ( result ) {
                        var detail = [];
                        $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
                            if ( $(ipt).prop('checked') ) {
                                var tr = $(ipt).closest('tr');

                                var _detail = {
                                    'tgl_mutasi': $(tr).find('td.tgl_mutasi').attr('data-val'),
                                    'no_sj': $(tr).find('td.no_sj').attr('data-val'),
                                    'ekspedisi': $(tr).find('td.ekspedisi').attr('data-val'),
                                    'no_polisi': $(tr).find('td.no_polisi').attr('data-val'),
                                    'sub_total': $(tr).find('td.sub_total').attr('data-val')
                                };

                                detail.push( _detail );
                            }
                        });

                        var params = {
                            'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                            'periode_mutasi': $(modal_body).find('div.periode_mutasi').text(),
                            'ekspedisi': $(modal_body).find('div.ekspedisi').data('val'),
                            'ekspedisi_id': $(modal_body).find('div.ekspedisi').data('id'),
                            'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                            'bank': $(modal_body).find('.bank option:selected').attr('data-bank'),
                            'rekening': $(modal_body).find('.bank option:selected').attr('data-norek'),
                            'sub_total': $(modal_body).find('div.sub_total').attr('data-val'),
                            'potongan_pph_23': $(modal_body).find('div.potongan_pph_23').attr('data-val'),
                            'biaya_materai': $(modal_body).find('div.biaya_materai').attr('data-val'),
                            'total': $(modal_body).find('div.total').attr('data-val'),
                            'invoice': $(modal_body).find('.invoice').val(),
                            'detail': detail
                        };

                        var formData = new FormData();

                        var _file = $('.file_lampiran').get(0).files[0];
                        formData.append('files', _file);
                        formData.append('params', JSON.stringify(params));

                        $.ajax({
                            url : 'pembayaran/KonfirmasiPembayaranOaPakan/save',
                            type : 'POST',
                            // dataType : 'JSON',
                            data : formData,
                            contentType : false,
                            processData : false,
                            beforeSend : function(){ showLoading() },
                            success : function(data){
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert(data.message, function() {
                                        var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                                        kpoap.loadForm($(btn), null, 'transaksi');

                                        bootbox.hideAll();
                                        // location.reload();
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                }
                            },
                        });
                    }
                });
            }
        }
    }, // end - save

    edit: function(elm) {
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
                                'tgl_mutasi': $(tr).find('td.tgl_mutasi').attr('data-val'),
                                'no_sj': $(tr).find('td.no_sj').attr('data-val'),
                                'ekspedisi': $(tr).find('td.ekspedisi').attr('data-val'),
                                'no_polisi': $(tr).find('td.no_polisi').attr('data-val'),
                                'sub_total': $(tr).find('td.sub_total').attr('data-val')
                            };

                            detail.push( _detail );
                        }
                    });

                    var params = {
                        'id': $(elm).attr('data-id'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
                        'periode_mutasi': $(modal_body).find('div.periode_mutasi').text(),
                        'ekspedisi': $(modal_body).find('div.ekspedisi').data('val'),
                        'ekspedisi_id': $(modal_body).find('div.ekspedisi').data('id'),
                        'perusahaan': $(modal_body).find('div.perusahaan').data('val'),
                        'bank': $(modal_body).find('.bank option:selected').attr('data-bank'),
                        'rekening': $(modal_body).find('.bank option:selected').attr('data-norek'),
                        'sub_total': $(modal_body).find('div.sub_total').attr('data-val'),
                        'potongan_pph_23': $(modal_body).find('div.potongan_pph_23').attr('data-val'),
                        'biaya_materai': $(modal_body).find('div.biaya_materai').attr('data-val'),
                        'total': $(modal_body).find('div.total').attr('data-val'),
                        'invoice': $(modal_body).find('.invoice').val(),
                        'lampiran_old': $(modal_body).find('.file_lampiran').attr('data-old'),
                        'detail': detail
                    };

                    var formData = new FormData();

                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('params', JSON.stringify(params));

                    $.ajax({
                        url : 'pembayaran/KonfirmasiPembayaranOaPakan/edit',
                        type : 'POST',
                        // dataType : 'JSON',
                        data : formData,
                        contentType : false,
                        processData : false,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();

                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                                    kpoap.loadForm($(btn), null, 'transaksi');

                                    bootbox.hideAll();
                                    // location.reload();
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
                    url : 'pembayaran/KonfirmasiPembayaranOaPakan/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                var btn = '<button type="button" data-href="transaksi"></button>';
                                kpoap.loadForm($(btn), null, 'transaksi');
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete
}

kpoap.startUp();