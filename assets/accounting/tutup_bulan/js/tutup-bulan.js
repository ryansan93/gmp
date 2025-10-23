var tb = {
	startUp: function () {
		tb.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.perusahaan').select2();
        $('.bulan').select2().on('select2:select', function() {
			$('div.data').html('');
			$('div.btn-tutup').addClass('hide');
			$('div.btn-hapus').addClass('hide');
		});

		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

		$('input').change(function () {
			$('div.data').html('');
			$('div.btn-tutup').addClass('hide');
			$('div.btn-hapus').addClass('hide');
		});
	}, // end - settingUp

	cekDataLhkAkhirBulan: function () {
		var err = 0;
		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi parameter terlebih dahulu.');
		} else {			
			var params = {
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
			};

			$.ajax({
				url : 'accounting/TutupBulan/cekDataLhkAkhirBulan',
				data : {
					'params' : params
				},
				dataType : 'json',
				type : 'post',
				beforeSend : function(){ showLoading('Proses cek data LHK akhir bulan . . .'); },
				success : function(data){
					hideLoading();

					console.log( data );

					if ( data.status == 1 ) {
						tb.tutupBulan();
					} else {
						if ( data.status == 2 ) {
							tb.formDataLhkAkhirBulan( params );
						} else {
							bootbox.alert(data.message);
						}
					}
				}
			});
		}
	}, // end - cekDataLhkAkhirBulan

	formDataLhkAkhirBulan: function( params ) {
		showLoading();
		$.get('accounting/TutupBulan/formDataLhkAkhirBulan',{
				'params': params
			},function(data){
			hideLoading();
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				$(this).find('.modal-dialog').css({'max-width':'100%', 'width': '70%'});

				var modal_dialog = $(this).find('.modal-dialog');
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
	}, // end - formDataLhkAkhirBulan

	tutupBulan: function () {
		var tahun = $('#tahun').find('input[type="text"]').val();
		var nama_bulan = $('.bulan').find('option:selected').text();
		
		bootbox.confirm('Apakah anda yakin ingin mem-proses tutup bulan <b>'+nama_bulan+' '+tahun+'</b> ?', function (result) {
			if ( result ) {
				var params = {
					'bulan': $('.bulan').select2().val(),
					'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
				};

				$.ajax({
					url : 'accounting/TutupBulan/tutupBulan',
					data : {
						'params' : params
					},
					dataType : 'json',
					type : 'post',
					beforeSend : function(){ showLoading('Proses tutup bulan . . .'); },
					success : function(data){
						hideLoading();

						if ( data.status == 1 ) {
							bootbox.alert(data.message, function() {
								location.reload();
							});
						} else {
							bootbox.alert(data.message);
						}
					}
				});
			}
		});
	}, // end - tutupBulan

	hapusTutupBulan: function () {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi parameter terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menghapus data tutup bulan ?', function (result) {
				if ( result ) {
					var params = {
						'perusahaan': $('.perusahaan').select2().val(),
						'bulan': $('.bulan').select2().val(),
						'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
					};
	
					$.ajax({
						url : 'accounting/TutupBulan/hapusTutupBulan',
						data : {
							'params' : params
						},
						dataType : 'json',
						type : 'post',
						beforeSend : function(){ showLoading(); },
						success : function(data){
							hideLoading();
	
							if ( data.status == 1 ) {
								bootbox.alert(data.message, function() {
									tb.getData();
								});
							} else {
								bootbox.alert(data.message);
							}
						}
					});
				}
			});
		}
	}, // end - hapusTutupBulan
};

tb.startUp();