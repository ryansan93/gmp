const options = {
	enableHighAccuracy: true,
	timeout: 30000,
	maximumAge: 0,
};
var formData = null;

var pp = {
	startUp: function () {
		pp.settingUp();

		formData = new FormData();
	}, // end - startUp

	getLists: function() {
		var div_riwayat = $('div#riwayat');

		var no_plasma = $(div_riwayat).find('select.mitra').select2('val');

		var params = {'no_plasma': no_plasma};

		$.ajax({
            url: 'parameter/PeternakPosisi/getLists',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('table.tbl_riwayat').find('tbody').html( data.html );

                hideLoading();
            }
        });
	}, // end - getLists

	changeTab: function(elm) {
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		pp.loadForm(id, edit, href);
	}, // end - changeTab

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'parameter/PeternakPosisi/loadForm',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

				pp.settingUp();

                formData = new FormData();

                hideLoading();
            }
        });
	}, // end - loadForm

	settingUp: function () {
		$('div#riwayat select.mitra').select2();
		$('div#action select.mitra').select2().on("select2:select", function (e) {
			var no_plasma = e.params.data.id;
			pp.getKandang( no_plasma );
			pp.getAlamat( no_plasma );
		});

		$('div#action select.kandang').select2();
	}, // end - settingUp

	getKandang: function (no_plasma) {
		var div = $('div#action');

		var opt = '<option value="">Pilih Kandang</option>';

		if ( !empty( no_plasma ) ) {
			var params = {
				'no_plasma': no_plasma
			};

			$.ajax({
				url : 'parameter/PeternakPosisi/getKandang',
				data : {'params' : params},
				dataType : 'JSON',
				type : 'POST',
				beforeSend : function () {
					showLoading();
				},
				success : function(data){
					hideLoading();

					if (data.status == 1) {
						for (let i = 0; i < data.content.kandang.length; i++) {	
							opt += '<option value="'+data.content.kandang[i]+'">'+data.content.kandang[i]+'</option>';						
						}

						console.log( data.content.kandang );
						console.log( data.content.kandang.length );
						console.log( opt );

						$('select.kandang').html( opt );
						$('select.kandang').select2();
					} else {
						bootbox.alert( data.message );
					}
				}
			});
		} else {
			$('select.kandang').html( opt );
			$('select.kandang').select2();
		}
	}, // end - getKandang

	getAlamat: function (no_plasma) {
		var div = $('div#action');

		if ( !empty( no_plasma ) ) {
			var params = {
				'no_plasma': no_plasma
			};

			$.ajax({
				url : 'parameter/PeternakPosisi/getAlamat',
				data : {'params' : params},
				dataType : 'JSON',
				type : 'POST',
				beforeSend : function () {
					showLoading();
				},
				success : function(data){
					hideLoading();

					if (data.status == 1) {
						$(div).find('textarea.alamat_plasma').val( data.content.alamat_plasma );
					} else {
						bootbox.alert( data.message );
					}
				}
			});
		} else {
			$(div).find('textarea.alamat_plasma').val('');
			// $(div).find('textarea.alamat_usaha').val('');
		}
	}, // end - getAlamat

	getLocation: function (elm) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				function(position) {
					var isMobile = $(elm).attr('data-ismobile');
					
					var latitude = position.coords.latitude;
					var longitude = position.coords.longitude;

					var lat_long = {
						'latitude': latitude,
						'longitude': longitude
					};

					var tr = $(elm).closest('tr');
					var td_data = $(tr).find('td.data');

					if ( isMobile ) {
						var a = '<a class="cursor-p" href="geo:0, 0?z=15&q='+latitude+','+longitude+'" target="_blank">'+latitude+', '+longitude+'</a>';
					} else {
						var a = '<a class="cursor-p" href="https://www.google.com/maps/?q='+latitude+','+longitude+'" target="_blank">'+latitude+', '+longitude+'</a>';
					}

					$(td_data).html( a );
				},
				function(error) {
					var err_text = pp.showError(error);
					bootbox.alert( err_text );

					// console.log( text );
				}
				,
				options
			);
		} else { 
			bootbox.alert("Geolocation is not supported by this browser.");
		}
	}, // end - getLocation

	showPosition: function (position) {
		var latitude = position.coords.latitude;
		var longitude = position.coords.longitude;

		var lat_long = {
			'latitude': latitude,
			'longitude': longitude
		};

		return lat_long;
	}, // end - showPosition

	showError: function (error) {
		var error_text = null;

		switch(error.code) {
			case error.PERMISSION_DENIED:
				// error_text = "User denied the request for Geolocation."
				error_text = "User menolak akses lokasi pada device."
				break;
			case error.POSITION_UNAVAILABLE:
				// error_text = "Location information is unavailable."
				error_text = "Informasi lokasi tidak tersedia."
				break;
			case error.TIMEOUT:
				error_text = "The request to get user location timed out."
				break;
			case error.UNKNOWN_ERROR:
				error_text = "An unknown error occurred."
				break;
		}

		return error_text;
	}, // end - showError

	compress_img: function(elm) {
		var tr = $(elm).closest('tr');

		showLoading();

		var files = $(elm)[0].files;

		ci.compress(files, 480, function(data) {
			formData.append("foto_kunjungan", data[0]);

		    var _namafile = data[0].name;
		    _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);
		    var temp_url = URL.createObjectURL(data[0]);

		    $(tr).find('td.data').html('<a href='+temp_url+' target="_blank">' + _namafile + '</a>');

			hideLoading();
		});
	}, // end - compress_img

	save: function () {
		var div = $('div#action');

		var err = 0;

		$(div).find('[data-required=1]').parent().removeClass('has-error');
		$(div).find('td.data .btn').removeClass('has-error');

		$.map( $(div).find('[data-required=1]'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			}
		});

		$.map( $(div).find('td.data'), function (td) {
			var tr = $(td).closest('tr');
			if ( $(td).text().trim() == 'Get Location' || $(td).text().trim() == 'Get Photo' ) {
				$(tr).find('.btn').addClass('btn-has-error');

				err++;
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data pada kolom yang bertanda merah.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data kunjungan ?', function (result) {
				if ( result ) {
					var data = null;
					var data = {
						'status': 1,
						'no_plasma': $(div).find('select.mitra').select2('val'),
						'kandang': $(div).find('select.kandang').select2('val'),
						'lat_long': $(div).find('td.lat_long').text(),
						'foto_kunjungan': $(div).find('td.foto_kunjungan').text()
					};

					formData.append("data", JSON.stringify(data));

					$.ajax({
			            url: 'parameter/PeternakPosisi/save',
			            dataType: 'json',
			            type: 'post',
			            processData: false,
			            contentType: false,
			            data: formData,
			            beforeSend: function() {
			                showLoading();
			            },
			            success: function(data) {
			                hideLoading();
			                if ( data.status == 1 ) {
			                    bootbox.alert(data.message, function(){
			                    	location.reload();
			                    	// lhk.load_form(data.content.id, null, 'transaksi');
			                    });
			                } else {
			                    bootbox.alert(data.message);
			                }
			            }
			        });
				}
			});
		}
	}, // end - save
};

pp.startUp();