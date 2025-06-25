var pm = {
    startUp: function() {
        pm.settingUp();
    }, // end - startUp

    settingUp: function() {
        var div_riwayat = $('div#riwayat');
        var div_action = $('div#action');

        $(div_riwayat).find('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $.map( $(div_riwayat).find('.date'), function(div) {
            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).data('DateTimePicker').date(new Date(tgl));
            }
        });

        $(div_action).find('select.mitra').select2();
        $(div_action).find('select.perusahaan').select2();
        $(div_action).find('select.tf_bank').select2();

        $(div_action).find('#Tanggal').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $.map( $(div_action).find('#Tanggal'), function(div) {
            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).data('DateTimePicker').date(new Date(tgl));
            }
        });

        $(div_action).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});
    }, // end - settingUp

    changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        var id = $(elm).data('id');

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

        pm.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'id': id
        };

        $.ajax({
            url : 'transaksi/PiutangMitra/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                pm.settingUp();
            },
        });
    }, // end - loadForm

    getLists: function() {
        var dcontent = $('div#riwayat');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            var data = {
                'start_date': dateSQL($(dcontent).find('#StartDate').data('DateTimePicker').date()),
                'end_date': dateSQL($(dcontent).find('#EndDate').data('DateTimePicker').date())
            };

            var dtbody = $(dcontent).find('table tbody');

            $.ajax({
                url :'transaksi/PiutangMitra/getLists',
                dataType: 'html',
				type: 'get',
                data : {
                    'params': data
                },
                beforeSend : function(){ App.showLoaderInContent(dtbody); },
                success : function(html){
                    App.hideLoaderInContent(dtbody, html);
                },
            });
        }
    }, // end - getLists

    save: function() {
        var dcontent = $('div#action');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    var data = {
                        'tanggal': dateSQL($(dcontent).find('#Tanggal').data('DateTimePicker').date()),
                        'mitra': $(dcontent).find('.mitra').select2('val'),
                        'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
                        'nominal': numeral.unformat($(dcontent).find('.nominal').val()),
                        'keterangan': $(dcontent).find('.keterangan').val(),
                        'tf_bank': $(dcontent).find('.tf_bank').select2('val'),
                    };

                    var formData = new FormData();

                    if ( !empty($('.file_lampiran').val()) ) {
                        var _file = $('.file_lampiran').get(0).files[0];
                        formData.append('file', _file);
                    }
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url :'transaksi/PiutangMitra/save',
                        type : 'POST',
                        data : formData,
                        contentType : false,
                        processData : false,
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    pm.loadForm(data.content.id, null, 'action');
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
        var dcontent = $('div#action');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
                if ( result ) {
                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tanggal': dateSQL($(dcontent).find('#Tanggal').data('DateTimePicker').date()),
                        'mitra': $(dcontent).find('.mitra').select2('val'),
                        'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
                        'nominal': numeral.unformat($(dcontent).find('.nominal').val()),
                        'keterangan': $(dcontent).find('.keterangan').val(),
                        'tf_bank': $(dcontent).find('.tf_bank').select2('val'),
                    };

                    var formData = new FormData();

                    if ( !empty($('.file_lampiran').val()) ) {
                        var _file = $('.file_lampiran').get(0).files[0];
                        formData.append('file', _file);
                    }
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url :'transaksi/PiutangMitra/edit',
                        type : 'POST',
                        data : formData,
                        contentType : false,
                        processData : false,
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    pm.loadForm(data.content.id, null, 'action');
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
        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if ( result ) {
                var data = {
                    'id': $(elm).attr('data-id')
                };

                $.ajax({
                    url :'transaksi/PiutangMitra/delete',
                    type : 'POST',
                    dataType : 'JSON',
                    data : {
                        'params': data
                    },
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function(){
                                pm.loadForm(null, null, 'action');
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete

    pindahPerusahaanForm: function( elm ) {
        $.get('transaksi/PiutangMitra/pindahPerusahaanForm',{
            'id': $(elm).attr('data-id')
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

                // var tgl_bayar = $(modal_body).find('#tgl_bayar').data('val');
                // $(modal_body).find('#tgl_bayar').datetimepicker({
                //     locale: 'id',
                //     format: 'DD MMM Y'
                // });

                // if ( !empty(tgl_bayar) ) {
                //     // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment(new Date(tgl_bayar)));
                //     $(modal_body).find('#tgl_bayar').data("DateTimePicker").date(new Date(tgl_bayar));
                // } else {
                //     // $(modal_body).find('#tgl_bayar').data("DateTimePicker").minDate(moment());
                // }

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
    }, // end - pindahPerusahaan
};

pm.startUp();