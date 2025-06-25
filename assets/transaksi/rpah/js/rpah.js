var rpah = {
	start_up: function () {
        // rpah.get_lists();
	}, // end - start_up

    settingUp: function () {
        $("[name=startDate]").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("[name=endDate]").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false //Important! See issue #1075
        });
        $("[name=startDate]").on("dp.change", function (e) {
            $("[name=endDate]").data("DateTimePicker").minDate(e.date);
            $("[name=endDate]").data("DateTimePicker").date(e.date);
        });
        $("[name=endDate]").on("dp.change", function (e) {
            $('[name=startDate]').data("DateTimePicker").maxDate(e.date);
        });
        $("#tgl_panen").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false //Important! See issue #1075
        });

        let tgl = $("#tgl_panen").data('tgl');
        if ( !empty(tgl) && tgl != 'undefined' ) {
            $("#tgl_panen").datetimepicker({ date: new Date(tgl) });
        }

        $('select.unit').select2();
        $('select.pelanggan').select2().on("select2:select", function (e) {
            var no_pelanggan = e.params.data.id;

            rpah.cekPelanggan(this, no_pelanggan);
        });
    }, // end - settingUp

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');

        $(row).find('select.pelanggan').select2('destroy')
                                    .removeAttr('data-live-search')
                                    .removeAttr('data-select2-id')
                                    .removeAttr('aria-hidden')
                                    .removeAttr('tabindex');
        $(row).find('select.pelanggan option').removeAttr('data-select2-id');

        let newRow = row.clone();

        newRow.find('input, select').val('');
        newRow.find('input.outstanding').val(0);
        row.find('.btn-ctrl').hide();
        row.after(newRow);

        $.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('select.pelanggan').select2().on("select2:select", function (e) {
                var no_pelanggan = e.params.data.id;

                rpah.cekPelanggan(this, no_pelanggan);
            });
        });

        App.formatNumber();
    }, // end - addRow

    removeRow: function(elm) {
        let table = $(elm).closest('table.detail');
        let row = $(elm).closest('tr');
        if ($(row).prev('tr').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
        }
    }, // end - removeRow

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

        if ( vhref == 'rpah' ) {
            let v_id = $(elm).attr('data-id');
            rpah.load_form(v_id, resubmit);
        } else {
            rpah.load_form(null, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#rpah');

        $.ajax({
            url : 'transaksi/RPAH/load_form',
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

                rpah.settingUp();
            },
        });
    }, // end - load_form

    get_lists: function() {
        let dcontent = $('table.tbl_rpah tbody');

        if ( empty($('#datetimepicker_start').find('input').val()) && empty($('#datetimepicker_end').find('input').val()) ) {
            bootbox.alert('Harap isi periode terlebih dahulu.');
        } else {
            var params = {
                'start_date': dateSQL( $('#datetimepicker_start').data('DateTimePicker').date() ),
                'end_date': dateSQL( $('#datetimepicker_end').data('DateTimePicker').date() )
            };

            $.ajax({
                url : 'transaksi/RPAH/get_lists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    hideLoading();
                    $(dcontent).html( html );
                },
            });
        }
    }, // end - get_lists

    get_data: function(elm) {
        let form = $(elm).closest('form');
        
        let unit = $(elm).val();
        // let tgl_panen = $(form).find('label.tgl_panen').data('tgl');
        let tgl_panen = dateSQL( $('#tgl_panen').data('DateTimePicker').date() );

        let params = {
            'unit': unit,
            'tgl_panen': tgl_panen
        };

        $.ajax({
            url : 'transaksi/RPAH/get_data',
            data : {
                'params' :  params
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
                hideLoading();

                $('table.tbl_data_konfir').find('tbody').html( html );

                App.formatNumber();

                $('select.pelanggan').select2().on("select2:select", function (e) {
                    var no_pelanggan = e.params.data.id;

                    rpah.cekPelanggan(this, no_pelanggan);
                });
            },
        });
    }, // end - get_data

    hit_bb: function(elm) {
        let tr = $(elm).closest('tr');

        let tonase = numeral.unformat( $(tr).find('input.tonase').val() );
        let ekor = numeral.unformat( $(tr).find('input.ekor').val() );

        let bb = 0;
        if ( (tonase != 0 && !empty(tonase)) || (ekor != 0 && !empty(ekor)) ) {
            bb = tonase / ekor;
        };

        $(tr).find('input.bb').val( numeral.formatDec(bb) );

        rpah.hit_tot_rpah( tr );
    }, // end - hit_bb

    hit_tot_rpah: function(_tr) {
        var tbody = $(_tr).closest('tbody');
        var table = $(tbody).closest('table');

        var tot_ekor = 0;
        var tot_kg = 0;
        var tot_bb = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            var ekor = numeral.unformat( $(tr).find('input.ekor').val() );
            var tonase = numeral.unformat( $(tr).find('input.tonase').val() );
            tot_ekor += ekor;
            tot_kg += tonase;
        });

        if ( tot_ekor > 0 && tot_kg > 0 ) {
            tot_bb = tot_kg / tot_ekor;
        }

        $(table).find('tfoot td.detail_tot_ekor b').text(numeral.formatInt(tot_ekor));
        $(table).find('tfoot td.detail_tot_kg b').text(numeral.formatDec(tot_kg));
        $(table).find('tfoot td.detail_tot_bb b').text(numeral.formatDec(tot_bb));

        var tr_detail = $(table).closest('tr.detail');
        var tr_header = $(tr_detail).prev('tr.head');

        rpah.cek_tot_rpah(tr_detail, tr_header);
    }, // end - hit_tot_rpah

    cek_tot_rpah: function(tr_detail, tr_header) {
        var header_tot_ekor = numeral.unformat( $(tr_header).find('td.head_tot_ekor').text() );
        var header_tot_kg = numeral.unformat( $(tr_header).find('td.head_tot_kg').text() );
        var detail_tot_ekor = numeral.unformat( $(tr_detail).find('td.detail_tot_ekor b').text() );
        var detail_tot_kg = numeral.unformat( $(tr_detail).find('td.detail_tot_kg b').text() );

        if ( header_tot_ekor < detail_tot_ekor ) {
            $(tr_detail).find('td.detail_tot_ekor').addClass('lebih');
        } else {
            $(tr_detail).find('td.detail_tot_ekor').removeClass('lebih');
        }

        if ( header_tot_kg < detail_tot_kg ) {
            $(tr_detail).find('td.detail_tot_kg').addClass('lebih');
        } else {
            $(tr_detail).find('td.detail_tot_kg').removeClass('lebih');
        }
    }, // end - cek_tot_rpah

    save: function() {
        let err = 0;

        var pilih_jml_data = 0;
        $.map( $('div#rpah').find('input[type="checkbox"]'), function(checkbox) {
            if ( $(checkbox).prop('checked') == true ) {
                pilih_jml_data++;
                var tr_head = $(checkbox).closest('tr');
                var tr_detail = $(tr_head).next('tr.detail');
                $.map( $(tr_detail).find('[data-required=1]'), function(ipt) {
                    if ( empty( $(ipt).val() ) ) {
                        $(ipt).parent().addClass('has-error');
                        err++;
                    } else {
                        $(ipt).parent().removeClass('has-error');
                    };
                });
            }
        });

        // $.map( $('div#rpah').find('[data-required=1]'), function(ipt) {
        //     if ( empty( $(ipt).val() ) ) {
        //         $(ipt).parent().addClass('has-error');
        //         err++;
        //     } else {
        //         $(ipt).parent().removeClass('has-error');
        //     };
        // });

        if ( pilih_jml_data == 0 ) {
            bootbox.alert( 'Belum ada mitra yang anda pilih.' );
        } else {
            if ( err > 0 ) {
                bootbox.alert( 'Harap lengkapi data pada mitra yang telah anda pilih.' );
            } else {
                bootbox.confirm( 'Apakah anda yakin ingin menyimpan data Rencana Penjualan Harian ?', function(result) {
                    if ( result ) {
                        let table = $('table.tbl_data_konfir');

                        let data_detail = null;
                        $.map( $(table).find('tbody tr.head'), function(tr_head) {
                            var checkbox = $(tr_head).find('input[type="checkbox"]');
                            if ( $(checkbox).prop('checked') == true ) {
                                let tr_detail = $(tr_head).next('tr.detail');
                                data_detail = $.map( $(tr_detail).find('table tbody tr'), function(tr) {
                                    let _data = {
                                        'id_konfir' : $(tr_head).data('idkonfir'),
                                        'noreg' : $(tr_head).find('td.noreg').text().trim(),
                                        'unit' : $(tr_head).find('td.kandang').data('unit'),
                                        'no_plg' : $(tr).find('select.pelanggan').val(),
                                        'plg': $(tr).find('select.pelanggan option:selected').text().trim(),
                                        'outstanding': $(tr).find('input.outstanding').val(),
                                        'tonase': numeral.unformat($(tr).find('input.tonase').val()),
                                        'ekor': numeral.unformat($(tr).find('input.ekor').val()),
                                        'bb': numeral.unformat($(tr).find('input.bb').val()),
                                        'harga': numeral.unformat($(tr).find('input.harga').val())
                                    };

                                    return _data
                                });
                            }
                        });

                        let data = {
                            'id_unit': $('select.unit').val(),
                            'unit': $('select.unit option:selected').text().trim(),
                            'bottom_price': numeral.unformat( $('input.bottom_price').val() ),
                            // 'tgl_panen': $('label.tgl_panen').data('tgl'),
                            'tgl_panen': dateSQL( $('#tgl_panen').data('DateTimePicker').date() ),
                            'data_detail': data_detail
                        };

                        // console.log( data );
                        rpah.execute_save( data );
                    };
                });
            };
        }
    }, // end - save

    execute_save: function(params) {
        $.ajax({
            url : 'transaksi/RPAH/save',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert( data.message, function() {
                        // rpah.get_lists();
                        rpah.load_form(data.content.id);
                    });
                } else {
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - execute_save

    edit: function(elm) {
        let err = 0;

        $.map( $('div#rpah').find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin meng-ubah data Rencana Penjualan Harian ?', function(result) {
                if ( result ) {
                    let table = $('table.tbl_data_konfir');

                    let data_detail = [];
                    $.map( $(table).find('tbody tr.head'), function(tr_head) {
                        let tr_detail = $(tr_head).next('tr.detail');

                        $.map( $(tr_detail).find('table tbody tr'), function(tr) {
                            let _data = {
                                'id_konfir' : $(tr_head).data('idkonfir'),
                                'no_do' : $(tr).find('input.no_do').val(),
                                'no_sj' : $(tr).find('input.no_sj').val(),
                                'noreg' : $(tr_head).find('td.noreg').text().trim(),
                                'unit' : $(tr_head).find('td.kandang').data('unit'),
                                'no_plg' : $(tr).find('select.pelanggan').val(),
                                'plg': $(tr).find('select.pelanggan option:selected').text().trim(),
                                'outstanding': $(tr).find('input.outstanding').val(),
                                'tonase': numeral.unformat($(tr).find('input.tonase').val()),
                                'ekor': numeral.unformat($(tr).find('input.ekor').val()),
                                'bb': numeral.unformat($(tr).find('input.bb').val()),
                                'harga': numeral.unformat($(tr).find('input.harga').val())
                            };

                            data_detail.push( _data );
                        });
                    });

                    let data = {
                        'id_rpah': $(elm).data('id'),
                        'id_unit': $('select.unit').val(),
                        'unit': $('select.unit option:selected').text().trim(),
                        'bottom_price': numeral.unformat( $('input.bottom_price').val() ),
                        // 'tgl_panen': $('label.tgl_panen').data('tgl'),
                        'tgl_panen': dateSQL( $('#tgl_panen').data('DateTimePicker').date() ),
                        'data_detail': data_detail
                    };

                    rpah.execute_edit( data );
                };
            });
        };
    }, // end - edit

    execute_edit: function(params) {
        $.ajax({
            url : 'transaksi/RPAH/edit',
            data : {
                'params' : params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert( data.message, function() {
                        // rpah.get_lists();
                        rpah.load_form(data.content.id);
                    });
                } else {
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - execute_edit

    delete: function(elm) {
        let id = $(elm).data('id');

        bootbox.confirm( 'Apakah anda yakin ingin meng-hapus data Rencana Penjualan Harian ?', function(result) {
            if ( result ) {
                $.ajax({
                    url : 'transaksi/RPAH/delete',
                    data : {
                        'id' : id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                // rpah.get_lists();
                                rpah.changeTabActive(elm);
                            });
                        } else {
                            bootbox.alert( data.message );
                        };
                    },
                });
            }
        });
    }, // end - delete

    approve: function(elm) {
        let id = $(elm).data('id');

        bootbox.confirm( 'Apakah anda yakin ingin meng-approve data Rencana Penjualan Harian ?', function(result) {
            if ( result ) {
                $.ajax({
                    url : 'transaksi/RPAH/approve',
                    data : {
                        'id' : id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                // rpah.get_lists();
                                rpah.load_form(data.content.id);
                            });
                        } else {
                            bootbox.alert( data.message );
                        };
                    },
                });
            }
        });
    }, // end - approve

    reject: function(elm) {
        let id = $(elm).data('id');

        bootbox.confirm( 'Apakah anda yakin ingin meng-reject data Rencana Penjualan Harian ?', function(result) {
            if ( result ) {
                $.ajax({
                    url : 'transaksi/RPAH/reject',
                    data : {
                        'id' : id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                // rpah.get_lists();
                                rpah.load_form(data.content.id);
                            });
                        } else {
                            bootbox.alert( data.message );
                        };
                    },
                });
            }
        });
    }, // end - reject

    cekPelanggan: function (elm, no_pelanggan) {
        var tr = $(elm).closest('tr');

        var params = {
            'no_pelanggan': no_pelanggan
        };

        $.ajax({
            url : 'transaksi/RPAH/cekPelanggan',
            data : {
                'params' : params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading('Cek kelayakan bakul . . .'); },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    if ( data.content.fulfil == 0 ) {
                        bootbox.confirm( data.content.html, function(result) {
                            if ( !result ) {
                                $(tr).find('select.pelanggan').select2().val('');
                                $(tr).find('select.pelanggan').select2().trigger('change');
                            }
                        });
                    }
                } else {
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - cekPelanggan
};

rpah.start_up();