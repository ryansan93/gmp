var kpm = {
	start_up : function () {
		kpm.setting_up();
        kpm.getLists();
	}, // end - start_up

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$('#datetimepicker1').datetimepicker({
            locale: 'id',
            viewMode: 'years',
            format: 'MMM Y',
            useCurrent: false,
			allowInputToggle: true,
        });

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

            kpm.load_form(v_id, resubmit);
        } else {
            kpm.load_form(null, resubmit);
        };;
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'transaksi/KPM/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){showLoading();},
            success : function(html){
                $(div_action).html(html);
                kpm.setting_up();

                hideLoading();

                if ( !empty(resubmit) ) {
                	let tgl = $('#datetimepicker1').data('tgl');
        			$('#datetimepicker1').data('DateTimePicker').date( moment(tgl) );
        			$('#datetimepicker1').find('input').attr('disabled', 'disabled');

        			$('select.noreg').attr('disabled', 'disabled');

                	kpm.get_noreg(resubmit);
                }

            },
        });
    }, // end - load_form

    getLists : function(keyword = null){
        $.ajax({
            url : 'transaksi/KPM/list_kpm',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_kpm tbody').html(data);
            }
        });
    }, // end - getLists

	get_noreg : function (resubmit = null) {
		var date = dateSQL($('#datetimepicker1').data('DateTimePicker').date()).substr(0, 7);

		$.ajax({
			url: 'transaksi/KPM/get_noreg',
			data: {
				'periode': date
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				$('select.noreg option').remove();

				// NOTE : OPTION FOR NOREG RDIM
				var sel_noreg = $('select.noreg');
				var option = '<option value="">-- Pilih No. Reg --</option>';
				for (var i = 0; i < data.content.length; i++) {
					let selected = null;
					let noreg = $(sel_noreg).data('noreg');

					if ( !empty(noreg) ) {
						if ( noreg == data.content[i].noreg ) {
							selected = 'selected';
						}
					}

					option += '<option value='+ data.content[i].noreg +' data-mitra="'+ data.content[i].mitra +'" '+ selected +'>'+ data.content[i].noreg +'</option>';
				};
				sel_noreg.append(option);

				kpm.set_data_rdim(sel_noreg, resubmit);

				if ( data.status == 1 ) {
					hideLoading();
				};
			},
	    });
	}, // end - get_noreg

	set_data_rdim : function(elm, resubmit = null) {
		var body = $(elm).closest('div.panel-body');
		var noreg = $(elm).val();
		var div_action = $(elm).closest('div#action');

		$.ajax({
			url: 'transaksi/KPM/get_data_rdim',
			data: {
				'noreg': noreg,
				'resubmit': resubmit
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					$(div_action).attr('data-id', data.content.id);

					$(body).find('div.populasi span').html(numeral.formatInt(data.content.populasi));
					$(body).find('div.mitra span').html(data.content.nama_mitra);

					var _docin = moment(data.content.tgl_docin).locale('id');
					var _bapdoc = moment(data.content.tgl_bapdoc).locale('id');

					$(body).find('div.tgl_docin span').html(_docin.format('LL'));

					$(body).find('div.tgl_docin span').html(_docin.format('LL'));
					$(body).find('div.tgl_bapdoc span').html(_bapdoc.format('LL'));

					$(body).find('div.kebutuhan_kg span').html(numeral.formatInt(data.content.populasi * 3));
					$(body).find('div.kebutuhan_zak span').html(numeral.formatInt( (data.content.populasi * 3) / 50 ));

					$(body).find('table.list_kpm tbody').html(data.content.list);
				} else {
					$(body).find('div.populasi span').html(null);
					$(body).find('div.mitra span').html(null);

					$(body).find('div.tgl_docin span').html('-');

					$(body).find('div.tgl_docin span').html('-');
					$(body).find('div.tgl_bapdoc span').html('-');

					$(body).find('div.kebutuhan_kg span').html('-');
					$(body).find('div.kebutuhan_zak span').html('-');

					$(body).find('table.list_kpm tbody').html(data.content.list);
				};

				kpm.setting_up();
				kpm.set_header_pakan();

				$.map( $(body).find('tbody tr'), function(tr) {
					$.map( $(tr).find('.datetimepicker'), function (div){
						var tgl = $(div).data('tanggal');
						// var exist = $(div).data('exist');
						if ( !empty(tgl) ) {
							$(div).data('DateTimePicker').date( moment(tgl) );
						}
					});
				});
			},
		});
	}, // end - setData

	set_header_pakan: function() {
		var nama_pakan = $('select.jenis_pakan').find('option:selected').text();
		var val = $('select.jenis_pakan').val();

		var div_action = $('select.jenis_pakan').closest('div#action');
		var table = $(div_action).find('table.list_kpm');

		if ( !empty(val) ) {
			$(table).find('span.nama_pakan').html('( '+ nama_pakan +' )');
		} else {
			$(table).find('span.nama_pakan').html('');
		};
	}, // end - set_header_pakan

	hit_rcn_kirim: function(elm) {
		var setting = numeral.unformat( $(elm).val() );

		var _data_td = $(elm).data('td');
		var _umur = $(elm).data('umur');

		var div_action = $(elm).closest('div#action');

		var div_populasi = $(div_action).find('div.populasi');
		var populasi = (numeral.unformat( $(div_populasi).find('span').text() )) / 1000;

		var _tr = $(elm).closest('tr');
		var _table = $(_tr).closest('table');

		var rcn_kirim = 0;
		var rcn_kirim_prev = 0;
		var _rcn_kirim = 0;
		var _rcn_kirim2 = 0;

		var _rcn_sblm = false;
		var _rcn_ssdh = false;

		// NOTE : GET RENCANA KIRIM SEBELUMNYA
		while ( _rcn_sblm == false ) {
			_umur--;

			if ( _umur >= 0 ) {
				var _umur1 = $(_table).find("td.umur1[name="+_umur+"]");
				var _umur2 = $(_table).find("td.umur2[name="+_umur+"]");

				if ( _umur1.length > 0 || _umur2.length > 0 ) {
					// NOTE : KOLOM KIRI
					if ( _umur1.length > 0 ) {
						var href = $(_umur1).data('href');
						var tr_umur = $(_umur1).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						if ( setting != 0 ) {
							if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
								// NOTE : GET RCN_KIRIM SEBELUM URUTAN
								rcn_kirim = val_rcn_kirim;
								rcn_kirim_prev = rcn_kirim;
								_rcn_sblm = true;
							};
						};
					};

					// NOTE : KOLOM KANAN
					if ( _umur2.length > 0 ) {
						var href = $(_umur1).data('href');
						var tr_umur = $(_umur1).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						if ( setting != 0 ) {
							if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
								// NOTE : GET RCN_KIRIM SEBELUM URUTAN
								rcn_kirim = val_rcn_kirim;
								rcn_kirim_prev = rcn_kirim;
								_rcn_sblm = true;
							};
						};
					};
				};
			} else {
				rcn_kirim_prev = 0;
				_rcn_sblm = true;
			};
		};

		if ( setting != 0 ) {
			rcn_kirim = numeral.formatDec ( ((setting * populasi) / 50) - numeral.unformat(rcn_kirim_prev) );
			$(_tr).find('td.'+_data_td+' input').val(rcn_kirim);
		} else {
			$(_tr).find('td.'+_data_td+' input').val(null);
		}


		if ( _rcn_sblm = true ) {
			var _rcn_kirim_before = ((setting * populasi) / 50) - numeral.unformat(rcn_kirim_prev);

			// NOTE : GET RENCANA KIRIM SESUDAHNYA
			_umur = $(elm).data('umur');
			for (var i = _umur; i <= 42; i++) {
				var _umur1 = $(_table).find("td.umur1[name="+i+"]");
				var _umur2 = $(_table).find("td.umur2[name="+i+"]");

				if ( _umur1.length > 0 || _umur2.length > 0 ) {
					// NOTE : KOLOM KIRI
					if ( _umur1.length > 0 ) {
						var href = $(_umur1).data('href');

						var tr_umur = $(_umur1).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_std = $(tr_umur).find('td[data-ipt='+href+']');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_std = $(td_std).find('input').val();
						if ( i != _umur ) {
							if ( setting != 0 ) {
								if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
									// NOTE : GET RCN_KIRIM SETELAH URUTAN
									setting = numeral.unformat(val_std);

									// NOTE : HITUNG RCN_KIRIM SELETAH URUTAN
									var _rcn_kirim = ((setting * populasi) / 50) - numeral.unformat(_rcn_kirim_bef);

									// NOTE : SET RCN_KIRIM SELETAH URUTAN
									$(td_rcn_kirim).find('input').val( numeral.formatDec(_rcn_kirim) );
									_rcn_kirim_before = _rcn_kirim;
								};
							};
						};
					};

					// NOTE : KOLOM KANAN
					if ( _umur2.length > 0 ) {
						var href = $(_umur2).data('href');

						var tr_umur = $(_umur2).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_std = $(tr_umur).find('td[data-ipt='+href+']');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_std = $(td_std).find('input').val();
						if ( i != _umur ) {
							if ( setting != 0 ) {
								if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
									// NOTE : GET RCN_KIRIM SETELAH URUTAN
									setting = numeral.unformat(val_std);

									// NOTE : HITUNG RCN_KIRIM SELETAH URUTAN
									var _rcn_kirim = ((setting * populasi) / 50) - numeral.unformat(_rcn_kirim_bef);

									// NOTE : SET RCN_KIRIM SELETAH URUTAN
									$(td_rcn_kirim).find('input').val( numeral.formatDec(_rcn_kirim) );
									_rcn_kirim_before = _rcn_kirim;
								};
							};
						}
					};
				};
			};
		};
	}, // end - hit_rcn_kirim

	save_kpm: function(elm) {
		var div_action = $(elm).closest('div#action');

		var err = 0;

		$.map( $(div_action).find('tr.data'), function(tr) {
			let _ipt_umur1 = 0;
			let href1 = $(tr).find('td.umur1').data('href');
			$.map( $(tr).find('input[data-href="'+href1+'"], select[data-href="'+href1+'"]'), function(ipt) {
				if ( !empty( $(ipt).val() ) ) {
					_ipt_umur1 = 1;
				}
			});

			if ( _ipt_umur1 == 1 ) {
				$.map( $(tr).find('input[data-href="'+href1+'"], select[data-href="'+href1+'"]'), function(ipt) {
					if ( empty( $(ipt).val() ) ) {
						$(ipt).parent().addClass('has-error');
						err++;
					} else {
						$(ipt).parent().removeClass('has-error');
					}
				});				
			}

			let _ipt_umur2 = 0;
			let href2 = $(tr).find('td.umur2').data('href');
			$.map( $(tr).find('input[data-href="'+href2+'"], select[data-href="'+href2+'"]'), function(ipt) {
				if ( !empty( $(ipt).val() ) ) {
					_ipt_umur2 = 1;
				}
			});

			if ( _ipt_umur2 == 1 ) {
				$.map( $(tr).find('input[data-href="'+href2+'"], select[data-href="'+href2+'"]'), function(ipt) {
					if ( empty( $(ipt).val() ) ) {
						$(ipt).parent().addClass('has-error');
						err++;
					} else {
						$(ipt).parent().removeClass('has-error');
					}
				});				
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			var data = {};
			var detail = [];
			var _table = $(div_action).find('table');
			var jml_data = (($(_table).find('tr.data').length) * 2) - 1;
			var idx = 0;

			var noreg = $(div_action).find('select.noreg').val();
			var supplier = $(div_action).find('select.supplier').val();
			var jenis_pakan = null;
			var id = $(div_action).data('id');

			for (var i = 0; i < jml_data; i++) {
				var umur = i;
				var _umur1 = $(_table).find("td.umur1[data-umur="+i+"]");
				var _umur2 = $(_table).find("td.umur2[data-umur="+i+"]");

				// NOTE : KOLOM KIRI
				if ( _umur1.length > 0 ) {
					var href = $(_umur1).data('href');
					var exist = $(_umur1).data('exist');

					if ( exist != 'disabled' ) {
						var tr_umur = $(_umur1).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_setting = $(tr_umur).find('td[data-ipt='+href+']');
						var td_tgl = $(tr_umur).find('td.tanggal[data-href='+href+']');
						var ipt_tgl_kirim = $(tr_umur).find('input.datetimepicker[data-href='+href+']');
						var td_jns_pakan = $(tr_umur).find('td.jns_pakan');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_setting = $(td_setting).find('input').val();
						var val_tgl = $(td_tgl).data('tanggal');
						var val_tgl_kirim = dateSQL( $(ipt_tgl_kirim).data('DateTimePicker').date() );
						var val_jns_pakan = $(td_jns_pakan).find('select').val();

						// if ( i != _umur ) {
							if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
								detail[idx] = {
									'umur': umur,
									'tgl_umur': val_tgl,
									'setting': numeral.unformat(val_setting),
									'rcn_kirim': numeral.unformat(val_rcn_kirim),
									'tgl_kirim': val_tgl_kirim,
									'jns_pakan': val_jns_pakan
								};

								idx++;
							};
						// };
					};
				};

				// NOTE : KOLOM KANAN
				if ( _umur2.length > 0 ) {
					var href = $(_umur2).data('href');
					var exist = $(_umur2).data('exist');

					if ( exist != 'disabled' ) {
						var tr_umur = $(_umur2).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_setting = $(tr_umur).find('td[data-ipt='+href+']');
						var td_tgl = $(tr_umur).find('td.tanggal[data-href='+href+']');
						var ipt_tgl_kirim = $(tr_umur).find('input.datetimepicker[data-href='+href+']');
						var td_jns_pakan = $(tr_umur).find('td.jns_pakan2');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_setting = $(td_setting).find('input').val();
						var val_tgl = $(td_tgl).data('tanggal');
						var val_tgl_kirim = dateSQL( $(ipt_tgl_kirim).data('DateTimePicker').date() );
						var val_jns_pakan = $(td_jns_pakan).find('select').val();

						// if ( i != _umur ) {
							if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
								detail[idx] = {
									'umur': umur,
									'tgl_umur': val_tgl,
									'setting': numeral.unformat(val_setting),
									'rcn_kirim': numeral.unformat(val_rcn_kirim),
									'tgl_kirim': val_tgl_kirim,
									'jns_pakan': val_jns_pakan
								};

								idx++;
							};
						// };
					}
				};
			};

			data = {
				'noreg': noreg,
				'jenis_pakan': jenis_pakan,
				'supplier': supplier,
				'id': id,
				'detail': detail
			};

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data KPM ?', function(result) {
					if ( result ) {
						// console.log(data);
						kpm.exec_save_kpm(data);
					};
				});
			} else {
				bootbox.alert('Tidak ada data yang anda masukkan pada tabel KPM.');
			};
		};
	}, // end - save_kpm

	exec_save_kpm : function(params){
        $.ajax({
            url : 'transaksi/KPM/save_kpm',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        kpm.getLists();
                        kpm.load_form(data.content.id);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_kpm

    edit_kpm: function(elm) {
		var div_action = $(elm).closest('div#action');

		var err = 0;

		$.map( $(div_action).find('tr.data'), function(tr) {
			let _ipt_umur1 = 0;
			let href1 = $(tr).find('td.umur1').data('href');
			$.map( $(tr).find('input[data-href="'+href1+'"], select[data-href="'+href1+'"]'), function(ipt) {
				if ( !empty( $(ipt).val() ) ) {
					_ipt_umur1 = 1;
				}
			});

			if ( _ipt_umur1 == 1 ) {
				$.map( $(tr).find('input[data-href="'+href1+'"], select[data-href="'+href1+'"]'), function(ipt) {
					if ( empty( $(ipt).val() ) ) {
						$(ipt).parent().addClass('has-error');
						err++;
					} else {
						$(ipt).parent().removeClass('has-error');
					}
				});				
			}

			let _ipt_umur2 = 0;
			let href2 = $(tr).find('td.umur2').data('href');
			$.map( $(tr).find('input[data-href="'+href2+'"], select[data-href="'+href2+'"]'), function(ipt) {
				if ( !empty( $(ipt).val() ) ) {
					_ipt_umur2 = 1;
				}
			});

			if ( _ipt_umur2 == 1 ) {
				$.map( $(tr).find('input[data-href="'+href2+'"], select[data-href="'+href2+'"]'), function(ipt) {
					if ( empty( $(ipt).val() ) ) {
						$(ipt).parent().addClass('has-error');
						err++;
					} else {
						$(ipt).parent().removeClass('has-error');
					}
				});				
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			var data = {};
			var detail = [];
			var _table = $(div_action).find('table');
			var jml_data = (($(_table).find('tr.data').length) * 2) - 1;
			var idx = 0;

			var noreg = $(div_action).find('select.noreg').val();
			var supplier = $(div_action).find('select.supplier').val();
			var jenis_pakan = null;
			var id = $(div_action).data('id');

			for (var i = 0; i < jml_data; i++) {
				var umur = i;
				var _umur1 = $(_table).find("td.umur1[data-umur="+i+"]");
				var _umur2 = $(_table).find("td.umur2[data-umur="+i+"]");


				// NOTE : KOLOM KIRI
				if ( _umur1.length > 0 ) {
					var href = $(_umur1).data('href');
					var id_detail = $(_umur1).data('id');

					if ( !empty(id_detail) ) {
						var tr_umur = $(_umur1).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_setting = $(tr_umur).find('td[data-ipt='+href+']');
						var td_tgl = $(tr_umur).find('td.tanggal[data-href='+href+']');
						var div_tgl_kirim = $(tr_umur).find('.tgl-terima[data-href='+href+']');
						var td_jns_pakan = $(tr_umur).find('td.jns_pakan');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_setting = $(td_setting).find('input').val();
						var val_tgl = $(td_tgl).data('tanggal');
						var val_tgl_kirim = dateSQL( $(div_tgl_kirim).data('DateTimePicker').date() );
						var val_jns_pakan = $(td_jns_pakan).find('select').val();

						if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
							detail[idx] = {
								'id': id_detail,
								'umur': umur,
								'tgl_umur': val_tgl,
								'setting': numeral.unformat(val_setting),
								'rcn_kirim': numeral.unformat(val_rcn_kirim),
								'tgl_kirim': val_tgl_kirim,
								'jns_pakan': val_jns_pakan
							};

							idx++;
						};
					};
				};

				// NOTE : KOLOM KANAN
				if ( _umur2.length > 0 ) {
					var href = $(_umur2).data('href');
					var id_detail = $(_umur2).data('id');

					if ( !empty(id_detail) ) {
						var tr_umur = $(_umur2).closest('tr');
						var td_rcn_kirim = $(tr_umur).find('td.'+href);
						var td_setting = $(tr_umur).find('td[data-ipt='+href+']');
						var td_tgl = $(tr_umur).find('td.tanggal[data-href='+href+']');
						var div_tgl_kirim = $(tr_umur).find('.tgl-terima[data-href='+href+']');
						var td_jns_pakan = $(tr_umur).find('td.jns_pakan2');

						var val_rcn_kirim = $(td_rcn_kirim).find('input').val();
						var val_setting = $(td_setting).find('input').val();
						var val_tgl = $(td_tgl).data('tanggal');
						var val_tgl_kirim = dateSQL( $(div_tgl_kirim).data('DateTimePicker').date() );
						var val_jns_pakan = $(td_jns_pakan).find('select').val();

						if ( val_rcn_kirim != 0 && !empty(val_rcn_kirim) ) {
							detail[idx] = {
								'id': id_detail,
								'umur': umur,
								'tgl_umur': val_tgl,
								'setting': numeral.unformat(val_setting),
								'rcn_kirim': numeral.unformat(val_rcn_kirim),
								'tgl_kirim': val_tgl_kirim,
								'jns_pakan': val_jns_pakan
							};

							idx++;
						};
					}
				};
			};

			data = {
				'noreg': noreg,
				'jenis_pakan': jenis_pakan,
				'supplier': supplier,
				'id': id,
				'detail': detail
			};

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-ubah data KPM ?', function(result) {
					if ( result ) {
						// console.log(data);
						kpm.exec_edit_kpm(data);
					};
				});
			} else {
				bootbox.alert('Tidak ada data yang anda masukkan pada tabel KPM.');
			};
		};
	}, // end - edit_kpm

	exec_edit_kpm : function(params){
        $.ajax({
            url : 'transaksi/KPM/edit_kpm',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        kpm.getLists();
                        kpm.load_form(data.content.id);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_kpm

    delete_kpm: function(elm) {
    	let params = $(elm).data('id');

    	bootbox.confirm( 'Apakah anda yakin ingin meng-hapus data KPM ?', function(result) {
            if ( result ) {
		    	$.ajax({
		            url : 'transaksi/KPM/delete_kpm',
		            data : {
		                'params' : params
		            },
		            dataType : 'JSON',
		            type : 'POST',
		            beforeSend : function(){
		                showLoading();
		            },
		            success : function(data){
		                hideLoading();

		                if ( data.status == 1 ) {
		                    bootbox.alert(data.message, function() {
		                        kpm.getLists();
		                        kpm.changeTabActive(elm);
		                    });
		                } else {
		                    bootbox.alert(data.message);
		                };
		            }
		        });
		    }
		});
    }, // end - delete_kpm

	// get_datetimepicker_html: function(elm) {
	// 	setTimeout(function() {
	// 		var div = $(elm).closest('div#datetimepicker1');
	// 		var div_date = $(div).find('div.bootstrap-datetimepicker-widget').html();
	// 		console.log(div_date);
	// 	}, 3000);
	// }
};

kpm.start_up();