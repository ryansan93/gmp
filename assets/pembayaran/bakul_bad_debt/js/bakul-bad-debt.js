var bakul = {
	start_up: function() {
		bakul.setting_up();
	}, // end - start_up

	filter_all(elm, sensitive = false) {
	    var _target_table = $(elm).data('table');

	    var _table = $('table.'+_target_table);
	    var _tbody = $(_table).find('tbody');
	    var _content, _target;

	    _tbody.find('tr').removeClass('hide');
	    _content = $(elm).val().toUpperCase().trim();

	    if (!empty(_content) && _content != '') {
	        $.map( $(_tbody).find('tr.search'), function(tr){

	            // CEK DI TR ADA ATAU TIDAK
	            var ada = 0;
	            $.map( $(tr).find('td'), function(td){
	                var td_val = $(td).html().trim();
	                if ( !sensitive ) {
	                    if (td_val.toUpperCase().indexOf(_content) > -1) {
	                        ada = 1;
	                    }
	                } else {
	                    if (td_val.toUpperCase() == _content) {
	                        ada = 1;
	                    }
	                }
	            });

	            if ( ada == 0 ) {
	                $(tr).addClass('hide');
	            } else {
	                $(tr).removeClass('hide');
	            };
	        });
	    }

	    bakul.hit_total_riwayat();
	}, // end - filter_all

	setting_up: function(resubmit = null) {
		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('.unit').next('span.select2').css('width', '100%');
        });
        $('.unit').next('span.select2').css('width', '100%');

        $('.pelanggan').selectpicker();
        $('.perusahaan').selectpicker();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

		if ( empty(resubmit) ) {
			// var minDate = moment(new Date()).subtract(3, 'days').format('YYYY-MM-DD');
			var minDate = moment(new Date('2023-10-01'));

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
	}, // end - setting_up

	changeTabActive: function(elm) {
		var vhref = $(elm).data('href');
		// change tab-menu
		$('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

		if ( vhref == 'action' ) {
			var v_id = $(elm).attr('data-id');
			var resubmit = $(elm).attr('data-resubmit');

			bakul.load_form(v_id, resubmit);
		};
	}, // end - changeTabActive

	load_form: function(v_id = null, resubmit = null) {
		var div_action = $('div#action');

		$.ajax({
			url : 'pembayaran/BakulBadDebt/load_form',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(html){
				$(div_action).html(html);
				bakul.setting_up(resubmit);

				if ( resubmit == 'resubmit' ) {
					var tgl = $('div#tglBayar input').data('val');
					var minDate = new Date(tgl);
					minDate.setDate(minDate.getDate());
					$('#tglBayar').datetimepicker({
			            locale: 'id',
			            format: 'DD MMM Y',
			            minDate: minDate, // Set min Date
			        });
					$("#tglBayar").data("DateTimePicker").date(moment(minDate));
				}

				hideLoading();
			},
		});
	}, // end - load_form

	get_list_pembayaran: function() {
		var start_date = $('div#StartDate_PP input').val();
		var end_date = $('div#EndDate_PP input').val();

		$('#StartDate_PP').parent().removeClass('has-error')
		$('#EndDate_PP').parent().removeClass('has-error')
		if ( empty(start_date) || empty(end_date) ) {
			if ( empty(start_date) ) { $('#StartDate_PP').parent().addClass('has-error'); }
			if ( empty(end_date) ) { $('#EndDate_PP').parent().addClass('has-error'); }
			bootbox.alert('Harap lengkapi periode terlebih dahulu.');
		} else  {
			var params = {
				'start_date': dateSQL( $('#StartDate_PP').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate_PP').data('DateTimePicker').date() )
			};

			$.ajax({
	            url : 'pembayaran/BakulBadDebt/get_list_pembayaran',
	            data : {
	            	'params' : params
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$('table.tbl_list_pembayaran').find('tbody').html(data.html);
	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_pembayaran

	hit_total_riwayat: function() {
		var jml_transfer = 0;
		$.map( $('table.tbl_list_pembayaran').find('tbody tr.data:not(.hide)'), function(tr) {
			var _jml_transfer = numeral.unformat($(tr).find('td.jml_transfer').text());
			jml_transfer += _jml_transfer;
		});

		$('table.tbl_list_pembayaran').find('tbody td.grand_total b').text(numeral.formatDec(jml_transfer));
	}, // end - hit_total_riwayat

	get_list_do: function() {
		var id = ($('.btn-get-list-do').length > 0) ? $('.btn-get-list-do').data('id') : null;
		var pelanggan = $('select.pelanggan').val();
		var unit = $('select.unit').val();
		var tgl_bayar = $('div#tglBayar input').val();
		var perusahaan = $('select.perusahaan').val();
		var jenis_mitra = $('select.perusahaan option:selected').attr('data-jenismitra');

		$('select.pelanggan').parent().removeClass('has-error');
		$('select.unit').parent().removeClass('has-error');
		$('div#tglBayar input').parent().removeClass('has-error');
		if ( empty(pelanggan) || empty(tgl_bayar) || empty(unit) ) {
			if ( empty(pelanggan) ) {
				$('select.pelanggan').parent().addClass('has-error');
			}
			if ( empty(unit) ) {
				$('select.unit').parent().addClass('has-error');
			}
			if ( empty(tgl_bayar) ) {
				$('div#tglBayar input').parent().addClass('has-error');
			}
			bootbox.alert('Harap isi tanggal bayar dan pelanggan terlebih dahulu.');
		} else {
			$.ajax({
	            url : 'pembayaran/BakulBadDebt/get_list_do',
	            data : {
	            	'id' : id,
	            	'pelanggan' : pelanggan,
	            	'unit' : unit,
	            	'tgl_bayar' : dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
	            	'perusahaan' : perusahaan,
	            	'jenis_mitra': jenis_mitra
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$('table.tbl_list_do').find('tbody').html(data.html);
	            	bakul.setting_up();

	            	$('input.saldo').val(numeral.formatDec(data.saldo));

	            	bakul.hit_total_uang();

	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_do

	hit_total_uang: function() {
		var jml_bayar = 0;
		$.map( $('table.tbl_list_do tbody tr.data'), function(tr) {
			var _total = numeral.unformat($(tr).find('td.total').text());

			var _jml_bayar = $(tr).find('td.jml_bayar').data('sudah');
			var total = _total - _jml_bayar;

			$(tr).find('td.jml_bayar').attr('data-bayar', total);
			$(tr).find('td.jml_bayar').text(numeral.formatDec(total));

			jml_bayar += total;

			bakul.cek_status_pembayaran( tr );
		});

		$('input.jml_bayar').val(numeral.formatDec(jml_bayar));
		$('input.jml_transfer').val(numeral.formatDec(jml_bayar));

		var jml_trf = jml_bayar;
		var saldo = numeral.unformat($('input.saldo').val());
		var nil_pajak = numeral.unformat($('input.nilai_pajak').val());

		var total_uang = jml_trf + saldo + nil_pajak;
		$('input.total').val(numeral.formatDec(total_uang));

		$('input.lebih_kurang').val(numeral.formatDec(total_uang-jml_bayar));

		// bakul.bagi_total_uang();
	}, // end - hit_total_uang

	// bagi_total_uang: function() {
	// 	var total_uang = numeral.unformat($('input.total').val());
	// 	var _total_uang = total_uang;
	// 	var jml_bayar = 0;
	// 	$.map( $('table.tbl_list_do tbody tr.data'), function(tr) {
	// 		var _total = numeral.unformat($(tr).find('td.total').text());

	// 		var _jml_bayar = $(tr).find('td.jml_bayar').data('sudah');
	// 		var total = _total - _jml_bayar;

	// 		jml_bayar += total;
	// 		if ( _total_uang > 0 ) {
	// 		}

	// 		if ( _total_uang >= total ) {
	// 			$(tr).find('td.jml_bayar').attr('data-bayar', total);
	// 			$(tr).find('td.jml_bayar').text(numeral.formatDec(total));
	// 			// $(tr).find('td.penyesuaian input').attr('readonly', true);
	// 			_total_uang -= total;
	// 		} else {
	// 			$(tr).find('td.jml_bayar').attr('data-bayar', _total_uang);
	// 			$(tr).find('td.jml_bayar').text(numeral.formatDec(_total_uang));
	// 			_total_uang = 0;
	// 		}

	// 		bakul.cek_status_pembayaran( tr );
	// 	});

	// 	$('input.jml_bayar').val(numeral.formatDec(jml_bayar));
	// 	$('input.lebih_kurang').val(numeral.formatDec(total_uang-jml_bayar));
	// }, // end - bagi_total_uang

	cek_status_pembayaran: function(elm) {
		var tr = $(elm).closest('tr');

		var total = parseFloat(numeral.unformat($(tr).find('td.total').text()));
		var sudah_bayar = $(tr).find('td.jml_bayar').attr('data-sudah').length > 0 ? parseFloat($(tr).find('td.jml_bayar').attr('data-sudah')) : 0;
		var jml_bayar = parseFloat(numeral.unformat($(tr).find('td.jml_bayar').attr('data-bayar')));
		var penyesuaian = parseFloat(numeral.unformat($(tr).find('td.penyesuaian input').val()));

		var total_bayar = sudah_bayar+jml_bayar+penyesuaian;

		if ( total == total_bayar ) {
			var span = '<span style="color: blue;"><b>LUNAS</b></span>';
			$(tr).find('td.status').html(span);
		} else {
			var span = '<span style="color: red;"><b>BELUM</b></span>';
			$(tr).find('td.status').html(span);
		}

		var total_penyesuaian = 0;
		$.map( $('tr.data'), function(_tr) {
			var penyesuaian = numeral.unformat($(_tr).find('td.penyesuaian input').val());
			total_penyesuaian += penyesuaian;
		});

		$('input.total_penyesuaian').val(numeral.formatDec(total_penyesuaian));

		var jml_bayar = numeral.unformat( $('input.jml_bayar').val() );
		var total = numeral.unformat( $('input.total').val() );

		$('input.lebih_kurang').val(numeral.formatDec((total + total_penyesuaian)-jml_bayar));
	}, // end - cek_status_pembayaran

	save: function() {
		var div = $('div#action');

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
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.data'), function(tr) {
				var _detail = {
					'id': $(tr).data('id'),
					'potongan': numeral.unformat($(tr).find('td.potongan').text()),
					'total': numeral.unformat($(tr).find('td.total').text()),
					'jml_bayar': numeral.unformat($(tr).find('td.jml_bayar').data('bayar')),
					'penyesuaian': numeral.unformat($(tr).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr).find('textarea').val(),
					'status': $(tr).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'tgl_bayar': dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $('select.pelanggan').val(),
							'jml_transfer': numeral.unformat($('input.jml_transfer').val()),
							'saldo': numeral.unformat($('input.saldo').val()),
							'nil_pajak': numeral.unformat($('input.nilai_pajak').val()),
							'total_uang': numeral.unformat($('input.total').val()),
							'total_penyesuaian': numeral.unformat($('input.total_penyesuaian').val()),
							'total_bayar': numeral.unformat($('input.jml_bayar').val()),
							'lebih_kurang': numeral.unformat($('input.lebih_kurang').val()),
							'perusahaan': $('select.perusahaan').val(),
							'detail': detail
						};

						var formData = new FormData();

						if ( !empty($('.file_lampiran').val()) ) {
							var _file = $('.file_lampiran').get(0).files[0];
							formData.append('files', _file);
						}
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
			url :'pembayaran/BakulBadDebt/save',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                    	var start_date = $('div#StartDate_PP input').val();
						var end_date = $('div#EndDate_PP input').val();
						if ( !empty(start_date) && !empty(end_date) ) {
							bakul.get_list_pembayaran();
						}
                    	bakul.load_form();
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
		var div = $('div#action');

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
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.data'), function(tr) {
				var _detail = {
					'id': $(tr).data('id'),
					'total': numeral.unformat($(tr).find('td.total').text()),
					'jml_bayar': numeral.unformat($(tr).find('td.jml_bayar').data('bayar')),
					'penyesuaian': numeral.unformat($(tr).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr).find('textarea').val(),
					'status': $(tr).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-edit data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'id': $(elm).data('id'),
							'tgl_bayar': dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $('select.pelanggan').val(),
							'jml_transfer': numeral.unformat($('input.jml_transfer').val()),
							'saldo': numeral.unformat($('input.saldo').val()),
							'nil_pajak': numeral.unformat($('input.nilai_pajak').val()),
							'total_uang': numeral.unformat($('input.total').val()),
							'total_penyesuaian': numeral.unformat($('input.total_penyesuaian').val()),
							'total_bayar': numeral.unformat($('input.jml_bayar').val()),
							'lebih_kurang': numeral.unformat($('input.lebih_kurang').val()),
							'perusahaan': $('select.perusahaan').val(),
							'detail': detail
						};

						var formData = new FormData();

						var _file = $('.file_lampiran').get(0).files[0];
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
			url :'pembayaran/BakulBadDebt/edit',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                    	var start_date = $('div#StartDate_PP input').val();
						var end_date = $('div#EndDate_PP input').val();
						if ( !empty(start_date) && !empty(end_date) ) {
							bakul.get_list_pembayaran();
						}
                    	bakul.load_form();
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
					url :'pembayaran/BakulBadDebt/delete',
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
		                    	var start_date = $('div#StartDate_PP input').val();
								var end_date = $('div#EndDate_PP input').val();
								if ( !empty(start_date) && !empty(end_date) ) {
									bakul.get_list_pembayaran();
								}
		                    	bakul.load_form();
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