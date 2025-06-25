var formData = null;

var rsm = {
	start_up: function () {
		rsm.setting_up('riwayat', 'div#riwayat');
		rsm.setting_up('transaksi', 'div#transaksi');

        formData = new FormData();
	}, // start_up

	setting_up: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    rsm.get_noreg(this);
		});
        if ( $(div).find('#select_mitra').is('[readonly]') ) {
            $(div).find('#select_mitra').next('button').attr('disabled', 'disabled');
            $(div).find('#select_mitra').next('button').css({'background-color': '#eee'});
        } else {
            $(div).find('#select_mitra').next('button').removeAttr('disabled');
            $(div).find('#select_mitra').next('button').css({'background-color': 'transparent'});
        }

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	rsm.set_umur_panen(this);
			}
		});
        if ( $(div).find('#select_noreg').is('[readonly]') ) {
            $(div).find('#select_noreg').next('button').attr('disabled', 'disabled');
            $(div).find('#select_noreg').next('button').css({'background-color': '#eee'});
        } else {
            $(div).find('#select_noreg').next('button').removeAttr('disabled');
            $(div).find('#select_noreg').next('button').css({'background-color': 'transparent'});
        }

		$(div).find('.select_unit').selectpicker();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$(div).find('[name=tanggal]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false, //Important! See issue #1075
            widgetPositioning: {
	            horizontal: "auto",
	            vertical: "auto"
	          }
		});

		$.map( $(div).find('[name=tanggal]'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
                // $(ipt).data("DateTimePicker").minDate(moment(tgl).subtract(2, 'days'));
                // $(ipt).data("DateTimePicker").maxDate(moment(tgl).add(2, 'days'));
            }
        });

		$(div).find('[name=tanggal]').on('dp.change', function (e) {
			if ( jenis_div != 'riwayat' ) {
				rsm.set_umur_panen(this);
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

            rsm.compress_img($(elm));
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
		var tr_rpah_top = $(elm).closest('tr.rpah_top');
		var tr_rpah_bottom = $(tr_rpah_top).next('tr.rpah_bottom');
		var tr_rpah_bottom2 = $(tr_rpah_bottom).next('tr.rpah_bottom');
		var tr_rpah_top_clone = $(tr_rpah_top).clone();
		var tr_rpah_bottom_clone = $(tr_rpah_bottom).clone();
		var tr_rpah_bottom_clone2 = $(tr_rpah_bottom2).clone();

        $(tr_rpah_top_clone).find('input').val('');
		$(tr_rpah_top_clone).attr('data-id', '');
		$(tr_rpah_bottom_clone).find('input, select').val('');
		$(tr_rpah_bottom_clone2).find('input, select').val('');

		$(tr_rpah_top_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$(tbody).append(tr_rpah_top_clone);
		$(tbody).append(tr_rpah_bottom_clone);
		$(tbody).append(tr_rpah_bottom_clone2);
	}, // end - add_row

	remove_row: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr.rpah_top').length > 1 ) {
			var tr_rpah_top = $(elm).closest('tr.rpah_top');
			var tr_rpah_bottom = $(tr_rpah_top).next('tr.rpah_bottom');
			var tr_rpah_bottom2 = $(tr_rpah_bottom).next('tr.rpah_bottom');

			$(tr_rpah_top).remove();
			$(tr_rpah_bottom).remove();
			$(tr_rpah_bottom2).remove();
		}

		rsm.hit_total();
	}, // end - remove_row

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

        rsm.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'noreg': $(elm).data('noreg'),
        	'tgl_panen': $(elm).data('tglpanen'),
        	'nomor': $(elm).data('nomor'),
        };

        $.ajax({
            url : 'transaksi/RealisasiSjMobile/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                rsm.setting_up(href, 'div#'+href);

                if ( !empty(edit) ) {
                	rsm.get_noreg( $('div#'+href).find('#select_mitra') );
                }

                formData = new FormData();
            },
        });
    }, // end - load_form

    get_lists: function() {
    	let div = $('div#riwayat');
        let dcontent = $('table.tbl_riwayat tbody');

        if ( empty($(div).find('select#select_unit').val()) && empty((div).find('#tanggal input').val()) ) {
            bootbox.alert('Harap isi data filter terlebih dahulu.');
        } else {
            var params = {
            	'kode_unit': $(div).find('select#select_unit').val(),
                'tgl_panen': dateSQL($(div).find('#tanggal').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'transaksi/RealisasiSjMobile/get_lists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    hideLoading();
                },
            });
        }
    }, // end - get_lists

	hit_total: function() {
		var tot_ekor = 0;
		var tot_tonase = 0;
		$.map( $('table.data_do').find('tr.detail'), function(tr_detail) {
			$.map( $(tr_detail).find('table tbody tr'), function(tr) {
				var ekor = numeral.unformat($(tr).find('input.ekor').val());
				var tonase = numeral.unformat($(tr).find('input.tonase').val());
				var bb = 0;
				if ( ekor > 0 && tonase > 0 ) {
					bb = tonase / ekor;
				}

				$(tr).find('input.bb').val( numeral.formatDec(bb) );

				tot_ekor += ekor;
				tot_tonase += tonase;
			});
		});

		var tot_bb = 0;
		if ( tot_ekor > 0 && tot_tonase > 0 ) {
			tot_bb = tot_tonase / tot_ekor;
		}

		var ekor_konfir = numeral.unformat($('input.ekor_konfir').val());
        $('label.tot_ekor').text( numeral.formatInt(tot_ekor) );
		$('label.tot_ekor').attr( 'data-val', tot_ekor );
		if ( tot_ekor > ekor_konfir ) {
			$('label.tot_ekor').css({'color': 'red'});
		} else {
			$('label.tot_ekor').css({'color': '#333'});
		}

		var tonase_konfir = numeral.unformat($('input.tonase_konfir').val());
        $('label.tot_tonase').text( numeral.formatDec(tot_tonase) );
		$('label.tot_tonase').attr( 'data-val', tot_tonase );
		if ( tot_tonase > tonase_konfir ) {
			$('label.tot_tonase').css({'color': 'red'});
		} else {
			$('label.tot_tonase').css({'color': '#333'});
		}
		$('label.tot_bb').text( numeral.formatDec(tot_bb) );
	}, // end - hit_total

	get_noreg: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var nomor_mitra = $(div).find('#select_mitra').val();

    	var option = '<option value="">Pilih Noreg</option>';
    	if ( !empty(nomor_mitra) ) {
    		$.ajax({
	            url: 'transaksi/RealisasiSjMobile/get_noreg',
	            data: { 'params': nomor_mitra },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	                if ( data.content.length > 0 ) {
	                	var noreg = $(div).find('select#select_noreg').data('val');
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].noreg == noreg ) {
	                			selected = 'selected';
	                		}
	                		option += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" data-kodeunit="'+data.content[i].kode_unit+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
	                	}
	                }
	                $(div).find('select#select_noreg').removeAttr('disabled');
	                $(div).find('select#select_noreg').html(option);
	                $(div).find('#select_noreg').selectpicker('refresh');

	                hideLoading();
	            }
	        });
    	} else {
    		$(div).find('select#select_noreg').attr('disabled', 'disabled');
    		$(div).find('select#select_noreg').html(option);
    		$(div).find('#select_noreg').selectpicker('refresh');
    	}
    }, // end - get_noreg

    set_umur_panen: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var div_tgl = $(div).find('div#tanggal');

    	var ipt_tgl = $(div_tgl).find('input');
    	var select_noreg = $(div).find('#select_noreg');
    	var select_mitra = $(div).find('#select_mitra');

    	$(ipt_tgl).removeAttr('disabled');

    	var umur = 0;
    	if ( !empty($(select_noreg).val()) ) {
    		if ( !empty($(ipt_tgl).val()) ) {
	    		var tgl_docin = $(select_noreg).find('option:selected').data('tgldocin');
	    		var tgl_panen = dateSQL( $(div_tgl).data("DateTimePicker").date() );

	    		var params = {
	    			'noreg': $(select_noreg).val(),
	    			'mitra': $(select_mitra).val(),
	    			'tgl_panen': tgl_panen,
	    			'kode_unit': $(select_mitra).find('option:selected').data('kodeunit')
	    		};

	    		$.ajax({
		            url: 'transaksi/RealisasiSjMobile/get_data_rpah',
		            data: { 'params': params },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	var umur = App.selisihWaktuDalamHari(tgl_docin, tgl_panen);

				    		$(div).find('input.umur').val( umur );
				    		$(div).find('input.ekor_konfir').val( numeral.formatInt(data.content.ekor) );
				    		$(div).find('input.tonase_konfir').val( numeral.formatDec(data.content.tonase) );
					    	$(div).find('input.harga_dasar').val( numeral.formatInt(data.content.harga_dasar) );
					    	$(div).find('table.data_do tbody').html( data.content.html );

                            $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                                $(this).priceFormat(Config[$(this).data('tipe')]);
                            });
		                } else {
		                	bootbox.alert( data.message, function() {
		                		$(div).find('input.umur').val('');
					    		$(div).find('input.ekor_konfir').val('');
					    		$(div).find('input.tonase_konfir').val('');
					    		$(div).find('input.harga_dasar').val('');
					    		$(div).find('table.data_do tbody').html( '<tr><td colspan="2">Data tidak ditemukan.</td></tr>' );
		                	});
		                }
		                rsm.hit_total();
		            }
		        });
    		}
    	} else {
    		$(ipt_tgl).attr('disabled', 'disabled');
    		$(ipt_tgl).val('');
    		$(div).find('input.umur').val('');
    	}
    }, // end - set_umur_panen

    save: function() {
    	var div = $('div#transaksi');

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
    		bootbox.confirm('Apakah anda yakin ingin menyimpan data realisai SJ ?', function(result) {
    			if ( result ) {
    				// var formData = new FormData();

    				var detail = [];
    				$.map( $(div).find('table.data_do tbody tr.header'), function(tr_header) {
    					var tr_detail = $(tr_header).next('tr.detail');

    					var id_det_rpah = $(tr_header).data('iddetrpah');
    					var no_pelanggan = $(tr_header).data('noplg');
    					var nama_pelanggan = $(tr_header).data('pelanggan');
    					var no_do = $(tr_header).data('do');
    					var no_sj = $(tr_header).data('sj');

    					$.map( $(tr_detail).find('table.tbl_detail tbody tr.rpah_top'), function(tr_rpah_top) {
    						var tr_rpah_bottom = $(tr_rpah_top).next('tr.rpah_bottom');
    						var tr_rpah_bottom2 = $(tr_rpah_bottom).next('tr.rpah_bottom');

    						var _detail = {
    							'id_det_rpah': id_det_rpah,
    							'no_pelanggan': no_pelanggan,
    							'pelanggan': nama_pelanggan,
    							'tonase': numeral.unformat( $(tr_rpah_top).find('input.tonase').val() ),
    							'ekor': numeral.unformat( $(tr_rpah_top).find('input.ekor').val() ),
    							'bb': numeral.unformat( $(tr_rpah_top).find('input.bb').val() ),
    							'harga': numeral.unformat( $(tr_rpah_top).find('input.harga').val() ),
    							'jenis_ayam': $(tr_rpah_bottom).find('select.jenis_ayam').val(),
    							'no_do': no_do,
								'no_sj': no_sj,
    							'no_nota': $(tr_rpah_bottom2).find('input.no_nota').val()
    						};

    						// var key = no_do;
    						// var file_tmp = $(tr_header).find('.file_lampiran').get(0).files[0];
    						// formData.append('file['+key+']', file_tmp);

    						detail.push( _detail );
    					});
    				});

    				var tgl_panen = dateSQL( $(div).find('#tanggal').data("DateTimePicker").date() );
    				var noreg = $(div).find('select#select_noreg').val();
    				var nomor = $(div).find('select#select_mitra').val();

    				var data = {
    					'noreg': noreg,
    					// 'kode_unit': $(div).find('select#select_mitra option:selected').data('kodeunit'),
    					'kode_unit': $(div).find('select#select_noreg option:selected').data('kodeunit'),
    					'tgl_panen': tgl_panen,
    					'ekor': $(div).find('.tot_ekor').attr('data-val'),
    					'kg': $(div).find('.tot_tonase').attr('data-val'),
    					'bb': numeral.unformat( $(div).find('.tot_bb').text() ),
    					'detail': detail
    				};

    				formData.append("data", JSON.stringify(data));

    				$.ajax({
			            url: 'transaksi/RealisasiSjMobile/save',
						dataType: 'JSON',
			            type: 'POST',
			            async:false,
			            processData: false,
			            contentType: false,
			            data: formData,
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		var btn = '<button type="button" data-tglpanen="'+tgl_panen+'" data-noreg="'+noreg+'" data-nomor="'+nomor+'" data-edit="" data-href="transaksi"></button>';
			                		rsm.load_form( $(btn), null, 'transaksi' );
			                	});
			                } else {
			                	bootbox.alert( data.message );
			                }
			            }
			        });
    			}
    		});
    	}
    }, // end - save

    edit: function() {
    	var div = $('div#transaksi');

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
    		bootbox.confirm('Apakah anda yakin ingin meng-ubah data realisasi SJ ?', function(result) {
    			if ( result ) {
                    // var formData = new FormData();

    				var detail = [];
                    $.map( $(div).find('table.data_do tbody tr.header'), function(tr_header) {
                        var tr_detail = $(tr_header).next('tr.detail');

                        var id_det_rpah = $(tr_header).data('iddetrpah');
                        var no_pelanggan = $(tr_header).data('noplg');
                        var nama_pelanggan = $(tr_header).data('pelanggan');
                        var no_do = $(tr_header).data('do');
                        var no_sj = $(tr_header).data('sj');
                        var lampiran_old = $(tr_header).find('.file_lampiran').data('old');

                        // if ( !empty( $(tr_header).find('.file_lampiran').val() ) ) {
                        //     var file_tmp = $(tr_header).find('.file_lampiran').get(0).files[0];
                        //     formData.append('file['+no_do+']', file_tmp);
                        // }

                        $.map( $(tr_detail).find('table.tbl_detail tbody tr.rpah_top'), function(tr_rpah_top) {
                            var id_det_real_sj = $(tr_rpah_top).data('id');
                            var tr_rpah_bottom = $(tr_rpah_top).next('tr.rpah_bottom');
							var tr_rpah_bottom2 = $(tr_rpah_bottom).next('tr.rpah_bottom');

                            var _detail = {
                                'id_det_real_sj': id_det_real_sj,
                                'id_det_rpah': id_det_rpah,
                                'no_pelanggan': no_pelanggan,
                                'pelanggan': nama_pelanggan,
                                'tonase': numeral.unformat( $(tr_rpah_top).find('input.tonase').val() ),
                                'ekor': numeral.unformat( $(tr_rpah_top).find('input.ekor').val() ),
                                'bb': numeral.unformat( $(tr_rpah_top).find('input.bb').val() ),
                                'harga': numeral.unformat( $(tr_rpah_top).find('input.harga').val() ),
                                'jenis_ayam': $(tr_rpah_bottom).find('select.jenis_ayam').val(),
                                'no_do': no_do,
                                'no_sj': no_sj,
                                'lampiran_old': lampiran_old,
    							'no_nota': $(tr_rpah_bottom2).find('input.no_nota').val()
                            };

                            detail.push( _detail );
                        });
                    });

    				var tgl_panen = dateSQL( $(div).find('#tanggal').data("DateTimePicker").date() );
    				var noreg = $(div).find('select#select_noreg').val();
    				var nomor = $(div).find('select#select_mitra').val();

    				var data = {
    					'noreg': noreg,
    					'noreg_old': $(div).find('select#select_noreg').data('old'),
    					// 'kode_unit': $(div).find('select#select_mitra option:selected').data('kodeunit'),
    					'kode_unit': $(div).find('select#select_noreg option:selected').data('kodeunit'),
    					'kode_unit_old': $(div).find('select#select_mitra').data('old'),
    					'tgl_panen': tgl_panen,
    					'tgl_panen_old': $(div).find('#tanggal input').data('old'),
    					'ekor': $(div).find('.tot_ekor').attr('data-val'),
                        'kg': $(div).find('.tot_tonase').attr('data-val'),
                        'bb': numeral.unformat( $(div).find('.tot_bb').text() ),
    					'detail': detail
    				};

                    formData.append("data", JSON.stringify(data));

    				$.ajax({
			            url: 'transaksi/RealisasiSjMobile/edit',
                        dataType: 'JSON',
                        type: 'POST',
                        async:false,
                        processData: false,
                        contentType: false,
                        data: formData,
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
                                    var div_riwayat = $('div#riwayat');
                                    if ( !empty($(div_riwayat).find('select#select_unit').val()) && !empty($(div_riwayat).find('#tanggal input').val()) ) {
                                        $('button#btn-get-lists').click();
                                    }

			                		var btn = '<button type="button" data-tglpanen="'+tgl_panen+'" data-noreg="'+noreg+'" data-nomor="'+nomor+'" data-edit="" data-href="transaksi"></button>';
			                		rsm.load_form( $(btn), null, 'transaksi' );
			                	});
			                } else {
			                	bootbox.alert( data.message );
			                }
			            }
			        });
    			}
    		});
    	}
    }, // end - edit

    delete: function() {
    	var div = $('div#transaksi');

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data realisasi SJ ?', function(result) {
			if ( result ) {
				var data = {
					'noreg': $(div).find('div.noreg').data('val'),
					'tgl_panen': $(div).find('div.tgl_panen').data('val')
				};

				$.ajax({
		            url: 'transaksi/RealisasiSjMobile/delete',
		            data: { 'params': data },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	bootbox.alert( data.message, function() {
		                		var div_riwayat = $('div#riwayat');
		                		if ( !empty($(div_riwayat).find('select#select_unit').val()) && !empty($(div_riwayat).find('#tanggal input').val()) ) {
		                			$('button#btn-get-lists').click();
		                		}

		                		var btn = '<button type="button" data-href="transaksi"></button>';
		                		rsm.load_form( $(btn), null, 'transaksi' );
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            }
		        });
			}
		});
    }, // end - delete

    compress_img: function(elm) {
        showLoading();

        var tr_header = $(elm).closest('tr.header');

        var key = $(tr_header).data('do');
        var file_tmp = $(tr_header).find('.file_lampiran').get(0).files[0];

        ci.compress_img(file_tmp, file_tmp.name, 480, function(data) {
            formData.append('file['+key+']', data);

            hideLoading();
        });
    }, // end - compress_img
};

rsm.start_up();