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

	getData: function() {
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
				'perusahaan': $('.perusahaan').select2().val(),
				'bulan': $('.bulan').select2().val(),
				'tahun': dateSQL($('#tahun').data('DateTimePicker').date())
			};

			$.ajax({
                url : 'accounting/TutupBulan/getData',
                data : {
                    'params' : params
                },
                dataType : 'json',
                type : 'post',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

					if ( data.status == 1 ) {
						$('.data').html( data.content.html );

						if ( data.content.btn_tutup == 1 ) {
							$('div.btn-tutup').removeClass('hide');
						} else {
							$('div.btn-tutup').addClass('hide');
						}

						if ( data.content.btn_hapus == 1 ) {
							$('div.btn-hapus').removeClass('hide');
						} else {
							$('div.btn-hapus').addClass('hide');
						}
					} else {
						bootbox.alert(data.message);
					}
                }
            });
		}
	}, // end - getData

	tutupBulan: function () {
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
			bootbox.confirm('Apakah anda yakin ingin proses tutup bulan ?', function (result) {
				if ( result ) {
					var params = {
						'perusahaan': $('.perusahaan').select2().val(),
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