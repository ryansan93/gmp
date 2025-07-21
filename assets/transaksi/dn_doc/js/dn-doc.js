var dn = {
	startUp: function() {
        dn.settingUp();
	}, // end - startUp

	setSelect2NoSj: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/DnDoc/getNoSj',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        supplier: $('div#action').find('select.supplier').select2().val()
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
            placeholder: 'Search for a SJ...',
            // minimumInputLength: 2,
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                return data.text;
            },
        });
    }, // end - setSelect2NoSj

	setSelect2DetJurnalTrans: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/DnDoc/getDetJurnalTrans',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        jurnal_trans: $('div#action').find('select.jurnal_trans').select2().val()
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
            placeholder: 'Search for a Transaksi ...',
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

				var asal = !empty(data.asal) ? data.asal : (!empty(dataset) ? dataset.asal : null);
                var coa_asal = !empty(data.coa_asal) ? data.coa_asal : (!empty(dataset) ? dataset.coa_asal : null);
                var tujuan = !empty(data.tujuan) ? data.tujuan : (!empty(dataset) ? dataset.tujuan : null);
                var coa_tujuan = !empty(data.coa_tujuan) ? data.coa_tujuan : (!empty(dataset) ? dataset.coa_tujuan : null);

				var ket_asal = '-';
				if ( !empty(asal) && !empty(coa_asal) ) {
					var ket_asal = coa_asal+' | '+asal;
				}

				var ket_tujuan = '-';
				if ( !empty(tujuan) && !empty(coa_tujuan) ) {
					var ket_tujuan = coa_tujuan+' | '+tujuan;
				}

				$('div#action').find('.asal').text(ket_asal);
				$('div#action').find('.tujuan').text(ket_tujuan);

                return data.text;
            },
        });
    }, // end - setSelect2DetJurnalTrans

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

		$('.jurnal_trans').select2();
		$('.supplier').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$(document).ready(function () {
            dn.setSelect2NoSj( $('.no_sj') );
            dn.setSelect2DetJurnalTrans( $('.det_jurnal_trans') );
        });
    }, // end - settingUp

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

        $(tr_clone).find('input, textarea, select').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_clone) );

        dn.setSelect2NoSj( $(tbody).find('select.no_sj') );
    }, // end - addRow

    removeRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(tr).remove();
        }

        dn.hitTot();
    }, // end - addRow

	hitTot: function() {
		var tot_dn = 0;
		$.map( $('div#action').find('table tbody tr'), function(tr) {
			var nominal = numeral.unformat( $(tr).find('.nominal').val() );
			tot_dn += nominal;
		});

		$('div#action').find('.tot_dn').val( numeral.formatDec( tot_dn ) );
	}, // end - hitTot

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
                'supplier': $(div).find('.supplier').select2().val()
            };

            $.ajax({
                url: 'transaksi/DnDoc/getLists',
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
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		dn.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/DnDoc/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                dn.settingUp();

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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var _detail = {
							'no_sj': $(tr).find('.no_sj').select2().val(),
							'ket': $(tr).find('.ket').val(),
							'nominal': numeral.unformat( $(tr).find('.nominal').val() )
						};

						return _detail;
					});

					var params = {
						'tgl_dn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_dn': $(div).find('.ket_dn').val(),
						'tot_dn': numeral.unformat($(div).find('.tot_dn').val()),
						'det_jurnal_trans': $(div).find('.det_jurnal_trans').select2('val'),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/DnDoc/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
							hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
			            			dn.loadForm(data.content.id, null, 'action');
									dn.getLists();
			            		});
			            	} else{
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function (result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var _detail = {
							'no_sj': $(tr).find('.no_sj').select2().val(),
							'ket': $(tr).find('.ket').val(),
							'nominal': numeral.unformat( $(tr).find('.nominal').val() )
						};

						return _detail;
					});

					var params = {
						'id': $(elm).attr('data-id'),
						'tgl_dn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_dn': $(div).find('.ket_dn').val(),
						'tot_dn': numeral.unformat($(div).find('.tot_dn').val()),
						'det_jurnal_trans': $(div).find('.det_jurnal_trans').select2('val'),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/DnDoc/edit',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
							hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
									dn.loadForm(data.content.id, null, 'action');
									dn.getLists();
			            		});
			            	} else{
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
		}
	}, // end - edit

	delete: function(elm) {
		var div = $('#action');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
			if ( result ) {
				var params = {
					'id': $(elm).attr('data-id'),
				};

				$.ajax({
					url: 'transaksi/DnDoc/delete',
					data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
					beforeSend: function(){ showLoading() },
					success: function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function() {
								dn.loadForm(null, null, 'action');
								dn.getLists();
							});
						} else{
							bootbox.alert(data.message);
						}
					}
				});
			}
		});
	}, // end - delete
};

dn.startUp();