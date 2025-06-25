var aiv = {
	startUp: function() {
        aiv.settingUp();
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

        $('.gudang').select2();
		$('div#riwayat').find('.barang').select2();
        $('div#action').find('.barang').select2().on('select2:select', function() {
			aiv.cekDecimalHarga( $(this) );
		});

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

	cekDecimalHarga: function(elm) {
		var barang = $(elm).val();

		$('.hrg_beli').removeAttr('disabled');

		var data_tipe = 'decimal';
		var maxlength = 10;
		if ( !empty(barang) ) {
			var decimal = $(elm).find('option:selected').attr('data-decimal');

			if ( decimal > 2 ) {
				data_tipe += decimal;
				maxlength = 13;
			}
		}

		$('.hrg_beli, .hrg_jual').attr('data-tipe', data_tipe);
		$('.hrg_beli, .hrg_jual').attr('maxlength', maxlength);

		$('.hrg_beli, .hrg_jual').each(function() {
			$(this).priceFormat(Config[$(this).attr('data-tipe')]);
		});
	}, // end - cek_decimal_barang

	hitHrgJual: function(elm) {
		var select_brg = $('div#action').find('select.barang');
		var hit_hrg_jual = $(select_brg).find('option:selected').attr('data-hithrgjual');

		var harga_beli = numeral.unformat($(elm).val());
		var harga_jual = 0;
		if ( hit_hrg_jual == 1 ) {
			var harga = harga_beli + (harga_beli  * (5/100));

			var sisa_hasil_bagi = harga % 50;

			harga_jual = harga - sisa_hasil_bagi;
		} else {
			harga_jual = harga_beli;
		}

		var decimal = $('input.hrg_jual').attr('data-tipe').replace('decimal', '');
		var _hrg_jual = numeral.formatDec(harga_jual);
		if ( !empty(decimal) ) {
			_hrg_jual = numeral.formatDec(harga_jual, decimal);
		}
		$('input.hrg_jual').val( _hrg_jual );
	}, // end - hit_hrg_jual_voadip

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
                'gudang': $(div).find('.gudang').select2('val'),
                'barang': $(div).find('.barang').select2('val')
            };

            $.ajax({
                url: 'transaksi/AdjustmentInVoadip/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ showLoading() },
                success: function(html){
                    $(div).find('.tbl_riwayat tbody').html( html );

                    aiv.settingUp();

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

		aiv.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/AdjustmentInVoadip/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                aiv.settingUp();

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
					var params = {
						'tgl_adjust': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'kode_gudang': $(div).find('.gudang').select2('val'),
						'kode_barang': $(div).find('.barang').select2('val'),
						'hrg_beli': numeral.unformat( $(div).find('.hrg_beli').val() ),
						'hrg_jual': numeral.unformat( $(div).find('.hrg_jual').val() ),
						'jumlah': numeral.unformat( $(div).find('.jumlah').val() ),
						'keterangan': $(div).find('.keterangan').val(),
					};

					$.ajax({
			            url: 'transaksi/AdjustmentInVoadip/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){

			            	if ( data.status == 1 ) {
			            		aiv.hitungStokByTransaksi(data.content);
			            		// aiv.hitungStokAwal( data.content.id, $(div).find('.no_sj').select2('val') );
			            		// bootbox.alert(data.message, function() {
			            		// 	location.reload();
			            		// });
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
					url: 'transaksi/AdjustmentInVoadip/delete',
					data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
					beforeSend: function(){ showLoading() },
					success: function(data){

						if ( data.status == 1 ) {
							aiv.hitungStokByTransaksi(data.content);
							// aiv.hitungStokAwal( data.content.id, $(div).find('.no_sj').select2('val') );
							// bootbox.alert(data.message, function() {
							// 	location.reload();
							// });
						} else{
							hideLoading();
							bootbox.alert(data.message);
						}
					}
				});
			}
		});
	}, // end - delete

	hitungStokByTransaksi: function(content) {
		var params = content;

		$.ajax({
			url: 'transaksi/AdjustmentInVoadip/hitungStokByTransaksi',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading('Hitung Stok Ulang . . .');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(content.message, function() {
						// location.reload();

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

						if ( err == 0 ) {
							aiv.getLists();
						}

						var id = content.id;
						if ( content.delete == 1 ) {
							id = null;
						}

						aiv.loadForm(id, null, 'action');
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokByTransaksi
};

aiv.startUp();