var formData = null;

var kk = {
	startUp: function () {
		kk.settingUp();

		formData = new FormData();
	}, // end - startUp

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
            _a.attr('href', _temp_url);
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);

            var key = $(elm).attr( 'data-key' );

            kk.compress_img($(elm), key);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

    compress_img: function(elm, key) {
        showLoading();

        var tr = $(elm).closest('tr');
        var div = $('div#transaksi');

        var file_tmp = $(elm).get(0).files;
		if ( file_tmp[0]['type'] == 'image/jpeg') {	
			if ( empty(key) ) {
				if ( $(elm).hasClass('file_lampiran') ) {
					key = $(div).find('input.no_sj').val().toUpperCase();
				} else {
					key = $(tr).find('textarea.keterangan').val();
				}
			}
	
			ci.compress(file_tmp, 480, function(data) {
				for (var i = 0; i < data.length; i++) {
					formData.append('file['+key+']', data[i]);
				}
	
				hideLoading();
			});
		} else {
			hideLoading();
		}
    }, // end - compress_img

	settingUp: function () {
		$('select.kendaraan').select2().on("select2:select", function (e) {
			var id = $('.kendaraan').select2().val();

			kk.getDataKendaraan( id );

			$('.kendaraan').next('span.select2').css('width', '100%');
		});

		// $('.perusahaan').select2();
		// $('.unit').select2().on("select2:select", function (e) {
        //     var unit_kode = $('.unit').select2().val();

        //     kk.getKaryawan( unit_kode );

        //     $('.unit').next('span.select2').css('width', '100%');
        // });

		// $('.karyawan').select2();
		$('.status_kredit').select2();

		$('#tanggal').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
        });
        var tanggal_val = $('#tanggal').find('input').attr('data-val');
    	if ( !empty(tanggal_val) ) {
    		$('#tanggal').data('DateTimePicker').date( new Date(tanggal_val) );
    	}
    	$('#tanggal').on("dp.change", function (e) {
            kk.generateRowAngsuran();
        });

    	$('#tgl_bayar_angsuran1').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
        });
		var tanggal_bayar_val = $('#tgl_bayar_angsuran1').find('input').attr('data-val');
    	if ( !empty(tanggal_bayar_val) ) {
    		$('#tgl_bayar_angsuran1').data('DateTimePicker').date( new Date(tanggal_bayar_val) );
    	}

		$('#tgl_jatuh_tempo').datetimepicker({
			locale: 'id',
            format: 'DD'
        });
        var tgl_jatuh_tempo_val = $('#tgl_jatuh_tempo').find('input').attr('data-val');
    	if ( !empty(tgl_jatuh_tempo_val) ) {
    		$('#tgl_jatuh_tempo').data('DateTimePicker').date( new Date(tgl_jatuh_tempo_val) );
    	}
    	$('#tgl_jatuh_tempo').on("dp.change", function (e) {
            kk.generateRowAngsuran();
        });

        $('tr.header').find('td:not(.btn-row, .btn-bpkb)').click(function() {
        	var tr = $(this).closest('tr.header');

        	kk.changeTabActive( $(tr) );
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - settingUp

	settingUpDetail: function() {
		$('.tgl_bayar').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
        }).on("dp.change", function (e) {
        	var tr = $(this).closest('tr');
        	var data_edit = $(this).find('input').attr('data-edit');

        	if ( empty(data_edit) ) {
	        	var next_tr = $(tr).next('tr');

	        	if ( $(next_tr).length > 0 ) {
		        	if ( e.date ) {
		        		$(next_tr).find('.tgl_bayar').find('input').removeAttr('disabled');
		        	} else {
		        		$(next_tr).find('.tgl_bayar').find('input').attr('disabled', 'disabled');
		        	}
	        	}
        	}
        });

        $.map( $('.tgl_bayar'), function(div) {
        	var data_val = $(div).find('input').attr('data-val');
        	if ( !empty(data_val) ) {
        		$(div).data('DateTimePicker').date( new Date(data_val) );
        	}
        });
	}, // end - settingUpDetail

	collapseRow: function() {
		$('tr.header .btn-collapse').click(function() {
            var row = $(this).closest('tr.header');
            if ($(this).hasClass('fa-caret-square-o-right')) {
                $(this).removeClass('fa-caret-square-o-right');
                $(this).addClass('fa-caret-square-o-down');

                $(row).next('tr.detail').css({'display': 'table-row'});
            } else {
                $(this).removeClass('fa-caret-square-o-down');
                $(this).addClass('fa-caret-square-o-right');

                $(row).next('tr.detail').css({'display': 'none'});
            }
        });
	}, // end - collapseRow

	changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        var id = $(elm).data('id');
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

        kk.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id = null, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': id
        };

        $.ajax({
            url : 'transaksi/KreditKendaraan/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                kk.settingUp();
				kk.settingUpDetail();
            },
        });
    }, // end - loadForm

	getLists: function() {
		var dcontent = $('#riwayat');

		var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
			var status = $(dcontent).find('.status_kredit').select2().val();

			$.ajax({
	            url : 'transaksi/KreditKendaraan/getLists',
	            data : {
	                'params' : status
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	                hideLoading();

	                var table = $(dcontent).find('.tbl_riwayat');

	                $(table).find('tbody').html( html );

	                kk.collapseRow();
	                kk.settingUp();
	            },
	        });
		}
	}, // end - getLists

	getDataKendaraan: function (id) {
		var params = {
			'id': id
		};

		$.ajax({
			url : 'transaksi/KreditKendaraan/dataKendaraan',
			data : {
				'params' : params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				hideLoading();

				if ( data.status == 1 ) {
					console.log( data );

					$('.perusahaan').val( data.content.kendaraan.nama_perusahaan );
					$('.perusahaan').attr('data-val', data.content.kendaraan.kode_perusahaan);

					$('.merk_jenis').val( data.content.kendaraan.merk+' '+data.content.kendaraan.tipe );
					$('.warna').val( data.content.kendaraan.warna );
					$('.tahun').val( data.content.kendaraan.tahun );

					$('.unit').val( data.content.kendaraan.nama_unit );
					$('.unit').attr('data-val', data.content.kendaraan.kode_unit);

					$('.karyawan').val( data.content.kendaraan.nama_karyawan );
					$('.karyawan').attr('data-val', data.content.kendaraan.kode_karyawan);
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - getKaryawan

	getKaryawan: function (unit_kode) {
		if ( !empty(unit_kode) ) {
			$('.karyawan').select2('destroy');
			$('.karyawan').removeAttr('disabled');

			$.ajax({
	            url : 'transaksi/KreditKendaraan/getKaryawan',
	            data : {
	                'params' : unit_kode
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	                hideLoading();

	                var option = '<option value="">-- Pilih Karyawan --</option>';

	                var _data = data.content.karyawan;

	                for(var key in _data) {
                		option += '<option value="'+_data[key].nik+'">'+_data[key].jabatan.toUpperCase()+' | '+_data[key].nama.toUpperCase()+'</option>';
					}

					$('.karyawan').html( option );

	                kk.settingUp();
	            },
	        });
		} else {
			$('.karyawan').attr('disabled', 'disabled');
			$('.karyawan').select2();
		}
	}, // end - getKaryawan

	generateRowAngsuran: function () {
		var tenor = numeral.unformat($('.tenor').val());
		var angsuran = numeral.unformat($('.angsuran').val());
		var tanggal = $('#tanggal').find('input').val();
		var tgl_jatuh_tempo = $('#tgl_jatuh_tempo').find('input').val();

		if ( !empty(tenor) && !empty(angsuran) && !empty(tanggal) && !empty(tgl_jatuh_tempo) ) {
			var params = {
				'tenor': tenor,
				'angsuran': angsuran,
				'tanggal': dateSQL($('#tanggal').data('DateTimePicker').date()),
				'tgl_jatuh_tempo': dateSQL($('#tgl_jatuh_tempo').data('DateTimePicker').date()),
			};

			$.ajax({
	            url : 'transaksi/KreditKendaraan/generateRowAngsuran',
	            data : {
	                'params' : params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	                hideLoading();

	                $('.tbl_angsuran tbody').html( html );

	                kk.settingUpDetail();
	            },
	        });
		}
	}, // end - generateRowAngsuran

	editDetail: function(elm) {
		var tr = $(elm).closest('tr');
		var div_attachment = $(tr).find('div.attachment');
		var div_act_edit = $(tr).find('div.act_edit');
		var div_act_save = $(tr).find('div.act_save');

		$(tr).find('.tgl_bayar input').removeAttr('disabled');
		$(div_attachment).find('label.control-label').removeClass('hide');
		$(div_act_save).removeClass('hide');
		$(div_act_edit).addClass('hide');
	}, // end - editDetail

	batalDetail: function(elm) {
		var tr = $(elm).closest('tr');
		var div_attachment = $(tr).find('div.attachment');
		var div_act_save = $(tr).find('div.act_save');
		var div_act_edit = $(tr).find('div.act_edit');

		$(tr).find('.tgl_bayar input').attr('disabled', 'disabled');
		$(div_attachment).find('label.control-label').addClass('hide');
		$(div_act_save).addClass('hide');
		$(div_act_edit).removeClass('hide');

		var date = $(tr).find('.tgl_bayar input').attr('data-val');
		if ( !empty(date) ) {
			$(tr).find('.tgl_bayar').data('DateTimePicker').date(new Date(date));
		} else {
			$(tr).find('.tgl_bayar input').val('');
			var next_tr = $(tr).next('tr');

        	if ( $(next_tr).length > 0 ) {
	        	$(next_tr).find('.tgl_bayar').find('input').attr('disabled', 'disabled');
        	}
		}
	}, // end - batalDetail

	saveDetail: function(elm) {
		var tr = $(elm).closest('tr');

		var err = 0;
		$.map( $(tr).find('input'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap isi tanggal bayar dan lampiran terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan tanggal bayar ?', function(result) {
				if ( result ) {
					var data = {
						'kredit_kendaraan_kode': $(tr).attr('data-kode'),
						'angsuran_ke': $(tr).attr('data-no'),
						'tgl_bayar': dateSQL($(tr).find('.tgl_bayar').data('DateTimePicker').date())
					};

					formData.append("data", JSON.stringify(data));

					$.ajax({
			            url : 'transaksi/KreditKendaraan/saveDetail',
			            data : formData,
			            type : 'POST',
			            dataType : 'JSON',
			            async:false,
			            processData: false,
			            contentType: false,
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		var next_tr = $(tr).next('tr');
			                		var data_edit = $(next_tr).find('.tgl_bayar input').attr('data-edit');

			                		$(tr).find('.tgl_bayar input').attr('disabled', 'disabled');
			                		$(tr).find('div.act_save').addClass('hide');
			                		$(tr).find('div.act_save div.save').addClass('hide');
			                		$(tr).find('div.act_edit').removeClass('hide');

			                		if ( empty(data_edit) ) {
			                			$(next_tr).find('.tgl_bayar input').attr('data-edit', 'edit');

				                		$(next_tr).find('.tgl_bayar input').removeAttr('disabled');
				                		$(next_tr).find('div.act_save').removeClass('hide');
				                		$(next_tr).find('div.act_save div.save').removeClass('hide');
				                		$(next_tr).find('div.act_save div.batal').addClass('hide');
			                		}
			                	});

			                	formData = null;
								formData = new FormData();
			                } else {
			                	bootbox.alert( data.message );
			                }
			            },
			        });
				}
			});
		}
	}, // end - saveDetail

	save: function () {
		var dcontent = $('#action');

		var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
				if ( result ) {
					var detail = $.map( $(dcontent).find('table tbody tr.data'), function(tr) {
						var tgl_bayar = null;
						if ( !empty($(tr).find('.tgl_bayar input').val()) ) {
							tgl_bayar = dateSQL($(tr).find('.tgl_bayar').data('DateTimePicker').date());
						}

						var _detail = {
							'angsuran_ke': $(tr).attr('data-no'),
							'tgl_jatuh_tempo': $(tr).find('td.tgl_jatuh_tempo').attr('data-val'),
							'jumlah_angsuran': $(tr).find('td.jumlah').attr('data-val'),
							'tgl_bayar': tgl_bayar
						};

						return _detail;
					});

					var data = {
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'kendaraan': $(dcontent).find('.kendaraan').select2().val(),
						// 'perusahaan': $(dcontent).find('.perusahaan').select2().val(),
						'perusahaan': $(dcontent).find('.perusahaan').attr('data-val'),
						'merk_jenis': $(dcontent).find('.merk_jenis').val(),
						'warna': $(dcontent).find('.warna').val(),
						'tahun': $(dcontent).find('.tahun').val(),
						// 'unit': $(dcontent).find('.unit').select2().val(),
						'unit': $(dcontent).find('.unit').attr('data-val'),
						// 'peruntukan': $(dcontent).find('.karyawan').select2().val(),
						'peruntukan': $(dcontent).find('.karyawan').attr('data-val'),
						'harga': numeral.unformat($(dcontent).find('.harga').val()),
						'dp': numeral.unformat($(dcontent).find('.dp').val()),
						'angsuran': numeral.unformat($(dcontent).find('.angsuran').val()),
						'tenor': $(dcontent).find('.tenor').val(),
						'tgl_jatuh_tempo': dateSQL($(dcontent).find('#tgl_jatuh_tempo').data('DateTimePicker').date()),
						'tgl_bayar_angsuran1': dateSQL($(dcontent).find('#tgl_bayar_angsuran1').data('DateTimePicker').date()),
						'tanda_jadi': numeral.unformat($(dcontent).find('.tanda_jadi').val()),
						'detail': detail
					};

					formData.append("data", JSON.stringify(data));

					$.ajax({
			            url : 'transaksi/KreditKendaraan/save',
			            data : formData,
			            type : 'POST',
			            dataType : 'JSON',
			            async:false,
			            processData: false,
			            contentType: false,
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		kk.loadForm(data.content.id, null, 'action');
			                		if ( !empty($('.status_kredit').select2().val()) ) {
			                			kk.getLists();
			                		}

			                		formData = null;
									formData = new FormData();
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

	edit: function (elm) {
		var dcontent = $('#action');

		var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
				if ( result ) {
					var detail = $.map( $(dcontent).find('table tbody tr.data'), function(tr) {
						var tgl_bayar = null;
						if ( !empty($(tr).find('.tgl_bayar input').val()) ) {
							tgl_bayar = dateSQL($(tr).find('.tgl_bayar').data('DateTimePicker').date());
						}

						var _detail = {
							'angsuran_ke': $(tr).attr('data-no'),
							'tgl_jatuh_tempo': $(tr).find('td.tgl_jatuh_tempo').attr('data-val'),
							'jumlah_angsuran': $(tr).find('td.jumlah').attr('data-val'),
							'tgl_bayar': tgl_bayar
						};

						return _detail;
					});

					var data = {
						'kode': $(elm).attr('data-kode'),
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'kendaraan': $(dcontent).find('.kendaraan').select2().val(),
						// 'perusahaan': $(dcontent).find('.perusahaan').select2().val(),
						'perusahaan': $(dcontent).find('.perusahaan').attr('data-val'),
						'merk_jenis': $(dcontent).find('.merk_jenis').val(),
						'warna': $(dcontent).find('.warna').val(),
						'tahun': $(dcontent).find('.tahun').val(),
						// 'unit': $(dcontent).find('.unit').select2().val(),
						'unit': $(dcontent).find('.unit').attr('data-val'),
						// 'peruntukan': $(dcontent).find('.karyawan').select2().val(),
						'peruntukan': $(dcontent).find('.karyawan').attr('data-val'),
						'harga': numeral.unformat($(dcontent).find('.harga').val()),
						'dp': numeral.unformat($(dcontent).find('.dp').val()),
						'angsuran': numeral.unformat($(dcontent).find('.angsuran').val()),
						'tenor': $(dcontent).find('.tenor').val(),
						'tgl_jatuh_tempo': dateSQL($(dcontent).find('#tgl_jatuh_tempo').data('DateTimePicker').date()),
						'tgl_bayar_angsuran1': dateSQL($(dcontent).find('#tgl_bayar_angsuran1').data('DateTimePicker').date()),
						'tanda_jadi': numeral.unformat($(dcontent).find('.tanda_jadi').val()),
						'detail': detail
					};

					formData.append("data", JSON.stringify(data));

					$.ajax({
			            url : 'transaksi/KreditKendaraan/edit',
			            data : formData,
			            type : 'POST',
			            dataType : 'JSON',
			            async:false,
			            processData: false,
			            contentType: false,
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		kk.loadForm(data.content.id, null, 'action');
			                		if ( !empty($('.status_kredit').select2().val()) ) {
			                			kk.getLists();
			                		}

			                		formData = null;
									formData = new FormData();
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

	delete: function (elm) {
		var dcontent = $('#action');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
			if ( result ) {
				var kode = $(elm).attr('data-id');

				$.ajax({
		            url : 'transaksi/KreditKendaraan/delete',
		            data : {
		                'params' : kode
		            },
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();

		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		kk.loadForm(null, null, 'action');
		                		if ( !empty($('.status_kredit').select2().val()) ) {
		                			kk.getLists();
		                		}

		                		formData = null;
								formData = new FormData();
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            },
		        });
			}
		});
	}, // end - delete

	modalBpkb: function(elm) {
		var kode = $(elm).attr('data-kode');

		showLoading();

		$.get('transaksi/KreditKendaraan/modalBpkb',{
			'kode': kode
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				hideLoading();

				// var modal_body = $(this).find('.modal-body');
				// var table = $(modal_body).find('table');
				// var tbody = $(table).find('tbody');
				// if ( $(tbody).find('.modal-body tr').length <= 1 ) {
				// 	$(this).find('tr #btn-remove').addClass('hide');
				// };

				// $(this).find('button.close').click(function() {
				// 	$('div.modal.show').css({'overflow': 'auto'});
				// });
			});
		},'html');
	}, // end - modalBpkb

	saveBpkb: function(elm) {
		var modal = $('.modal');

		var err = 0;
		$.map( $(modal).find('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap isi no BPKB dan lampiran terlebih dahulu.');
		} else {
			var data = {
				'kode': $(elm).attr('data-kode'),
				'no_bpkb': $(modal).find('input.no_bpkb').val()
			};

			formData.append("data", JSON.stringify(data));

			$.ajax({
				url : 'transaksi/KreditKendaraan/saveBpkb',
				data : formData,
				type : 'POST',
				dataType : 'JSON',
				async:false,
				processData: false,
				contentType: false,
				beforeSend : function(){ showLoading(); },
				success : function(data){
					hideLoading();

					if ( data.status == 1 ) {
						bootbox.alert( data.message, function() {
							$(modal).modal('hide');

							formData = null;
							formData = new FormData();
						});
					} else {
						bootbox.alert( data.message );
					}
				},
			});
		}
	}, // end - saveBpkb
};

kk.startUp();