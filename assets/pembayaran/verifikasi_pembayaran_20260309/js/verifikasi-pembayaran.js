var vp = {
    startUp: function () {
        vp.settingUp();
        vp.getDataOutstanding();
    }, // end - startUp

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false, //Important! See issue #1075
            widgetPositioning: {
                horizontal: "auto",
                vertical: "auto"
            }
        });

        $("#startDate").on("dp.change", function (e) {
            $("#startDate").data("DateTimePicker").minDate(e.date);
        });
        $("#endDate").on("dp.change", function (e) {
            $('#endDate').data("DateTimePicker").maxDate(e.date);
        });

        $('select.jenis_transaksi').select2({placeholder: 'Pilih Jenis Transaksi'}).on("select2:select", function (e) {
            var jt = $('select.jenis_transaksi').select2().val();
	
            for (var i = 0; i < jt.length; i++) {
                if ( jt[i] == 'all' ) {
                    $('select.jenis_transaksi').select2().val('all').trigger('change');

                    i = jt.length;
                }
            }

            $('select.jenis_transaksi').next('span.select2').css('width', '100%');
        });
        $('select.jenis_transaksi').next('span.select2').css('width', '100%');

        $('div#outstanding').find('select.bank').select2().on("select2:select", function (e) {
            vp.filterOutstanding();
        });
        $('div#history').find('select.bank').select2();
    }, // end - settingUp

    getDataOutstanding: function() {
        var dcontent = $('#outstanding').find('table tbody');

        $.ajax({
            url : 'pembayaran/VerifikasiPembayaran/getDataOutstanding',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                vp.filterOutstanding();
            },
        });
    }, // end - getDataOutstanding

    filterOutstanding: function() {
        var div = $('div#outstanding');

        var bank = $(div).find('select.bank').select2().val();

        $(div).find('tr.data').addClass('hide');
        if ( bank != 'all' ) {
            $(div).find('tr.data[data-coabank="'+bank+'"]').removeClass('hide');
        } else {
            $(div).find('tr.data').removeClass('hide');
        }
    }, // end - filterOutstanding

    getLists: function() {
        let dcontent = $('table.tbl_riwayat tbody');

        var err = 0;
        $.map( $('[data-required=1]'), function(ipt) {
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
                'start_date': dateSQL($('#startDate').data('DateTimePicker').date()),
                'end_date': dateSQL($('#endDate').data('DateTimePicker').date()),
                'jenis': $('.jenis_transaksi').select2().val(),
                'bank': $('div#history').find('.bank').select2().val()
            };

            $.ajax({
                url : 'pembayaran/VerifikasiPembayaran/getLists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ App.showLoaderInContent(dcontent); },
                success : function(html){
                    App.hideLoaderInContent(dcontent, html);
                },
            });
        }
    }, // end - get_lists

    formDetail: function(elm) {
        var tr = $(elm).closest('tr.data');

        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table'),
            'no_rek': $(tr).attr('data-norek'),
            'atas_nama': $(tr).attr('data-atasnama'),
            'bank': $(tr).attr('data-bank')
        };

        $.get('pembayaran/VerifikasiPembayaran/formDetail',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formDetail

    encryptParams: function(elm) {
        var modal = $(elm).closest('.modal-body');

        var params = {
            'id': $(elm).attr('data-id'),
            'no_rek': $(modal).find('span.norek').text(),
            'atas_nama': $(modal).find('span.atasnama').text(),
            'bank': $(modal).find('span.bank').text()
        };

        $.ajax({
            url: 'pembayaran/VerifikasiPembayaran/encryptParams',
            data: {
                'params': params
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();

                if ( data.status == 1 ) {
                    vp.exportExcel(data.content);
                } else {
                    bootbox.alert( data.message );
                }
            }
        });
	}, // end - encryptParams

	exportExcel : function (params) {
		goToURL('pembayaran/VerifikasiPembayaran/exportExcel/'+params);
	}, // end - exportExcel

    formRealisasiBayar: function(elm) {
        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayar',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayar

    formRealisasiBayarDetail: function(elm) {
        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayarDetail',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayarDetail

    formRealisasiBayarEdit: function(elm) {
        $('.modal').modal('hide');

        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayarEdit',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').attr('data-val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tglBayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayarEdit

    save: function(elm) {
        var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            $(elm).attr('disabled', 'disabled')
            bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
                if ( result ) {
                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tbl_name': $(elm).attr('data-table'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tglBayar').data('DateTimePicker').date()),
                        'no_bukti': $(modal_body).find('.no_bukti').val(),
                        'ket_bayar': $(modal_body).find('.ket_bayar').val()
                    };
        
                    var formData = new FormData();
        
                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));
        
                    $.ajax({
                        url : 'pembayaran/VerifikasiPembayaran/save',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');
        
                                    vp.getDataOutstanding();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
                } else {
                    $(elm).removeAttr('disabled', 'disabled')
                }
            });
        }
    }, // end - save

    edit: function(elm) {
        var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            $(elm).attr('disabled', 'disabled')
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data pembayaran ?', function(result) {
                if ( result ) {
                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tbl_name': $(elm).attr('data-table'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tglBayar').data('DateTimePicker').date()),
                        'no_bukti': $(modal_body).find('.no_bukti').val(),
                        'ket_bayar': $(modal_body).find('.ket_bayar').val()
                    };
        
                    var formData = new FormData();
        
                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));
        
                    $.ajax({
                        url : 'pembayaran/VerifikasiPembayaran/edit',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');

                                    vp.getLists();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
                } else {
                    $(elm).removeAttr('disabled', 'disabled')
                }
            });
        }
    }, // end - edit

    delete: function(elm) {
        bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-id'),
                    'tbl_name': $(elm).attr('data-table'),
                };
    
                $.ajax({
                    url : 'pembayaran/VerifikasiPembayaran/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                $('.modal').modal('hide');

                                vp.getLists();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - delete

    printPreview: function (elm) {
        var id = $(elm).attr('data-id');

        window.open('pembayaran/VerifikasiPembayaran/printPreview/'+id, 'blank');
    }, // end - printPreview
};

vp.startUp();