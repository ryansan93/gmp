var pvm = {
	start_up: function() {
		pvm.setting_up('riwayat', 'div#riwayat');
		pvm.setting_up('transaksi', 'div#transaksi');
	}, // end - start_up

	list_riwayat: function(elm) {
		var div_riwayat = $(elm).closest('div#riwayat');

		var noreg = $(div_riwayat).find('#select_noreg').val();

		var params = {
			'noreg': noreg
		};

		$.ajax({
            url: 'transaksi/PenerimaanVoadipMobile/list_riwayat',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                hideLoading();

                $('table.tbl_riwayat').find('tbody').html( data.html );
            }
        });
	}, // end - list_riwayat

	change_tab: function(elm) {
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		pvm.load_form(id, edit, href);
	}, // end - change_tab

	load_form: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/PenerimaanVoadipMobile/load_form',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

                pvm.setting_up('transaksi', 'div#transaksi');

                if ( !empty(edit) ) {
                	pvm.get_noreg( $('div#'+href).find('#select_mitra') );
                }

                hideLoading();
            }
        });
	}, // end - list_riwayat

	setting_up: function(jenis_div, div) {
		$(div).find('#select_no_sj').selectpicker();

		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    pvm.get_noreg(this);
		});

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	pvm.get_no_sj(this);
			}
		});

        $(div).find('#select_no_sj').selectpicker();
        $(div).find('#select_no_sj').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
            if ( jenis_div == 'transaksi' ) {
                pvm.get_data_sj(this);
            }
        });

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('[name=tanggal_tiba]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
                $(ipt).data("DateTimePicker").minDate(moment(tgl));
                $(ipt).data("DateTimePicker").maxDate(moment(new Date()));
            }
        });
	}, // end - setting_up

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

	add_row: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var tr_clone = $(tr).clone();

		$(tr_clone).find('input, textarea').val('');
		$(tr_clone).find('input.file_lampiran_ket').removeAttr('data-old');
		$(tr_clone).find('a').addClass('hide');

		$(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$(tr).closest('tbody').append(tr_clone);

		var no_urut = 0;
		$.map( $(tbody).find('tr'), function(tr) {
			no_urut++;
			$(tr).find('td.no_urut').text( no_urut );
		});
	}, // end - add_row

	remove_row: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr').length > 1 ) {
			$(elm).closest('tr').remove();
		}
	}, // end - remove_row

    get_noreg: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var nomor_mitra = $(div).find('#select_mitra').val();

    	var option = '<option value="">Pilih Noreg</option>';
    	if ( !empty(nomor_mitra) ) {
    		$.ajax({
	            url: 'transaksi/PenerimaanVoadipMobile/get_noreg',
	            data: { 'params': nomor_mitra },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	                var noreg = $(div).find('select#select_noreg').data('val');
	                if ( data.content.length > 0 ) {
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].noreg == noreg ) {
	                			selected = 'selected';
	                		}
	                		option += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" data-populasi="'+data.content[i].populasi+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
	                	}
	                }
	                $(div).find('select#select_noreg').removeAttr('disabled');
	                $(div).find('select#select_noreg').html(option);
	                $(div).find('#select_noreg').selectpicker('refresh');

	                hideLoading();

	                if ( !empty(noreg) ) {
	                	pvm.get_no_sj( $(div).find('select#select_noreg') );
	                }
	            }
	        });
    	} else {
    		$(div).find('select#select_noreg').attr('disabled', 'disabled');
    		$(div).find('select#select_noreg').html(option);
    		$(div).find('#select_noreg').selectpicker('refresh');
    	}
    }, // end - get_noreg

    get_no_sj: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var noreg = $(elm).val();

        var no_sj = $(div).find('select#select_no_sj').data('val');
    	
    	var option = '<option value="">Pilih No. SJ</option>';
    	if ( !empty(noreg) ) {
    		$.ajax({
	            url: 'transaksi/PenerimaanVoadipMobile/get_no_sj',
	            data: { 
                    'noreg': noreg,
                    'no_sj': no_sj
                },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	                if ( data.content.length > 0 ) {
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].no_sj == no_sj ) {
	                			selected = 'selected';
	                		}
	                		option += '<option data-tokens="'+data.content[i].no_sj+'" value="'+data.content[i].no_sj+'" '+selected+'>'+data.content[i].tgl_kirim_after_format+' | '+data.content[i].no_sj+'</option>';
	                	}
	                }
	                $(div).find('select#select_no_sj').removeAttr('disabled');
	                $(div).find('select#select_no_sj').html(option);
	                $(div).find('#select_no_sj').selectpicker('refresh');

	                hideLoading();
	            }
	        });
    	} else {
    		$(div).find('select#select_no_sj').attr('disabled', 'disabled');
    		$(div).find('select#select_no_sj').html(option);
    		$(div).find('#select_no_sj').selectpicker('refresh');
    	}
    }, // end - get_no_sj

    get_data_sj: function(elm) {
        var div = $(elm).closest('.tab-pane');
        var no_sj = $(elm).val();
        
        if ( !empty(no_sj) ) {
            $.ajax({
                url: 'transaksi/PenerimaanVoadipMobile/get_data_sj',
                data: { 'no_sj': no_sj },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(){ showLoading() },
                success: function(data){
                    $(div).find('input.asal').val( data.content.asal );
                    $(div).find('input.nopol').val( data.content.nopol );
                    $(div).find('input.sopir').val( data.content.sopir );
                    $(div).find('input.ekspedisi').val( data.content.ekspedisi );

                    $(div).find('#tanggal_tiba input').removeAttr('disabled');
                    $(div).find('#tanggal_tiba').data("DateTimePicker").minDate(moment(data.content.tgl_kirim));
                    $(div).find('#tanggal_tiba').data("DateTimePicker").maxDate(moment(new Date()));

                    $(div).find('.data_brg tbody').html( data.content.html_detail );
                    $(div).find('.data_brg tbody').find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                        $(this).priceFormat(Config[$(this).data('tipe')]);
                    });

                    hideLoading();
                }
            });
        } else {
            $(div).find('input.asal, input.nopol, input.sopir, input.ekspedisi').val('');

            $(div).find('div#tanggal_tiba input').attr('disabled', 'disabled');
            $(div).find('div#tanggal_tiba input').val('');
            $(div).find('.data_brg tbody').html('<tr><td colspan="4">Data tidak ditemukan.</td></tr>');
        }
    }, // end - get_data_sj

	save: function(elm) {
        let err = 0;
        let div = $(elm).closest('div#transaksi');

        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan voadip.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let data_brg = $.map( $(div).find('table.data_brg tbody tr'), function(tr) {
                        let _data = {
                            'kode_brg': $(tr).find('td.brg').data('kode'),
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah_terima').val() ),
                            'kondisi': $(tr).find('input.kondisi').val()
                        };

	                    return _data;
                    });

                    var no_sj = $(div).find('#select_no_sj').val();

                    let data = {
                        'no_sj': no_sj,
                        'tiba': dateSQL( $(div).find('#tanggal_tiba').data('DateTimePicker').date() ),
                        'data_brg': data_brg
                    };

                    $.ajax({
			            url : 'transaksi/PenerimaanVoadipMobile/save',
			            dataType: 'JSON',
			            type: 'POST',
			            data: {'params': data},
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                if ( data.status == 1 ) {
			                    // pvm.hitungStokAwal( data.content.id_terima, no_sj );
                                pvm.hitungStokByTransaksi(data.content, no_sj);
			                } else {
                                hideLoading();
			                    bootbox.alert( data.message );
			                }
			            },
			        });
                }
            });
        }
    }, // end - save

    hitungStokAwal: function(id_terima, no_sj) {
        var params = {
            'id_terima': id_terima
        };

        $.ajax({
            url: 'transaksi/PenerimaanVoadipMobile/hitungStokAwal',
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
                        pvm.load_form(no_sj, null, 'transaksi');
                    });
                } else {                    
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - hitungStokAwal

    hitungStokByTransaksi: function(content, no_sj) {
        var params = content;

        $.ajax({
            url: 'transaksi/PenerimaanVoadipMobile/hitungStokByTransaksi',
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
                        pvm.load_form(no_sj, null, 'transaksi');
                    });
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - hitungStokByTransaksi

	// edit: function(elm) {
 //        let err = 0;
 //        let div = $(elm).closest('div#transaksi');

 //        $.map( $(div).find('[data-required=1]'), function(ipt) {
 //            if ( empty($(ipt).val()) ) {
 //                $(ipt).parent().addClass('has-error');
 //                err++;
 //            } else {
 //                $(ipt).parent().removeClass('has-error');
 //            }
 //        });

 //        if ( err > 0 ) {
 //            bootbox.alert( 'Harap lengkapi data penerimaan voadip.' );
 //        } else {
 //            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
 //                if ( result ) {
 //                    let data_brg = $.map( $(div).find('table.data_brg tbody tr'), function(tr) {
 //                        let _data = {
 //                            'kode_brg': $(tr).find('td.brg').data('kode'),
 //                            'jumlah': numeral.unformat( $(tr).find('input.jumlah_terima').val() ),
 //                            'kondisi': $(tr).find('input.kondisi').val()
 //                        };

 //                        return _data;
 //                    });

 //                    var no_sj = $(div).find('#select_no_sj').val();

 //                    let data = {
 //                        'no_sj': no_sj,
 //                        'no_sj_old': $(div).find('#select_no_sj').data('old'),
 //                        'tiba': dateSQL( $(div).find('#tanggal_tiba').data('DateTimePicker').date() ),
 //                        'data_brg': data_brg
 //                    };

 //                    $.ajax({
 //                        url : 'transaksi/PenerimaanVoadipMobile/edit',
 //                        dataType: 'JSON',
 //                        type: 'POST',
 //                        data: {'params': data},
 //                        beforeSend : function(){ showLoading() },
 //                        success : function(data){
 //                            hideLoading();
 //                            if ( data.status == 1 ) {
 //                                bootbox.alert( data.message, function() {
 //                                    pvm.load_form(no_sj, null, 'transaksi');
 //                                    // location.reload();
 //                                });
 //                            } else {
 //                                bootbox.alert( data.message );
 //                            }
 //                        },
 //                    });
 //                }
 //            });
 //        }
	// }, // end - edit

	// batal_edit: function(elm) {
	// 	var id = $(elm).data('id');
	// 	pvm.load_form(id, null, 'transaksi');
	// }, // end - batal_edit

	// delete: function() {
 //    	var div = $('div#transaksi');

 //    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data penerimaan voadip ?', function(result) {
	// 		if ( result ) {
	// 			var data = {
	// 				'no_sj': $(div).find('div.no_sj').data('val')
	// 			};

	// 			$.ajax({
	// 	            url: 'transaksi/PenerimaanVoadipMobile/delete',
	// 	            data: { 'params': data },
	// 	            type: 'POST',
	// 	            dataType: 'JSON',
	// 	            beforeSend: function(){ showLoading() },
	// 	            success: function(data){
	// 	                hideLoading();
	// 	                if ( data.status == 1 ) {
	// 	                	bootbox.alert( data.message, function() {
	// 	                		var div_riwayat = $('div#riwayat');
	// 	                		if ( !empty($(div_riwayat).find('select#select_mitra').val()) && !empty($(div_riwayat).find('select#select_noreg').val()) ) {
	// 	                			$('button.tampilkan_riwayat').click();
	// 	                		}

	// 	                		$('button.tambah_penerimaan').click();
	// 	                	});
	// 	                } else {
	// 	                	bootbox.alert( data.message );
	// 	                }
	// 	            }
	// 	        });
	// 		}
	// 	});
 //    }, // end - delete
};

pvm.start_up();