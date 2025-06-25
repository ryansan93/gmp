var kpm = {
	start_up: function() {
		kpm.setting_up('riwayat', 'div#riwayat');
		kpm.setting_up('transaksi', 'div#transaksi');
	}, // end - start_up

	list_riwayat: function(elm) {
		var div_riwayat = $(elm).closest('div#riwayat');

		var noreg = $(div_riwayat).find('#select_noreg').val();

		var params = {
			'noreg': noreg
		};

		$.ajax({
            url: 'transaksi/KonfirmasiPanenMobile/list_riwayat',
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

		kpm.load_form(id, edit, href);
	}, // end - change_tab

	load_form: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/KonfirmasiPanenMobile/load_form',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

                kpm.setting_up('transaksi', 'div#transaksi');

                if ( !empty(edit) ) {

                	kpm.get_noreg( $('div#'+href).find('#select_mitra') );
                }

                hideLoading();
            }
        });
	}, // end - list_riwayat

	setting_up: function(jenis_div, div) {
		$(div).find('#select_mitra').selectpicker();
		$(div).find('#select_mitra').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
		    kpm.get_noreg(this);
		});

		$(div).find('#select_noreg').selectpicker();
		$(div).find('#select_noreg').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
			if ( jenis_div == 'transaksi' ) {
		    	kpm.set_umur_populasi(this);
			}
		});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('input[type=file]').on('change', function() {
			kpm.cek_type_file(this);
		});

		$('.date').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
                $(ipt).data("DateTimePicker").minDate(moment(tgl));
            }
        });

        $("#tanggal").on("dp.change", function (e) {
			kpm.set_umur_populasi(this);
		});
	}, // end - setting_up

	add_row: function(elm) {
		var tbody = $(elm).closest('tbody');
		var tr = $(elm).closest('tr');
		var tr_clone = $(tr).clone();

		$(tr_clone).find('input').val('');

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
			var no_urut = 0;
			$.map( $(tbody).find('tr'), function(tr) {
				no_urut++;
				$(tr).find('td.no_urut').text( no_urut );
			});
		}
	}, // end - remove_row

    get_noreg: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var nomor_mitra = $(div).find('#select_mitra').val();

    	var option = '<option value="">Pilih Noreg</option>';
    	if ( !empty(nomor_mitra) ) {
    		$.ajax({
	            url: 'transaksi/KonfirmasiPanenMobile/get_noreg',
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
	                		option += '<option data-tokens="'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'" data-umur="'+data.content[i].umur+'" data-tgldocin="'+data.content[i].real_tgl_docin+'" data-populasi="'+data.content[i].populasi+'" value="'+data.content[i].noreg+'" '+selected+'>'+data.content[i].tgl_docin+' | '+data.content[i].kandang+' | '+data.content[i].noreg+'</option>';
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

    set_umur_populasi: function(elm) {
    	var div = $(elm).closest('.tab-pane');
    	var div_tgl = $(div).find('div#tanggal');

    	var ipt_tgl = $(div_tgl).find('input');
    	var select_noreg = $(div).find('#select_noreg');

    	$(ipt_tgl).removeAttr('disabled');

    	var umur = 0;
    	if ( !empty($(select_noreg).val()) ) {
    		if ( !empty($(ipt_tgl).val()) ) {
	    		var tgl_docin = $(select_noreg).find('option:selected').data('tgldocin');
	    		var populasi = $(select_noreg).find('option:selected').data('populasi');

	    		var tgl = dateSQL($(div_tgl).data('DateTimePicker').date());

	    		var umur = App.selisihWaktuDalamHari(tgl_docin, tgl);

	    		$(div).find('input.umur').val( umur );
	    		$(div).find('input.populasi').val( numeral.formatInt(populasi) );
	    	}
    	} else {
    		$(ipt_tgl).attr('disabled', 'disabled');
    		$(ipt_tgl).val('');
    		$(div).find('input.umur').val('');
    	}
    }, // end - set_umur_populasi

    hitung_total: function(elm) {
        let tbody = $(elm).closest('tbody');
        let table = $(tbody).closest('table');
        let tfoot = $(table).find('tfoot');
        let div = $(table).closest('div#transaksi');

        let total_sekat = 0;
        let total_jumlah = 0;
        let total_bb = 0;
        let bb_rata2_sekat = 0;

        $.map( $(tbody).find('tr'), function(tr) {
            let jml = numeral.unformat( $(tr).find('input.jumlah').val() );
            let bb = numeral.unformat( $(tr).find('input.bb').val() );

            total_sekat += jml * bb;

            total_jumlah += jml;
            total_bb += bb;
        });

        $(tfoot).find('td.tot_jumlah b').html( numeral.formatInt(total_jumlah) );
        // $(tfoot).find('td.tot_bb b').html( numeral.formatDec(total_bb) );

        // bb_rata2_sekat = numeral.unformat( $(tfoot).find('td.tot_bb b').text() ) / $(tbody).find('tr').length;
        // total_sekat = numeral.unformat( $(tfoot).find('td.tot_jumlah b').text() ) * bb_rata2_sekat;

        $(div).find('input.tot_sekat').val( numeral.formatDec(total_sekat) );
        $(div).find('input.bb_rata2').val( numeral.formatDec(total_sekat / total_jumlah) );
    }, // end - hitung_total

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
            bootbox.alert( 'Harap lengkapi data konfirmasi panen.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    let tgl_docin = $(div).find('#select_noreg option:selected').data('tgldocin');
                    let tgl_panen = dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() );
                    let noreg = $(div).find('#select_noreg').val();
                    let populasi = numeral.unformat( $(div).find('input.populasi').val() );
                    let bb_rata2 = numeral.unformat( $(div).find('input.bb_rata2').val() );
                    let tot_sekat = numeral.unformat( $(div).find('input.tot_sekat').val() );

                    let data_sekat = $.map( $(div).find('table.data_sekat tbody tr'), function(tr) {
                        let _data = {
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'bb': numeral.unformat( $(tr).find('input.bb').val() ),
                        };

                        return _data;
                    });

                    let data = {
                        'tgl_docin': tgl_docin,
                        'tgl_panen': tgl_panen,
                        'noreg': noreg,
                        'populasi': populasi,
                        'bb_rata2': bb_rata2,
                        'tot_sekat': tot_sekat,
                        'data_sekat': data_sekat
                    };

                    $.ajax({
			            url : 'transaksi/KonfirmasiPanenMobile/save',
			            data : {
			                'params' :  data
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert( data.message, function() {
			                        kpm.load_form(data.content.id, null, 'transaksi');
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
            bootbox.alert( 'Harap lengkapi data konfirmasi panen.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin meng-ubah data ?', function(result) {
                if ( result ) {
                    let tgl_docin = $(div).find('#select_noreg option:selected').data('tgldocin');
                    let tgl_panen = dateSQL( $(div).find('#tanggal').data('DateTimePicker').date() );
                    let noreg = $(div).find('#select_noreg').val();
                    let populasi = numeral.unformat( $(div).find('input.populasi').val() );
                    let bb_rata2 = numeral.unformat( $(div).find('input.bb_rata2').val() );
                    let tot_sekat = numeral.unformat( $(div).find('input.tot_sekat').val() );

                    let data_sekat = $.map( $(div).find('table.data_sekat tbody tr'), function(tr) {
                        let _data = {
                            'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
                            'bb': numeral.unformat( $(tr).find('input.bb').val() ),
                        };

                        return _data;
                    });

                    let data = {
                        'id': $(elm).data('id'),
                        'tgl_docin': tgl_docin,
                        'tgl_panen': tgl_panen,
                        'noreg': noreg,
                        'populasi': populasi,
                        'bb_rata2': bb_rata2,
                        'tot_sekat': tot_sekat,
                        'data_sekat': data_sekat
                    };

                    $.ajax({
			            url : 'transaksi/KonfirmasiPanenMobile/edit',
			            data : {
			                'params' :  data
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading() },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert( data.message, function() {
			                        kpm.load_form(data.content.id, null, 'transaksi');
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
		kpm.load_form(id, null, 'transaksi');
	}, // end - batal_edit

	delete: function(elm) {
		var id = $(elm).data('id');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
			if ( result ) {
				$.ajax({
		            url: 'transaksi/KonfirmasiPanenMobile/delete',
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
		                    	kpm.load_form(null, null, 'transaksi');
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
};

kpm.start_up();