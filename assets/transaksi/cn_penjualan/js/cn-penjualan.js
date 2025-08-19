var cn = {
	startUp: function() {
        cn.settingUp();
	}, // end - startUp

	setSelect2NoSj: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CnPenjualan/getNoSj',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        pelanggan: $('div#action').find('select.pelanggan').select2().val(),
                        mitra: $('div#action').find('select.mitra').select2().val(),
						jenis_cn: $('div#action').find('select.jurnal_trans').select2().val()
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
            placeholder: 'Search for a No. SJ ...',
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

	setSelect2Barang: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CnPenjualan/getBarang',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        jenis_cn: $('div#action').find('select.jurnal_trans').select2().val()
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
            placeholder: 'Search for a Barang ...',
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
    }, // end - setSelect2Barang

	setSelect2DetJurnalTrans: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CnPenjualan/getDetJurnalTrans',
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
				var _tr = $(data.element).closest('tr');

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

				$(data.element).attr('data-asal', asal);
                $(data.element).attr('data-coa_asal', coa_asal);
                $(data.element).attr('data-tujuan', tujuan);
                $(data.element).attr('data-coa_tujuan', coa_tujuan);

				var ket_asal = '';
				if ( !empty(asal) && !empty(coa_asal) ) {
					var ket_asal = coa_asal+' | '+asal;
				}

				var ket_tujuan = '';
				if ( !empty(tujuan) && !empty(coa_tujuan) ) {
					var ket_tujuan = coa_tujuan+' | '+tujuan;
				}

				$(_tr).find('.asal').text(ket_asal);
				$(_tr).find('.tujuan').text(ket_tujuan);

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

		// $('.jenis_cn').select2();
		$('.jurnal_trans').select2();
		$('.pelanggan').select2();
		$('.mitra').select2();
		$('div#riwayat').find('.gudang').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$(document).ready(function () {
            cn.setSelect2NoSj( $('.no_sj') );
            cn.setSelect2Barang( $('.barang') );
            // cn.setSelect2DetJurnalTrans( $('.det_jurnal_trans') );
        });
    }, // end - settingUp

	addRow: function (elm) {
        var tr_head = $(elm).closest('tr.head');
        // var tr_detail = $(tr_head).next('tr.detail');

        var tbody = $(tr_head).closest('tbody');

        $(tr_head).find('select.no_sj, select.barang').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr_head).find('select.no_sj option, select.barang option').removeAttr('data-select2-id');

		// $(tr_detail).find('select.det_jurnal_trans').select2('destroy')
        //                            .removeAttr('data-live-search')
        //                            .removeAttr('data-select2-id')
        //                            .removeAttr('aria-hidden')
        //                            .removeAttr('tabindex');
        // $(tr_detail).find('select.det_jurnal_trans option').removeAttr('data-select2-id');

        var tr_head_clone = $(tr_head).clone();
        // var tr_detail_clone = $(tr_detail).clone();

        $(tr_head_clone).find('input, textarea, select').val('');
        // $(tr_detail_clone).find('select').val('');
        // $(tr_detail_clone).find('table tbody tr:not(:first)').remove();
        // $(tr_detail_clone).find('table tbody tr:first td.asal').text('');
        // $(tr_detail_clone).find('table tbody tr:first td.tujuan').text('');

        $(tr_head_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_head_clone) );
        // $(tbody).append( $(tr_detail_clone) );

        cn.setSelect2NoSj( $(tbody).find('select.no_sj') );
        cn.setSelect2Barang( $(tbody).find('select.barang') );
        // cn.setSelect2DetJurnalTrans( $(tbody).find('.det_jurnal_trans') );
    }, // end - addRow

    removeRow: function (elm) {
        var tr_head = $(elm).closest('tr.head');
        var tr_detail = $(tr_head).next('tr.detail');

        var tbody = $(tr_head).closest('tbody');

        if ( $(tbody).find('tr.head').length > 1 ) {
            $(tr_head).remove();
            $(tr_detail).remove();
        }

        cn.hitTot();
    }, // end - addRow

	// addRowDet: function (elm) {
    //     var tr = $(elm).closest('tr');
    //     var tbody = $(tr).closest('tbody');

    //     $(tr).find('select.det_jurnal_trans').select2('destroy')
    //                                .removeAttr('data-live-search')
    //                                .removeAttr('data-select2-id')
    //                                .removeAttr('aria-hidden')
    //                                .removeAttr('tabindex');
    //     $(tr).find('select.det_jurnal_trans option').removeAttr('data-select2-id');

    //     var tr_clone = $(tr).clone();

    //     $(tr_clone).find('select').val('');
	// 	$(tr_clone).find('td.asal').text('');
    //     $(tr_clone).find('td.tujuan').text('');

    //     $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
    //         $(this).priceFormat(Config[$(this).data('tipe')]);
    //     });

    //     $(tbody).append( $(tr_clone) );

    //     cn.setSelect2DetJurnalTrans( $(tbody).find('.det_jurnal_trans') );
    // }, // end - addRow

    // removeRowDet: function (elm) {
    //     var tr = $(elm).closest('tr');
    //     var tbody = $(tr).closest('tbody');

    //     if ( $(tbody).find('tr').length > 1 ) {
    //         $(tr).remove();
    //     }
    // }, // end - addRow

	hitTot: function() {
		var tot_cn = 0;
		$.map( $('div#action').find('table tbody tr'), function(tr) {
			var nominal = numeral.unformat( $(tr).find('.nominal').val() );
			tot_cn += nominal;
		});

		$('div#action').find('.tot_cn').val( numeral.formatDec( tot_cn ) );
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
                'pelanggan': $(div).find('.pelanggan').select2().val(),
                'mitra': $(div).find('.mitra').select2().val()
            };

            $.ajax({
                url: 'transaksi/CnPenjualan/getLists',
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

		cn.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/CnPenjualan/loadForm',
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr.head'), function(tr_head) {
						// var tr_detail = $(tr_head).next('tr.detail');

						// var det_jurnal_trans = $.map( $(tr_detail).find('table tbody tr'), function(tr) {
						// 	var _det_jurnal_trans = $(tr).find('.det_jurnal_trans').select2().val();
						// 	if ( !empty(_det_jurnal_trans) ) {
						// 		return _det_jurnal_trans;
						// 	}
						// });

						var _detail = {
							'no_sj': $(tr_head).find('.no_sj').select2().val(),
							'kode_brg': $(tr_head).find('.barang').select2().val(),
							'jumlah': numeral.unformat( $(tr_head).find('.jumlah').val() ),
							'ket': $(tr_head).find('.ket').val(),
							'nominal': numeral.unformat( $(tr_head).find('.nominal').val() ),
							// 'det_jurnal_trans': det_jurnal_trans
						};

						return _detail;
					});

					var params = {
                        'jurnal_trans': $(div).find('.jurnal_trans').select2('val'),
						// 'jenis_cn': $(div).find('.jenis_cn').select2('val'),
						'tgl_cn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'pelanggan': $(div).find('.pelanggan').select2('val'),
						'mitra': $(div).find('.mitra').select2('val'),
						'ket_cn': $(div).find('.ket_cn').val(),
						'tot_cn': numeral.unformat($(div).find('.tot_cn').val()),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/CnPenjualan/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
							hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
			            			cn.loadForm(data.content.id, null, 'action');
									cn.getLists();
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
					var detail = $.map( $(div).find('table tbody tr.head'), function(tr_head) {
						// var tr_detail = $(tr_head).next('tr.detail');

						// var det_jurnal_trans = $.map( $(tr_detail).find('table tbody tr'), function(tr) {
						// 	var _det_jurnal_trans = $(tr).find('.det_jurnal_trans').select2().val();
						// 	if ( !empty(_det_jurnal_trans) ) {
						// 		return _det_jurnal_trans;
						// 	}
						// });

						var _detail = {
							'no_sj': $(tr_head).find('.no_sj').select2().val(),
							'kode_brg': $(tr_head).find('.barang').select2().val(),
							'jumlah': numeral.unformat( $(tr_head).find('.jumlah').val() ),
							'ket': $(tr_head).find('.ket').val(),
							'nominal': numeral.unformat( $(tr_head).find('.nominal').val() ),
							// 'det_jurnal_trans': det_jurnal_trans
						};

						return _detail;
					});

					var params = {
						'id': $(elm).attr('data-id'),
                        'jurnal_trans': $(div).find('.jurnal_trans').select2('val'),
						// 'jenis_cn': $(div).find('.jenis_cn').select2('val'),
						'tgl_cn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'pelanggan': $(div).find('.pelanggan').select2('val'),
						'mitra': $(div).find('.mitra').select2('val'),
						'ket_cn': $(div).find('.ket_cn').val(),
						'tot_cn': numeral.unformat($(div).find('.tot_cn').val()),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/CnPenjualan/edit',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
							hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
									cn.loadForm(data.content.id, null, 'action');
									cn.getLists();
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
					url: 'transaksi/CnPenjualan/delete',
					data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
					beforeSend: function(){ showLoading() },
					success: function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function() {
								cn.loadForm(null, null, 'action');
								cn.getLists();
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

cn.startUp();