var oap = {
	startUp: function () {
		oap.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

		$("[name=startDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		$("[name=endDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			$("[name=endDate]").data("DateTimePicker").minDate(e.date);
			// $("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});

		$('.unit').select2();
		$('.unit').on('select2:select', function (e) {
			var data = e.params.data.id;

			oap.getSjByUnit( data );
		});

		if ( !empty($('.unit').select2('val')) ) {
			oap.getSjByUnit( $('.unit').select2('val'), 'edit' );
			$('input.ongkos_angkut').removeAttr('disabled');
		}
	}, // end - settingUp

	getLists: function() {
    	let div = $('div#riwayat');
        
        var err = 0;

        $.map( $(div).find('[data-required=1]'), function (ipt) {
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
        	var params = {
        		'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
        		'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
        	};

	        $.ajax({
	            url : 'transaksi/OngkosAngkutPindahPakan/getLists',
	            data : { 'params': params },
	            type : 'get',
	            dataType : 'html',
	            beforeSend : function(){ showLoading() },
	            success : function(html){
	                $(div).find('.tbl_riwayat tbody').html( html );
	                hideLoading();
	            },
	        });
        }
    }, // end - getLists

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

        oap.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id = null, edit = null, href) {
        var dcontent = $('div#'+href);

        $.ajax({
            url: 'transaksi/OngkosAngkutPindahPakan/loadForm',
            data: {
                'id': id,
                'edit': edit
            },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading(); },
            success: function(html){
            	hideLoading();

                $(dcontent).html( html );

                oap.settingUp();
            },
        });
    }, // end - load_form

	getSjByUnit: function ( unit, edit = null ) {
		var opt = '<option value="">Pilih SJ</option>';
		if ( !empty(edit) ) {
			opt = '';
		}

		if ( !empty(unit) ) {
			var params = {
				'unit': unit
			};

			$.ajax({
	            url: 'transaksi/OngkosAngkutPindahPakan/getSjByUnit',
	            dataType: 'json',
	            type: 'post',
	            data: {
	                'params': params
	            },
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
	                    if ( !empty(data.content) ) {
	                    	for (var i = 0; i < data.content.length; i++) {
	                    		opt += '<option value="'+data.content[i].no_sj+'" data-jk="'+data.content[i].jenis_kirim+'" data-namaasal="'+data.content[i].nama_asal+'" data-namatujuan="'+data.content[i].nama_tujuan+'" data-idasal="'+data.content[i].id_asal+'" data-noregtujuan="'+data.content[i].noreg_tujuan+'" data-tglterima="'+data.content[i].tgl_terima+'" data-tglterimatext="'+data.content[i].tgl_terima_text+'" data-ekspedisi="'+data.content[i].ekspedisi+'" data-nopolisi="'+data.content[i].no_polisi+'" data-sopir="'+data.content[i].sopir+'">'+data.content[i].jenis_kirim.toUpperCase()+' | '+data.content[i].tgl_terima_text+' | '+data.content[i].no_sj+'</option>';
	                    	}

							$('.no_sj').removeAttr('disabled');
							if ( !empty(edit) ) {
								$('.no_sj').append( opt );
							} else {
								$('.no_sj').html( opt );
							}
	                    	$('.no_sj').select2();
	                    	$('.no_sj').on('select2:select', function (e) {
								var data = e.params.data.element.dataset;

								$('input.tgl_terima').val( data.tglterimatext );
								var id_asal = data.idasal;
								var noreg_tujuan = data.noregtujuan;
								var jenis_kirim = data.jk;

								if ( jenis_kirim == 'opkp' ) {
									$('input.asal').val( data.namaasal+' ( KDG : '+parseInt(id_asal.substring(id_asal.length-2, id_asal.length))+' )' );
								} else {
									$('input.asal').val( data.namaasal );
								}

								$('input.tujuan').val( data.namatujuan+' ( KDG : '+parseInt(noreg_tujuan.substring(noreg_tujuan.length-2, noreg_tujuan.length))+' )' );
								$('input.ekspedisi').val( data.ekspedisi.toUpperCase() );
								$('input.no_polisi').val( data.nopolisi.toUpperCase() );
								$('input.sopir').val( data.sopir.toUpperCase() );
								$('input.ongkos_angkut').removeAttr('disabled');
							});
	                    } else {
	                    	$('.no_sj').select2('destroy');
							$('.no_sj').html( opt );
							$('.no_sj').attr('disabled', 'disabled');
							$('input[type=text]').val('');
							$('input[type=text]').attr('disabled', 'disabled');
	                    }
	                } else {
	                    bootbox.alert(data.message);
	                };
	            },
	        });
		} else {
			$('.no_sj').select2('destroy');
			$('.no_sj').html( opt );
			$('.no_sj').attr('disabled', 'disabled');
			$('input[type=text]').val('');
			$('input[type=text]').attr('disabled', 'disabled');
		}
	}, // end - getSjByUnit

	save: function () {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data OA ?', function (result) {
				if ( result ) {
					var params = {
						'no_sj': $(div).find('.no_sj').select2('val'),
						'ongkos_angkut': numeral.unformat( $(div).find('.ongkos_angkut').val() ),
						'ekspedisi': $(div).find('.ekspedisi').val().toUpperCase(),
						'no_polisi': $(div).find('.no_polisi').val().toUpperCase(),
						'sopir': $(div).find('.sopir').val().toUpperCase()

					};

					$.ajax({
			            url: 'transaksi/OngkosAngkutPindahPakan/save',
			            dataType: 'json',
			            type: 'post',
			            data: {
			                'params': params
			            },
			            beforeSend: function() { showLoading(); },
			            success: function(data) {
			                hideLoading();

			                if ( data.status == 1 ) {
			                    bootbox.alert(data.message, function () {
			                    	location.reload();
			                    });
			                } else {
			                    bootbox.alert(data.message);
			                };
			            },
			        });
				}
			});
		}
	}, // end - save

	edit: function (elm) {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data OA ?', function (result) {
				if ( result ) {
					var params = {
						'id': $(elm).attr('data-id'),
						'no_sj': $(div).find('.no_sj').select2('val'),
						'ongkos_angkut': numeral.unformat( $(div).find('.ongkos_angkut').val() ),
						'ekspedisi': $(div).find('.ekspedisi').val().toUpperCase(),
						'no_polisi': $(div).find('.no_polisi').val().toUpperCase(),
						'sopir': $(div).find('.sopir').val().toUpperCase()
					};

					$.ajax({
			            url: 'transaksi/OngkosAngkutPindahPakan/edit',
			            dataType: 'json',
			            type: 'post',
			            data: {
			                'params': params
			            },
			            beforeSend: function() { showLoading(); },
			            success: function(data) {
			                hideLoading();

			                if ( data.status == 1 ) {
			                    bootbox.alert(data.message, function () {
			                    	oap.loadForm( $(elm).attr('data-id'), null, 'action' );
			                    });
			                } else {
			                    bootbox.alert(data.message);
			                };
			            },
			        });
				}
			});
		}
	}, // end - edit

	delete: function (elm) {
		var div = $('div#action');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data OA ?', function (result) {
			if ( result ) {
				var params = {
					'id': $(elm).attr('data-id')
				};

				$.ajax({
		            url: 'transaksi/OngkosAngkutPindahPakan/delete',
		            dataType: 'json',
		            type: 'post',
		            data: {
		                'params': params
		            },
		            beforeSend: function() { showLoading(); },
		            success: function(data) {
		                hideLoading();

		                if ( data.status == 1 ) {
		                    bootbox.alert(data.message, function () {
		                    	oap.loadForm( null, null, 'action' );
		                    });
		                } else {
		                    bootbox.alert(data.message);
		                };
		            },
		        });
			}
		});
	}, // end - delete
};

oap.startUp();