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

				$(this).find('button.btn-export-unit-image').click(function() {
					tb.exportUnitImage( $(this) );
				});

				$(this).find('button.btn-wa-unit-image').click(function() {
					tb.sendUnitImageWA( $(this) );
				});
			});
		},'html');
	}, // end - formDataLhkAkhirBulan

	renderUnitCanvas: function( btn ) {
		var block = $(btn).closest('.unit-lhk-block');

		$(btn).addClass('hide');

		return html2canvas( block[0], { backgroundColor: '#ffffff', scale: 2 } ).then(function(canvas) {
			$(btn).removeClass('hide');
			return canvas;
		}).catch(function(err) {
			$(btn).removeClass('hide');
			throw err;
		});
	}, // end - renderUnitCanvas

	exportUnitImage: function( btn ) {
		var unit = $(btn).data('unit');

		$(btn).prop('disabled', true);

		tb.renderUnitCanvas( btn ).then(function(canvas) {
			$(btn).prop('disabled', false);

			var link = document.createElement('a');
			link.download = 'LHK Belum Input - ' + unit + '.png';
			link.href = canvas.toDataURL('image/png');
			link.click();
		}).catch(function() {
			$(btn).prop('disabled', false);
			bootbox.alert('Gagal membuat gambar.');
		});
	}, // end - exportUnitImage

	sendUnitImageWA: function( btn ) {
		var unit = $(btn).data('unit');

		$(btn).prop('disabled', true);

		tb.renderUnitCanvas( btn ).then(function(canvas) {
			canvas.toBlob(function(blob) {
				var file = new File([blob], 'LHK Belum Input - ' + unit + '.png', { type: 'image/png' });

				if ( navigator.canShare && navigator.canShare({ files: [file] }) ) {
					navigator.share({
						files: [file],
						title: 'LHK Belum Input - ' + unit,
						text: 'LHK Belum Input - ' + unit
					}).catch(function(err) {
						// user membatalkan share -- bukan error, diamkan saja
						if ( err && err.name !== 'AbortError' ) {
							bootbox.alert('Gagal mengirim gambar : ' + err.message);
						}
					}).finally(function() {
						$(btn).prop('disabled', false);
					});
				} else {
					// Browser/perangkat tidak dukung share file langsung (umum terjadi di desktop).
					// WhatsApp Web/wa.me cuma bisa terima teks lewat URL, tidak bisa lampiran gambar
					// otomatis -- jadi gambar didownload dulu, WA Web dibuka dgn teks siap pakai,
					// user tinggal lampirkan manual gambar yg baru didownload itu.
					var link = document.createElement('a');
					link.download = 'LHK Belum Input - ' + unit + '.png';
					link.href = canvas.toDataURL('image/png');
					link.click();

					var text = encodeURIComponent('LHK Belum Input - ' + unit);
					window.open('https://api.whatsapp.com/send?text=' + text, '_blank');

					$(btn).prop('disabled', false);
					bootbox.alert('Gambar sudah terdownload & WhatsApp Web dibuka di tab baru. Pilih kontak/grup tujuan, lalu lampirkan gambar yang baru terdownload.');
				}
			}, 'image/png');
		}).catch(function() {
			$(btn).prop('disabled', false);
			bootbox.alert('Gagal membuat gambar.');
		});
	}, // end - sendUnitImageWA

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