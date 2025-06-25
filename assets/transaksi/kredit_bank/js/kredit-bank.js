var kb = {
	startUp: function () {
		kb.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.perusahaan').select2();
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
            kb.generateRowAngsuran();
        });

		$('#tgl_jatuh_tempo').datetimepicker({
			locale: 'id',
            format: 'DD'
        });
        var tgl_jatuh_tempo_val = $('#tgl_jatuh_tempo').find('input').attr('data-val');
    	if ( !empty(tgl_jatuh_tempo_val) ) {
    		$('#tgl_jatuh_tempo').data('DateTimePicker').date( new Date(tgl_jatuh_tempo_val) );
    	}
    	$('#tgl_jatuh_tempo').on("dp.change", function (e) {
            kb.generateRowAngsuran();
        });

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

        $('tr.header').find('td:not(.btn-row)').click(function() {
        	var tr = $(this).closest('tr.header');

        	kb.changeTabActive( $(tr) );
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - settingUp

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

        kb.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id = null, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': id
        };

        $.ajax({
            url : 'transaksi/KreditBank/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                kb.settingUp();
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
	            url : 'transaksi/KreditBank/getLists',
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

	                kb.collapseRow();
	                kb.settingUp();
	            },
	        });
		}
	}, // end - getLists

	hitungAngsuran: function () {
		var pokok_pinjaman = numeral.unformat($('.pokok_pinjaman').val());
		var bunga = numeral.unformat($('.bunga').val());
		var tenor = numeral.unformat($('.tenor').val());

		var angsuran = 0;
		if ( pokok_pinjaman > 0 && bunga > 0 && tenor > 0 ) {
			angsuran = (pokok_pinjaman + bunga) / tenor;
		}

		$('.angsuran').val( numeral.formatDec(angsuran) );

		kb.generateRowAngsuran();
	}, // end - hitungAngsuran

	generateRowAngsuran: function () {
		var pokok_pinjaman = numeral.unformat($('.pokok_pinjaman').val());
		var bunga = numeral.unformat($('.bunga').val());
		var tenor = numeral.unformat($('.tenor').val());
		var angsuran = numeral.unformat($('.angsuran').val());
		var tanggal = $('#tanggal').find('input').val();
		var tgl_jatuh_tempo = $('#tgl_jatuh_tempo').find('input').val();

		if ( pokok_pinjaman > 0 && bunga > 0 && !empty(tanggal) && !empty(tgl_jatuh_tempo) ) {
			var params = {
				'pokok_pinjaman': pokok_pinjaman,
				'bunga': bunga,
				'tenor': tenor,
				'angsuran': angsuran,
				'tanggal': dateSQL($('#tanggal').data('DateTimePicker').date()),
				'tgl_jatuh_tempo': dateSQL($('#tgl_jatuh_tempo').data('DateTimePicker').date()),
			};

			$.ajax({
	            url : 'transaksi/KreditBank/generateRowAngsuran',
	            data : {
	                'params' : params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	                hideLoading();

	                $('.tbl_angsuran tbody').html( html );

	                kb.settingUp();
	            },
	        });
		}
	}, // end - generateRowAngsuran

	editDetail: function(elm) {
		var tr = $(elm).closest('tr');
		var div = $(elm).closest('div.act_edit');
		var next_div = $(div).next('div.act_save');

		$(tr).find('.tgl_bayar input').removeAttr('disabled');
		$(div).addClass('hide');
		$(next_div).removeClass('hide');
	}, // end - editDetail

	batalDetail: function(elm) {
		var tr = $(elm).closest('tr');
		var div = $(elm).closest('div.act');
		var prev_div = $(div).prev('div.act');

		$(tr).find('.tgl_bayar input').attr('disabled', 'disabled');
		$(div).addClass('hide');
		$(prev_div).removeClass('hide');

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

		var date = $(tr).find('.tgl_bayar input').val();

		if ( empty(date) ) {
			$(tr).find('.tgl_bayar input').parent().addClass('has-error');
			bootbox.alert('Harap isi tanggal bayar terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan tanggal bayar ?', function(result) {
				if ( result ) {
					var data = {
						'kredit_kendaraan_kode': $(tr).attr('data-kode'),
						'angsuran_ke': $(tr).attr('data-no'),
						'tgl_bayar': dateSQL($(tr).find('.tgl_bayar').data('DateTimePicker').date())
					};

					$.ajax({
			            url : 'transaksi/KreditBank/saveDetail',
			            data : {
			                'params' : data
			            },
			            type : 'POST',
			            dataType : 'JSON',
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
							'jumlah_angsuran': $(tr).find('td.jumlah').attr('data-val'),
							'jumlah_angsuran_pokok': $(tr).find('td.pokok').attr('data-val'),
							'jumlah_angsuran_bunga': $(tr).find('td.bunga').attr('data-val'),
							'tgl_jatuh_tempo': $(tr).find('td.tgl_jatuh_tempo').attr('data-val'),
							'tgl_bayar': tgl_bayar
						};

						return _detail;
					});

					var data = {
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'perusahaan': $(dcontent).find('.perusahaan').select2().val(),
						'jenis_kredit': $(dcontent).find('.jenis_kredit').val(),
						'bank': $(dcontent).find('.bank').val(),
						'agunan': $(dcontent).find('.agunan').val(),
						'no_dokumen': $(dcontent).find('.no_dokumen').val(),
						'pokok_pinjaman': numeral.unformat($(dcontent).find('.pokok_pinjaman').val()),
						'bunga': numeral.unformat($(dcontent).find('.bunga').val()),
						'bunga_per_tahun': numeral.unformat($(dcontent).find('.bunga_per_tahun').val()),
						'tenor': $(dcontent).find('.tenor').val(),
						'angsuran': numeral.unformat($(dcontent).find('.angsuran').val()),
						'tgl_jatuh_tempo': dateSQL($(dcontent).find('#tgl_jatuh_tempo').data('DateTimePicker').date()),
						'detail': detail
					};

					$.ajax({
			            url : 'transaksi/KreditBank/save',
			            data : {
			                'params' : data
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		kb.loadForm(data.content.id, null, 'action');
			                		if ( !empty($('.status_kredit').select2().val()) ) {
			                			kb.getLists();
			                		}
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
							'jumlah_angsuran': $(tr).find('td.jumlah').attr('data-val'),
							'jumlah_angsuran_pokok': $(tr).find('td.pokok').attr('data-val'),
							'jumlah_angsuran_bunga': $(tr).find('td.bunga').attr('data-val'),
							'tgl_jatuh_tempo': $(tr).find('td.tgl_jatuh_tempo').attr('data-val'),
							'tgl_bayar': tgl_bayar
						};

						return _detail;
					});

					var data = {
						'kode': $(elm).attr('data-kode'),
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'perusahaan': $(dcontent).find('.perusahaan').select2().val(),
						'jenis_kredit': $(dcontent).find('.jenis_kredit').val(),
						'bank': $(dcontent).find('.bank').val(),
						'agunan': $(dcontent).find('.agunan').val(),
						'no_dokumen': $(dcontent).find('.no_dokumen').val(),
						'pokok_pinjaman': numeral.unformat($(dcontent).find('.pokok_pinjaman').val()),
						'bunga': numeral.unformat($(dcontent).find('.bunga').val()),
						'bunga_per_tahun': numeral.unformat($(dcontent).find('.bunga_per_tahun').val()),
						'tenor': $(dcontent).find('.tenor').val(),
						'angsuran': numeral.unformat($(dcontent).find('.angsuran').val()),
						'tgl_jatuh_tempo': dateSQL($(dcontent).find('#tgl_jatuh_tempo').data('DateTimePicker').date()),
						'detail': detail
					};

					$.ajax({
			            url : 'transaksi/KreditBank/edit',
			            data : {
			                'params' : data
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert( data.message, function() {
			                		kb.loadForm(data.content.id, null, 'action');
			                		if ( !empty($('.status_kredit').select2().val()) ) {
			                			kb.getLists();
			                		}
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
		            url : 'transaksi/KreditBank/delete',
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
		                		kb.loadForm(null, null, 'action');
		                		if ( !empty($('.status_kredit').select2().val()) ) {
		                			kb.getLists();
		                		}
		                	});
		                } else {
		                	bootbox.alert( data.message );
		                }
		            },
		        });
			}
		});
	}, // end - delete
};

kb.startUp();