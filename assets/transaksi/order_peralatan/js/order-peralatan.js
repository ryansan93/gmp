var op = {
	startUp: function() {
        op.settingUp();
	}, // end - startUp

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $.map( $('.date'), function(div) {
            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).data('DateTimePicker').date(new Date(tgl));
            }
        });

        $('.mitra').select2();
        $('.supplier').select2();
        $('.barang').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    getLists: function() {
        var div = $('#riwayat');

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
                'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
                'mitra': $(div).find('.mitra').select2('val'),
                'supplier': $(div).find('.supplier').select2('val')
            };

            $.ajax({
                url: 'transaksi/OrderPeralatan/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ showLoading() },
                success: function(html){
                    $(div).find('.tbl_riwayat tbody').html( html );

                    op.settingUp();

                    hideLoading();
                }
            });
        }
    }, // end - getLists

	changeTabActive: function(elm) {
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		op.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/OrderPeralatan/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                op.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

    addRow: function(elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        $(tr).find('select.barang').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr).find('select.barang option').removeAttr('data-select2-id');

        var tr_clone = $(tr).clone();

        $(tr_clone).find('input, select').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_clone) );

        $.each($(tbody).find('select.barang'), function(a) {
            $(this).select2();
        });
    }, // end - addRow

    removeRow: function(elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(tr).remove();
        }
    }, // end - addRow

    hitTotal: function(elm) {
        var tr = $(elm).closest('tr');

        var jml = numeral.unformat( $(tr).find('.jumlah').val() );
        var harga = numeral.unformat( $(tr).find('.harga').val() );

        var total = harga * jml;

        $(tr).find('.total').val( numeral.formatDec( total ) );

        op.hitGrandTotal();
    }, // end - hitTotal

    hitGrandTotal: function() {
        var grand_total = 0;

        $.map( $('.tbl_data').find('tbody tr'), function (tr) {
            var total = numeral.unformat( $(tr).find('input.total').val() );

            grand_total += parseFloat(total);
        });

        $('.grand_total').val( numeral.formatDec( grand_total ) );
    }, // end - hitGrandTotal

    save: function() {
        var div = $('#action');

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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    var detail = $.map( $(div).find('.tbl_data tbody tr'), function(tr) {
                        var _data = {
                            'kode_barang': $(tr).find('.barang').select2('val'),
                            'jumlah': numeral.unformat( $(tr).find('.jumlah').val() ),
                            'harga': numeral.unformat( $(tr).find('.harga').val() ),
                            'total': numeral.unformat( $(tr).find('.total').val() ),
                        };

                        return _data;
                    });

                    var data = {
                        'tgl_order': dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() ),
                        'mitra': $(div).find('.mitra').select2('val'),
                        'kode_unit': $(div).find('.mitra').select2().find(':selected').data('kodeunit'),
                        'supplier': $(div).find('.supplier').select2('val'),
                        'grand_total': numeral.unformat( $(div).find('.grand_total').val() ),
                        'detail': detail
                    };

                    $.ajax({
                        url : 'transaksi/OrderPeralatan/save',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            'params': data
                        },
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    op.loadForm(data.content.id, null, 'action');
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
        var div = $('#action');

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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    var detail = $.map( $(div).find('.tbl_data tbody tr'), function(tr) {
                        var _data = {
                            'kode_barang': $(tr).find('.barang').select2('val'),
                            'jumlah': numeral.unformat( $(tr).find('.jumlah').val() ),
                            'harga': numeral.unformat( $(tr).find('.harga').val() ),
                            'total': numeral.unformat( $(tr).find('.total').val() ),
                        };

                        return _data;
                    });

                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tgl_order': dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() ),
                        'mitra': $(div).find('.mitra').select2('val'),
                        'kode_unit': $(div).find('.mitra').select2().find(':selected').data('kodeunit'),
                        'supplier': $(div).find('.supplier').select2('val'),
                        'grand_total': numeral.unformat( $(div).find('.grand_total').val() ),
                        'detail': detail
                    };

                    $.ajax({
                        url : 'transaksi/OrderPeralatan/edit',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            'params': data
                        },
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    op.loadForm(data.content.id, null, 'action');
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
    	var div = $('#action');

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data order peralatan ?', function(result) {
			if ( result ) {
				var data = {
					'id': $(elm).data('id')
				};

				$.ajax({
		            url: 'transaksi/OrderPeralatan/delete',
		            data: { 'params': data },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		op.loadForm(null, null, 'action');
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            }
		        });
			}
		});
    }, // end - delete
};

op.startUp();