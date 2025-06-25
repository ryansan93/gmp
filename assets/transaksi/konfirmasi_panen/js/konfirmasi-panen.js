var hrg_kesepakatan = {};
var kp = {
    start_up : function () {
        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('#start_date, #end_date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - start_up

    load_form: function(elm) {
        let tr = $(elm);

        showLoading();

        let params = {
            'noreg' : $(tr).find('td.noreg').text().trim(),
            'id' : $(tr).data('id')
        }

        $.get('transaksi/KonfirmasiPanen/load_form',{
                'params': params
            },function(data){
            hideLoading();

            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                $(modal_dialog).css({'max-width' : '80%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                var modal_body = $(this).find('.modal-body');

                var table = $(modal_body).find('table');
                var tbody = $(table).find('tbody');
                if ( $(tbody).find('.modal-body tr').length <= 1 ) {
                    $(this).find('tr #btn-remove').addClass('hide');
                };

                $('#tgl_docin, #tgl_panen').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                let tgl_docin = $('#tgl_docin').data('tgl');
                $('#tgl_docin').data("DateTimePicker").date(moment(tgl_docin));

                let tgl_panen = $('#tgl_panen').data('tgl');
                if ( !empty(tgl_panen) ) {
                    $('#tgl_panen').data("DateTimePicker").date(moment(tgl_panen));
                }

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
    }, // end - load_form

    addRow: function(elm) {
        let tbody = $(elm).closest('tbody');

        let row = $(elm).closest('tr');
        let newRow = row.clone();

        newRow.find('input').val('');
        row.find('.btn-ctrl').hide();
        row.after(newRow);

        let no = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            no++;
            $(tr).find('td.no').text(no);
        });

        App.formatNumber();
    }, // end - addRowChild

    removeRow: function(elm) {
        let row = $(elm).closest('tr');
        if ($(row).prev('tr').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).addClass('inactive');
        }
    }, // end - removeRowChild

    get_lists: function() {
        let err = 0;

        $.map( $('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
        } else {
            let dcontent = $('table.tbl_list_konfirmasi_panen').find('tbody');

            let unit = $('select[name=unit]').val();
            let start_date_docin = dateSQL($('#start_date').data('DateTimePicker').date());
            let end_date_docin = dateSQL($('#end_date').data('DateTimePicker').date());

            let params = {
                'unit' : unit,
                'start_date' : start_date_docin,
                'end_date' : end_date_docin
            };

            $.ajax({
                url : 'transaksi/KonfirmasiPanen/get_lists',
                data : {
                    'params' :  params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    hideLoading();
                    // if ( data.status == 1 ) {
                        // let html = data.content;
                        $(dcontent).html( html );
                    // } else {
                    //     bootbox.alert( data.message );
                    // }
                },
            });
        }
    }, // end - get_lists

    edit_batal: function(elm) {
        let modal_body = $(elm).closest('.modal-body');
        let jenis = $(elm).data('jenis');

        if ( jenis == 'edit' ) {
            let div = $(elm).closest('div');
            $(div).addClass('hide');

            $(div).prev('div.update').removeClass('hide');

            $(modal_body).find('div#tgl_panen input').removeAttr('readonly');
            $(modal_body).find('input.jumlah').removeAttr('readonly');
            $(modal_body).find('input.bb').removeAttr('readonly');

            let table_detail = $(modal_body).find('table.detail');
            let table_edit = $(modal_body).find('table.edit');
            $(table_detail).addClass('hide');
            $(table_edit).removeClass('hide');

            $(table_edit).find('tbody').html( $(table_detail).find('tbody').html() );

            $(table_edit).find('tbody tr:last div.btn-ctrl').attr('style', 'display:block');

            $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                $(this).priceFormat(Config[$(this).data('tipe')]);
            });
        } else {
            let div = $(elm).closest('div');
            $(div).addClass('hide');

            $(div).next('div.action').removeClass('hide');

            $(modal_body).find('div#tgl_panen input').attr('readonly', true);
            $(modal_body).find('input.jumlah').attr('readonly', true);
            $(modal_body).find('input.bb').attr('readonly', true);

            let table_detail = $(modal_body).find('table.detail');
            let table_edit = $(modal_body).find('table.edit');
            $(table_detail).removeClass('hide');
            $(table_edit).addClass('hide');

            let tgl_panen = $(modal_body).find('#tgl_panen').data('tgl');
            if ( !empty(tgl_panen) ) {
                $(modal_body).find('#tgl_panen').data("DateTimePicker").date(moment(tgl_panen));
            }
        }
    }, // end - edit

    hitung_total: function(elm) {
        let tbody = $(elm).closest('tbody');
        let table = $(tbody).closest('table');
        let tfoot = $(table).find('tfoot');
        let modal_body = $(table).closest('div.modal-body');

        let total_sekat = 0;
        let total_jumlah = 0;
        let total_bb = 0;
        let bb_rata2_sekat = 0;

        $.map( $(tbody).find('tr'), function(tr) {
            let jml = numeral.unformat( $(tr).find('input.jumlah').val() );
            let bb = numeral.unformat( $(tr).find('input.bb').val() );

            total_jumlah += jml;
            total_bb += bb;
        });

        $(tfoot).find('td.tot_jumlah b').html( numeral.formatInt(total_jumlah) );
        $(tfoot).find('td.tot_bb b').html( numeral.formatDec(total_bb) );

        bb_rata2_sekat = numeral.unformat( $(tfoot).find('td.tot_bb b').text() ) / $(tbody).find('tr').length;
        total_sekat = numeral.unformat( $(tfoot).find('td.tot_jumlah b').text() ) * bb_rata2_sekat;

        $(modal_body).find('input.tot_sekat').val( numeral.formatDec(total_sekat) );
        $(modal_body).find('input.bb_rata2').val( numeral.formatDec(bb_rata2_sekat) );
    }, // end - hitung_total

    save: function(elm) {
        let err = 0;
        let modal_body = $(elm).closest('div.modal-body');

        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data konfirmasi panen.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let tgl_docin = dateSQL( $(modal_body).find('#tgl_docin').data('DateTimePicker').date() );
                    let tgl_panen = dateSQL( $(modal_body).find('#tgl_panen').data('DateTimePicker').date() );
                    let noreg = $(modal_body).find('input.noreg').val();
                    let populasi = numeral.unformat( $(modal_body).find('input.populasi').val() );
                    let bb_rata2 = numeral.unformat( $(modal_body).find('input.bb_rata2').val() );
                    let tot_sekat = numeral.unformat( $(modal_body).find('input.tot_sekat').val() );

                    let data_sekat = $.map( $(modal_body).find('table.data_sekat tbody tr'), function(tr) {
                        let _data = {
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'bb': numeral.unformat( $(tr).find('input.bb').val() ),
                        };

                        return _data;
                    });

                    let data = {
                        'tgl_docin': tgl_docin,
                        'tgl_panen': tgl_panen,
                        'noreg': noreg,
                        'populasi': populasi,
                        'bb_rata2': bb_rata2,
                        'tot_sekat': tot_sekat,
                        'data_sekat': data_sekat
                    };

                    // console.log( data );
                    kp.exec_save( data );
                }
            });
        }
    }, // end - save

    exec_save: function( params ) {
        $.ajax({
            url : 'transaksi/KonfirmasiPanen/save',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert( data.message, function() {
                        kp.get_lists();
                        bootbox.hideAll();
                    });
                } else {
                    bootbox.alert( data.message );
                }
            },
        });
    }, // end - exec_save

    update: function(elm) {
        let err = 0;
        let modal_body = $(elm).closest('div.modal-body');

        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data konfirmasi panen.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin mengubah data ?', function(result) {
                if ( result ) {
                    let id = $(elm).data('id');
                    let tgl_docin = dateSQL( $(modal_body).find('#tgl_docin').data('DateTimePicker').date() );
                    let tgl_panen = dateSQL( $(modal_body).find('#tgl_panen').data('DateTimePicker').date() );
                    let noreg = $(modal_body).find('input.noreg').val();
                    let populasi = numeral.unformat( $(modal_body).find('input.populasi').val() );
                    let bb_rata2 = numeral.unformat( $(modal_body).find('input.bb_rata2').val() );
                    let tot_sekat = numeral.unformat( $(modal_body).find('input.tot_sekat').val() );

                    let data_sekat = $.map( $(modal_body).find('table.edit tbody tr'), function(tr) {
                        let _data = {
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'bb': numeral.unformat( $(tr).find('input.bb').val() ),
                        };

                        return _data;
                    });

                    let data = {
                        'id': id,
                        'tgl_docin': tgl_docin,
                        'tgl_panen': tgl_panen,
                        'noreg': noreg,
                        'populasi': populasi,
                        'bb_rata2': bb_rata2,
                        'tot_sekat': tot_sekat,
                        'data_sekat': data_sekat
                    };

                    // console.log( data );
                    kp.exec_update( data );
                }
            });
        }
    }, // end - update

    exec_update: function( params ) {
        $.ajax({
            url : 'transaksi/KonfirmasiPanen/update',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert( data.message, function() {
                        kp.get_lists();
                        bootbox.hideAll();
                    });
                } else {
                    bootbox.alert( data.message );
                }
            },
        });
    }, // end - exec_update

    delete: function(elm) {
        let err = 0;
        let modal_body = $(elm).closest('div.modal-body');

        bootbox.confirm( 'Apakah anda yakin ingin menghapus data ?', function(result) {
            if ( result ) {
                let id = $(elm).data('id');

                $.ajax({
                    url : 'transaksi/KonfirmasiPanen/delete',
                    data : {
                        'id' :  id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                kp.get_lists();
                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert( data.message );
                        }
                    },
                });
            }
        });
    }, // end - delete
};

kp.start_up();