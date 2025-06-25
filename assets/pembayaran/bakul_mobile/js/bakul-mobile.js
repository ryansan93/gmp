var bakul = {
	start_up: function() {
		bakul.setting_up();
	}, // end - start_up

	setting_up: function(resubmit = null) {
		$('div#riwayat').find('#select_pelanggan').selectpicker();
		$('div#riwayat').find('#select_unit').selectpicker();

		$('div#transaksi').find('#select_pelanggan').selectpicker();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

		if ( empty(resubmit) ) {
			var minDate = new Date();
			minDate.setDate(minDate.getDate() - 3);
			$('#tglBayar').datetimepicker({
	            locale: 'id',
	            format: 'DD MMM Y',
	            minDate: minDate, // Set min Date
	        });
		}

        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });
	}, // end - setting_up

	changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+href).addClass('show');
        $('div#'+href).addClass('active');

        bakul.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'id': $(elm).data('id')
        };

        $.ajax({
            url : 'pembayaran/BakulMobile/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                bakul.setting_up();
            },
        });
    }, // end - load_form

	get_list_pembayaran: function() {
		var div = $('div#riwayat')

		var pelanggan = $(div).find('select#select_pelanggan').val();
		var tgl_bayar = $(div).find('div#tanggal input').val();

		if ( empty(pelanggan) || empty(tgl_bayar) ) {
			if ( empty(pelanggan) ) { $(div).find('select#select_pelanggan').parent().addClass('has-error'); }
			if ( empty(tgl_bayar) ) { $(div).find('div#tanggal input').parent().addClass('has-error'); }
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else  {
			var params = {
				'pelanggan': pelanggan,
				'tgl_bayar': dateSQL( $(div).find('div#tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
	            url : 'pembayaran/BakulMobile/get_list_pembayaran',
	            data : {
	            	'params' : params
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$(div).find('table.tbl_riwayat tbody').html(data.html);
	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_pembayaran

	get_list_do: function(elm) {
		var div = $('div#transaksi')

		var pelanggan = $(div).find('select#select_pelanggan').val();
		var tgl_bayar = $(div).find('div#tglBayar input').val();
		var edit = $(elm).data('edit');

		$(div).find('select#select_pelanggan').parent().removeClass('has-error');
		$(div).find('div#tglBayar input').parent().removeClass('has-error');
		if ( empty(pelanggan) || empty(tgl_bayar) ) {
			if ( empty(pelanggan) ) {
				$(div).find('select#select_pelanggan').parent().addClass('has-error');
			}
			if ( empty(tgl_bayar) ) {
				$(div).find('div#tglBayar input').parent().addClass('has-error');
			}
			bootbox.alert('Harap isi tanggal bayar dan pelanggan terlebih dahulu.');
		} else {
			$.ajax({
	            url : 'pembayaran/BakulMobile/get_list_do',
	            data : {
	            	'pelanggan' : pelanggan,
	            	'tgl_bayar' : dateSQL( $(div).find('#tglBayar').data('DateTimePicker').date() ),
	            	'edit' : edit
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$(div).find('table.tbl_list_do tbody').html(data.html);
	            	bakul.setting_up();

	            	$(div).find('input.saldo').val(numeral.formatDec(data.saldo));

	            	bakul.hit_total_uang();

	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_do

	hit_total_uang: function() {
		var jml_trf = numeral.unformat($('input.jml_transfer').val());
		var saldo = numeral.unformat($('input.saldo').val());

		var total_uang = jml_trf + saldo;
		$('input.total').val(numeral.formatDec(total_uang));

		bakul.bagi_total_uang();
	}, // end - hit_total_uang

	bagi_total_uang: function() {
		var total_uang = numeral.unformat($('input.total').val());
		var _total_uang = total_uang;
		var jml_bayar = 0;
		$.map( $('table.tbl_list_do tbody tr.detail'), function(tr) {
			var _total = numeral.unformat($(tr).find('td.total').text());

			var _jml_bayar = $(tr).find('td.jml_bayar').data('sudah');
			var total = _total - _jml_bayar;

			jml_bayar += total;
			if ( _total_uang > 0 ) {
			}

			if ( _total_uang >= total ) {
				$(tr).find('td.jml_bayar').attr('data-bayar', total);
				$(tr).find('td.jml_bayar').text(numeral.formatDec(total));
				$(tr).find('td.penyesuaian input').attr('readonly', true);
				_total_uang -= total;
			} else {
				$(tr).find('td.jml_bayar').attr('data-bayar', _total_uang);
				$(tr).find('td.jml_bayar').text(numeral.formatDec(_total_uang));
				_total_uang = 0;
			}

			bakul.cek_status_pembayaran( tr );
		});

		$('input.jml_bayar').val(numeral.formatDec(jml_bayar));
		$('input.lebih_kurang').val(numeral.formatDec(total_uang-jml_bayar));
	}, // end - bagi_total_uang

	cek_status_pembayaran: function(elm) {
		var tr_detail = $(elm).closest('tr.detail');

		var total = numeral.unformat($(tr_detail).find('td.total').text());
		var sudah_bayar = numeral.unformat($(tr_detail).find('td.jml_bayar').attr('data-sudah'));
		var jml_bayar = numeral.unformat($(tr_detail).find('td.jml_bayar').attr('data-bayar'));
		var penyesuaian = numeral.unformat($(tr_detail).find('td.penyesuaian input').val());

		var sisa_bayar = total - (sudah_bayar+jml_bayar);
		if ( penyesuaian > sisa_bayar ) {
			bootbox.alert('<b>Cek kembali penyesuaian !<b><br>Penyesuaian lebih dari sisa yang harus di bayar.');
		} else {
			var total_bayar = sudah_bayar+jml_bayar+penyesuaian;

			if ( total == total_bayar ) {
				var span = '<span style="color: blue;"><b>LUNAS</b></span>';
				$(tr_detail).find('td.status').html(span);
			} else {
				var span = '<span style="color: red;"><b>BELUM</b></span>';
				$(tr_detail).find('td.status').html(span);
			}

			var total_penyesuaian = 0;
			$.map( $('tr.header'), function(tr_header) {
				var _tr_detail = $(tr_header).next('tr.detail');

				var penyesuaian = numeral.unformat($(_tr_detail).find('td.penyesuaian input').val());
				total_penyesuaian += penyesuaian;
			});

			$('input.total_penyesuaian').val(numeral.formatDec(total_penyesuaian));
		}
	}, // end - cek_status_pembayaran

	save: function() {
		var div = $('div#transaksi');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#000000'});
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.header'), function(tr_header) {
				var tr_detail = $(tr_header).next('tr.detail');

				var _detail = {
					'id': $(tr_header).data('id'),
					'total': numeral.unformat($(tr_detail).find('td.total').text()),
					'jml_bayar': numeral.unformat($(tr_detail).find('td.jml_bayar').data('bayar')),
					'penyesuaian': numeral.unformat($(tr_detail).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr_detail).find('textarea').val(),
					'status': $(tr_detail).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'tgl_bayar': dateSQL( $(div).find('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $(div).find('select#select_pelanggan').val(),
							'jml_transfer': numeral.unformat($(div).find('input.jml_transfer').val()),
							'saldo': numeral.unformat($(div).find('input.saldo').val()),
							'total_uang': numeral.unformat($(div).find('input.total').val()),
							'total_penyesuaian': numeral.unformat($(div).find('input.total_penyesuaian').val()),
							'total_bayar': numeral.unformat($(div).find('input.jml_bayar').val()),
							'lebih_kurang': numeral.unformat($(div).find('input.lebih_kurang').val()),
							'detail': detail
						};

						var formData = new FormData();

						var _file = $(div).find('.file_lampiran').get(0).files[0];
						formData.append('files', _file);
						formData.append('data', JSON.stringify(data));

						bakul.execute_save(formData);
					}
				});
			} else {
				bootbox.alert('Tidak ada data DO yang akan anda bayar.');
			}
		}
	}, // end - save

	execute_save: function(formData) {
		$.ajax({
			url :'pembayaran/BakulMobile/save',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                    	var btn = '<button type="button" data-id="'+data.content.id+'" data-edit="" data-href="transaksi"></button>';
						bakul.load_form( $(btn), null, 'transaksi' );
                    });
                } else {
                    bootbox.alert(data.message);
                }
			},
			contentType : false,
			processData : false,
		});
	}, // end - execute_save

	edit: function(elm) {
		var div = $('div#transaksi');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#000000'});
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.header'), function(tr_header) {
				var tr_detail = $(tr_header).next('tr.detail');

				var _detail = {
					'id': $(tr_header).data('id'),
					'total': numeral.unformat($(tr_detail).find('td.total').text()),
					'jml_bayar': numeral.unformat($(tr_detail).find('td.jml_bayar').data('bayar')),
					'penyesuaian': numeral.unformat($(tr_detail).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr_detail).find('textarea').val(),
					'status': $(tr_detail).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-edit data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'id': $(elm).data('id'),
							'tgl_bayar': dateSQL( $(div).find('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $(div).find('select#select_pelanggan').val(),
							'jml_transfer': numeral.unformat($(div).find('input.jml_transfer').val()),
							'saldo': numeral.unformat($(div).find('input.saldo').val()),
							'total_uang': numeral.unformat($(div).find('input.total').val()),
							'total_penyesuaian': numeral.unformat($(div).find('input.total_penyesuaian').val()),
							'total_bayar': numeral.unformat($(div).find('input.jml_bayar').val()),
							'lebih_kurang': numeral.unformat($(div).find('input.lebih_kurang').val()),
							'detail': detail
						};

						var formData = new FormData();

						var _file = $(div).find('.file_lampiran').get(0).files[0];
						if ( !empty(_file) ) {
							formData.append('files', _file);
						}
						formData.append('data', JSON.stringify(data));

						bakul.execute_edit(formData);
					}
				});
			} else {
				bootbox.alert('Tidak ada data DO yang akan anda bayar.');
			}
		}
	}, // end - edit

	execute_edit: function(formData) {
		$.ajax({
			url :'pembayaran/BakulMobile/edit',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
						var btn = '<button type="button" data-id="'+data.content.id+'" data-edit="" data-href="transaksi"></button>';
						bakul.load_form( $(btn), null, 'transaksi' );
                    });
                } else {
                    bootbox.alert(data.message);
                }
			},
			contentType : false,
			processData : false,
		});
	}, // end - execute_edit

	delete: function(elm) {
		var id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
			if ( result ) {
				$.ajax({
					url :'pembayaran/BakulMobile/delete',
					data : {
						'params': id
					},
					dataType : 'JSON',
	            	type : 'POST',
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
		                if ( data.status == 1 ) {
		                    bootbox.alert(data.message, function(){
								var btn = '<button type="button" data-edit="" data-href="transaksi"></button>';
								bakul.load_form( $(btn), null, 'transaksi' );
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

bakul.start_up();