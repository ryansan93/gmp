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
		var jenis_label = $('.jenis').find('option:selected').text();
		var bulan = $('.bulan').select2().val();
		var jenis = $('.jenis').select2().val();
		var tahun_sql = dateSQL($('#tahun').data('DateTimePicker').date());

		bootbox.confirm('Apakah anda yakin ingin melakukan posting ulang data <b>'+jenis_label+'</b> bulan <b>'+nama_bulan+' '+tahun+'</b> ?', function (result) {
			if ( result ) {
				// Pembayaran Bakul & Pembayaran Vendor diproses PER TANGGAL (bukan langsung 1 bulan)
				// supaya progress-nya kelihatan & tidak 1 request raksasa -- jenis lain tetap 1 kali
				// panggilan spt semula.
				if ( jenis === 'bakul' || jenis === 'pembayaran' ) {
					pu.postingUlangPerHari(bulan, tahun_sql, jenis, jenis_label, nama_bulan+' '+tahun);
				} else {
					pu.postingUlangSatuKali(bulan, tahun_sql, jenis);
				}
			}
		});
	}, // end - postingUlang

	postingUlangSatuKali: function (bulan, tahun, jenis) {
		var params = {
			'bulan': bulan,
			'tahun': tahun,
			'jenis': jenis
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
	}, // end - postingUlangSatuKali

	// Loop 1 request per tanggal dalam bulan terpilih -- menampilkan tanggal yang sedang
	// diproses di dialog loading, supaya progress-nya kelihatan (bukan diam 1 bulan sekaligus).
	postingUlangPerHari: function (bulan, tahun, jenis, jenis_label, label_periode) {
		// 'tahun' adalah string tanggal (hasil dateSQL(), mis. "2026-08-01"), bukan angka tahun --
		// harus di-parse dulu, kalau tidak new Date(tahun, bulan, 0) jadi NaN & loop tanggalnya kosong
		// (bug ditemukan 2026-09-02: dialog langsung "selesai" tanpa memproses satu tanggal pun).
		var tahun_angka = parseInt(tahun.substring(0, 4), 10);
		var tgl_akhir_bulan = new Date(tahun_angka, bulan, 0).getDate();
		var daftar_tanggal = [];
		for (var i = 1; i <= tgl_akhir_bulan; i++) {
			daftar_tanggal.push(dateSQL(new Date(tahun_angka, bulan - 1, i)));
		}

		var idx = 0;
		var total = daftar_tanggal.length;

		showLoading('Posting ulang '+jenis_label+' '+label_periode+' . . . (0/'+total+')');

		var prosesSatuTanggal = function () {
			if ( idx >= total ) {
				hideLoading();
				bootbox.alert('Data berhasil di posting ulang, cek laporan yang berhubung.', function() {
					location.reload();
				});
				return;
			}

			var tgl = daftar_tanggal[idx];
			$('.txt-msg-loading').text('Posting ulang '+jenis_label+' . . . tanggal '+tgl+' ('+(idx+1)+'/'+total+')');

			$.ajax({
				url : 'accounting/PostingUlang/postingUlang',
				data : {
					'params' : {
						'bulan': bulan,
						'tahun': tahun,
						'jenis': jenis,
						'tanggal': tgl
					}
				},
				dataType : 'json',
				type : 'post',
				success : function(data){
					if ( data.status != 1 ) {
						hideLoading();
						bootbox.alert(data.message);
						return;
					}

					idx++;
					prosesSatuTanggal();
				},
				error : function(){
					hideLoading();
					bootbox.alert('Terjadi kesalahan saat memproses tanggal '+tgl+'.');
				}
			});
		}; // end - prosesSatuTanggal

		prosesSatuTanggal();
	}, // end - postingUlangPerHari
};

pu.startUp();