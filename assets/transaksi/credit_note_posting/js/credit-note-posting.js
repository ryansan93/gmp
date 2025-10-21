var cn = {
	startUp: function() {
        cn.settingUp();
	}, // end - startUp

    setSelect2Cn: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CreditNotePosting/getCn',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
						jenis_cn: $('div#action').find('select.jenis_cn').select2().val(),
						id: $('div#action').find('select.cn').attr('data-kode')
                    }
    
                    // Query parameters will be ?search=[term]&type=user_search
                    return query;
                },
                processResults: function (data) {
					// $('li.select2-results__option').attr('aria-selected', false);

                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    // console.log(error + ": " + jqXHR.responseText);
                    return { results: [] }; // Return dataset to load after error
                }
            },
            cache: true,
            placeholder: 'Search for a CN ...',
            // minimumInputLength: 2,
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                var dataset = null;
                if ( typeof data.element !== 'undefined' ) {
                    if ( typeof data.element.dataset !== 'undefined' ) {
                        dataset = data.element.dataset;
                    }
                }

                var tot_cn = !empty(data.tot_cn) ? data.tot_cn : (!empty(dataset) ? dataset.totcn : null);

                $(data.element).attr('data-totcn', data.tot_cn);

                $('.nilai_cn').val(numeral.formatDec(tot_cn));

                return data.text;
            },
        });
    }, // end - setSelect2Cn

    setSelect2NoSj: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CreditNotePosting/getSj',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
						jenis_cn: $('div#action').find('select.jenis_cn').select2().val(),
                        id: $('div#action').find('select.cn').attr('data-kode')
                    }
    
                    // Query parameters will be ?search=[term]&type=user_search
                    return query;
                },
                processResults: function (data) {
					// $('li.select2-results__option').attr('aria-selected', false);

                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    // console.log(error + ": " + jqXHR.responseText);
                    return { results: [] }; // Return dataset to load after error
                }
            },
            cache: true,
            placeholder: 'Search for a SJ ...',
            // minimumInputLength: 2,
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"' data-tagihan='"+data.tagihan+"' data-sisatagihan='"+data.sisa_tagihan+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                var _tr = $(data.element).closest('tr');
                
                var dataset = null;
                if ( typeof data.element !== 'undefined' ) {
                    if ( typeof data.element.dataset !== 'undefined' ) {
                        dataset = data.element.dataset;
                    }
                }

                var tagihan = !empty(data.tagihan) ? data.tagihan : (!empty(dataset) ? dataset.tagihan : null);
                var sisa_tagihan = !empty(data.sisa_tagihan) ? data.sisa_tagihan : (!empty(dataset) ? dataset.sisatagihan : null);

                $(data.element).attr('data-tagihan', data.tagihan);
                $(data.element).attr('data-sisatagihan', data.sisa_tagihan);

                $(_tr).find('.tagihan').text(numeral.formatDec(tagihan));
                $(_tr).find('.sisa').text(numeral.formatDec(sisa_tagihan));

                return data.text;
            },
        });
    }, // end - setSelect2NoSj

    samakanSisaTagihan: function(elm) {
        var _tr = $(elm).closest('tr');

        var sisa_tagihan = numeral.unformat($(_tr).find('td.sisa').text());

        $(_tr).find('.pakai').val( numeral.formatDec(sisa_tagihan) );

        cn.hitTotalPakai();
    }, // end - samakanSisaTagihan

    hitTotalPakai: function() {
        var total_pakai = 0;
        $.map( $('div#action').find('table tbody tr'), function(tr) {
            var pakai = numeral.unformat($(tr).find('input.pakai').val());

            total_pakai += pakai;
        });

        $('div#action').find('input.pakai_cn').val( numeral.formatDec(total_pakai) );
    }, // end - hitTotalPakai

    addRow: function (elm) {
        var tr = $(elm).closest('tr');

        var tbody = $(tr).closest('tbody');

        $(tr).find('select.no_sj').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr).find('select.no_sj option').removeAttr('data-select2-id');

        var tr_clone = $(tr).clone();

        $(tr_clone).find('input, select').val('');
        $(tr_clone).find('td.tagihan, td.sisa').text(0);

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_clone) );

        cn.setSelect2NoSj( $(tbody).find('.no_sj') );
    }, // end - addRow

    removeRow: function (elm) {
        var tr_head = $(elm).closest('tr.head');

        var tbody = $(tr_head).closest('tbody');

        if ( $(tbody).find('tr.head').length > 1 ) {
            $(tr_head).remove();
        }

        cn.hitTotalPakai();
    }, // end - addRow

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

		$('#riwayat').find('.jenis_cn').select2().select2();
		$('#action').find('.jenis_cn').select2().select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(document).ready(function () {
            cn.setSelect2Cn( $('.cn') );
            cn.setSelect2NoSj( $('.no_sj') );
        });

        App.setTutupBulan();
    }, // end - settingUp

    getLists: function() {
        var div = $('#riwayat');
		var dcontent = $(div).find('.tbl_riwayat tbody');

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
                'jenis_cn': $(div).find('.jenis_cn').select2().val()
            };

            $.ajax({
                url: 'transaksi/CreditNotePosting/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ App.showLoaderInContent( $(dcontent) ) },
                success: function(html){
					App.hideLoaderInContent( $(dcontent), html );
                }
            });
        }
    }, // end - getLists

	changeTabActive: function(elm) {
		var id = $(elm).data('kode');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		cn.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/CreditNotePosting/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                cn.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

	save: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
            var tot_pakai_cn = numeral.unformat($(div).find('.pakai_cn').val());
            var tot_cn = numeral.unformat($(div).find('.nilai_cn').val());

            if ( tot_pakai_cn > tot_cn ) {
                bootbox.alert('Total pemakaian CN yang anda post melebihi nilai CN. Harap cek kembali data yang anda masukkan.');
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
                    if ( result ) {
                        var detail = $.map( $(div).find('table tbody tr'), function(tr) {
                            var _detail = {
                                'nomor': $(tr).find('.no_sj').select2().val(),
                                'pakai': numeral.unformat($(tr).find('.pakai').val()),
                            };

                            return _detail;
                        });
    
                        var params = {
                            'tanggal': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
                            'jenis_cn': $(div).find('.jenis_cn').select2('val'),
                            'no_cn': $(div).find('.cn').select2('val'),
                            'tot_pakai': numeral.unformat($(div).find('.pakai_cn').val()),
                            'detail': detail
                        };
            
                        $.ajax({
                            url : 'transaksi/CreditNotePosting/save',
                            data: { 'params': params },
                            type: 'POST',
                            dataType: 'JSON',
                            beforeSend : function(){ showLoading() },
                            success : function(data){
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert(data.message, function() {
                                        cn.loadForm(data.content.id, null, 'action');
                                        cn.getLists();
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                }
                            }
                        });
                    }
                });
            }
		}
	}, // end - save

    edit: function(elm) {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
            var tot_pakai_cn = numeral.unformat($(div).find('.pakai_cn').val());
            var tot_cn = numeral.unformat($(div).find('.nilai_cn').val());

            if ( tot_pakai_cn > tot_cn ) {
                bootbox.alert('Total pemakaian CN yang anda post melebihi nilai CN. Harap cek kembali data yang anda masukkan.');
            } else {
                bootbox.confirm('Apakah anda yakin ingin meng-edit data ?', function (result) {
                    if ( result ) {
                        var detail = $.map( $(div).find('table tbody tr'), function(tr) {
                            var _detail = {
                                'nomor': $(tr).find('.no_sj').select2().val(),
                                'pakai': numeral.unformat($(tr).find('.pakai').val()),
                            };

                            return _detail;
                        });
    
                        var params = {
                            'id': $(elm).attr('data-kode'),
                            'tanggal': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
                            'jenis_cn': $(div).find('.jenis_cn').select2('val'),
                            'no_cn': $(div).find('.cn').select2('val'),
                            'tot_pakai': numeral.unformat($(div).find('.pakai_cn').val()),
                            'detail': detail
                        };
                        
                        $.ajax({
                            url : 'transaksi/CreditNotePosting/edit',
                            data: { 'params': params },
                            type: 'POST',
                            dataType: 'JSON',
                            beforeSend : function(){ showLoading() },
                            success : function(data){
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert(data.message, function() {
                                        cn.loadForm(data.content.id, null, 'action');
                                        cn.getLists();
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                }
                            }
                        });
                    }
                });
            }
		}
	}, // end - edit

    delete: function(elm) {
		var div = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-kode')
                };
                
                $.ajax({
                    url : 'transaksi/CreditNotePosting/delete',
                    data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                cn.loadForm(null, null, 'action');
                                cn.getLists();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
	}, // end - delete
};

cn.startUp();