var aivs = {
	startUp: function() {
        aivs.settingUp();
	}, // end - startUp

	setSelect2Plasma: function(elm, all = null) {
        $(elm).select2({
            ajax: {
                url: 'transaksi/AdjustmentInVoadipSiklus/getPlasma',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        all: all
                    }

                    return query;
                },
                processResults: function (data) {
                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    return { results: [] }; // Return dataset to load after error
                }
            },
            cache: true,
            placeholder: 'Search for a Plasma ...',
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                return data.text;
            },
        });
    }, // end - setSelect2Plasma

	setSelect2Noreg: function(elm, all = null) {
        $(elm).select2({
            ajax: {
                url: 'transaksi/AdjustmentInVoadipSiklus/getNoreg',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
                        mitra: $('#action').find('.mitra').val()
                    }

                    return query;
                },
                processResults: function (data) {
                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    return { results: [] }; // Return dataset to load after error
                }
            },
            cache: true,
            placeholder: 'Search for a Noreg ...',
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                return data.text;
            },
        });
    }, // end - setSelect2Noreg

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

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$(document).ready(function () {
            aivs.setSelect2Plasma( $('#riwayat').find('.mitra'), 1 );
            aivs.setSelect2Plasma( $('#action').find('.mitra') );
            aivs.setSelect2Noreg( $('#action').find('.noreg') );

            // reset Noreg tiap kali Plasma berubah, biar tidak ada filter mitra yang basi
            $('#action').find('.mitra').on('change', function() {
                $('#action').find('.noreg').val(null).trigger('change');
            });
        });

        $('#action').find('.barang').select2();
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
                'mitra': $(div).find('.mitra').select2('val')
            };

            $.ajax({
                url: 'transaksi/AdjustmentInVoadipSiklus/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ showLoading() },
                success: function(html){
                    $(div).find('.tbl_riwayat tbody').html( html );

                    aivs.settingUp();

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

		aivs.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/AdjustmentInVoadipSiklus/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                aivs.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

	getStokSiklus: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('.param_getstok[data-required="1"]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi Plasma, Noreg, dan Barang terlebih dahulu.');
		} else {
			var params = {
				'noreg': $(div).find('.noreg').select2('val'),
				'barang': $(div).find('.barang').select2('val')
			};

			$.ajax({
	            url: 'transaksi/AdjustmentInVoadipSiklus/getStokSiklus',
	            data: { 'params': params },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	            	if ( data.status == 1 ) {
	            		$(div).find('.sisa_stok').val( numeral.formatDec(data.content.sisa_stok) );
	            	} else{
	            		bootbox.alert(data.message);
	            	}

	                hideLoading();
	            }
	        });
		}
	}, // end - getStokSiklus

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
					var params = {
						'tgl_adjust': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'mitra': $(div).find('.mitra').select2('val'),
						'noreg': $(div).find('.noreg').select2('val'),
						'barang': $(div).find('.barang').select2('val'),
						'harga': numeral.unformat( $(div).find('.harga').val() ),
						'jumlah': numeral.unformat( $(div).find('.jumlah').val() ),
						'keterangan': $(div).find('.keterangan').val(),
					};

					$.ajax({
			            url: 'transaksi/AdjustmentInVoadipSiklus/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){

			            	if ( data.status == 1 ) {
			            		aivs.execHitStokSiklus(data.content);
			            	} else{
			            		hideLoading();
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
		}
	}, // end - save

	delete: function(elm) {
		var div = $('#action');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
			if ( result ) {
				var params = {
					'id': $(elm).attr('data-id'),
				};

				$.ajax({
					url: 'transaksi/AdjustmentInVoadipSiklus/delete',
					data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
					beforeSend: function(){ showLoading() },
					success: function(data){

						if ( data.status == 1 ) {
							aivs.execHitStokSiklus(data.content);
						} else{
							hideLoading();
							bootbox.alert(data.message);
						}
					}
				});
			}
		});
	}, // end - delete

	execHitStokSiklus: function(content) {
		var params = content;

		$.ajax({
			url: 'transaksi/AdjustmentInVoadipSiklus/execHitStokSiklus',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				$('span.txt-msg-loading').text('Hitung stok di kandang . . .');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					aivs.execInsertJurnal(data.content);
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - execHitStokSiklus

    execInsertJurnal: function(content) {
		var params = content;

		$.ajax({
			url: 'transaksi/AdjustmentInVoadipSiklus/execInsertJurnal',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				$('span.txt-msg-loading').text('Insert jurnal . . .');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.content.message, function() {
                        if ( data.content.status == 3 ) {
                            aivs.loadForm(null, null, 'action');
                        } else {
                            aivs.loadForm(data.content.id, null, 'action');
                        }
                    });
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - execInsertJurnal
};

aivs.startUp();
