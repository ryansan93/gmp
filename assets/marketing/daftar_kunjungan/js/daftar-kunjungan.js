const options = {
	enableHighAccuracy: true,
	timeout: 5000,
	maximumAge: 0,
};
var formData = null;

var dk = {
	startUp: function () {
		dk.settingUp();

		formData = new FormData();
	}, // end - startUp

	getLists: function() {
		var div_riwayat = $('div#riwayat');

		var no_pelanggan = $(div_riwayat).find('select.pelanggan').select2('val');

		var params = {'no_pelanggan': no_pelanggan};

		$.ajax({
            url: 'marketing/DaftarKunjungan/getLists',
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

		dk.loadForm(id, edit, href);
	}, // end - changeTab

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'marketing/DaftarKunjungan/loadForm',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                $('div#'+href).html( data.html );

				dk.settingUp();

                // if ( !empty(edit) ) {
                // 	lhk.get_noreg( $('div#'+href).find('#select_mitra') );
                // }

                formData = new FormData();

                hideLoading();
            }
        });
	}, // end - loadForm

	settingUp: function () {
		$('div#riwayat select.pelanggan').select2();
		$('div#action select.pelanggan').select2().on("select2:select", function (e) {
			var no_pelanggan = e.params.data.id;
			dk.getAlamat( no_pelanggan );
		});

		$('select.lokasi').select2({placeholder: '-- Pilih Lokasi --'}).on("select2:select", function (e) {
			var div_tab_pane = $(this).closest('div.tab-pane');
            var lokasi = $(this).select2('val');

            $(div_tab_pane).find('select.pelanggan option').attr('disabled', 'disabled');
            if ( !empty(lokasi) ) {
            	$(div_tab_pane).find('select.pelanggan').removeAttr('disabled');

            	var _lokasi = JSON.parse( lokasi );

            	for (var i = 0; i < _lokasi.length; i++) {
            		$(div_tab_pane).find('select.pelanggan option[data-kabkota='+_lokasi[i]+']').removeAttr('disabled');
            	}
            } else {
            	$(div_tab_pane).find('select.pelanggan').attr('disabled', 'disabled');
            }

            $(div_tab_pane).find('select.pelanggan').select2();
        });

        $('div#action select.kecamatan_plg').select2();
        $('div#action select.kecamatan_usaha').select2();
	}, // end - settingUp

	getAlamat: function (no_pelanggan) {
		var div = $('div#action');

		if ( !empty( no_pelanggan ) ) {
			var params = {
				'no_pelanggan': no_pelanggan
			};

			$.ajax({
				url : 'marketing/DaftarKunjungan/getAlamat',
				data : {'params' : params},
				dataType : 'JSON',
				type : 'POST',
				beforeSend : function () {
					showLoading();
				},
				success : function(data){
					hideLoading();

					if (data.status == 1) {
						$(div).find('textarea.alamat_plg').val( data.content.alamat_pelanggan );
						$(div).find('textarea.alamat_usaha').val( data.content.alamat_usaha );
					} else {
						bootbox.alert( data.message );
					}
				}
			});
		} else {
			$(div).find('textarea.alamat_plg').val('');
			$(div).find('textarea.alamat_usaha').val('');
		}
	}, // end - getAlamat

	plgBaru: function (elm) {
		var div_tab_pane = $(elm).closest('div.tab-pane');

		$(div_tab_pane).find('.not-plg-baru').addClass('hide');
		$(div_tab_pane).find('.plg-baru').removeClass('hide');

		$(div_tab_pane).find('textarea.alamat_plg').removeAttr('disabled');
		$(div_tab_pane).find('textarea.alamat_usaha').removeAttr('disabled');
		$(div_tab_pane).find('textarea.alamat_plg').val('');
		$(div_tab_pane).find('textarea.alamat_usaha').val('');
	}, // end - plgBaru

	notPlgBaru: function (elm) {
		var div_tab_pane = $(elm).closest('div.tab-pane');

		$(div_tab_pane).find('.not-plg-baru').removeClass('hide');
		$(div_tab_pane).find('.plg-baru').addClass('hide');

		$(div_tab_pane).find('.alamat_plg').attr('disabled', 'disabled');
		$(div_tab_pane).find('.alamat_usaha').attr('disabled', 'disabled');
		$(div_tab_pane).find('textarea.alamat_plg').val('');
		$(div_tab_pane).find('textarea.alamat_usaha').val('');
	}, // end - notPlgBaru

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
					bootbox.alert( error );
				},
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
				error_text = "User denied the request for Geolocation."
				break;
			case error.POSITION_UNAVAILABLE:
				error_text = "Location information is unavailable."
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

		if ( !$(div).find('button.not-plg-baru').hasClass('hide') ) {
			$.map( $(div).find('div.not-plg-baru [data-required=1]'), function (ipt) {
				if ( empty( $(ipt).val() ) ) {
					$(ipt).parent().addClass('has-error');
					err++;
				}
			});
		} else {
			$.map( $(div).find('div.plg-baru [data-required=1]'), function (ipt) {
				if ( empty( $(ipt).val() ) ) {
					$(ipt).parent().addClass('has-error');
					err++;
				}
			});
		}

		$.map( $(div).find('textarea.catatan'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
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
					if ( !$(div).find('button.not-plg-baru').hasClass('hide') ) {
						var data = {
							'status': 1,
							'kab_kota': $(div).find('div.not-plg-baru select.lokasi option:selected').text(),
							'no_pelanggan': $(div).find('div.not-plg-baru select.pelanggan').select2('val'),
							'catatan': $(div).find('textarea.catatan').val().trim(),
							'lat_long': $(div).find('td.lat_long').text(),
							'foto_kunjungan': $(div).find('td.foto_kunjungan').text()
						};
					} else {
						var data = {
							'status': 2,
							'nama_pelanggan': $(div).find('div.plg-baru input.nama_pelanggan').val(),
							'kecamatan_plg': $(div).find('div.plg-baru select.kecamatan_plg').select2('val'),
							'rt_plg': $(div).find('div.plg-baru input.rt_plg').val(),
							'rw_plg': $(div).find('div.plg-baru input.rw_plg').val(),
							'alamat_plg': $(div).find('div.plg-baru textarea.alamat_plg').val(),
							'kecamatan_usaha': $(div).find('div.plg-baru select.kecamatan_usaha').select2('val'),
							'rt_usaha': $(div).find('div.plg-baru input.rt_usaha').val(),
							'rw_usaha': $(div).find('div.plg-baru input.rw_usaha').val(),
							'alamat_usaha': $(div).find('div.plg-baru textarea.alamat_usaha').val(),
							'catatan': $(div).find('textarea.catatan').val().trim(),
							'lat_long': $(div).find('td.lat_long').text(),
							'foto_kunjungan': $(div).find('td.foto_kunjungan').text()
						};
					}

					formData.append("data", JSON.stringify(data));

					$.ajax({
			            url: 'marketing/DaftarKunjungan/save',
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

dk.startUp();