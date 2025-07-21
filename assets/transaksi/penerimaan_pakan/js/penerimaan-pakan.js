var pp = {
	start_up: function () {
		pp.setting_up();
	}, // end - start_up

	setBindSHA1 : function(){
        $('input:file').off('change.sha1');
        $('input:file').on('change.sha1',function(){
            var elm = $(this);
            var file = elm.get(0).files[0];
            elm.attr('data-sha1', '');
            sha1_file(file).then(function (sha1) {
                elm.attr('data-sha1', sha1);
            });
        });
    }, // end - setBindSHA1

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('.date').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function(ipt) {
			var minDate = new Date();
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
            	minDate = new Date(tgl);
            }
            
            $(ipt).data("DateTimePicker").date(new Date(tgl));
        });

		App.setTutupBulan();

		pp.setBindSHA1();
	}, // end - setting_up

	changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var edit = $(elm).data('edit');
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

        if ( vhref == 'penerimaan' ) {
            var v_id = $(elm).attr('data-id');

            pp.load_form(v_id, edit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#penerimaan');

        $.ajax({
            url : 'transaksi/PenerimaanPakan/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
                hideLoading();
                $(dcontent).html(html);
                pp.setting_up();

                if ( !empty(v_id) ) {
                	$(dcontent).find('button.get_sj_not_terima').click();
                }
            },
        });
    }, // end - load_form

	get_lists: function() {
    	var div_riwayat = $('div#riwayat');

    	var start_date = $(div_riwayat).find('[name=startDate]').data('DateTimePicker').date();
		var end_date = $(div_riwayat).find('[name=endDate]').data('DateTimePicker').date();
		var kode_unit = $(div_riwayat).find('select.unit').val();

		if ( empty(start_date) || empty(end_date) ) {
			bootbox.alert('Harap lengkapi periode terlebih dahulu.');
		} else {
			var start_date = dateSQL( $(div_riwayat).find('[name=startDate]').data('DateTimePicker').date() );
			var end_date = dateSQL( $(div_riwayat).find('[name=endDate]').data('DateTimePicker').date() );

			var params = {
				'start_date': start_date,
				'end_date': end_date,
				'kode_unit': kode_unit
			};

			$.ajax({
				url: 'transaksi/PenerimaanPakan/get_lists',
				data: {'params': params},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function() {
					showLoading();
				},
				success: function(data) {
					hideLoading();
					if ( data.status == 1 ) {
						$(div_riwayat).find('table.tbl_penerimaan tbody').html( data.content );
					};
				},
		    });
		}
    }, // end - get_lists

    get_sj_not_terima: function(elm) {
    	var div_filter = $(elm).closest('div.filter');
    	var div_action = $(div_filter).closest('div#penerimaan');

    	var unit = $(div_filter).find('.unit').val();
    	var tgl_kirim = $(div_filter).find('div#tgl_kirim input').val();

    	if ( empty(unit) || empty(tgl_kirim) ) {
    		bootbox.alert('Harap isi data filter terlebih dahulu.');
    	} else {
    		var params = {
    			'unit': unit,
    			'tgl_kirim': dateSQL( $(div_filter).find('[name=tgl_kirim]').data('DateTimePicker').date() )
    		};

    		$.ajax({
				url: 'transaksi/PenerimaanPakan/get_sj_not_terima',
				data: {
					'params': params
				},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function() {
					showLoading();
				},
				success: function(data) {
					var id = $(div_action).find('select.no_sj').attr('data-id');
					var no_sj = $(div_action).find('select.no_sj').attr('data-nosj');

					var selected = '';
					var option = '<option value="">-- Pilih No. SJ --</option>';
					if ( !empty(no_sj) && no_sj != 'undefined' ) {
						selected = 'selected';
                        option += '<option value="'+id+'" '+selected+' >'+no_sj+'</option>';
                    }
					if ( data.content.length > 0 ) {
						for (var i = 0; i < data.content.length; i++) {
							if ( !empty(id) ) {
								if ( id == data.content[i].id ) {
									if ( empty(selected) ) {
										selected = 'selected';
									}
								}
							}
							option += '<option value="'+data.content[i].id+'" >'+data.content[i].no_sj+'</option>';
						}
					}

					$(div_action).find('select.no_sj').removeAttr('disabled');
					$(div_action).find('select.no_sj').html(option);

					hideLoading();
				},
		    });
    	}
    }, // end - get_sj_not_terima

	get_data_by_sj: function (elm) {
		var div_penerimaan = $('div#penerimaan')
		var id_kirim = $(elm).val();

		$.ajax({
			url: 'transaksi/PenerimaanPakan/get_data_by_sj',
			data: {
				'id_kirim': id_kirim
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();

				$('input.no_pol').val( data.content.no_pol );
				$('input.ekspedisi').val( data.content.ekspedisi );
				$('input.sopir').val( data.content.sopir );
				$('input.jenis_kirim').attr( 'data-kode', data.content.kode_jenis_kirim );
				$('input.jenis_kirim').val( data.content.jenis_kirim );
				$('input.no_order').val( data.content.no_order );
				$('input.tgl_kirim').val( data.content.tgl_kirim );
				$('input.asal').val( data.content.asal );
				$('input.tujuan').val( data.content.tujuan );

				var tr = null;
				if ( data.content.detail.length > 0 ) {
					var length = data.content.detail.length;
					for (var i = 0; i < length; i++) {
						tr += '<tr class="v-center">';
						tr += '<td class="barang" data-kode="'+data.content.detail[i].item+'">'+data.content.detail[i].nama+'</td>';
						tr += '<td class="text-right">'+numeral.formatInt(data.content.detail[i].jumlah)+'</td>';
						tr += '<td>'+data.content.detail[i].kondisi+'</td>';
						tr += '<td><input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="integer" data-required="1" value="'+numeral.formatInt(data.content.detail[i].jumlah)+'"></td>';
						tr += '<td><input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1"></td>';
						tr += '</tr>';
					}
				}

				$(div_penerimaan).find('tbody').html(tr);

				pp.setting_up();
			},
	    });
	}, // end - get_data_by_sj

	save_terima_pakan: function() {
		var div_penerimaan = $('div#penerimaan');

		var err = 0;
		$.map( $(div_penerimaan).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
				if ( result ) {
					var detail = $.map( $(div_penerimaan).find('tbody tr'), function(tr) {
						var _detail = {
							'barang': $(tr).find('td.barang').data('kode'),
							'jumlah': numeral.unformat($(tr).find('input.jumlah').val()),
							'kondisi': $(tr).find('input.kondisi').val(),
						};

						return _detail;
					});

					var data = {
						'id_kirim_pakan' : $('select.no_sj').val(),
						'tgl_terima' : dateSQL( $('[name=tgl_terima]').data('DateTimePicker').date() ),
						'kode_jenis_kirim' : $('input.jenis_kirim').attr('data-kode'),
						'detail' : detail
					};

					// var file_tmp = $('.file_lampiran_sj').get(0).files[0];
					var file_tmp = null;

					pp.exec_save_terima_pakan( data, file_tmp );
				}
			});
		}
	}, // end - save_terima_pakan

	exec_save_terima_pakan: function(data = null, file_tmp) {
		var table = $('table');
		var tbody = $(table).find('tbody');

		var formData = new FormData();

		formData.append("data", JSON.stringify(data));
        // formData.append('file', file_tmp);

		$.ajax({
			url: 'transaksi/PenerimaanPakan/save',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				if ( data.status == 1 ) {
					// pp.hitungStokAwal( data.content.id_terima );
					pp.hitungStokByTransaksi(data.content);
				} else {
					hideLoading();
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_save_terima_pakan

	hitungStokAwal: function(id_terima) {
		var params = {
			'id_terima': id_terima
		};

		$.ajax({
			url: 'transaksi/PenerimaanPakan/hitungStokAwal',
			dataType: 'json',
            type: 'post',
            data: {
            	'params': params
            },
			beforeSend: function() {},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function() {
						var div_riwayat = $('div#riwayat');
				    	var start_date = $(div_riwayat).find('[name=startDate]').data('DateTimePicker').date();
						var end_date = $(div_riwayat).find('[name=endDate]').data('DateTimePicker').date();
						if ( !empty(start_date) && !empty(end_date) ) {
							// pp.get_lists();
						}

						var btn = '<button data-href="riwayat">';
						pp.changeTabActive(btn);
						pp.load_form();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokAwal

	edit_terima_pakan: function(elm) {
		var div_penerimaan = $('div#penerimaan');

		var err = 0;
		$.map( $(div_penerimaan).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
				if ( result ) {
					var detail = $.map( $(div_penerimaan).find('tbody tr'), function(tr) {
						var _detail = {
							'barang': $(tr).find('td.barang').data('kode'),
							'jumlah': numeral.unformat($(tr).find('input.jumlah').val()),
							'kondisi': $(tr).find('input.kondisi').val(),
						};

						return _detail;
					});

					var data = {
						'id' : $(elm).data('id'),
						'id_kirim_pakan' : $('select.no_sj').val(),
						'tgl_terima' : dateSQL( $('[name=tgl_terima]').data('DateTimePicker').date() ),
						'kode_jenis_kirim' : $('input.jenis_kirim').attr('data-kode'),
						'detail' : detail
					};

					// var file_tmp = $('.file_lampiran_sj').get(0).files[0];
					var file_tmp = null;

					pp.exec_edit_terima_pakan( data, file_tmp );
				}
			});
		}
	}, // end - edit_terima_pakan

	exec_edit_terima_pakan: function(data = null, file_tmp) {
		var table = $('table');
		var tbody = $(table).find('tbody');

		var formData = new FormData();

		formData.append("data", JSON.stringify(data));
        // formData.append('file', file_tmp);

		$.ajax({
			url: 'transaksi/PenerimaanPakan/edit',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				if ( data.status == 1 ) {
					// pp.hitungStokAwal( data.content.id_terima );
					pp.hitungStokByTransaksi(data.content);
				} else {
					hideLoading();
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_terima_pakan

	delete: function(elm) {
		var id = $(elm).data('id');

		var params = {'id': id};

		bootbox.confirm('Apakah anda yakin ingin menghapus data ?', function(result) {
			if ( result ) {
				$.ajax({
					url: 'transaksi/PenerimaanPakan/delete',
					data: {
						'params': params
					},
					type: 'POST',
					dataType: 'JSON',
					beforeSend: function() {
						showLoading();
					},
					success: function(data) {
						if ( data.status == 1 ) {
							pp.hitungStokByTransaksi(data.content);
							// bootbox.alert(data.message, function() {
							// 	pp.get_lists();
							// 	pp.load_form();
							// });
						} else {
							hideLoading();
							bootbox.alert(data.message);
						};
					},
			    });
			}
		});
	}, // end - delete

	hitungStokByTransaksi: function(content) {
		var params = content;

		$.ajax({
			url: 'transaksi/PenerimaanPakan/hitungStokByTransaksi',
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
						pp.get_lists();
						pp.load_form();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokByTransaksi

	listActivity: function(elm) {
		let tr = $(elm).closest('tr');

        let params = {
            'id' : $(elm).data('id'),
            'no_sj' : $(tr).find('td.no_sj').text(),
            'tgl_terima' : $(tr).find('td.tgl_terima').text(),
            'asal' : $(tr).find('td.asal').text(),
            'tujuan' : $(tr).find('td.tujuan').text(),
            'nopol' : $(tr).find('td.nopol').text(),
        }

        $.get('transaksi/PenerimaanPakan/listActivity',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                $(modal_dialog).css({'max-width' : '80%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});
            });
        },'html');
	}, // end - listActivity
};

pp.start_up();