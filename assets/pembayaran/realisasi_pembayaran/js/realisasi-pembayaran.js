let dn = [];
let cn = [];
let potongan = null;

var rp = {
    start_up: function () {
        rp.setting_up();
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

            rp.hit_total_pilih( this );
        });
            
        $('select.jenis_pembayaran').select2();
        $('select.supplier').select2({placeholder: 'Pilih Supplier'});
        $('select.ekspedisi').select2({placeholder: 'Pilih Supplier'});
        $('select.mitra').select2({placeholder: 'Pilih Plasma'});
        $('select.perusahaan_non_multiple').select2({placeholder: 'Pilih Perusahaan'});

        $('select.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var option = $(e);
            var last_select = option[0].params.data.id;

            var unit = $('select.unit').select2('val');

            if ( last_select == 'all' ) {
                $('select.unit').select2().val(['all']).trigger('change');
            } else {
                var kode_unit = [];
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] != 'all' ) {
                        kode_unit.push( unit[i] );
                    }
                }

                $('select.unit').select2().val(kode_unit).trigger('change');
            }

            $('select.unit').next('span.select2').css('width', '100%');

            rp.get_mitra(this);
        });
        $('select.unit').next('span.select2').css('width', '100%');

        $('select.unit_ovk').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var option = $(e);
            var last_select = option[0].params.data.id;

            var unit = $('select.unit_ovk').select2('val');

            if ( last_select == 'all' ) {
                $('select.unit_ovk').select2().val(['all']).trigger('change');
            } else {
                var kode_unit = [];
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] != 'all' ) {
                        kode_unit.push( unit[i] );
                    }
                }

                $('select.unit_ovk').select2().val(kode_unit).trigger('change');
            }

            $('select.unit_ovk').next('span.select2').css('width', '100%');
        });
        $('select.unit_ovk').next('span.select2').css('width', '100%');

        $('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var option = $(e);
            var last_select = option[0].params.data.id;

            var perusahaan = $('select.perusahaan').select2('val');

            if ( last_select == 'all' ) {
                $('select.perusahaan').select2().val(['all']).trigger('change');
            } else {
                var kode_perusahaan = [];
                for (var i = 0; i < perusahaan.length; i++) {
                    if ( perusahaan[i] != 'all' ) {
                        kode_perusahaan.push( perusahaan[i] );
                    }
                }

                $('select.perusahaan').select2().val(kode_perusahaan).trigger('change');
            }

            $('select.perusahaan').next('span.select2').css('width', '100%');
        });
        $('select.perusahaan').next('span.select2').css('width', '100%');

        $('div#riwayat').find('select.jenis_transaksi').select2({placeholder: 'Pilih Jenis Transaksi'}).on("select2:select", function (e) {
            var jt = $('div#riwayat').find('select.jenis_transaksi').select2().val();
	
            for (var i = 0; i < jt.length; i++) {
                if ( jt[i] == 'all' ) {
                    $('div#riwayat').find('select.jenis_transaksi').select2().val('all').trigger('change');

                    i = jt.length;
                }
            }

            $('div#riwayat').find('select.jenis_transaksi').next('span.select2').css('width', '100%');
        });
        $('div#riwayat').find('select.jenis_transaksi').next('span.select2').css('width', '100%');

        $.map( $('div.jenis'), function(div) {
            $(div).find('select.jenis_transaksi').select2({placeholder: 'Pilih Jenis'}).on("select2:select", function (e) {
                var option = $(e);
                var last_select = option[0].params.data.id;

                $(div).find('div.ovk').addClass('hide');
                $(div).find('div.ovk select, input').removeAttr('data-required');
                if ( last_select == 'voadip' ) {
                    console.log('coba');

                    $(div).find('div.ovk').removeClass('hide');
                    $(div).find('div.ovk select.unit_ovk').attr('data-required', 1);
                }

                // var jenis_transaksi = $(div).find('select.jenis_transaksi').select2('val');

                // if ( last_select == 'all' ) {
                //     $(div).find('select.jenis_transaksi').select2().val(['all']).trigger('change');
                // } else {
                //     var kode_jenis_transaksi = [];
                //     for (var i = 0; i < jenis_transaksi.length; i++) {
                //         if ( jenis_transaksi[i] != 'all' ) {
                //             kode_jenis_transaksi.push( jenis_transaksi[i] );
                //         }
                //     }

                // }
                $(div).find('select.jenis_transaksi').select2().val(last_select).trigger('change');

                $(div).find('select.jenis_transaksi').next('span.select2').css('width', '100%');
            });
            $(div).find('select.jenis_transaksi').next('span.select2').css('width', '100%');
        });

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

        $("#start_date_bayar").on("dp.change", function (e) {
            $("#end_date_bayar").data("DateTimePicker").minDate(e.date);
        });
        $("#end_date_bayar").on("dp.change", function (e) {
            $('#start_date_bayar').data("DateTimePicker").maxDate(e.date);
        });

        $.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });

        $('select.jenis_pembayaran').on('change', function() {
            rp.jenis_pembayaran( this );
        });
        rp.jenis_pembayaran( $('select.jenis_pembayaran') );
    }, // end - setting_up

    jenis_pembayaran: function(elm) {
        var jenis = $(elm).val();

        $('div.jenis').addClass('hide');
        $('div.jenis').find('input:not(.select2-search__field), select').removeAttr('data-required');
        $('div.'+jenis).removeClass('hide');
        $('div.'+jenis).find('input:not(.select2-search__field), select').attr('data-required', 1);

        var jenis_transaksi = $('div.'+jenis).find('select.jenis_transaksi').select2('val');

        if ( !empty(jenis_transaksi) && jenis_transaksi.indexOf('voadip') === -1 ) {
            $('div.ovk').find('input:not(.select2-search__field), select').removeAttr('data-required');
        }
    }, // end - jenis_pembayaran

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

        rp.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'pembayaran/RealisasiPembayaran/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                rp.setting_up();

                cn = [];
                dn = [];
                potongan = null;

                if ( !empty(edit) ) {
                    var div = $('div#transaksi');
                    var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');
                    var jenis_transaksi = $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val');

                    if ( !empty(jenis_transaksi) && jenis_transaksi.indexOf('peternak') !== -1 ) {
                        rp.get_mitra( $('select.unit') );
                    } else {
                        $(div).find('button#btn-get-lists').click();
                    }
                }
            },
        });
    }, // end - load_form

    get_lists: function() {
        let div = $('div#riwayat');
        let dcontent = $('table.tbl_riwayat tbody');

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
                'start_date': dateSQL($(div).find('#start_date_bayar').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_bayar').data('DateTimePicker').date()),
                'perusahaan': $(div).find('.perusahaan').select2().val(),
                'jenis': $(div).find('.jenis_transaksi').select2().val()
            };

            $.ajax({
                url : 'pembayaran/RealisasiPembayaran/get_lists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    hideLoading();

                    $(div).find('#select_peternak').next('span.select2').css('width', '100%');
                    $(div).find('.perusahaan').next('span.select2').css('width', '100%');
                },
            });
        }
    }, // end - get_lists

    get_mitra: function(elm) {
        var kode_unit = $('select.unit').select2().val();
        var select_peternak = $('select.mitra');

        var option = '<option value="">Pilih Peternak</option>';
        if ( !empty(kode_unit) ) {
            var params = {
                'kode_unit': kode_unit
            };

            var nomor = $(select_peternak).attr('data-val');

            $.ajax({
                url : 'pembayaran/RealisasiPembayaran/get_mitra',
                data : { 'params': params },
                type : 'post',
                dataType : 'json',
                beforeSend : function(){ showLoading() },
                success : function(data){
                    hideLoading();

                    if ( !empty(data.content) && data.content.length > 0 ) {
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

                    $(select_peternak).html( option );

                    $(select_peternak).select2("destroy");
                    $(select_peternak).select2();
                },
            });
        } else {
            $(select_peternak).attr('disabled', 'disabled');
            $(select_peternak).html( option );
            $(select_peternak).select2("destroy");
            $(select_peternak).select2();
        }
    }, // end - get_mitra

    get_data_rencana_bayar: function(elm) {
        let div = $('div#transaksi');
        let dcontent = $(div).find('table.tbl_transaksi tbody');

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
            var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');

            var params = {
                'id': $(elm).attr('data-id'),
                'jenis_pembayaran': jenis_pembayaran,
                'jenis_transaksi': $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val'),
                'kode_unit_ovk': $(div).find('select.unit_ovk').select2('val'),
                'kode_unit': $(div).find('select.unit').select2('val'),
                'mitra': $(div).find('select.mitra').select2('val'),
                'supplier': $(div).find('select.supplier').select2('val'),
                'ekspedisi': $(div).find('select.ekspedisi').select2('val'),
                'perusahaan': $(div).find('select.perusahaan_non_multiple').val(),
                'start_date': dateSQL($(div).find('#start_date_bayar').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#end_date_bayar').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/RealisasiPembayaran/get_data_rencana_bayar',
                data : { 'params': params },
                type : 'post',
                dataType : 'json',
                beforeSend : function(){ showLoading() },
                success : function(data){
                    $(dcontent).html( data.html );
                    hideLoading();

                    $('.check').change(function() {
                        var target = $(this).attr('target');

                        var length = $('.check[target='+target+']').length;
                        var length_checked = $('.check[target='+target+']:checked').length;

                        if ( length == length_checked ) {
                            $('.check_all').prop( 'checked', true );
                        } else {
                            $('.check_all').prop( 'checked', false );
                        }

                        rp.hit_total_pilih( this );
                    });
                },
            });
        }
    }, // end - get_data_rencana_bayar

    hit_total_pilih: function(elm) {
        var table = $(elm).closest('table');
        var tbody = $(table).find('tbody');
        var thead = $(table).find('thead');

        var total_tagihan = 0;
        var total_dn = 0;
        var total_cn = 0;
        var total_transfer = 0;
        var total_bayar = 0;
        var total_sisa = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            var _tagihan = parseFloat($(tr).find('td._tagihan').attr('data-val'));
            var _dn = parseFloat($(tr).find('td._dn').attr('data-val'));
            var _cn = parseFloat($(tr).find('td._cn').attr('data-val'));
            var _transfer = parseFloat($(tr).find('td._transfer').attr('data-val'));
            var _bayar = parseFloat($(tr).find('td._bayar').attr('data-val'));
            total_tagihan += _tagihan;
            total_dn += _dn;
            total_cn += _cn;
            total_transfer += _transfer;
            total_bayar += _bayar;
            
            var checkbox = $(tr).find('input[type=checkbox]');
            if ( $(checkbox).prop('checked') ) {
                var _sisa = parseFloat($(tr).find('td._sisa').attr('data-val'));

                total_sisa += _sisa;
            }
        });

        $(thead).find('td.total_tagihan b').html( numeral.formatDec(total_tagihan) );
        $(thead).find('td.total_dn b').html( numeral.formatDec(total_dn) );
        $(thead).find('td.total_cn b').html( numeral.formatDec(total_cn) );
        $(thead).find('td.total_transfer b').html( numeral.formatDec(total_transfer) );
        $(thead).find('td.total_bayar b').html( numeral.formatDec(total_bayar) );
        $(thead).find('td.total_sisa b').html( numeral.formatDec(total_sisa) );
    }, // end - hit_total_pilih

    submit: function(elm) {
        var div = $('div#transaksi');

        var id = $(elm).attr('data-id');

        var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');
        var jenis_transaksi = $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val');
        var peternak = $(div).find('select.mitra').select2('val');
        var supplier = $(div).find('select.supplier').select2('val');
        var ekspedisi = $(div).find('select.ekspedisi').select2('val');
        var perusahaan = $(div).find('.perusahaan_non_multiple').val();

        var detail = [];
        $.map( $(div).find('tbody input[type=checkbox]'), function(ipt) {
            if ( $(ipt).prop('checked') ) {
                var tr = $(ipt).closest('tr');

                var _detail = {
                    'transaksi': $(tr).find('td.transaksi').attr('data-val'),
                    'no_bayar': $(tr).find('td.no_bayar').attr('data-val'),
                    'tagihan': $(tr).find('td.tagihan').attr('data-val')
                };

                detail.push( _detail );
            }
        });

        if ( detail.length == 0 ) {
            bootbox.alert('Tidak ada data yang akan anda submit.');
        } else {
            var params = {
                'id': id,
                'jenis_pembayaran': jenis_pembayaran,
                'jenis_transaksi': jenis_transaksi,
                'peternak': peternak,
                'supplier': supplier,
                'ekspedisi': ekspedisi,
                'perusahaan': perusahaan,
                'detail': detail
            };
            
            $.get('pembayaran/RealisasiPembayaran/realisasi_pembayaran',{
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

                    if ( !empty(id) ) {
                        var d_cn = [];
                        var d_dn = [];
                        if ( (empty(cn) || cn.length <= 0) ) {
                            var json_cn = $(modal_body).find('span.d_cn').text();
                            var d_cn = !empty(json_cn) ? JSON.parse(json_cn) : [];
                        }

                        if ( (empty(dn) || dn.length <= 0) ) {
                            var json_dn = $(modal_body).find('span.d_dn').text();
                            var d_dn = !empty(json_dn) ? JSON.parse(json_dn) : [];
                        }

                        if ( !empty(d_cn) && d_cn.length > 0 ) {
                            for (let i = 0; i < d_cn.length; i++) {                                
                                cn[i] = {
                                    'id': parseInt(d_cn[i].id_cn),
                                    'saldo': parseFloat(d_cn[i].saldo),
                                    'sisa_saldo': parseFloat(d_cn[i].sisa_saldo),
                                    'pakai': parseFloat(d_cn[i].pakai)
                                };
                            }
                        }

                        if ( !empty(d_dn) && d_dn.length > 0 ) {
                            for (let i = 0; i < d_dn.length; i++) {                                
                                dn[i] = {
                                    'id': parseInt(d_dn[i].id_dn),
                                    'saldo': parseFloat(d_dn[i].saldo),
                                    'sisa_saldo': parseFloat(d_dn[i].sisa_saldo),
                                    'pakai': parseFloat(d_dn[i].pakai)
                                };
                            }
                        }

                        rp.hit_jml_bayar();
                    }
                });
            },'html');
        }
    }, // end - submit

    modalPilihDN: function(elm) {
        let div = $('div#transaksi');
        var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');
        var jenis_transaksi = $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val');
        var _supplier = ($(div).find('select.supplier').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.supplier').select2('val') : null;
        var _ekspedisi = ($(div).find('select.ekspedisi').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.ekspedisi').select2('val') : null;
        var supplier = (!empty(_supplier)) ? _supplier : _ekspedisi;
        var mitra = ($(div).find('select.mitra').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.mitra').select2('val') : null;
        var perusahaan = $(div).find('select.perusahaan_non_multiple').val();

        var params = {
            'id': $(elm).attr('data-id'),
            'jenis_pembayaran': jenis_pembayaran,
            'jenis_transaksi': jenis_transaksi,
            'supplier': supplier,
            'mitra': mitra,
            'perusahaan': perusahaan
        };

        $.get('pembayaran/RealisasiPembayaran/modalPilihDN',{
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

                $(modal_dialog).css({'max-width' : '60%'});
                $(modal_dialog).css({'width' : '100%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                if ( !empty(dn) && dn.length > 0 ) {
                    $.map( $(modal_body).find('table tbody tr.data'), function(tr) {
                        var id_dn = $(tr).find('input[type="checkbox"]').attr('data-id');

                        for (var i = 0; i < dn.length; i++) {
                            if ( id_dn == dn[i].id ) {
                                $(tr).find('input[type="checkbox"]').prop('checked', true);
                                $(tr).find('input.pakai').val(numeral.formatDec(dn[i].pakai));
                            }
                        }
                    });
                }
            });
        },'html');
    }, // end - modalPilihDN

    cekPakaiDN: function(elm) {
        var tr = $(elm).closest('tr');

        var saldo = numeral.unformat( $(tr).find('td.saldo').text() );
        var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

        if ( pakai > saldo ) {
            bootbox.alert('DN yang anda masukkan melebihi saldo DN, harap cek kembali.', function () {
                $(tr).find('input.pakai').val( 0 );
            });
        }
    }, // end - cekPakaiDN

    pilihDN: function(elm) {
        var modal_dialog = $(elm).closest('.modal-dialog');
        var div = $(modal_dialog).find('.modal_dn');

        dn = [];
        var total_dn = 0;
        if ( $(div).find('[type=checkbox]:checked').length > 0 ) {
            var idx = 0;
            $.map( $(div).find('[type=checkbox]:checked'), function(check) {
                var tr = $(check).closest('tr');

                var saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var sisa_saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

                dn[idx] = {
                    'id': $(check).attr('data-id'),
                    'saldo': saldo,
                    'sisa_saldo': sisa_saldo,
                    'pakai': pakai
                };  

                total_dn += pakai;

                idx++;
            });
        }

        $('.total_dn').attr('data-val', total_dn);
        $('.total_dn').find('h4 b').text(numeral.formatDec(total_dn));

        $(modal_dialog).find('.btn-danger').click();

        rp.hit_jml_bayar();
    }, // end - pilihDN

    modalPilihCN: function(elm) {
        let div = $('div#transaksi');
        var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');
        var jenis_transaksi = $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val');
        var _supplier = ($(div).find('select.supplier').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.supplier').select2('val') : null;
        var _ekspedisi = ($(div).find('select.ekspedisi').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.ekspedisi').select2('val') : null;
        var supplier = (!empty(_supplier)) ? _supplier : _ekspedisi;
        var mitra = ($(div).find('select.mitra').closest('div.jenis:not(.hide)').length > 0) ? $(div).find('select.mitra').select2('val') : null;
        var perusahaan = $(div).find('select.perusahaan_non_multiple').val();

        var params = {
            'id': $(elm).attr('data-id'),
            'jenis_pembayaran': jenis_pembayaran,
            'jenis_transaksi': jenis_transaksi,
            'supplier': supplier,
            'mitra': mitra,
            'perusahaan': perusahaan
        };

        $.get('pembayaran/RealisasiPembayaran/modalPilihCN',{
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

                $(modal_dialog).css({'max-width' : '60%'});
                $(modal_dialog).css({'width' : '100%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                if ( !empty(cn) && cn.length > 0 ) {
                    $.map( $(modal_body).find('table tbody tr.data'), function(tr) {
                        var id_cn = $(tr).find('input[type="checkbox"]').attr('data-id');

                        for (var i = 0; i < cn.length; i++) {
                            if ( id_cn == cn[i].id ) {
                                $(tr).find('input[type="checkbox"]').prop('checked', true);
                                $(tr).find('input.pakai').val(numeral.formatDec(cn[i].pakai));
                            }
                        }
                    });
                }
            });
        },'html');
    }, // end - modalPilihCN

    cekPakaiCN: function(elm) {
        var tr = $(elm).closest('tr');

        var saldo = numeral.unformat( $(tr).find('td.saldo').text() );
        var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

        if ( pakai > saldo ) {
            bootbox.alert('CN yang anda masukkan melebihi saldo CN, harap cek kembali.', function () {
                $(tr).find('input.pakai').val( 0 );
            });
        }
    }, // end - cekPakaiCN

    pilihCN: function(elm) {
        var modal_dialog = $(elm).closest('.modal-dialog');
        var div = $(modal_dialog).find('.modal_cn');

        cn = [];
        var total_cn = 0;
        if ( $(div).find('[type=checkbox]:checked').length > 0 ) {
            var idx = 0;
            $.map( $(div).find('[type=checkbox]:checked'), function(check) {
                var tr = $(check).closest('tr');

                var saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var sisa_saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

                cn[idx] = {
                    'id': $(check).attr('data-id'),
                    'saldo': saldo,
                    'sisa_saldo': sisa_saldo,
                    'pakai': pakai
                };  

                total_cn += pakai;

                idx++;
            });
        }

        $('.total_cn').attr('data-val', total_cn);
        $('.total_cn').find('h4 b').text(numeral.formatDec(total_cn));

        $(modal_dialog).find('.btn-danger').click();

        rp.hit_jml_bayar();
    }, // end - pilihCN

    modalPotongan: function(elm) {
        let div = $('div#transaksi');
        var jenis_pembayaran = $(div).find('select.jenis_pembayaran').select2('val');
        var jenis_transaksi = $(div).find('div.'+jenis_pembayaran+' select.jenis_transaksi').select2('val');
        var supplier = $(div).find('select.supplier').select2('val');
        var perusahaan = $(div).find('select.perusahaan_non_multiple').val();

        var params = {
            'jenis_pembayaran': jenis_pembayaran,
            'jenis_transaksi': jenis_transaksi,
            'supplier': supplier,
            'perusahaan': perusahaan
        };

        $.get('pembayaran/RealisasiPembayaran/modalPotongan',{
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
                $(modal_dialog).css({'padding-top' : '15%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
    }, // end - modalPotongan

    simpanPotongan: function(elm) {
        var div = $(elm).closest('.modal-body');

        var total_potongan = 0;
        potongan = $.map( $(div).find('tbody tr'), function(tr) {
            var nominal = numeral.unformat( $(tr).find('input').val() );
            total_potongan += nominal;

            var _potongan = {
                'id': $(tr).attr('data-id'),
                'nominal': numeral.unformat( $(tr).find('input').val() )
            };

            return _potongan;
        });

        $('.total_potongan').attr('data-val', total_potongan);
        $('.total_potongan').find('h4 b').text(numeral.formatDec(total_potongan));

        $(div).find('.btn-danger').click();

        rp.hit_jml_bayar();
    }, // end - simpanPotongan

    hit_jml_bayar: function() {
        var div = $('.modal-body');

        var total = ($('.total').length > 0) ? $('.total').attr('data-val') : 0;
        var total_dn = ($(div).find('.total_dn').length > 0) ? $(div).find('.total_dn').attr('data-val') : 0;
        var total_cn = ($(div).find('.total_cn').length > 0) ? $(div).find('.total_cn').attr('data-val') : 0;
        var total_potongan = parseFloat($('.total_potongan').attr('data-val'));
        var total_uang_muka = numeral.unformat($('.uang_muka').val());
        var total_jml_transfer = numeral.unformat($('.jml_transfer').val()) + total_potongan + total_uang_muka;

        var tot_bayar = parseFloat(total_cn) + parseFloat(total_jml_transfer);

        if ( !empty(dn) && dn.length > 0 ) {
            for (var i = 0; i < dn.length; i++) {
                dn[i].sisa_saldo = dn[i].pakai;
            }
        }

        if ( !empty(cn) && cn.length > 0 ) {
            for (var i = 0; i < cn.length; i++) {
                cn[i].sisa_saldo = cn[i].pakai;
            }
        }

        var idx_cn = 0;
        var stts_cn = 1;
        var idx_dn = 0;
        $.map( $('table.tbl_tagihan tbody tr'), function(tr) {
            var no_bayar = $(tr).find('td.no_bayar').attr('data-val');
            var _tagihan = $(tr).find('td.tagihan').attr('data-val');
            
            var _dn = 0;
            var tagihan = parseFloat(_tagihan);
            if ( total_dn > 0 ) {
                var prs = _tagihan/total;
                var _dn = total_dn * (prs);

                $(tr).find('td.dn').attr('data-val', _dn);
                $(tr).find('td.dn').text(numeral.formatDec(_dn));

                tagihan += parseFloat(_dn);

                while ( _dn > 0) {
                    if ( dn[idx_dn].sisa_saldo >= _dn ) {
                        dn[idx_dn].sisa_saldo -= _dn;

                        if ( typeof dn[idx_dn].detail == 'undefined' ) {
                            dn[idx_dn].detail = { [no_bayar]: {
                                    'no_bayar': no_bayar,
                                    'jml_bayar': _dn,
                                    'id_dn': dn[idx_dn].id
                                }
                            };
                        } else {
                            dn[idx_dn].detail[no_bayar] = {
                                'no_bayar': no_bayar,
                                'jml_bayar': _dn,
                                'id_dn': dn[idx_dn].id
                            };
                        }

                        _dn = 0;
                    } else {
                        _dn -= dn[idx_dn].sisa_saldo;
                        
                        if ( typeof dn[idx_dn].detail == 'undefined' ) {
                            dn[idx_dn].detail = { [no_bayar]: {
                                    'no_bayar': no_bayar,
                                    'jml_bayar': dn[idx_dn].sisa_saldo,
                                    'id_dn': dn[idx_dn].id
                                }
                            };
                        } else {
                            dn[idx_dn].detail[no_bayar] = {
                                'no_bayar': no_bayar,
                                'jml_bayar': dn[idx_dn].sisa_saldo,
                                'id_dn': dn[idx_dn].id
                            };
                        }
                        
                        dn[idx_dn].sisa_saldo -= dn[idx_dn].sisa_saldo;

                        idx_dn++;
                    }
                }
            }

            var _cn = 0;
            var _transfer = 0;
            var tot_bayar = 0;

            while ( tagihan > 0 ) {
                if ( !empty(cn[idx_cn]) && cn[idx_cn].sisa_saldo > 0 ) {
                    if ( cn[idx_cn].sisa_saldo >= tagihan ) {
                        cn[idx_cn].sisa_saldo -= tagihan;

                        if ( typeof cn[idx_cn].detail == 'undefined' ) {
                            cn[idx_cn].detail = { [no_bayar]: {
                                    'no_bayar': no_bayar,
                                    'jml_bayar': tagihan,
                                    'id_cn': cn[idx_cn].id
                                }
                            };
                        } else {
                            cn[idx_cn].detail[no_bayar] = {
                                'no_bayar': no_bayar,
                                'jml_bayar': tagihan,
                                'id_cn': cn[idx_cn].id
                            };
                        }

                        tot_bayar += tagihan;
                        _cn += tagihan;

                        tagihan = 0;
                    } else {
                        tagihan -= cn[idx_cn].sisa_saldo;
                        
                        if ( typeof cn[idx_cn].detail == 'undefined' ) {
                            cn[idx_cn].detail = { [no_bayar]: {
                                    'no_bayar': no_bayar,
                                    'jml_bayar': cn[idx_cn].sisa_saldo,
                                    'id_cn': cn[idx_cn].id
                                }
                            };
                        } else {
                            cn[idx_cn].detail[no_bayar] = {
                                'no_bayar': no_bayar,
                                'jml_bayar': cn[idx_cn].sisa_saldo,
                                'id_cn': cn[idx_cn].id
                            };
                        }
                        
                        tot_bayar += cn[idx_cn].sisa_saldo;
                        _cn += cn[idx_cn].sisa_saldo;
                        
                        cn[idx_cn].sisa_saldo -= cn[idx_cn].sisa_saldo;

                        stts_cn = 0;

                        idx_cn++;
                    }
                } else {
                    stts_cn = 0;

                    break;
                }
            }

            while ( tagihan > 0 && stts_cn == 0 ) {
                if ( tagihan > 0 ) {
                    if ( total_jml_transfer > 0 ) {
                        if ( total_jml_transfer >= tagihan ) {
                            total_jml_transfer -= tagihan;

                            tot_bayar += tagihan;
                            _transfer += tagihan;

                            tagihan = 0;
                        } else {
                            tagihan -= total_jml_transfer;

                            tot_bayar += total_jml_transfer;
                            _transfer += total_jml_transfer;

                            total_jml_transfer = 0;
                        }
                    } else {
                        break;
                    }
                }
            }

            $(tr).find('td.cn').attr('data-val', _cn);
            $(tr).find('td.cn').text(numeral.formatDec(_cn));

            $(tr).find('td.transfer').attr('data-val', _transfer);
            $(tr).find('td.transfer').text(numeral.formatDec(_transfer));

            $(tr).find('td.bayar').attr('data-val', tot_bayar);
            $(tr).find('td.bayar').text(numeral.formatDec(tot_bayar));
        });

        var kurang_bayar = (parseFloat(total) + parseFloat(total_dn)) - tot_bayar;

        $('.total_bayar').attr('data-val', tot_bayar);
        $('.total_bayar h4 b').text(numeral.formatDec(tot_bayar));

        $('.kurang_bayar').attr('data-val', kurang_bayar);
        $('.kurang_bayar h4 b').text(numeral.formatDec(kurang_bayar));
    }, // end - hit_jml_bayar

    save: function() {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

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
            var tagihan = parseFloat($(modal_body).find('.total').attr('data-val')) + parseFloat($(modal_body).find('.total_dn').attr('data-val'));
            var total_bayar = $(modal_body).find('.total_bayar').attr('data-val');

            var ket = null;
            if ( tagihan != total_bayar ) {
                bootbox.prompt({
                    title: 'Jumlah transfer dan tagihan tidak sama, harap isi keterangan terlebih dahulu sebelum simpan data',
                    inputType: 'textarea',
                    placeholder: 'Alasan',
                    buttons: {
                        confirm: {
                            label: 'Ya',
                            className: 'btn-primary'
                        },
                        cancel: {
                            label: 'Tidak',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if(result != null){
                            if( empty(result) ){
                                bootbox.alert('Mohon isi kolom keterangan terlebih dahulu.');
                            }else{
                                ket = result;

                                rp.exec_save( ket );
                            }
                        }
                    }
                });
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data realisasi pembayaran ?', function(result) {
                    if ( result ) {
                        rp.exec_save();
                    }
                });

            }
        }
    }, // end - save

    exec_save: function (ket = null) {
        var modal_body = $('.modal-body');

        var detail = $.map( $(modal_body).find('tbody tr'), function(tr) {
            var _detail = {
                'transaksi': $(tr).find('.transaksi').attr('data-val'),
                'no_bayar': $(tr).find('.no_bayar').attr('data-val'),
                'tagihan': parseFloat($(tr).find('.tagihan').attr('data-val')),
                'bayar': parseFloat($(tr).find('td.bayar').attr('data-val')),
                'cn': parseFloat($(tr).find('td.cn').attr('data-val')),
                'dn': parseFloat($(tr).find('td.dn').attr('data-val')),
                'transfer': parseFloat($(tr).find('td.transfer').attr('data-val'))
            };

            return _detail;
        });

        var data = {
            'tagihan': $(modal_body).find('.total').attr('data-val'),
            'total_dn': ($(modal_body).find('.total_dn').length > 0) ? $(modal_body).find('.total_dn').attr('data-val') : 0,
            'total_cn': ($(modal_body).find('.total_cn').length > 0) ? $(modal_body).find('.total_cn').attr('data-val') : 0,
            'total_potongan': ($(modal_body).find('.total_potongan').length > 0) ? $(modal_body).find('.total_potongan').attr('data-val') : 0,
            'uang_muka': numeral.unformat($(modal_body).find('.uang_muka').val()),
            'jml_transfer': numeral.unformat($(modal_body).find('.jml_transfer').val()),
            'bayar': $(modal_body).find('.total_bayar').attr('data-val'),
            'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
            'perusahaan': $(modal_body).find('.perusahaan').attr('data-val'),
            'supplier': $(modal_body).find('.supplier').attr('data-val'),
            'peternak': $(modal_body).find('.peternak').attr('data-val'),
            'ekspedisi': $(modal_body).find('.ekspedisi').attr('data-val'),
            'no_rek': $(modal_body).find('.rekening').val(),
            'no_bukti': $(modal_body).find('.no_bukti').val(),
            'dn': !empty(dn) ? dn : null,
            'cn': !empty(cn) ? cn : null,
            'potongan': !empty(potongan) ? potongan : null,
            'keterangan': ket,
            'detail': detail
        };

        var formData = new FormData();

        var _file = $('.file_lampiran').get(0).files[0];
        formData.append('files', _file);
        formData.append('data', JSON.stringify(data));

        $.ajax({
            url : 'pembayaran/RealisasiPembayaran/save',
            type : 'post',
            data : formData,
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        cn = [];
                        dn = [];
                        potongan = null;

                        var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                        rp.load_form($(btn), null, 'transaksi');

                        bootbox.hideAll();
                    });
                } else {
                    bootbox.alert(data.message);
                }
            },
            contentType : false,
            processData : false,
        });
    }, // end - exec_save

    edit: function(elm) {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

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
            var tagihan = $(modal_body).find('.total').attr('data-val');
            var jml_transfer = $(modal_body).find('.total_bayar').attr('data-val');

            var ket = null;
            if ( tagihan > jml_transfer ) {
                bootbox.prompt({
                    title: 'Jumlah transfer dan tagihan tidak sama, harap isi keterangan terlebih dahulu sebelum simpan data',
                    inputType: 'textarea',
                    placeholder: 'Alasan',
                    buttons: {
                        confirm: {
                            label: 'Ya',
                            className: 'btn-primary'
                        },
                        cancel: {
                            label: 'Tidak',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if(result != null){
                            if( empty(result) ){
                                bootbox.alert('Mohon isi kolom keterangan terlebih dahulu.');
                            }else{
                                ket = result;

                                rp.exec_edit( elm, ket );
                            }
                        }
                    }
                });
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data realisasi pembayaran ?', function(result) {
                    if ( result ) {
                        rp.exec_edit(elm);
                    }
                });

            }
        }
    }, // end - edit

    exec_edit: function(elm, ket = null) {
        var modal_body = $('.modal-body');

        var detail = $.map( $(modal_body).find('tbody tr'), function(tr) {
            var _detail = {
                'transaksi': $(tr).find('.transaksi').attr('data-val'),
                'no_bayar': $(tr).find('.no_bayar').attr('data-val'),
                'tagihan': parseFloat($(tr).find('.tagihan').attr('data-val')),
                'bayar': parseFloat($(tr).find('td.bayar').attr('data-val')),
                'cn': parseFloat($(tr).find('td.cn').attr('data-val')),
                'dn': parseFloat($(tr).find('td.dn').attr('data-val')),
                'transfer': parseFloat($(tr).find('td.transfer').attr('data-val'))
            };

            return _detail;
        });

        var data = {
            'id': $(elm).attr('data-id'),
            'tagihan': $(modal_body).find('.total').attr('data-val'),
            'total_dn': ($(modal_body).find('.total_dn').length > 0) ? $(modal_body).find('.total_dn').attr('data-val') : 0,
            'total_cn': ($(modal_body).find('.total_cn').length > 0) ? $(modal_body).find('.total_cn').attr('data-val') : 0,
            'total_potongan': ($(modal_body).find('.total_potongan').length > 0) ? $(modal_body).find('.total_potongan').attr('data-val') : 0,
            'uang_muka': numeral.unformat($(modal_body).find('.uang_muka').val()),
            'jml_transfer': numeral.unformat($(modal_body).find('.jml_transfer').val()),
            'bayar': $(modal_body).find('.total_bayar').attr('data-val'),
            'tgl_bayar': dateSQL($(modal_body).find('#tgl_bayar').data('DateTimePicker').date()),
            'perusahaan': $(modal_body).find('.perusahaan').attr('data-val'),
            'supplier': $(modal_body).find('.supplier').attr('data-val'),
            'peternak': $(modal_body).find('.peternak').attr('data-val'),
            'ekspedisi': $(modal_body).find('.ekspedisi').attr('data-val'),
            'no_rek': $(modal_body).find('.rekening').val(),
            'no_bukti': $(modal_body).find('.no_bukti').val(),
            'dn': !empty(dn) ? dn : null,
            'cn': !empty(cn) ? cn : null,
            'potongan': !empty(potongan) ? potongan : null,
            'keterangan': ket,
            'detail': detail
        };

        var formData = new FormData();

        var _file = $('.file_lampiran').get(0).files[0];
        formData.append('files', _file);
        formData.append('data', JSON.stringify(data));

        $.ajax({
            url : 'pembayaran/RealisasiPembayaran/edit',
            type : 'post',
            data : formData,
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        cn = [];
                        dn = [];
                        potongan = null;

                        var btn = '<button type="button" data-href="transaksi" data-id="'+data.content.id+'"></button>';
                        rp.load_form($(btn), null, 'transaksi');

                        bootbox.hideAll();
                    });
                } else {
                    bootbox.alert(data.message);
                }
            },
            contentType : false,
            processData : false,
        });
    }, // end - exec_edit

    delete: function(elm) {
        var div = $('div#transaksi');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data realisasi pembayaran ?', function(result) {
            if ( result ) {
                var params = {
                    'id': $(elm).data('id')
                };

                $.ajax({
                    url : 'pembayaran/RealisasiPembayaran/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                dn = null;
                                cn = null;
                                potongan = null;

                                var btn = '<button type="button" data-href="transaksi"></button>';
                                rp.load_form($(btn), null, 'transaksi');
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

rp.start_up();