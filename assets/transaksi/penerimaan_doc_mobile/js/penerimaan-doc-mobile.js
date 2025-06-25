var formData = null;

var pdm = {
	start_up: function() {
		pdm.setting_up('riwayat', 'div#riwayat');
		pdm.setting_up('transaksi', 'div#transaksi');

        formData = new FormData();
	}, // end - start_up

	list_riwayat: function(elm) {
		var div_riwayat = $(elm).closest('div#riwayat');

		var noreg = $(div_riwayat).find('#select_noreg').val();

		var params = {
			'noreg': noreg
		};

		$.ajax({
            url: 'transaksi/PenerimaanDocMobile/list_riwayat',
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

		pdm.load_form(id, edit, href);
	}, // end - change_tab

	load_form: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/PenerimaanDocMobile/load_form',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

                pdm.setting_up('transaksi', 'div#transaksi');

                if ( !empty(edit) ) {
                	pdm.get_noreg( $('div#'+href).find('#select_mitra') );
                }

                formData = new FormData();

                hideLoading();
            }
        });
	}, // end - list_riwayat

	setting_up: function(jenis_div, div) {
		$(div).find('#select_no_order').selectpicker();

		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    pdm.get_noreg(this);
		});

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	pdm.get_no_order(this);
			}
		});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('[name=tanggal_kirim]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$('[name=tanggal_tiba]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
            sideBySide: true
		});

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
                $(ipt).data("DateTimePicker").minDate(moment(tgl).subtract(3, 'days'));
                // $(ipt).data("DateTimePicker").maxDate(moment(tgl).endOf('day'));
                $(ipt).data("DateTimePicker").maxDate(moment(tgl).add(3, 'days').endOf('day'));
            }
        });
	}, // end - setting_up

	showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG'];
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

            pdm.compress_img($(elm), null);
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
        var jenis = $(div).attr('id');
    	var nomor_mitra = $(div).find('#select_mitra').val();

    	if ( !empty(nomor_mitra) ) {
    		$.ajax({
	            url: 'transaksi/PenerimaanDocMobile/get_noreg',
	            data: { 
                    'params': nomor_mitra,
                    'jenis': jenis
                },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
                    var opt = '';
	                var noreg = $(div).find('select#select_noreg').attr('data-val');
	                if ( data.content.length > 0 ) {
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].noreg == noreg ) {
	                			selected = 'selected';
	                		}
	                		opt += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" data-populasi="'+data.content[i].populasi+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
	                	}
	                }
	                $(div).find('select#select_noreg').removeAttr('disabled');
                    if ( !empty(noreg) ) {
                        $(div).find('select#select_noreg').append(opt);
                    } else {
                        var option = '<option value="">Pilih Noreg</option>';
                        option += opt;
                        $(div).find('select#select_noreg').html(option);
                    }
	                $(div).find('#select_noreg').selectpicker('refresh');

	                hideLoading();

	                if ( !empty(noreg) ) {
	                	pdm.get_no_order( $(div).find('select#select_noreg'), noreg );
	                }
	            }
	        });
    	} else {
    		$(div).find('select#select_noreg').attr('disabled', 'disabled');
    		$(div).find('select#select_noreg').html('<option value="">Pilih Noreg</option>');
    		$(div).find('#select_noreg').selectpicker('refresh');
    	}
    }, // end - get_noreg

    get_no_order: function(elm, noreg = null) {
    	var div = $(elm).closest('.tab-pane');

        if ( empty(noreg) ) {
            noreg = $(elm).val();
        }
    	
    	var option = '<option value="">Pilih No. Order</option>';
    	if ( !empty(noreg) ) {
    		$.ajax({
	            url: 'transaksi/PenerimaanDocMobile/get_no_order',
	            data: { 'noreg': noreg },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	                if ( data.content.length > 0 ) {
	                	var no_order = $(div).find('select#select_no_order').data('val');
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].no_order == no_order ) {
	                			selected = 'selected';
	                		}
	                		option += '<option data-tokens="'+data.content[i].no_order+'" value="'+data.content[i].no_order+'" '+selected+'>'+data.content[i].no_order+'</option>';
	                	}
	                }
	                $(div).find('select#select_no_order').removeAttr('disabled');
	                $(div).find('select#select_no_order').html(option);
	                $(div).find('#select_no_order').selectpicker('refresh');

	                hideLoading();
	            }
	        });
    	} else {
    		$(div).find('select#select_no_order').attr('disabled', 'disabled');
    		$(div).find('select#select_no_order').html(option);
    		$(div).find('#select_no_order').selectpicker('refresh');
    	}
    }, // end - get_no_order

    hit_jml_box: function(elm) {
        let div = $(elm).closest('div#transaksi');

        var ekor = numeral.unformat( $(elm).val() );
        var box = 0;
        if ( ekor > 0 ) {
        	box = ekor / 100;
        }

        $(div).find('input.box').val( numeral.formatInt(box) );
    }, // end - hit_jml_box

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
            bootbox.alert( 'Harap lengkapi data penerimaan DOC.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                	// var formData = new FormData();

                    let data_ket = $.map( $(div).find('table.ket_doc tbody tr'), function(tr) {
                    	var ket = $(tr).find('textarea.keterangan').val();

                    	if ( !empty(ket) ) {
	                        let _data = {
	                            'keterangan': ket
	                        };

	          //               if ( !empty( $(tr).find('.file_lampiran_ket') ) ) {
		         //                var file_tmp = $(tr).find('.file_lampiran_ket').get(0).files[0];
		    					// formData.append('file['+ket+']', file_tmp);
	          //               }

	                        return _data;
                    	}
                    });

                    var no_order = $(div).find('#select_no_order').val();
                    var no_sj = $(div).find('input.no_sj').val().toUpperCase();

                    let data = {
                        'noreg': $(div).find('#select_noreg').val(),
                        'no_order': $(div).find('#select_no_order').val(),
                        'no_sj': no_sj,
                        'nopol': $(div).find('input.nopol').val().toUpperCase(),
                        'tiba': dateTimeSQL( $(div).find('#tanggal_tiba').data('DateTimePicker').date() ),
                        'jml_ekor': numeral.unformat( $(div).find('input.ekor').val() ),
                        'jml_box': numeral.unformat( $(div).find('input.box').val() ),
                        'kondisi': $(div).find('input.kondisi').val().toUpperCase(),
                        'kirim': dateSQL( $(div).find('#tanggal_kirim').data('DateTimePicker').date() ),
                        'bb': numeral.unformat( $(div).find('input.bb').val() ),
                        'uniformity': numeral.unformat( $(div).find('input.uniformity').val() ),
                        'data_ket': data_ket
                    };

         //            if ( !empty( $(div).find('.file_lampiran_sj') ) ) {
         //                var file_tmp = $(div).find('.file_lampiran_sj').get(0).files[0];
    					// formData.append('file['+no_sj+']', file_tmp);
         //            }

                    formData.append("data", JSON.stringify(data));

                    $.ajax({
			            url : 'transaksi/PenerimaanDocMobile/save',
			            dataType: 'JSON',
			            type: 'POST',
			            async:false,
			            processData: false,
			            contentType: false,
			            data: formData,
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert( data.message, function() {
			                        pdm.load_form(no_order, null, 'transaksi');
			                        // location.reload();
			                    });
			                } else {
			                    bootbox.alert( data.message );
			                }
			            },
			        });
                }
            });
        }
    }, // end - save

	edit: function(elm) {
        let div = $('div#transaksi');

		let err = 0;
        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data penerimaan DOC.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin meng-ubah data ?', function(result) {
                if ( result ) {
                    // var formData = new FormData();

                    let data_ket = $.map( $(div).find('table.ket_doc tbody tr'), function(tr) {
                    	var ket = $(tr).find('textarea.keterangan').val();

                    	if ( !empty(ket) ) {
	                        let _data = {
	                            'keterangan': ket,
	                            'lampiran_old': $(tr).find('.file_lampiran_ket').data('old')
	                        };

	          //               if ( !empty( $(tr).find('.file_lampiran_ket') ) ) {
		         //                var file_tmp = $(tr).find('.file_lampiran_ket').get(0).files[0];
		    					// formData.append('file['+ket+']', file_tmp);
	          //               }

	                        return _data;
                    	}
                    });

                    var no_order = $(div).find('#select_no_order').val();
                    var no_sj = $(div).find('input.no_sj').val().toUpperCase();

                    let data = {
                        'noreg': $(div).find('#select_noreg').val(),
                        'no_order': $(div).find('#select_no_order').val(),
                        'no_order_old': $(div).find('#select_no_order').data('old'),
                        'no_sj': no_sj,
                        'lampiran_sj_old': $(div).find('.file_lampiran_sj').data('old'),
                        'nopol': $(div).find('input.nopol').val().toUpperCase(),
                        'tiba': dateTimeSQL( $(div).find('#tanggal_tiba').data('DateTimePicker').date() ),
                        'jml_ekor': numeral.unformat( $(div).find('input.ekor').val() ),
                        'jml_box': numeral.unformat( $(div).find('input.box').val() ),
                        'kondisi': $(div).find('input.kondisi').val().toUpperCase(),
                        'kirim': dateSQL( $(div).find('#tanggal_kirim').data('DateTimePicker').date() ),
                        'bb': numeral.unformat( $(div).find('input.bb').val() ),
						'uniformity': numeral.unformat( $(div).find('input.uniformity').val() ),
                        'data_ket': data_ket
                    };

         //            if ( !empty( $(div).find('.file_lampiran_sj') ) ) {
         //                var file_tmp = $(div).find('.file_lampiran_sj').get(0).files[0];
    					// formData.append('file['+no_sj+']', file_tmp);
         //            }

                    formData.append("data", JSON.stringify(data));

                    $.ajax({
			            url : 'transaksi/PenerimaanDocMobile/edit',
			            dataType: 'JSON',
			            type: 'POST',
			            async:false,
			            processData: false,
			            contentType: false,
			            data: formData,
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert( data.message, function() {
			                        pdm.load_form(no_order, null, 'transaksi');
			                        // location.reload();
			                    });
			                } else {
			                    bootbox.alert( data.message );
			                }
			            },
			        });
                }
            });
        }
	}, // end - edit

	batal_edit: function(elm) {
		var id = $(elm).data('id');
		pdm.load_form(id, null, 'transaksi');
	}, // end - batal_edit

	delete: function() {
    	var div = $('div#transaksi');

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data penerimaan DOC ?', function(result) {
			if ( result ) {
				var data = {
					'no_order': $(div).find('div.no_order').data('val')
				};

				$.ajax({
		            url: 'transaksi/PenerimaanDocMobile/delete',
		            data: { 'params': data },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		var div_riwayat = $('div#riwayat');
		                		if ( !empty($(div_riwayat).find('select#select_mitra').val()) && !empty($(div_riwayat).find('select#select_noreg').val()) ) {
		                			$('button.tampilkan_riwayat').click();
		                		}

		                		$('button.tambah_penerimaan').click();
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            }
		        });
			}
		});
    }, // end - delete

    compress_img: function(elm, key) {
        showLoading();

        var tr = $(elm).closest('tr');
        var div = $('div#transaksi');

        var file_tmp = $(elm).get(0).files[0];

        if ( empty(key) ) {
            if ( $(elm).hasClass('file_lampiran_sj') ) {
                key = $(div).find('input.no_sj').val().toUpperCase();
            } else {
                key = $(tr).find('textarea.keterangan').val();
            }
        }

        ci.compress_img(file_tmp, file_tmp.name, 480, function(data) {
            formData.append('file['+key+']', data);

            hideLoading();
        });
    }, // end - compress_img
};

pdm.start_up();