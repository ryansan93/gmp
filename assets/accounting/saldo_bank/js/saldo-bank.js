var sb = {
	start_up: function () {
		sb.setting_up();

        if ( !empty($("#StartDate").find('input').data('tgl')) && empty($("#StartDate").find('input').val()) ) {
            var tgl = $("#StartDate").find('input').data('tgl');
            $("#StartDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        if ( !empty($("#EndDate").find('input').data('tgl')) && empty($("#EndDate").find('input').val()) ) {
            var tgl = $("#EndDate").find('input').data('tgl');
            $("#EndDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        sb.getLists();
	}, // end - start_up

	setting_up: function() {
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate, #EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });

        $("#Tanggal").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
        });
        $.map( $("#Tanggal"), function(div) {
            if ( !empty($(div).find('input').data('tgl')) ) {
                var tgl = $(div).find('input').data('tgl');
                $(div).data('DateTimePicker').date( moment(new Date((tgl))) );
            }
        });
        App.setTutupBulan();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal],[data-tipe=decimal3],[data-tipe=decimal4],[data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - setting_up

    hitGrandTotal: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        var grand_total = 0;
        
        $.map( $(tbody).find('tr'), function (tr) {
            var ipt = $(tr).find('input.saldo');
            var nilai = parseFloat(numeral.unformat( $(ipt).val() ));

            grand_total += nilai;
        });

        $('div.nilai input').val( numeral.format(grand_total) );
    }, // end - hitGrandTotal    

	changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var edit = $(elm).data('edit');
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
            var tanggal = $(elm).attr('data-tanggal');

            sb.loadForm(tanggal, edit);
        };
    }, // end - changeTabActive

    loadForm: function(tanggal = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'accounting/SaldoBank/loadForm',
            data : {
                'tanggal' :  tanggal,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
                hideLoading();
                $(dcontent).html(html);
                sb.setting_up();
            },
        });
    }, // end - loadForm

	getLists: function() {
        var dcontent = $('div#riwayat');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
            var tbody = $(dcontent).find('table.tbl_riwayat tbody');

            var params = {
                'start_date': dateSQL( $(dcontent).find('#StartDate').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(dcontent).find('#EndDate').data('DateTimePicker').date() )
            };

            $.ajax({
                url : 'accounting/SaldoBank/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(tbody) ); },
                success : function(html){
                    App.hideLoaderInContent( $(tbody), html );
                },
            });
        }
    }, // end - getLists

    cekData: function(callback) {
        var dcontent = $('#action');
        var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

        var return_status = 0;
        var return_keterangan_error = null;

		if ( err > 0 ) {
			return_keterangan_error = 'Harap lengkapi data terlebih dahulu.';
		} else {
            var return_status = 1;

            showLoading('Cek data saldo . . .');

            var params = {
                'tanggal_old': $(dcontent).find('#Tanggal input').attr('data-tglold'),
                'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() )
            };

            $.ajax({
                url: 'accounting/SaldoBank/cekDataSaldo',
                dataType: 'json',
                type: 'post',
                data: {
                    'params': params
                },
                beforeSend: function() {},
                success: function(data) {
                    hideLoading();
                    if ( data.status == 1 ) {
                        if ( data.content.status == 1 ) {
                            return_status = 0;
                            return_keterangan_error = data.message;
                        }
                    } else {
                        return_status = 0;
                        return_keterangan_error = data.message;
                    };

                    callback({'status': return_status, 'keterangan': return_keterangan_error});
                },
            });
        }

    }, // end - cekData

	save: function() {
		var dcontent = $('#action');
        
        sb.cekData(function(data) {
            var status = data.status;
            var keterangan = data.keterangan;

            if ( status == 0 ) {
                bootbox.alert( keterangan );
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data saldo bank ?' , function(result) {
                    if ( result ) {
                        showLoading('Proses simpan data saldo bank . . .');
    
                        var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                            var _detail = {
                                'coa': $(tr).find('td.no_coa').text(),
                                'saldo': numeral.unformat($(tr).find('input.saldo').val())
                            };
    
                            return _detail;
                        });
    
                        var data = {
                            'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() ),
                            'detail': detail
                        };
    
                        $.ajax({
                            url: 'accounting/SaldoBank/save',
                            dataType: 'json',
                            type: 'post',
                            data: {
                                'params': data
                            },
                            beforeSend: function() {},
                            success: function(data) {
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert( data.message, function () {
                                        sb.loadForm( data.content.tanggal );
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                };
                            },
                        });
                    }
                });
            }
        });
	}, // end - save

    edit: function(elm) {
        var dcontent = $('#action');
        
        sb.cekData(function(data) {
            var status = data.status;
            var keterangan = data.keterangan;

            if ( status == 0 ) {
                bootbox.alert( keterangan );
            } else {
                bootbox.confirm('Apakah anda yakin ingin meng-ubah data saldo bank ?' , function(result) {
                    if ( result ) {
                        showLoading('Proses simpan data saldo bank . . .');
    
                        var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                            var _detail = {
                                'coa': $(tr).find('td.no_coa').text(),
                                'saldo': numeral.unformat($(tr).find('input.saldo').val())
                            };
    
                            return _detail;
                        });
    
                        var data = {
                            'tanggal_old': $(dcontent).find('#Tanggal input').attr('data-tglold'),
                            'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() ),
                            'detail': detail
                        };
    
                        $.ajax({
                            url: 'accounting/SaldoBank/edit',
                            dataType: 'json',
                            type: 'post',
                            data: {
                                'params': data
                            },
                            beforeSend: function() {},
                            success: function(data) {
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert( data.message, function () {
                                        sb.loadForm( data.content.tanggal );
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                };
                            },
                        });
                    }
                });
            }
        });
    }, // end - edit

    delete: function(elm) {
        var dcontent = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if ( result ) {
                showLoading();

                var params = {
                    'tanggal': $(elm).attr('data-tanggal')
                };

                $.ajax({
                    url: 'accounting/SaldoBank/delete',
                    dataType: 'json',
                    type: 'post',
                    data: {
                        'params': params
                    },
                    beforeSend: function() {},
                    success: function(data) {
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function () {
                                sb.getLists();
                                sb.loadForm();
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    },
                });
            }
        });
    }, // end - delete
};

sb.start_up();