var pp = {
	start_up: function() {
		pp.setting_up('riwayat', 'div#riwayat');
		pp.setting_up('transaksi', 'div#transaksi');
	}, // end - start_up

	list_riwayat: function(elm) {
		var div_riwayat = $(elm).closest('div#riwayat');

		var mitra = $(div_riwayat).find('#select_mitra').val();

		var params = {
			'mitra': mitra
		};

		$.ajax({
            url: 'transaksi/PenjualanPeralatan/list_riwayat',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                hideLoading();

                $('table.tbl_riwayat').find('tbody').html( data.html );
            }
        });
	}, // end - list_riwayat

	change_tab: function(elm) {
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		pp.load_form(id, edit, href);
	}, // end - change_tab

	load_form: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/PenjualanPeralatan/load_form',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

                pp.setting_up('transaksi', 'div#transaksi');

                hideLoading();
            }
        });
	}, // end - list_riwayat

	setting_up: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();

		$(div).find('.select_peralatan').select2();

        $(div).find('span.select2').css({'width': '100%'});
        $(div).find('span.select2 span.select2-selection.select2-selection--single').css({'height': '34px', 'padding': '6px 12px'});
        $(div).find('.select2-container--default .select2-selection--single .select2-selection__rendered').css({'line-height': 'unset'});
        $(div).find('.select2-container--default .select2-selection--single .select2-selection__arrow').css({'height': '34px'});
        $(div).find('.select2-container .select2-selection--single .select2-selection__rendered').css({'padding': '0px'});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('[name=tanggal]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
                $(ipt).data("DateTimePicker").minDate(moment(tgl));
                $(ipt).data("DateTimePicker").maxDate(moment(new Date()));
            }
        });
	}, // end - setting_up

	showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

	add_row: function(elm) {
        var tbody = $(elm).closest('tbody');
        var tr = $(elm).closest('tr');

        // NOTE: FOR SELECT2
        $(tr).find('select.select_peralatan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr).find('select.select_peralatan option').removeAttr('data-select2-id');

        var tr_clone = $(tr).clone();

        $(tr_clone).find('input').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tr_clone).find('select:not(select.supplier) option.empty').attr('selected', 'selected');

        $(tr).closest('tbody').append(tr_clone);

        var no_urut = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            no_urut++;
            $(tr).find('td.no_urut').text( no_urut );
        });

        $('.select_peralatan').select2();
        $('.select_peralatan').next('span.select2').css('width', '100%');
    }, // end - add_row

    remove_row: function(elm) {
        var tbody = $(elm).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(elm).closest('tr').remove();
        }

        pp.hit_total( elm );
    }, // end - remove_row

    hit_total: function() {
        var div = $('div#transaksi');

        var tbody = $(div).find('table.data_brg tbody');

        // var total_bayar = numeral.unformat( $(div).find('input.bayar').val() );
        var total_jual = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            var jumlah = numeral.unformat( $(tr).find('input.jumlah').val() );
            var harga = numeral.unformat( $(tr).find('input.harga').val() );

            var _total = jumlah * harga;

            total_jual += _total;

            $(tr).find('td.sub_total').text( numeral.formatDec(_total) );
        });

        $(div).find('input.total').val( numeral.formatDec(total_jual) );

        // var sisa_bayar = (total_bayar < total_jual) ? total_jual - total_bayar : 0;
        // $(div).find('input.sisa_bayar').val( numeral.formatDec(sisa_bayar) );
    }, // end - hit_total

	save: function(elm) {
        let err = 0;
        let div = $(elm).closest('div#transaksi');

        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan pakan.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let data_brg = $.map( $(div).find('table.data_brg tbody tr'), function(tr) {
                        let _data = {
                            'kode_brg': $(tr).find('.select_peralatan').val(),
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'harga': numeral.unformat( $(tr).find('input.harga').val() ),
                            'total': numeral.unformat( $(tr).find('td.sub_total').text() )
                        };

	                    return _data;
                    });

                    let data = {
                        'mitra': $(div).find('select#select_mitra').val(),
                        'tanggal': dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() ),
                        'total': numeral.unformat( $(div).find('input.total').val() ),
                        // 'bayar': numeral.unformat( $(div).find('input.bayar').val() ),
                        // 'sisa_bayar': numeral.unformat( $(div).find('input.sisa_bayar').val() ),
                        'data_brg': data_brg
                    };

                    $.ajax({
			            url : 'transaksi/PenjualanPeralatan/save',
			            dataType: 'JSON',
			            type: 'POST',
			            data: {'params': data},
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert( data.message, function() {
			                        pp.load_form(data.content.id, null, 'transaksi');
			                        // location.reload();
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
        let err = 0;
        let div = $(elm).closest('div#transaksi');

        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan pakan.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let data_brg = $.map( $(div).find('table.data_brg tbody tr'), function(tr) {
                        let _data = {
                            'kode_brg': $(tr).find('.select_peralatan').val(),
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'harga': numeral.unformat( $(tr).find('input.harga').val() ),
                            'total': numeral.unformat( $(tr).find('td.sub_total').text() )
                        };

                        return _data;
                    });

                    let data = {
                        'id': $(elm).data('id'),
                        'mitra': $(div).find('select#select_mitra').val(),
                        'tanggal': dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() ),
                        'total': numeral.unformat( $(div).find('input.total').val() ),
                        // 'bayar': numeral.unformat( $(div).find('input.bayar').val() ),
                        // 'sisa_bayar': numeral.unformat( $(div).find('input.sisa_bayar').val() ),
                        'data_brg': data_brg
                    };

                    $.ajax({
                        url : 'transaksi/PenjualanPeralatan/edit',
                        dataType: 'JSON',
                        type: 'POST',
                        data: {'params': data},
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    pp.load_form(data.content.id, null, 'transaksi');
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

	batal_edit: function(elm) {
		var id = $(elm).data('id');
		pp.load_form(id, null, 'transaksi');
	}, // end - batal_edit

	delete: function(elm) {
    	var div = $('div#transaksi');

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data penjualan peralatan ?', function(result) {
			if ( result ) {
				var data = {
					'id': $(elm).data('id')
				};

				$.ajax({
		            url: 'transaksi/PenjualanPeralatan/delete',
		            data: { 'params': data },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		var div_riwayat = $('div#riwayat');
		                		if ( !empty($(div_riwayat).find('select#select_mitra').val()) ) {
		                			$('button.tampilkan_riwayat').click();
		                		}

		                		pp.load_form(null, null, 'transaksi');
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

pp.start_up();