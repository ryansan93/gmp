var aop = {
	startUp: function() {
        aop.settingUp();
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
                'gudang': $(div).find('.gudang').select2('val'),
                'barang': $(div).find('.barang').select2('val')
            };

            $.ajax({
                url: 'transaksi/AdjustmentOutPakan/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ showLoading() },
                success: function(html){
                    $(div).find('.tbl_riwayat tbody').html( html );

                    aop.settingUp();

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

		aop.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/AdjustmentOutPakan/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                aop.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

	getSj: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('.param_getsj[data-required="1"]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi parameter SJ terlebih dahulu.');
		} else {
			var params = {
				'gudang': $(div).find('.gudang').select2('val'),
				'barang': $(div).find('.barang').select2('val'),
				'tgl_sj': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
	            url: 'transaksi/AdjustmentOutPakan/getSj',
	            data: { 'params': params },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	            	if ( data.status == 1 ) {
	            		var opt = '<option value="">-- Pilih SJ --</option>';
	            		if ( !empty(data.content) && data.content.length > 0 ) {
		            		for (var i = 0; i < data.content.length; i++) {
		            			opt += '<option value="'+data.content[i].no_sj+'" data-stok="'+data.content[i].sisa_stok+'" data-harga="'+data.content[i].harga+'">'+data.content[i].tanggal+' | '+data.content[i].no_sj+'</option>';
		            		}
	            		}

	            		$(div).find('.no_sj').html( opt );

	            		$(div).find('.no_sj').select2().on('select2:select', function(e) {
	            			var stok = $(div).find('.no_sj option:selected').attr('data-stok');
	            			var harga = $(div).find('.no_sj option:selected').attr('data-harga');

	            			$(div).find('.harga').val( numeral.formatDec(harga) );
	            			$(div).find('.sisa_stok').val( numeral.formatDec(stok) );
				        });
	            	} else{
	            		bootbox.alert(data.message);
	            	}

	                aop.settingUp();

	                hideLoading();
	            }
	        });
		}
	}, // end - getSj

	cekJumlahAdjust: function(elm) {
		var div = $('#action');

		var jumlah = numeral.unformat($(elm).val());
		var stok = numeral.unformat($(div).find('.sisa_stok').val());

		if ( jumlah > stok ) {
			bootbox.alert('Jumlah adjust yang anda masukkan melebihi stok pada SJ tersebut.', function() {
				$(elm).val(0);
			});
		}
	}, // end - cekJumlahAdjust

	save: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('.param_getsj[data-required="1"]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi parameter SJ terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
				if ( result ) {
					var params = {
						'kode_gudang': $(div).find('.gudang').select2('val'),
						'kode_barang': $(div).find('.barang').select2('val'),
						'tgl_sj': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'no_sj': $(div).find('.no_sj').select2('val'),
						'harga': numeral.unformat( $(div).find('.harga').val() ),
						'sisa_stok': numeral.unformat( $(div).find('.sisa_stok').val() ),
						'jumlah': numeral.unformat( $(div).find('.jumlah').val() ),
						'keterangan': $(div).find('.keterangan').val(),
					};

					$.ajax({
			            url: 'transaksi/AdjustmentOutPakan/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){

			            	if ( data.status == 1 ) {
			            		aop.hitungStokByTransaksi(data.content);
			            		// aop.hitungStokAwal( data.content.id, $(div).find('.no_sj').select2('val') );
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

	hitungStokByTransaksi: function(content) {
		var params = content;

		$.ajax({
			url: 'transaksi/AdjustmentOutPakan/hitungStokByTransaksi',
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
						location.reload();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokByTransaksi

	// hitungStokAwal: function(id_adjout, no_sj) {
 //        var params = {
 //            'id_adjout': id_adjout
 //        };

 //        $.ajax({
 //            url: 'transaksi/AdjustmentOutPakan/hitungStokAwal',
 //            dataType: 'json',
 //            type: 'post',
 //            data: {
 //                'params': params
 //            },
 //            beforeSend: function() {},
 //            success: function(data) {
 //                hideLoading();
 //                if ( data.status == 1 ) {
 //                    bootbox.alert(data.message, function() {
 //                    	location.reload();
 //                        // aop.load_form(no_sj, null, 'transaksi');
 //                    });
 //                } else {                    
 //                    bootbox.alert(data.message);
 //                };
 //            },
 //        });
 //    }, // end - hitungStokAwal
};

aop.startUp();