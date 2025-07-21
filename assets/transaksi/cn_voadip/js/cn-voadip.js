var cn = {
	startUp: function() {
        cn.settingUp();
	}, // end - startUp

	setSelect2NoSj: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'transaksi/CnVoadip/getNoSj',
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

		$('.supplier').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$(document).ready(function () {
            cn.setSelect2NoSj( $('.no_sj') );
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

        cn.setSelect2NoSj( $(tbody).find('select.no_sj') );
    }, // end - addRow

    removeRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(tr).remove();
        }

        cn.hitTot();
    }, // end - addRow

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
                'supplier': $(div).find('.supplier').select2().val()
            };

            $.ajax({
                url: 'transaksi/CnVoadip/getLists',
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
            url: 'transaksi/CnVoadip/loadForm',
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
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var _detail = {
							'no_sj': $(tr).find('.no_sj').select2().val(),
							'ket': $(tr).find('.ket').val(),
							'nominal': numeral.unformat( $(tr).find('.nominal').val() )
						};

						return _detail;
					});

					var params = {
						'tgl_cn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_cn': $(div).find('.ket_cn').val(),
						'tot_cn': numeral.unformat($(div).find('.tot_cn').val()),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/CnVoadip/save',
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
						'tgl_cn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_cn': $(div).find('.ket_cn').val(),
						'tot_cn': numeral.unformat($(div).find('.tot_cn').val()),
						'detail': detail
					};

					$.ajax({
			            url: 'transaksi/CnVoadip/edit',
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
					url: 'transaksi/CnVoadip/delete',
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