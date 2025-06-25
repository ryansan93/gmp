var kk = {
	startUp: function () {
		kk.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('.unit').select2('val');

            $('.btn-tutup-bulan').addClass('hide');
        });

		$('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2('val');

            $('.btn-tutup-bulan').addClass('hide');
        });

		$('#periode').datetimepicker({
            locale: 'id',
            format: 'MMM YYYY'
        });

		var tgl = $('#periode').find('input').attr('data-tgl');
		if ( !empty(tgl) ) {
			$('#periode').data('DateTimePicker').date( moment(new Date(tgl)) );
		}

		var kode_unit = $('.unit').select2().val();
		if ( !empty(kode_unit) && !empty(tgl) ) {
			kk.getLists();
		}
	}, // end - settingUp

	getLists: function () {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
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
			var unit = $('.unit').select2('val');
			var perusahaan = $('.perusahaan').select2('val');

			var params = {
				'unit': unit,
				'perusahaan': perusahaan,
				'periode': dateSQL( $('#periode').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/KasKecil/getLists',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                	if ( data.status == 1 ) {
                    	$('table').find('tbody').html( data.html );
                    	$('.btn-tutup-bulan').attr('data-status', data.status_btn_tutup_bulan);
                    	$('button.submit').attr('data-status', data.status_btn_submit);
                    	$('button.ack').attr('data-status', data.status_btn_ack);
                    	kk.cekBtnTutupBulan( unit );
                	} else {
                		bootbox.alert( data.message );
                	}
                }
            });
		}
	}, // end - getLists

	cekBtnTutupBulan: function (unit) {
		var status = $('.btn-tutup-bulan').attr('data-status');

		if ( status == 1 && unit != 'all' ) {
			$('.btn-tutup-bulan').removeClass('hide');
		} else {
			$('.btn-tutup-bulan').addClass('hide');
		}
	}, // end - cekBtnTutupBulan

	save: function () {
		bootbox.confirm('Apakah anda yakin ingin menutup bulan kas kecil ?', function (result) {
			if ( result ) {
				var params = {
					'unit': $('.unit').select2('val'),
					'perusahaan': $('.perusahaan').select2('val'),
					'periode': dateSQL( $('#periode').data('DateTimePicker').date() ),
					'saldo_akhir': numeral.unformat( $('.saldo_akhir').find('b').text() )
				};
				
				$.ajax({
	                url : 'report/KasKecil/save',
	                data : {
	                    'params' : params
	                },
	                type : 'POST',
	                dataType : 'JSON',
	                beforeSend : function(){ showLoading(); },
	                success : function(data){
	                	hideLoading();

	                	if ( data.status == 1 ) {
	                    	bootbox.alert( data.message, function () {
	                    		kk.getLists();
	                    	});
	                	} else {
	                		bootbox.alert( data.message );
	                	}
	                }
	            });
			}
		});
	}, // end - save

	ack: function () {
		bootbox.confirm('Apakah anda yakin ingin meng-ack kas kecil ?', function (result) {
			if ( result ) {
				var params = {
					'unit': $('.unit').select2('val'),
					'perusahaan': $('.perusahaan').select2('val'),
					'periode': dateSQL( $('#periode').data('DateTimePicker').date() ),
					'saldo_akhir': numeral.unformat( $('.saldo_akhir').find('b').text() )
				};
				
				$.ajax({
	                url : 'report/KasKecil/ack',
	                data : {
	                    'params' : params
	                },
	                type : 'POST',
	                dataType : 'JSON',
	                beforeSend : function(){ showLoading(); },
	                success : function(data){
	                	hideLoading();

	                	if ( data.status == 1 ) {
	                    	bootbox.alert( data.message, function () {
	                    		kk.getLists();
	                    	});
	                	} else {
	                		bootbox.alert( data.message );
	                	}
	                }
	            });
			}
		});
	}, // end - ack

	getSumberTujuanCoa: function( elm, id ) {
    	var modal = $(elm).closest('.modal');

    	$.ajax({
            url : 'report/KasKecil/getSumberTujuanCoa',
            data : { 'params': id },
            type : 'post',
            dataType : 'json',
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                	$(modal).find('.sumber_coa label').text( ': '+data.content.sumber+' ('+data.content.sumber_coa+')' );
                	$(modal).find('.sumber_coa').attr( 'data-coa', data.content.sumber_coa );
                	$(modal).find('.tujuan_coa label').text( ': '+data.content.tujuan+' ('+data.content.tujuan_coa+')' );
                	$(modal).find('.tujuan_coa').attr( 'data-coa', data.content.tujuan_coa );
                } else {
                	bootbox.alert( data.message );
                }
            },
        });
    }, // end - getSumberTujuanCoa

	detailForm: function (elm) {
		$('.modal').modal('hide');

		var id = $(elm).attr('data-id');
		var g_status = $(elm).attr('data-gstatus');

		var params = {
			'id': id,
			'g_status': g_status
		};

		$.get('report/KasKecil/detailForm',{
			'params': params
		},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				var modal_dialog = $(this).find('.modal-dialog');
				var modal_body = $(this).find('.modal-body');

				$(modal_dialog).css({'max-width' : '50%'});
				$(modal_dialog).css({'width' : '50%'});

				var modal_header = $(this).find('.modal-header');
				$(modal_header).css({'padding-top' : '0px'});
			});
		},'html');
	}, // end - detailForm

	editForm: function (elm) {
		$('.modal').modal('hide');

		var id = $(elm).attr('data-id');

		var params = {
			'id': id
		};

		$.get('report/KasKecil/editForm',{
			'params': params
		},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				var modal_dialog = $(this).find('.modal-dialog');
				var modal_body = $(this).find('.modal-body');

				$(modal_dialog).css({'max-width' : '50%'});
				$(modal_dialog).css({'width' : '50%'});

				var modal_header = $(this).find('.modal-header');
				$(modal_header).css({'padding-top' : '0px'});

				$(modal_body).find('select.det_jurnal_trans').select2().on("select2:select", function (e) {
		            kk.getSumberTujuanCoa( this, e.params.data.id );
		        });

				$(this).removeAttr('tabindex');
			});
		},'html');
	}, // end - editForm

	edit: function (elm) {
		var modal = $(elm).closest('.modal');
		
		bootbox.confirm('Apakah anda yakin ingin meng-edit detail transaksi ini ?', function (result) {
			if ( result ) {
				var id = $(elm).attr('data-id');

				var params = {
					'id': id,
					'det_jurnal_trans_id': $(modal).find('select.det_jurnal_trans').select2().val(),
					'sumber': $(modal).find('.sumber_coa label').text().replace(': ', ''),
					'sumber_coa': $(modal).find('.sumber_coa').attr('data-coa'),
					'tujuan': $(modal).find('.tujuan_coa label').text().replace(': ', ''),
					'tujuan_coa': $(modal).find('.tujuan_coa').attr('data-coa')
				};
				
				$.ajax({
	                url : 'report/KasKecil/editDetJurnal',
	                data : {
	                    'params' : params
	                },
	                type : 'POST',
	                dataType : 'JSON',
	                beforeSend : function(){ showLoading(); },
	                success : function(data){
	                	hideLoading();

	                	if ( data.status == 1 ) {
	                    	bootbox.alert( data.message, function () {
								$(modal).modal('hide');
	                    		kk.getLists();
	                    	});
	                	} else {
	                		bootbox.alert( data.message );
	                	}
	                }
	            });
			}
		});
	}, // end - edit
};

kk.startUp();