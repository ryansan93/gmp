const options = {
	enableHighAccuracy: true,
	timeout: 30000,
	maximumAge: 0,
};

var formData = null;

var lhk = {
	start_up: function() {
		lhk.setting_lhk('riwayat', 'div#riwayat');
		lhk.setting_lhk('transaksi', 'div#transaksi');
	}, // end - start_up

	list_riwayat: function(elm) {
		var div_riwayat = $(elm).closest('div#riwayat');

		var noreg = $(div_riwayat).find('#select_noreg').val();

		var params = {
			'noreg': noreg
		};

		$.ajax({
            url: 'transaksi/LHK/list_riwayat',
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

		lhk.load_form(id, edit, href);
	}, // end - change_tab

	load_form: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/LHK/load_form',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

                lhk.setting_lhk('transaksi', 'div#transaksi');

                if ( !empty(edit) ) {
                	lhk.get_noreg( $('div#'+href).find('#select_mitra') );
                }

                formData = new FormData();

                hideLoading();
            }
        });
	}, // end - list_riwayat

	setting_lhk: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    lhk.get_noreg(this);
		});

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	lhk.set_umur(this);
			}
		});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('input[type=file]').on('change', function() {
			lhk.cek_type_file(this);
		});

		$('.date').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });

        $("#tanggal").on("dp.change", function (e) {
			lhk.set_umur(this);
		});
	}, // end - setting_lhk

	add_row: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var tr_clone = $(tr).clone();

		$(tr_clone).find('input').val('');

		$(tr).closest('tbody').append(tr_clone);

		var no_urut = 0;
		$.map( $(tbody).find('tr'), function(tr) {
			no_urut++;
			$(tr).find('td.no_urut').text( no_urut );
		});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - add_row

	remove_row: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr').length > 1 ) {
			$(elm).closest('tr').remove();
			var no_urut = 0;
			$.map( $(tbody).find('tr'), function(tr) {
				no_urut++;
				$(tr).find('td.no_urut').text( no_urut );
			});
		}
	}, // end - remove_row

    get_noreg: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var div_id = $(div).attr('id');

    	var nomor_mitra = $(div).find('#select_mitra').val();

    	var noreg = null;
		if ( div_id != 'riwayat' ) {
			noreg = $(div).find('#select_noreg').attr('data-val');;
		}

    	var option = '<option value="">Pilih Noreg</option>';
    	if ( !empty(nomor_mitra) ) {
    		$.ajax({
	            url: 'transaksi/LHK/get_noreg',
	            data: { 
					'params': nomor_mitra,
					'noreg': noreg,
					'div_id': div_id
				},
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
	                		option += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" data-tgllhkterakhir="'+data.content[i].tgl_lhk_terakhir+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
	                	}
	                }
	                $(div).find('select#select_noreg').removeAttr('disabled');
	                $(div).find('select#select_noreg').html(option);
	                $(div).find('#select_noreg').selectpicker('refresh');

	                if ( !empty( noreg ) ) {
	                	lhk.set_umur( $(div).find('select#select_noreg') );
	                }

	                hideLoading();
	            }
	        });
    	} else {
    		$(div).find('select#select_noreg').attr('disabled', 'disabled');
    		$(div).find('select#select_noreg').html(option);
    		$(div).find('#select_noreg').selectpicker('refresh');
    	}
    }, // end - get_noreg

    set_umur: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var div_tgl = $(div).find('div#tanggal');

    	var ipt_tgl = $(div_tgl).find('input');
    	var select_noreg = $(div).find('#select_noreg');

    	$(ipt_tgl).removeAttr('disabled');

    	var umur = 0;
    	if ( !empty($(select_noreg).val()) ) {
    		if ( !empty($(ipt_tgl).val()) ) {
	    		var tgl_docin = $(select_noreg).find('option:selected').data('tgldocin');
	    		// var minDate = moment(tgl_docin);
	    		// var tgl_lhk_terahir = $(select_noreg).find('option:selected').data('tgllhkterakhir');
	    		// if ( !empty(tgl_lhk_terahir) ) {
	    		// 	minDate = moment(tgl_lhk_terahir);
	    		// }
	    		// var maxDate = moment();

	    		// $(div_tgl).data('DateTimePicker').minDate( minDate );
	    		// $(div_tgl).data('DateTimePicker').maxDate( maxDate );

	    		var tgl = dateSQL($(div_tgl).data('DateTimePicker').date());

	    		var umur = (App.selisihWaktuDalamHari(tgl_docin.substring(0, 10), tgl.substring(0, 10)));

	    		$(div).find('input[name=umur]').val( umur );
	    		$(div).find('td.umur').text( umur );

				$(div).find('button[data-target="#myPeralatan"]').removeAttr('disabled');

				var datetime = new Date();
				var _day = datetime.getDate();
				var day = (_day < 10) ? '0'+_day : _day;
				var _month = datetime.getMonth()+1;
				var month = (_month < 10) ? '0'+_month : _month;
				var year = datetime.getFullYear();
				var _hour = datetime.getHours();
				var hour = (_hour < 10) ? '0'+_hour : _hour;
				var _minute = datetime.getMinutes();
				var minute = (_minute < 10) ? '0'+_minute : _minute;

				var date = year+'-'+month+'-'+day+' '+hour+':'+minute;
				var datename = App.dateName( date );
				var date_view = datename.toUpperCase()+', '+day+'/'+month+'/'+year+' '+hour+':'+minute;

				$(div).find('td.waktu').attr('data-val', date);
				$(div).find('td.waktu').text( date_view );
	    	}
    	} else {
    		$(ipt_tgl).attr('disabled', 'disabled');
    		$(ipt_tgl).val('');
    		$(div).find('input[name=umur]').val('');

			$(div).find('button[data-target="#myPeralatan"]').attr('disabled', 'disabled');

			$(div).find('table.tbl_peralatan td.umur').text(null);
			$(div).find('table.tbl_peralatan td.waktu').text(null);
			$(div).find('table.tbl_peralatan td.waktu').removeAttr('data-val');
			$(div).find('table.tbl_peralatan input:not([type="radio"])').val(null);
			$(div).find('table.tbl_peralatan input[type="radio"]').prop('checked', false);
    	}
    }, // end - set_umur

    cek_type_file: function(elm) {
    	var div_attachment = $(elm).closest('div.attachment');
    	var files = $(div_attachment).find('.file_lampiran')[0].files;

    	var _allowtypes = ['jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG'];
    	if ( files.length > 0 ) {
    		for (var i = 0; i < files.length; i++) {
		        var _type = files[i]['name'].split('.').pop();

	    		if (in_array(_type, _allowtypes)) {
	    			$(div_attachment).next('div.preview_file_attachment').removeAttr('data-url');
		        } else {
		            $(elm).val('');
		            bootbox.alert('Format file yang anda pilih tidak sesuai (JPG, JPEG, PNG).<br>Mohon pilih ulang file.');
		        }
		    }
    	}
    }, // end - cek_type_file

    cek_file_exist: function(elm) {
    	var td = $(elm).closest('td');

    	var ipt_file = $(td).find('input[type=file]');

    	if ( !empty($(ipt_file).val()) ) {
    		$(td).find('div.upload').removeClass('hide');
    	} else {
    		$(td).find('div.upload').addClass('hide');
    	}
    }, // end - cek_file_exist

	uploadSisaPakan: function(elm) {
		var contain = $(elm).closest('div.contain');

		var url = $(elm).attr('data-url');
		
		var formData = new FormData();

		var files = $(elm)[0].files;

		showLoading('Upload Foto . . .');

		ci.compress(files, 480, function(data) {
			for (var i = 0; i < data.length; i++) {
				formData.append("attachments[sisa_pakan]["+i+"]", data[i]);
			}

			formData.append("url", JSON.stringify(url));
	
			$.ajax({
				url: 'transaksi/LHK/uploadSisaPakan',
				dataType: 'json',
				type: 'post',
				async:false,
				processData: false,
				contentType: false,
				data: formData,
				beforeSend: function() {},
				success: function(data) {
					hideLoading();

					if ( data.status == 1 ) {
						$(contain).find('.preview_file_attachment').attr('data-url', data.content.path_folder);
						// $("[data-target='#myNekropsi']").click();
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	}, // end - uploadSisaPakan

	uploadKematian: function(elm) {
		var contain = $(elm).closest('div.contain');

		var url = $(elm).attr('data-url');
		
		var formData = new FormData();

		var files = $(elm)[0].files;

		showLoading('Upload Foto . . .');

		ci.compress(files, 480, function(data) {
			for (var i = 0; i < data.length; i++) {
				formData.append("attachments[kematian]["+i+"]", data[i]);
			}

			formData.append("url", JSON.stringify(url));
	
			$.ajax({
				url: 'transaksi/LHK/uploadKematian',
				dataType: 'json',
				type: 'post',
				async:false,
				processData: false,
				contentType: false,
				data: formData,
				beforeSend: function() {},
				success: function(data) {
					hideLoading();

					if ( data.status == 1 ) {
						$(contain).find('.preview_file_attachment').attr('data-url', data.content.path_folder);
						// $("[data-target='#myNekropsi']").click();
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	}, // end - uploadKematian

	checkboxCheck: function (elm) {
		var tr = $(elm).closest('tr');

		$(tr).find('.attachment').addClass('disable');
		$(tr).find('input[type="file"]').attr('disabled', 'disabled');

		var checked = 0;
		var dirStatus = $(elm).attr('data-dirstatus');
		if ( $(elm).is(':checked') ) {
			$(tr).find('.attachment').removeClass('disable');
			$(tr).find('input[type="file"]').removeAttr('disabled');
			checked = 1;
		}

		var id = $(tr).attr('data-id');

		$.ajax({
			url: 'transaksi/LHK/createFolderNekropsi',
			data: {
				'id_nekropsi': id,
				'checked': checked,
				'dirStatus': dirStatus
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() { 
				// showLoading(); 
			},
			success: function(data) {
				// hideLoading();

				if ( data.status != 1 ) {
					bootbox.alert(data.message);
				}
			}
		});
	}, // end - checkboxCheck

	uploadNekropsi: function(elm) {
		var tr = $(elm).closest('tr');
		
		var formData = new FormData();
		var foto_nekropsi = [];
		var id_nekropsi = $(tr).attr('data-id');

		var files = $(elm)[0].files;

		// $("#myNekropsi").modal('hide');

		ci.compress(files, 480, function(_data) {
			for (var i = 0; i < _data.length; i++) {
				formData.append("attachments[nekropsi]["+id_nekropsi+"]["+i+"]", _data[i]);

				_temp_url = _data[i].name;

				foto_nekropsi.push(_temp_url);
			}

			var  data = {
				'id': id_nekropsi,
				'foto_nekropsi': foto_nekropsi
			};
	
			formData.append("data", JSON.stringify(data));
	
			$.ajax({
				url: 'transaksi/LHK/uploadNekropsi',
				dataType: 'json',
				type: 'post',
				async:false,
				processData: false,
				contentType: false,
				data: formData,
				beforeSend: function() { showLoading('Upload Foto . . .'); },
				success: function(data) {
					hideLoading();

					if ( data.status == 1 ) {
						$(tr).find('.preview_file_attachment').attr('data-url', data.content.path_folder);
						$("[data-target='#myNekropsi']").click();
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	}, // end - uploadNekropsi

	preview_file_attachment: function(elm) {
    	var div_attachment = $(elm).prev('div.attachment');

		var judul = $(elm).attr('data-title');
    	var jenis = $(elm).attr('data-jenis');
    	var data_url = $(elm).attr('data-url');

		// var _url = [];
    	// if ( empty(data_url) ) {
    	// 	if ( $(div_attachment).length > 0 ) {
		//     	var files = $(div_attachment).find('.file_lampiran')[0].files;
		    	
		// 		for (var i = 0; i < files.length; i++) {
		// 			_temp_url = URL.createObjectURL(files[i]);

		// 			_url.push(_temp_url);
		// 		}
    	// 	}
    	// } else {
    	// 	var _data_url = JSON.parse(data_url);
    	// 	for(var i in _data_url) {
    	// 		_url.push('uploads/'+_data_url[i]);
    	// 	}
    	// }

    	if ( !empty(data_url) && data_url.length > 0 ) {
			$.get('transaksi/LHK/preview_file_attachment',{
					'data_url': data_url,
					'judul': judul,
					'jenis': jenis
				},function(data){
				var _options = {
					className : 'veryWidth',
					message : data,
					size : 'large',
				};
				bootbox.dialog(_options).bind('shown.bs.modal', function(){
					var modal_body = $(this).find('.modal-body');
					var table = $(modal_body).find('table');
					var tbody = $(table).find('tbody');
					if ( $(tbody).find('.modal-body tr').length <= 1 ) {
				        $(this).find('tr #btn-remove').addClass('hide');
				    };

				    $(this).find('button.close').click(function() {
				    	$('div.modal.show').css({'overflow': 'auto'});
				    });
				});
			},'html');
    	} else {
    		bootbox.alert('Tidak ada file yang akan di tampilkan.');
    	}
	}, // end - preview_file_attachment

	/* 
	get_pakan: function(noreg) {
		$.ajax({
            url: 'transaksi/LHK/get_pakan',
            data: { 'params': noreg },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                hideLoading();

                $('table.tbl_pakan').find('tbody').html( data.html );
            }
        });
	}, // end - get_pakan

	hit_total_pakan: function(elm) {
		var tbody = $(elm).closest('tbody');
		var modal = $(elm).closest('div.myPakan');
		var tr_data = $(modal).closest('tr.data');

		var total = 0;
		$.map( $(tbody).find('tr'), function(tr) {
			var jml_pakan = numeral.unformat($(tr).find('input').val());
			total += jml_pakan;
		});

		$(tr_data).find('span.total_zak').text( numeral.formatInt(total) );
	}, // end - hit_total_pakan
	*/

	save: function(elm) {
		var div_transaksi = $('#transaksi');
		var div = $(div_transaksi).find('div.row:first');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		var ket_err_nekropsi = [];
		var data_nekropsi = $.map( $(div_transaksi).find('table.tbl_nekropsi tbody tr.head'), function(tr_head) {
			var tr_detail = $(tr_head).next('tr.detail');

			var id = $(tr_head).attr('data-id');

			var checkbox = $(tr_head).find('input[type=checkbox]');
			if ( $(checkbox).is(':checked') ) {
				var  _data_nekropsi = {
					'id': id,
					'keterangan': $(tr_detail).find('textarea.ket').val().toUpperCase()
				};

				return _data_nekropsi;
			}
		});

		var data_solusi = $.map( $(div_transaksi).find('table.tbl_solusi tbody tr'), function(tr) {
			var checkbox = $(tr).find('input[type=checkbox]');
			if ( $(checkbox).prop('checked') == true ) {
				var  _data_solusi = {
					'id': $(tr).data('id')
				};

				return _data_solusi;
			}
		});

		var err_peralatan = 0;
		$.map( $(div_transaksi).find('table.tbl_peralatan tbody input:not([type="radio"])'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err_peralatan++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		var err_peralatan_cooling_pad_status = 0;
		var tr_perlatan_cooling_pad_status = $(div_transaksi).find('table.tbl_peralatan tbody input[type="radio"]:first').closest('tr.cooling_pad_status');
		$.map( $(tr_perlatan_cooling_pad_status).find('td.data'), function(td) {
			if ( $(td).find('input:checked').length == 0 ) {
				err_peralatan_cooling_pad_status++;
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			// if ( empty(data_nekropsi) && empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data nekropsi dan data solusi terlebih dahulu.');
			// } else if ( !empty(data_nekropsi) && empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data solusi terlebih dahul.');
			// } else if ( empty(data_nekropsi) && !empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data nekropsi terlebih dahul.');
			// } else if ( !empty(data_nekropsi) && !empty(data_solusi) ) {

			if ( empty(data_nekropsi) || empty(data_solusi) || err_peralatan > 0 || err_peralatan_cooling_pad_status > 0) {
				var ket_err = '';
				if ( empty(data_nekropsi) ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data nekropsi';
					} else {
						ket_err += ', nekropsi';
					}
				}

				if ( empty(data_solusi) ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data solusi';
					} else {
						ket_err += ', solusi';
					}
				}

				if ( err_peralatan > 0 ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data peralatan';
					} else {
						ket_err += ', peralatan';
					}
				} else {
					if ( err_peralatan_cooling_pad_status > 0 ) {
						if ( empty(ket_err) ) {
							ket_err += 'Harap lengkapi data peralatan pada kolom coolinng pad status';
						} else {
							ket_err += ', cooling pad status';
						}
					}
				}

				ket_err += ' terlebih dahulu.';

				bootbox.alert(ket_err);
			} else {
				var ket_confirm = null;
				if ( ket_err_nekropsi.length > 0 ) {
					// ket_confirm = 'Foto nekropsi di bawah belum terisi : <br>'+ket_err_nekropsi.join('<br>')+'<br><br>Apakah anda tetap ingin menyimpan data LHK ?';
					ket_confirm = 'Apakah anda yakin ingin menyimpan data LHK ?';
				} else {
					ket_confirm = 'Apakah anda yakin ingin menyimpan data LHK ?';
				}

				bootbox.confirm(ket_confirm, function(result) {
					if ( result ) {
						var data_sekat = $.map( $('table.tbl_sekat tbody tr'), function(tr) {
							var _data_sekat = {
								'no': $(tr).find('td.no_urut').text(),
								'bb': numeral.unformat($(tr).find('input[name=bb]').val())
							};

							return _data_sekat;
						});

						var tbl_peralatan = $('table.tbl_peralatan');
						var data_peralatan = {
							'umur': $(tbl_peralatan).find('td.umur').text(),
							'waktu': $(tbl_peralatan).find('td.waktu').attr('data-val'),
							'flok_lantai': $(tbl_peralatan).find('input.flok_lantai').val(),
							'tipe_controller': $(tbl_peralatan).find('input.tipe_controller').val(),
							'kelembapan1': numeral.unformat( $(tbl_peralatan).find('input.kelembapan1').val() ),
							'kelembapan2': numeral.unformat( $(tbl_peralatan).find('input.kelembapan2').val() ),
							'suhu_current1': numeral.unformat( $(tbl_peralatan).find('input.suhu_current1').val() ),
							'suhu_current2': numeral.unformat( $(tbl_peralatan).find('input.suhu_current2').val() ),
							'suhu_experience1': numeral.unformat( $(tbl_peralatan).find('input.suhu_experience1').val() ),
							'suhu_experience2': numeral.unformat( $(tbl_peralatan).find('input.suhu_experience2').val() ),
							'air_speed_depan_inlet1': numeral.unformat( $(tbl_peralatan).find('input.air_speed_depan_inlet1').val() ),
							'air_speed_depan_inlet2': numeral.unformat( $(tbl_peralatan).find('input.air_speed_depan_inlet2').val() ),
							'kerataan_air_speed1': numeral.unformat( $(tbl_peralatan).find('input.kerataan_air_speed1').val() ),
							'kerataan_air_speed2': numeral.unformat( $(tbl_peralatan).find('input.kerataan_air_speed2').val() ),
							'ukuran_kipas1': numeral.unformat( $(tbl_peralatan).find('input.ukuran_kipas1').val() ),
							'ukuran_kipas2': numeral.unformat( $(tbl_peralatan).find('input.ukuran_kipas2').val() ),
							'jumlah_kipas1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas1').val() ),
							'jumlah_kipas2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas2').val() ),
							'jumlah_kipas_on1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_on1').val() ),
							'jumlah_kipas_on2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_on2').val() ),
							'jumlah_kipas_off1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_off1').val() ),
							'jumlah_kipas_off2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_off2').val() ),
							'waktu_kipas_on1': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_on1').val() ),
							'waktu_kipas_on2': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_on2').val() ),
							'waktu_kipas_off1': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_off1').val() ),
							'waktu_kipas_off2': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_off2').val() ),
							'cooling_pad_status1': $(tbl_peralatan).find('input[name="cooling_pad_status1"]:checked').val(),
							'cooling_pad_status2': $(tbl_peralatan).find('input[name="cooling_pad_status2"]:checked').val()
						};

						var data = {
							'umur': $(div_transaksi).find('input[name=umur]').val(),
							'mitra': $(div_transaksi).find('select#select_mitra').val(),
							'noreg': $(div_transaksi).find('select#select_noreg').val(),
							'pakai_pakan': numeral.unformat($(div_transaksi).find('input[name=pakai_pakan]').val()),
							'sisa_pakan': numeral.unformat($(div_transaksi).find('input[name=sisa_pakan]').val()),
							'ekor_mati': numeral.unformat($(div_transaksi).find('input[name=ekor_mati]').val()),
							'keterangan': $(div_transaksi).find('textarea').val(),
							'tanggal': dateSQL($(div_transaksi).find('#tanggal').data('DateTimePicker').date()),
							'lat': $(elm).attr('data-lat'),
							'long': $(elm).attr('data-long'),
							'data_sekat': data_sekat,
							'data_nekropsi': data_nekropsi,
							'data_solusi': data_solusi,
							'data_peralatan': data_peralatan
						};

						$.ajax({
							url: 'transaksi/LHK/save',
							dataType: 'json',
							type: 'post',
							data: {
								'params': data
							},
							beforeSend: function() {
								showLoading();
							},
							success: function(data) {
								hideLoading();
								if ( data.status == 1 ) {
									bootbox.alert(data.message, function(){
										lhk.load_form(data.content.id, null, 'transaksi');
									});
								} else {
									bootbox.alert(data.message);
								}
							}
						});
					}
				});
			}
		}
	}, // end - save

	edit: function(elm) {
		var div_transaksi = $('#transaksi');
		var div = $(div_transaksi).find('div.row:first');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		var ket_err_nekropsi = [];
		var data_nekropsi = $.map( $(div_transaksi).find('table.tbl_nekropsi tbody tr.head'), function(tr_head) {
			var tr_detail = $(tr_head).next('tr.detail');

			var id = $(tr_head).attr('data-id');

			var checkbox = $(tr_head).find('input[type=checkbox]');
			if ( $(checkbox).is(':checked') ) {
				var  _data_nekropsi = {
					'id': id,
					'keterangan': $(tr_detail).find('textarea.ket').val().toUpperCase()
				};

				return _data_nekropsi;
			}
		});

		var data_solusi = $.map( $(div_transaksi).find('table.tbl_solusi tbody tr'), function(tr) {
			var checkbox = $(tr).find('input[type=checkbox]');
			if ( $(checkbox).prop('checked') == true ) {
				var  _data_solusi = {
					'id': $(tr).data('id')
				};

				return _data_solusi;
			}
		});

		var err_peralatan = 0;
		$.map( $(div_transaksi).find('table.tbl_peralatan tbody input:not([type="radio"])'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err_peralatan++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		var err_peralatan_cooling_pad_status = 0;
		var tr_perlatan_cooling_pad_status = $(div_transaksi).find('table.tbl_peralatan tbody input[type="radio"]:first').closest('tr.cooling_pad_status');
		$.map( $(tr_perlatan_cooling_pad_status).find('td.data'), function(td) {
			if ( $(td).find('input:checked').length == 0 ) {
				err_peralatan_cooling_pad_status++;
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			// if ( empty(data_nekropsi) && empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data nekropsi dan data solusi terlebih dahulu.');
			// } else if ( !empty(data_nekropsi) && empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data solusi terlebih dahul.');
			// } else if ( empty(data_nekropsi) && !empty(data_solusi) ) {
			// 	bootbox.alert('Harap isi data nekropsi terlebih dahul.');
			// } else if ( !empty(data_nekropsi) && !empty(data_solusi) ) {
			if ( empty(data_nekropsi) || empty(data_solusi) || err_peralatan > 0 || err_peralatan_cooling_pad_status > 0) {
				var ket_err = '';
				if ( empty(data_nekropsi) ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data nekropsi';
					} else {
						ket_err += ', nekropsi';
					}
				}

				if ( empty(data_solusi) ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data solusi';
					} else {
						ket_err += ', solusi';
					}
				}

				if ( err_peralatan > 0 ) {
					if ( empty(ket_err) ) {
						ket_err += 'Harap lengkapi data peralatan';
					} else {
						ket_err += ', peralatan';
					}
				} else {
					if ( err_peralatan_cooling_pad_status > 0 ) {
						if ( empty(ket_err) ) {
							ket_err += 'Harap lengkapi data peralatan pada kolom coolinng pad status';
						} else {
							ket_err += ', cooling pad status';
						}
					}
				}

				ket_err += ' terlebih dahulu.';

				bootbox.alert(ket_err);
			} else {
				var ket_confirm = 'Apakah anda yakin ingin meng-ubah data LHK ?';

				bootbox.confirm(ket_confirm, function(result) {
					if ( result ) {
						var data_sekat = $.map( $('table.tbl_sekat tbody tr'), function(tr) {
							var _data_sekat = {
								'no': $(tr).find('td.no_urut').text(),
								'bb': numeral.unformat($(tr).find('input[name=bb]').val())
							};

							return _data_sekat;
						});

						var tbl_peralatan = $('table.tbl_peralatan');
						var data_peralatan = {
							'umur': $(tbl_peralatan).find('td.umur').text(),
							'waktu': $(tbl_peralatan).find('td.waktu').attr('data-val'),
							'flok_lantai': $(tbl_peralatan).find('input.flok_lantai').val(),
							'tipe_controller': $(tbl_peralatan).find('input.tipe_controller').val(),
							'kelembapan1': numeral.unformat( $(tbl_peralatan).find('input.kelembapan1').val() ),
							'kelembapan2': numeral.unformat( $(tbl_peralatan).find('input.kelembapan2').val() ),
							'suhu_current1': numeral.unformat( $(tbl_peralatan).find('input.suhu_current1').val() ),
							'suhu_current2': numeral.unformat( $(tbl_peralatan).find('input.suhu_current2').val() ),
							'suhu_experience1': numeral.unformat( $(tbl_peralatan).find('input.suhu_experience1').val() ),
							'suhu_experience2': numeral.unformat( $(tbl_peralatan).find('input.suhu_experience2').val() ),
							'air_speed_depan_inlet1': numeral.unformat( $(tbl_peralatan).find('input.air_speed_depan_inlet1').val() ),
							'air_speed_depan_inlet2': numeral.unformat( $(tbl_peralatan).find('input.air_speed_depan_inlet2').val() ),
							'kerataan_air_speed1': numeral.unformat( $(tbl_peralatan).find('input.kerataan_air_speed1').val() ),
							'kerataan_air_speed2': numeral.unformat( $(tbl_peralatan).find('input.kerataan_air_speed2').val() ),
							'ukuran_kipas1': numeral.unformat( $(tbl_peralatan).find('input.ukuran_kipas1').val() ),
							'ukuran_kipas2': numeral.unformat( $(tbl_peralatan).find('input.ukuran_kipas2').val() ),
							'jumlah_kipas1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas1').val() ),
							'jumlah_kipas2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas2').val() ),
							'jumlah_kipas_on1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_on1').val() ),
							'jumlah_kipas_on2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_on2').val() ),
							'jumlah_kipas_off1': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_off1').val() ),
							'jumlah_kipas_off2': numeral.unformat( $(tbl_peralatan).find('input.jumlah_kipas_off2').val() ),
							'waktu_kipas_on1': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_on1').val() ),
							'waktu_kipas_on2': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_on2').val() ),
							'waktu_kipas_off1': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_off1').val() ),
							'waktu_kipas_off2': numeral.unformat( $(tbl_peralatan).find('input.waktu_kipas_off2').val() ),
							'cooling_pad_status1': $(tbl_peralatan).find('input[name="cooling_pad_status1"]:checked').val(),
							'cooling_pad_status2': $(tbl_peralatan).find('input[name="cooling_pad_status2"]:checked').val()
						};

						var data = {
							'id': $(elm).data('id'),
							'umur': $(div_transaksi).find('input[name=umur]').val(),
							'mitra': $(div_transaksi).find('select#select_mitra').val(),
							'noreg': $(div_transaksi).find('select#select_noreg').val(),
							'pakai_pakan': numeral.unformat($(div_transaksi).find('input[name=pakai_pakan]').val()),
							'sisa_pakan': numeral.unformat($(div_transaksi).find('input[name=sisa_pakan]').val()),
							'ekor_mati': numeral.unformat($(div_transaksi).find('input[name=ekor_mati]').val()),
							'keterangan': $(div_transaksi).find('textarea').val(),
							'lat': $(elm).attr('data-lat'),
							'long': $(elm).attr('data-long'),
							'data_sekat': data_sekat,
							'data_nekropsi': data_nekropsi,
							'data_solusi': data_solusi,
							'data_peralatan': data_peralatan
						};

						$.ajax({
							url: 'transaksi/LHK/edit',
							dataType: 'json',
							type: 'post',
							data: {
								'params': data
							},
							beforeSend: function() {
								showLoading();
							},
							success: function(data) {
								hideLoading();
								if ( data.status == 1 ) {
									bootbox.alert(data.message, function(){
										lhk.load_form(data.content.id, null, 'transaksi');
									});
								} else {
									bootbox.alert(data.message);
								}
							}
						});
					}
				});
			}
		}
	}, // end - edit

	batal_edit: function(elm) {
		var id = $(elm).data('id');
		lhk.load_form(id, null, 'transaksi');
	}, // end - batal_edit

	delete: function(elm) {
		var id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data LHK ?', function(result) {
			if ( result ) {
				$.ajax({
		            url: 'transaksi/LHK/delete',
		            data: {'params': id},
		            type: 'POST',
	            	dataType: 'JSON',
		            beforeSend: function() {
		                showLoading();
		            },
		            success: function(data) {
		            	hideLoading();

		                if ( data.status == 1 ) {
		                    bootbox.alert(data.message, function(){
		                    	lhk.load_form(null, null, 'transaksi');
		                    	$('button.tampilkan_riwayat').click();
		                    });
		                } else {
		                    bootbox.alert(data.message);
		                }
		            }
		        });
			}
		});
	}, // end - delete

	compress_img: function(elm) {
		showLoading();

		var jenis = $(elm).data('jenis');

		if ( jenis == 'nekropsi' ) {
			var tr = $(elm).closest('tr');
			var id = $(tr).data('id');

			var files = $(tr).find('.file_lampiran')[0].files;

			ci.compress(files, 480, function(data) {
				for (var i = 0; i < data.length; i++) {
					formData.append("attachments["+jenis+"]["+id+"]["+i+"]", data[i]);
				}

				hideLoading();
			});
		} else {
			var div = $(elm).closest('div');
			var files = $(div).find('.file_lampiran')[0].files;

			ci.compress(files, 480, function(data) {
				for (var i = 0; i < data.length; i++) {
					formData.append("attachments["+jenis+"]["+i+"]", data[i]);
				}

				hideLoading();
			});
		}
	}, // end - compress_img

	deleteFile: function (elm) {
		var div_contain = $(elm).closest('div.contain-img');
		var url = $(div_contain).find('img').attr('src');

		$.ajax({
			url: 'transaksi/LHK/deleteFile',
			data: {'url': url},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {},
			success: function(data) {
				if ( data.status == 1 ) {
					$(div_contain).remove();
				} else {
					bootbox.alert(data.message);
				}
			}
		});
	}, // end - deleteFile

	viewImage: function (elm) {
		var div_contain = $(elm).closest('div.contain-img');
		var url = $(div_contain).find('img').attr('src');

		window.open(url, 'blank');
	}, // end - viewImage

	getLocation: function (elm) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				function(position) {
					// var isMobile = $(elm).attr('data-ismobile');
					var jenis = $(elm).attr('data-jenis');
					
					var latitude = position.coords.latitude;
					var longitude = position.coords.longitude;

					$(elm).attr('data-lat', latitude);
					$(elm).attr('data-long', longitude);

					if ( jenis == 'save' ) {
						lhk.save( $(elm) );
					}

					if ( jenis == 'edit' ) {
						lhk.edit( $(elm) );
					}
				},
				function(error) {
					var err_text = lhk.showError(error);
					bootbox.alert( err_text );
				}
				,
				options
			);
		} else { 
			bootbox.alert("Geolocation is not supported by this browser.");
		}
	}, // end - getLocation

	showError: function (error) {
		var error_text = null;

		switch(error.code) {
			case error.PERMISSION_DENIED:
				// error_text = "User denied the request for Geolocation."
				error_text = "User menolak akses lokasi pada device."
				break;
			case error.POSITION_UNAVAILABLE:
				// error_text = "Location information is unavailable."
				error_text = "Informasi lokasi tidak tersedia."
				break;
			case error.TIMEOUT:
				error_text = "The request to get user location timed out."
				break;
			case error.UNKNOWN_ERROR:
				error_text = "An unknown error occurred."
				break;
		}

		return error_text;
	}, // end - showError
};

lhk.start_up();