;var data_kec = {};

var basttb = {
    start_up: function() {
        $('#tgl_terima').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $('#periode').datetimepicker({
            locale: 'id',
            viewMode: 'years',
            format: 'MMM Y',
            useCurrent: false,
            allowInputToggle: true,
        });

        basttb.getLists();
    }, // end - start_up

    getLists : function(keyword = null){
        $.ajax({
            url : 'transaksi/BASTTB/getLists',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                $('div#riwayat').find('div.data').html(data);
                hideLoading();
            }
        });
    }, // end - getLists

    getNoreg : function(elm, resubmit = null) {
        var _date = dateSQL($('#periode').data('DateTimePicker').date()).substr(0, 7);

        if ( _date != 'Invalid' ) {
            var date = _date + '-01';
            $.ajax({
                url: 'transaksi/BASTTB/getNoreg',
                data: {
                    'periode': date
                },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function() {
                    showLoading();
                },
                success: function(data) {
                    var body = $(elm).closest('div.panel-body');
                    var sel_noreg = $(body).find('select.noreg');

                    var option = '';
                    if ( data.status == 1 ) {
                        option += '<option value="">-- Pilih Noreg --</option>';
                        for (var i = 0; i < data.content.length; i++) {
                            option += '<option value='+ data.content[i].noreg +' data-mitra="'+ data.content[i].mitra +'" data-idrdim="'+ data.content[i].id +'">'+ data.content[i].noreg +'</option>';
                        };

                        sel_noreg.html(option);

                        if ( resubmit == 'edit' ) {
                            var noreg = $(sel_noreg).data('noreg');
                            $.map( $(sel_noreg).find('option'), function(opt) {
                                if ( $(opt).val() == noreg ) {
                                    $(opt).attr('selected', 'selected');

                                    basttb.setNamaMitra(sel_noreg);
                                };
                            });
                        };
                    } else {
                        option += '<option value="">-- Pilih Noreg --</option>';
                        sel_noreg.html(option);
                    };

                    hideLoading();
                },
            });
        };
    }, // end - getNoreg

    setNamaMitra : function(elm) {
        var body = $(elm).closest('div.panel-body');
        var input_mitra = $(body).find('input.mitra');

        var sel_noreg = $(elm).val();

        var nama_mitra = $(elm).find('option[value='+ sel_noreg +']').data('mitra');

        $(input_mitra).val(nama_mitra);
    }, // end - setNamaMitra

    hitungAll : function() {
        var s_em = $('[data-tipe=month]').MonthPicker('GetSelectedDate');
        var e_em = dateSQL(moment(s_em).startOf('month'));

        var periode_rdim = e_em;
        var noreg = $('select.noreg').val();
        var mitra = $('input.mitra').val();
        var tgl_terima = dateSQL($('input#tgl_terima').datepicker('getDate'));
        var no_sj = $('input.nosj').val();
        var ket_sj = $('textarea.ketsj').val();

        var sj_box = numeral.unformat( $('input.sj_box').val() );
        var sj_ekor = numeral.unformat( $('input.sj_ekor').val() );
        var terima_box = numeral.unformat( $('input.terima_box').val() );
        var terima_ekor = numeral.unformat( $('input.terima_ekor').val() );
        var terima_mati = numeral.unformat( $('input.terima_mati').val() );
        var terima_afkir = numeral.unformat( $('input.terima_afkir').val() );

        var z = terima_mati - ((terima_box * 100) * 0.02);

        var stok_awal = 0;

        if ( terima_ekor > 100 ) {
            if ( z > 0 ) {
                stok_awal = (terima_box * 100) - z;
            } else {
                stok_awal = (terima_box * 100);
            };
        } else {
            stok_awal = (terima_box * terima_ekor) - terima_mati;
        };

        $('input.terima_awal').val( numeral.formatInt(stok_awal) );

        var selisih_ekor = sj_ekor - terima_ekor;
        $('input.selisih_ekor').val( numeral.formatInt(Math.abs(selisih_ekor)) );

        $('input.selisih_persen').val( numeral.formatDec((selisih_ekor / sj_ekor) * 100) );
    }, // end - hitungAll

    changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var resubmit = $(elm).data('resubmit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

        if ( vhref == 'action' ) {
            var v_id = $(elm).attr('data-id');

            basttb.load_form(v_id, resubmit, elm);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'transaksi/BASTTB/loadContent_BASTTB',
            data : {
                'id' :  v_id,
                'resubmit' :  resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);
                App.formatNumber();

                $('#tgl_terima').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $('#periode').datetimepicker({
                    locale: 'id',
                    viewMode: 'years',
                    format: 'MMM Y',
                    useCurrent: false,
                    allowInputToggle: true,
                });

                if ( resubmit == 'edit' ) {
                    var elm = $('#periode');
                    basttb.getNoreg(elm, resubmit);

                    basttb.hitungAll();
                };
            },
        });
    }, // end - load_form

    save : function(elm){
        var err = 0;
        var div = $('div.attachement');

        $.map( $('[data-required=1]'), function(obj) {
            if ( empty($(obj).val()) ) {
                $(obj).parent().addClass('has-error');
                err++;
            } else {
                $(obj).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            var data_params = {};

            var s_em = $('[data-tipe=month]').MonthPicker('GetSelectedDate');
            var e_em = dateSQL(moment(s_em).startOf('month'));

            var periode_rdim = e_em;
            var noreg = $('select.noreg').val();
            var id_rdim = $('select.noreg').find('option[value='+ noreg +']').data('idrdim');
            var mitra = $('input.mitra').val();
            var tgl_terima = dateSQL($('input#tgl_terima').datepicker('getDate'));
            var no_sj = $('input.nosj').val();
            var ket_sj = $('textarea.ketsj').val();

            var sj_box = numeral.unformat( $('input.sj_box').val() );
            var sj_ekor = numeral.unformat( $('input.sj_ekor').val() );
            var terima_box = numeral.unformat( $('input.terima_box').val() );
            var terima_ekor = numeral.unformat( $('input.terima_ekor').val() );
            var terima_mati = numeral.unformat( $('input.terima_mati').val() );
            var terima_afkir = numeral.unformat( $('input.terima_afkir').val() );
            var terima_awal = numeral.unformat( $('input.terima_awal').val() );
            var terima_bb = numeral.unformat( $('input.terima_bb').val() );
            var selisih_ekor = numeral.unformat( $('input.selisih_ekor').val() );
            var selisih_persen = numeral.unformat( $('input.selisih_persen').val() );
            var ket_terima = $('input.ket_terima').val();

            data_params['data'] = {
                'periode_rdim' : periode_rdim,
                'noreg' : noreg,
                'id_rdim' : id_rdim,
                'mitra' : mitra,
                'tgl_terima' : tgl_terima,
                'no_sj' : no_sj,
                'ket_sj' : ket_sj,
                'sj_box' : sj_box,
                'sj_ekor' : sj_ekor,
                'terima_box' : terima_box,
                'terima_ekor' : terima_ekor,
                'terima_mati' : terima_mati,
                'terima_afkir' : terima_afkir,
                'terima_awal' : terima_awal,
                'terima_bb' : terima_bb,
                'selisih_ekor' : selisih_ekor,
                'selisih_persen' : selisih_persen,
                'ket_terima' : ket_terima
            };
            data_params['action'] = 'submit';

            App.confirmDialog('Apakah Anda yakin akan menyimpan Berita Acara Serah Terima Titip Budidaya?', function(isConfirm){
                if (isConfirm) {
                    basttb.execute_save(data_params);
                }
            });
        };
    }, // end - save

    execute_save : function(data_params){
        $.ajax({
            url: 'transaksi/BASTTB/save',
            data : {
                'params' :  data_params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend: function() {
                showLoading();
            },
            success: function(data) {
                hideLoading();
                console.log(data.status);
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                        $('#action').html(data.content);
                        basttb.getLists();
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_save

    edit : function(elm){
        var err = 0;
        var div = $('div.attachement');

        $.map( $('[data-required=1]'), function(obj) {
            if ( empty($(obj).val()) ) {
                $(obj).parent().addClass('has-error');
                err++;
            } else {
                $(obj).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            var data_params = {};

            var s_em = $('[data-tipe=month]').MonthPicker('GetSelectedDate');
            var e_em = dateSQL(moment(s_em).startOf('month'));

            var periode_rdim = e_em;
            var noreg = $('select.noreg').val();
            var id_rdim = $('select.noreg').find('option[value='+ noreg +']').data('idrdim');
            var mitra = $('input.mitra').val();
            var tgl_terima = dateSQL($('input#tgl_terima').datepicker('getDate'));
            var no_sj = $('input.nosj').val();
            var ket_sj = $('textarea.ketsj').val();

            var sj_box = numeral.unformat( $('input.sj_box').val() );
            var sj_ekor = numeral.unformat( $('input.sj_ekor').val() );
            var terima_box = numeral.unformat( $('input.terima_box').val() );
            var terima_ekor = numeral.unformat( $('input.terima_ekor').val() );
            var terima_mati = numeral.unformat( $('input.terima_mati').val() );
            var terima_afkir = numeral.unformat( $('input.terima_afkir').val() );
            var terima_awal = numeral.unformat( $('input.terima_awal').val() );
            var terima_bb = numeral.unformat( $('input.terima_bb').val() );
            var selisih_ekor = numeral.unformat( $('input.selisih_ekor').val() );
            var selisih_persen = numeral.unformat( $('input.selisih_persen').val() );
            var ket_terima = $('input.ket_terima').val();

            data_params['data'] = {
                'periode_rdim' : periode_rdim,
                'noreg' : noreg,
                'id_rdim' : id_rdim,
                'mitra' : mitra,
                'tgl_terima' : tgl_terima,
                'no_sj' : no_sj,
                'ket_sj' : ket_sj,
                'sj_box' : sj_box,
                'sj_ekor' : sj_ekor,
                'terima_box' : terima_box,
                'terima_ekor' : terima_ekor,
                'terima_mati' : terima_mati,
                'terima_afkir' : terima_afkir,
                'terima_awal' : terima_awal,
                'terima_bb' : terima_bb,
                'selisih_ekor' : selisih_ekor,
                'selisih_persen' : selisih_persen,
                'ket_terima' : ket_terima
            };
            data_params['action'] = 'update';
            data_params['id_old'] = $(elm).data('id');

            App.confirmDialog('Apakah Anda yakin akan update data Berita Acara Serah Terima Titip Budidaya?', function(isConfirm){
                if (isConfirm) {
                    // console.log(data_params);
                    basttb.execute_edit(data_params);
                }
            });
        };
    }, // end - edit

    execute_edit : function(data_params){
        $.ajax({
            url: 'transaksi/BASTTB/edit',
            data : {
                'params' :  data_params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend: function() {
                showLoading();
            },
            success: function(data) {
                hideLoading();
                console.log(data.status);
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                        $('#action').html(data.content);
                        basttb.getLists();
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_edit
};

basttb.start_up();
