var rm = {
	start_up: function () {
		rm.setting_up('riwayat', 'div#riwayat');
		rm.setting_up('transaksi', 'div#transaksi');
	}, // start_up

	setting_up: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    rm.get_noreg(this);
		});

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	rm.get_tgl_konfir(this);
			}
		});

		$(div).find('#select_tgl_konfir').selectpicker();
		$(div).find('#select_tgl_konfir').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	rm.set_umur_panen(this);
			}
		});

		$.map( $(div).find('table.data_plg tbody tr.header'), function (tr) {
			$(tr).find('.select_pelanggan').select2().on("select2:select", function (e) {
				var no_pelanggan = e.params.data.id;

				rm.cekPelanggan(this, no_pelanggan);
			});
			$(tr).find('.select_pelanggan').next('span.select2').css('width', '100%');
		});

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
                $(ipt).data("DateTimePicker").minDate(moment(tgl).subtract(2, 'days'));
                $(ipt).data("DateTimePicker").maxDate(moment(tgl).add(2, 'days'));
            }
        });

		$(div).find('[name=tanggal]').on('dp.change', function (e) {
			if ( jenis_div != 'riwayat' ) {
				rm.set_umur_panen(this);
			}
		});

		if ( jenis_div == 'riwayat' ) {
			if ( !empty($(div).find('select#select_unit').val()) && !empty($(div).find('#tanggal input').val()) ) {
				rm.get_lists();
			}
		}
	}, // end - setting_up

	add_row_plg: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr_header = $(elm).closest('tr.header');
		var tr_detail = $(tr_header).next('tr.detail');

		$(tr_header).find('select.select_pelanggan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr_header).find('select.select_pelanggan option').removeAttr('data-select2-id');

		var tr_header_clone = $(tr_header).clone(true);
		var tr_detail_clone = $(tr_detail).clone();

		// $(tr_header_clone).find('.bootstrap-select').replaceWith(function() { return $('select', this); });
		// var selectpicker = $(tr_header_clone).find('.selectpicker').data('selectpicker', null);
		// $(tr_header_clone).find('.bootstrap-select').remove();
		// $(selectpicker).selectpicker();
		// $(tr_header_clone).find('td.pelanggan').append($(selectpicker));

		$(tr_detail_clone).find('input').val('');
		$(tr_detail_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$(tr_detail_clone).find('table tbody tr:not(:first)').remove();

		$(tbody).append(tr_header_clone);
		$(tbody).append(tr_detail_clone);

		var no_urut = 0;
		$.map( $(tbody).find('tr.header'), function(tr) {
			no_urut++;
			$(tr).find('td.no_urut').text( no_urut );

			$(tr).find('.select_pelanggan').select2().on("select2:select", function (e) {
				var no_pelanggan = e.params.data.id;

				rm.cekPelanggan(this, no_pelanggan);
			});
			$(tr).find('.select_pelanggan').next('span.select2').css('width', '100%');
		});


		rm.setting_up();
	}, // end - add_row

	remove_row_plg: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr.header').length > 1 ) {
			var tr_header = $(elm).closest('tr.header');
			var tr_detail = $(tr_header).next('tr.detail');
			$(tr_detail).remove();
			$(tr_header).remove();

			var no_urut = 0;
			$.map( $(tbody).find('tr.header'), function(tr) {
				no_urut++;
				$(tr).find('td.no_urut').text( no_urut );
			});
		}

		rm.hit_total();
	}, // end - remove_row

	add_row: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var tr_clone = $(tr).clone();

		$(tr_clone).find('input').val('');

		$(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$(tbody).append(tr_clone);
	}, // end - add_row

	remove_row: function(elm) {
		var tbody = $(elm).closest('tbody');

		if ( $(tbody).find('tr').length > 1 ) {
			var tr = $(elm).closest('tr');
			$(tr).remove();
		}

		rm.hit_total();
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

        rm.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'noreg': $(elm).data('noreg'),
        	'tgl_panen': $(elm).data('tglpanen'),
        	'nomor': $(elm).data('nomor'),
        };

        $.ajax({
            url : 'transaksi/RpahMobile/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                rm.setting_up(href, 'div#'+href);

                if ( !empty(edit) ) {
                	rm.get_noreg( $('div#'+href).find('#select_mitra') );
                }
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
                url : 'transaksi/RpahMobile/get_lists',
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

	cekPelangganGanda: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var no_urut = $(tr).find('td.no_urut').text();
		var pelanggan = $(elm).val();
		var nama_pelanggan = $(elm).find('option:selected').text();

		var err = 0;
		$.map( $(tbody).find('tr.header'), function(_tr) {
			var _no_urut = $(_tr).find('td.no_urut').text();
			var _pelanggan = $(_tr).find('select').val();
			if ( _no_urut != no_urut ) {
				if ( !empty(_pelanggan) && !empty(pelanggan) ) {
					if ( _pelanggan == pelanggan ) {
						err++;
					}
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Pelanggan tidak boleh ganda, harap cek kembali pelanggan <b>'+nama_pelanggan.toUpperCase()+'</b>');
			$(elm).val('');
		}
	}, // end - cekPelangganGanda

	hit_total: function() {
		var tot_ekor = 0;
		var tot_tonase = 0;
		$.map( $('table.data_plg').find('tr.detail'), function(tr_detail) {
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
		if ( tot_ekor > ekor_konfir ) {
			$('label.tot_ekor').css({'color': 'red'});
		} else {
			$('label.tot_ekor').css({'color': '#333'});
		}

		var tonase_konfir = numeral.unformat($('input.tonase_konfir').val());
		$('label.tot_tonase').text( numeral.formatDec(tot_tonase) );
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
	            url: 'transaksi/RpahMobile/get_noreg',
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
	                		option += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
	                	}
	                }
	                $(div).find('select#select_noreg').removeAttr('disabled');
	                $(div).find('select#select_noreg').html(option);
	                $(div).find('#select_noreg').selectpicker('refresh');

	                if ( !empty(noreg) ) {
	                	rm.get_tgl_konfir(elm);
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

    get_tgl_konfir: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var noreg = $(div).find('#select_noreg').val();

    	var option = '<option value="">Pilih Tanggal</option>';
    	if ( !empty(noreg) ) {
    		$.ajax({
	            url: 'transaksi/RpahMobile/get_tgl_konfir',
	            data: { 'params': noreg },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	                if ( !empty(data.content) && data.content.length > 0 ) {
	                	var tgl_panen = $(div).find('select#select_tgl_konfir').data('val');
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;
	                		if ( data.content[i].tgl_panen == tgl_panen ) {
	                			selected = 'selected';
	                		}
	                		option += '<option data-tokens="'+data.content[i].tgl_panen_after_format+'" value="'+data.content[i].tgl_panen+'" '+selected+'>'+data.content[i].tgl_panen_after_format+'</option>';
	                	}
		                $(div).find('select#select_tgl_konfir').removeAttr('disabled');
		                $(div).find('select#select_tgl_konfir').html(option);
	                	$(div).find('#select_tgl_konfir').selectpicker('refresh');

	                	hideLoading();
	                } else {
	                	hideLoading();
	                	
	                	$(div).find('select#select_tgl_konfir').attr('disabled', 'disabled');
	                	$(div).find('select#select_tgl_konfir').html(option);
	                	$(div).find('#select_tgl_konfir').selectpicker('refresh');

	                	bootbox.alert('Data konfirmasi belum di submit.');
	                }
	            }
	        });
    	} else {
    		$(div).find('select#select_tgl_konfir').attr('disabled', 'disabled');
    		$(div).find('select#select_tgl_konfir').html(option);
    		$(div).find('#select_tgl_konfir').selectpicker('refresh');
    	}
    }, // end - get_tgl_konfir

    set_umur_panen: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var div_tgl = $(div).find('div#tanggal');

    	var ipt_tgl = $(div_tgl).find('input');
    	var select_noreg = $(div).find('#select_noreg');
    	var select_mitra = $(div).find('#select_mitra');
    	var select_tgl_konfir = $(div).find('#select_tgl_konfir');

    	$(ipt_tgl).removeAttr('disabled');
    	var tgl_panen = $(select_tgl_konfir).val();

    	var umur = 0;
    	if ( !empty($(select_noreg).val()) ) {
    		if ( !empty(tgl_panen) ) {
	    		var tgl_docin = $(select_noreg).find('option:selected').data('tgldocin');
	    		// var tgl_panen = dateSQL( $(div_tgl).data("DateTimePicker").date() );

	    		var params = {
	    			'noreg': $(select_noreg).val(),
	    			'mitra': $(select_mitra).val(),
	    			'tgl_panen': tgl_panen,
	    			'kode_unit': $(select_mitra).find('option:selected').data('kodeunit')
	    		};

	    		$.ajax({
		            url: 'transaksi/RpahMobile/get_konfir',
		            data: { 'params': params },
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		                hideLoading();
		                if ( data.status == 1 ) {
		                	var umur = App.selisihWaktuDalamHari(tgl_docin, tgl_panen);

				    		$(div).find('input.umur').val( umur );
				    		$(div).find('input.ekor_konfir').val( numeral.formatInt(data.content.konfir.populasi) );
				    		$(div).find('input.tonase_konfir').val( numeral.formatDec(data.content.konfir.total) );
					    	$(div).find('input.harga_dasar').val( numeral.formatInt(data.content.harga_dasar) );
				    		if ( data.content.harga_dasar > 0 ) {
					    		$(div).find('input.harga_dasar').attr('disabled', 'disabled');
				    		}
		                } else {
		                	bootbox.alert( data.message, function() {
		                		$(div).find('input.umur').val('');
					    		$(div).find('input.ekor_konfir').val('');
					    		$(div).find('input.tonase_konfir').val('');

					    		$(div).find('input.harga_dasar').val('');
					    		$(div).find('input.harga_dasar').removeAttr('disabled');
		                	});
		                }
		                rm.hit_total();
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
    		bootbox.confirm('Apakah anda yakin ingin menyimpan data rencana penjualan harian ?', function(result) {
    			if ( result ) {
    				var detail = [];
    				$.map( $(div).find('table.data_plg tbody tr.header'), function(tr_header) {
    					var tr_detail = $(tr_header).next('tr.detail');

    					var no_pelanggan = $(tr_header).find('select.select_pelanggan').val();
    					var nama_pelanggan = $(tr_header).find('select.select_pelanggan option:selected').text();
    					$.map( $(tr_detail).find('table.tbl_detail tbody tr'), function(tr) {
    						var _detail = {
    							'no_pelanggan': no_pelanggan,
    							'pelanggan': nama_pelanggan,
    							'tonase': numeral.unformat( $(tr).find('input.tonase').val() ),
    							'ekor': numeral.unformat( $(tr).find('input.ekor').val() ),
    							'bb': numeral.unformat( $(tr).find('input.bb').val() ),
    							'harga': numeral.unformat( $(tr).find('input.harga').val() )
    						};

    						detail.push( _detail );
    					});
    				});

    				// var tgl_panen = dateSQL( $(div).find('#tanggal').data("DateTimePicker").date() );
    				var tgl_panen = $(div).find('select#select_tgl_konfir').val();
    				var noreg = $(div).find('select#select_noreg').val();
    				var nomor = $(div).find('select#select_mitra').val();

    				var data = {
    					'noreg': noreg,
    					'kode_unit': $(div).find('select#select_mitra option:selected').data('kodeunit'),
    					'tgl_panen': tgl_panen,
    					'harga_dasar': numeral.unformat( $(div).find('input.harga_dasar').val() ),
    					'detail': detail
    				};

    				$.ajax({
			            url: 'transaksi/RpahMobile/save',
			            data: { 'params': data },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		var btn = '<button type="button" data-tglpanen="'+tgl_panen+'" data-noreg="'+noreg+'" data-nomor="'+nomor+'" data-edit="" data-href="transaksi"></button>';
			                		rm.load_form( $(btn), null, 'transaksi' );
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
    		bootbox.confirm('Apakah anda yakin ingin meng-ubah data rencana penjualan harian ?', function(result) {
    			if ( result ) {
    				var detail = [];
    				$.map( $(div).find('table.data_plg tbody tr.header'), function(tr_header) {
    					var tr_detail = $(tr_header).next('tr.detail');

    					var no_pelanggan = $(tr_header).find('select.select_pelanggan').val();
    					var nama_pelanggan = $(tr_header).find('select.select_pelanggan option:selected').text();
    					$.map( $(tr_detail).find('table.tbl_detail tbody tr'), function(tr) {
    						var _detail = {
    							'no_pelanggan': no_pelanggan,
    							'pelanggan': nama_pelanggan,
    							'tonase': numeral.unformat( $(tr).find('input.tonase').val() ),
    							'ekor': numeral.unformat( $(tr).find('input.ekor').val() ),
    							'bb': numeral.unformat( $(tr).find('input.bb').val() ),
    							'harga': numeral.unformat( $(tr).find('input.harga').val() )
    						};

    						detail.push( _detail );
    					});
    				});

    				// var tgl_panen = dateSQL( $(div).find('#tanggal').data("DateTimePicker").date() );
    				var tgl_panen = $(div).find('select#select_tgl_konfir').val();
    				var noreg = $(div).find('select#select_noreg').val();
    				var nomor = $(div).find('select#select_mitra').val();

    				var data = {
    					'noreg': noreg,
    					'noreg_old': $(div).find('select#select_noreg').data('old'),
    					'kode_unit': $(div).find('select#select_mitra option:selected').data('kodeunit'),
    					'kode_unit_old': $(div).find('select#select_mitra').data('old'),
    					'tgl_panen': tgl_panen,
    					'tgl_panen_old': $(div).find('select#select_tgl_konfir').data('old'),
    					'harga_dasar': numeral.unformat( $(div).find('input.harga_dasar').val() ),
    					'detail': detail
    				};

    				$.ajax({
			            url: 'transaksi/RpahMobile/edit',
			            data: { 'params': data },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		var btn = '<button type="button" data-tglpanen="'+tgl_panen+'" data-noreg="'+noreg+'" data-nomor="'+nomor+'" data-edit="" data-href="transaksi"></button>';
			                		rm.load_form( $(btn), null, 'transaksi' );
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

    	bootbox.confirm('Apakah anda yakin ingin meng-hapus data rencana penjualan harian ?', function(result) {
			if ( result ) {
				var data = {
					'noreg': $(div).find('div.noreg').data('val'),
					'tgl_panen': $(div).find('div.tgl_panen').data('val')
				};

				$.ajax({
		            url: 'transaksi/RpahMobile/delete',
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
		                		rm.load_form( $(btn), null, 'transaksi' );
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            }
		        });
			}
		});
    }, // end - delete

	cekPelanggan: function (elm, no_pelanggan) {
        var tr = $(elm).closest('tr');

        var params = {
            'no_pelanggan': no_pelanggan
        };

        $.ajax({
            url : 'transaksi/RpahMobile/cekPelanggan',
            data : {
                'params' : params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading('Cek kelayakan bakul . . .'); },
            success : function(data){
                hideLoading();
                if ( data.status == 1 ) {
                    if ( data.content.fulfil == 0 ) {
                        bootbox.confirm( data.content.html, function(result) {
							$('.modal').modal('hide');

                            if ( !result ) {
                                $(tr).find('.select_pelanggan').select2().val('');
                                $(tr).find('.select_pelanggan').select2().trigger('change');
                            } else {
								rm.cekPelangganGanda(elm);
							}
                        });
                    } else {
						rm.cekPelangganGanda(elm);
					}
                } else {
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - cekPelanggan
};

rm.start_up();