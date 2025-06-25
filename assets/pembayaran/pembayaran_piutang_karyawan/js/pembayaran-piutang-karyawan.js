var ppk = {
    startUp: function() {
        ppk.settingUp();
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

        $(div_action).find('select.piutang_kode').select2();
        $(div_action).find('select.karyawan').select2().on('select2:select', function (e) {
            ppk.getKodePiutang();
        });
        $(div_action).find('select.perusahaan').select2().on('select2:select', function (e) {
            ppk.getKodePiutang();
        });
        $(div_action).find('select.jns_bayar').select2();

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

        ppk.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'id': id
        };

        $.ajax({
            url : 'pembayaran/PembayaranPiutangKaryawan/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                ppk.settingUp();

                if ( !empty(edit) ) {
                    ppk.getKodePiutang();
                }
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
                url :'pembayaran/PembayaranPiutangKaryawan/getLists',
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

    getKodePiutang: function () {
        var div_action = $('div#action');

        var val_karyawan = $(div_action).find('select.karyawan').select2().val();
        var val_perusahaan = $(div_action).find('select.perusahaan').select2().val();

        var option = '<option value="">-- Pilih Kode Hutang --</option>';

        if ( !empty(val_karyawan) && !empty(val_perusahaan) ) {
            var piutang_kode = $(div_action).find('select.piutang_kode').attr('data-kode');
            var sisa_piutang = $(div_action).find('select.piutang_kode').attr('data-sisapiutang');

            var params = {
                'karyawan': val_karyawan,
                'perusahaan': val_perusahaan,
                'piutang_kode': piutang_kode
            };

            $.ajax({
                url :'pembayaran/PembayaranPiutangKaryawan/getKodePiutang',
                dataType: 'json',
				type: 'post',
                data : {
                    'params': params
                },
                beforeSend : function(){ showLoading('Ambil data hutang . . .'); },
                success : function(data){
                    hideLoading();
                    if ( data.status == 1 ) {
                        if ( data.content.length > 0 ) {
                            for (let i = 0; i < data.content.length; i++) {
                                var selected = null;
                                var _sisa_piutang = data.content[i].sisa_piutang;
                                if ( piutang_kode == data.content[i].kode ) {
                                    selected = 'selected';
                                    _sisa_piutang = sisa_piutang;
                                }

                                option += '<option value="'+data.content[i].kode+'" data-nominal="'+_sisa_piutang+'" '+selected+'>'+data.content[i].tanggal.replaceAll('-', '/')+' | '+data.content[i].kode+'</option>';
                            }
                        }

                        $(div_action).find('select.piutang_kode').removeAttr('disabled');
                        $(div_action).find('select.piutang_kode').html( option );
                        $(div_action).find('select.piutang_kode').select2().on('select2:select', function (e) {
                            var nominal = e.params.data.element.dataset.nominal;

                            $(div_action).find('input.sisa_piutang').val( numeral.formatDec( nominal ) );
                        });
                    } else {
                        bootbox.alert(data.message, function () {
                            $(div_action).find('select.piutang_kode').attr('disabled', 'disabled');
                            $(div_action).find('select.piutang_kode').html( option );
                            $(div_action).find('select.piutang_kode').select2();
                            $(div_action).find('input.sisa_piutang').val( numeral.formatDec(0) );
                        });
                    }
                },
            });
        } else {
            $(div_action).find('select.piutang_kode').attr('disabled', 'disabled');
            $(div_action).find('select.piutang_kode').html( option );
            $(div_action).find('select.piutang_kode').select2();
            $(div_action).find('input.sisa_piutang').val( numeral.formatDec(0) );
        }
    }, // end - getKodePiutang

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
                        'karyawan': $(dcontent).find('.karyawan').select2('val'),
                        'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
                        'piutang_kode': $(dcontent).find('.piutang_kode').select2('val'),
                        'sisa_piutang': numeral.unformat($(dcontent).find('.sisa_piutang').val()),
                        'nominal': numeral.unformat($(dcontent).find('.nominal').val()),
                        'jns_bayar': $(dcontent).find('.jns_bayar').select2('val'),
                        'keterangan': $(dcontent).find('.keterangan').val(),
                    };

                    var formData = new FormData();

                    if ( !empty($('.file_lampiran').val()) ) {
                        var _file = $('.file_lampiran').get(0).files[0];
                        formData.append('file', _file);
                    }
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url :'pembayaran/PembayaranPiutangKaryawan/save',
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
                                    ppk.loadForm(data.content.id, null, 'action');
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
                        'karyawan': $(dcontent).find('.karyawan').select2('val'),
                        'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
                        'piutang_kode': $(dcontent).find('.piutang_kode').select2('val'),
                        'sisa_piutang': numeral.unformat($(dcontent).find('.sisa_piutang').val()),
                        'nominal': numeral.unformat($(dcontent).find('.nominal').val()),
                        'jns_bayar': $(dcontent).find('.jns_bayar').select2('val'),
                        'keterangan': $(dcontent).find('.keterangan').val(),
                    };

                    var formData = new FormData();

                    if ( !empty($('.file_lampiran').val()) ) {
                        var _file = $('.file_lampiran').get(0).files[0];
                        formData.append('file', _file);
                    }
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url :'pembayaran/PembayaranPiutangKaryawan/edit',
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
                                    ppk.loadForm(data.content.id, null, 'action');
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
                    url :'pembayaran/PembayaranPiutangKaryawan/delete',
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
                                ppk.loadForm(null, null, 'action');
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

ppk.startUp();