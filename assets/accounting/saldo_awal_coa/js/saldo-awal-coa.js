var sa = {
	startUp: function () {
		sa.settingUp();
	}, // end - startUp

	settingUp: function() {
        $("#TglSa").datetimepicker({
            locale: 'id',
            format: 'MMM Y',
            useCurrent: false
        }).on('dp.change', function(e){
            var tgl = dateSQL($('#TglSa').data('DateTimePicker').date());
            sa.getDataByPeriode( tgl );
        });
        if ( !empty($("#TglSa").find('input').data('tgl')) ) {
            var tgl = $("#TglSa").find('input').data('tgl');
            $("#TglSa").data('DateTimePicker').date( moment(new Date((tgl))) );
        }
        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
    }, // end - settingUp

    getDataByPeriode: function(periode) {
        var params = {
            'periode': periode
        };

        $.ajax({
            url : 'accounting/SaldoAwalCoa/getDataByPeriode',
            data : {
                'params' : params
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading('Cek data saldo awal coa . . .'); },
            success : function(html){
                hideLoading();
                $('div.form_data').html( html );
                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            },
        });
    }, // end - getDataByPeriode

    editForm: function() {
        $('input.edit, select.edit, button.edit').removeAttr('disabled');
        $('div.update_delete').addClass('hide');
        $('div.edit').removeClass('hide');
    }, // end - editForm

    bataEditForm: function() {
        $('input.edit, select.edit, button.edit').attr('disabled', 'disabled');
        $('div.update_delete').removeClass('hide');
        $('div.edit').addClass('hide');
    }, // end - bataEditForm

    addRow: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var tr_clone = $(tr).clone();

		$(tr_clone).find('input.nominal, select').val('');

		$(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		// $(tr).closest('tbody').append(tr_clone);
		$(tr).after(tr_clone);
	}, // end - add_row

	removeRow: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr').length > 1 ) {
			$(elm).closest('tr').remove();
		}
	}, // end - remove_row

    save: function() {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    var data = $.map( $('table tbody tr'), function(tr) {
                        var _data = {
                            'periode': dateSQL($('#TglSa').data('DateTimePicker').date()),
                            'no_coa': $(tr).find('input.no_coa').val(),
                            'nama_coa': $(tr).find('input.nama_coa').val(),
                            'unit': $(tr).find('select.unit').val(),
                            'nominal': numeral.unformat($(tr).find('input.nominal').val())
                        };

                        return _data;
                    });

                    var params = {
                        'data': data
                    };

                    $.ajax({
                        url : 'accounting/SaldoAwalCoa/save',
                        data : {
                            'params' : params
                        },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading(); },
                        success : function(data){
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    location.reload();
                                });
                            } else {
                                bootbox.alert( data.message );
                            }
                        },
                    });
                }
            });
        }
    }, // end - save

    edit: function(elm) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
                if ( result ) {
                    var data = $.map( $('table tbody tr'), function(tr) {
                        var _data = {
                            'periode': dateSQL($('#TglSa').data('DateTimePicker').date()),
                            'no_coa': $(tr).find('input.no_coa').val(),
                            'nama_coa': $(tr).find('input.nama_coa').val(),
                            'unit': $(tr).find('select.unit').val(),
                            'nominal': numeral.unformat($(tr).find('input.nominal').val())
                        };

                        return _data;
                    });

                    var params = {
                        'data': data
                    };

                    $.ajax({
                        url : 'accounting/SaldoAwalCoa/edit',
                        data : {
                            'params' : params
                        },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading(); },
                        success : function(data){
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    location.reload();
                                });
                            } else {
                                bootbox.alert( data.message );
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
                var params = {
                    'periode': dateSQL($('#TglSa').data('DateTimePicker').date())
                };

                $.ajax({
                    url : 'accounting/SaldoAwalCoa/delete',
                    data : {
                        'params' : params
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                location.reload();
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

sa.startUp();