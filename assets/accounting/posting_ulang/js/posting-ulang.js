var pu = {
	startUp: function () {
		pu.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.jenis').select2();
        $('.bulan').select2().on('select2:select', function() {
			$('div.data').html('');
			$('div.btn-tutup').addClass('hide');
			$('div.btn-hapus').addClass('hide');
		});

		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'Y'
        });
	}, // end - settingUp

	postingUlang: function () {
		var tahun = $('#tahun').find('input[type="text"]').val();
		var nama_bulan = $('.bulan').find('option:selected').text();
		var jenis = $('.jenis').find('option:selected').text();
		
		bootbox.confirm('Apakah anda yakin ingin melakukan posting ulang data <b>'+jenis+'</b> bulan <b>'+nama_bulan+' '+tahun+'</b> ?', function (result) {
			if ( result ) {
				var params = {
					'bulan': $('.bulan').select2().val(),
					'tahun': dateSQL($('#tahun').data('DateTimePicker').date()),
					'jenis': $('.jenis').select2().val()
				};

				$.ajax({
					url : 'accounting/PostingUlang/postingUlang',
					data : {
						'params' : params
					},
					dataType : 'json',
					type : 'post',
					beforeSend : function(){ showLoading('Proses posting ulang . . .'); },
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
	}, // end - postingUlang
};

pu.startUp();