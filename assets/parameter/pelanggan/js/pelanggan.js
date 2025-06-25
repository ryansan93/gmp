var idxUploadFile = 0;

var plg = {
	start_up : function	() {
		$('button#search-pagination').on('click', function() {
			plg.set_pagination();
		});
		
		plg.setBindSHA1();
		if ( $('#history').attr('data-ismobile') == 1 ) {
			$('select.kecamatan').select2();
			$('select.pelanggan').select2();
		} else {
			plg.set_pagination();
		}

		$('[data-tipe=phone]').mask("9999 9999 9??999");
		$('[data-tipe=rt]').mask("999");
		$('[data-tipe=rw]').mask("999");
		$('[name=ktp_plg]').mask("9999999999999999");
		$('[name=npwp_plg]').mask("999.999.999.9-999.999");

		$('#tglHbsBerlaku').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		var tgl = $('#tglHbsBerlaku').find('input').attr('data-tgl');
		if ( !empty(tgl) ) {
			$('#tglHbsBerlaku').data('DateTimePicker').date(new Date(tgl));
		}

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
	}, // end - start_up

	set_pagination : function(){
		var search_by = $('#search-by-pagination').val();
		var search_val = $('#search-val-pagination').val();

		$.ajax({
			url : 'parameter/Pelanggan/amount_of_data',
			data : {'search_by': search_by, 'search_val': search_val},
			dataType : 'JSON',
			type : 'POST',
			beforeSend : function(){},
			success : function(data){
				pagination.set_pagination( data.content.jml_row, data.content.jml_page, data.content.list, plg.getLists );
			}
		});
	}, // end - set_pagination

	getLists : function(elm){
		var list_nomor = $(elm).data('list_id_page');
        $.ajax({
            url : 'parameter/Pelanggan/list_plg',
            data : {'params' : list_nomor},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                $('table.tbl_plg tbody').html(data);

                hideLoading();
            }
        });
    }, // end - getLists

	getListMobile : function(elm){
		var params = {
			'kecamatan': $('select.kecamatan').select2().val(),
			'pelanggan': $('select.pelanggan').select2().val()
		};

		$.ajax({
			url : 'parameter/Pelanggan/getListMobile',
			data : {'params' : params},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){
				showLoading();
			},
			success : function( html ){
				$('table.tbl_riwayat tbody').html( html );
				
				hideLoading();
			}
		});
	}, // end - getListMobile

	detailMobile: function (elm) {
		var vhref = $(elm).data('href');
		// change tab-menu
		$('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

		var dcontent = $('div#action');
		var params = {
			'nomor': $(elm).attr('data-id')
		};

		$.ajax({
			url : 'parameter/Pelanggan/detailMobile',
			data : {'params' : params},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){
				App.showLoaderInContent( $(dcontent) );
			},
			success : function( html ){
				App.hideLoaderInContent( $(dcontent), html );
			}
		});
	}, // end - detailMobile

	collapseRowDetailMobile: function(elm) {
		// NOTE: setup untuk expand collapse row table detail-keranjang
		var span = $(elm).find('span');
		var tr = $(elm).closest('tr.head');

		if ( $(span).hasClass('glyphicon-chevron-right') ) {
			$(span).removeClass('glyphicon-chevron-right');
			$(span).addClass('glyphicon-chevron-down');

			$(tr).next('tr.detail').removeClass('hide');
		} else {
			$(span).removeClass('glyphicon-chevron-down');
			$(span).addClass('glyphicon-chevron-right');

			$(tr).next('tr.detail').addClass('hide');
		}
	}, // end - collapseRow

	setBindSHA1 : function(){
		$('input:file').off('change.sha1');
		$('input:file').on('change.sha1',function(){
			var elm = $(this);
			var file = elm.get(0).files[0];
			elm.attr('data-sha1', '');
			sha1_file(file).then(function (sha1) {
				elm.attr('data-sha1', sha1);
			});
		});
	}, // end - setBindSHA1

	changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

        if ( vhref == 'action' ) {
            var v_id = $(elm).attr('data-id');
            var tgl_mulai = $(elm).attr('data-mulai');
            var resubmit = $(elm).attr('data-resubmit');

            plg.load_form(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/Pelanggan/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
                $(div_action).html(html);

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('[data-tipe=phone]').mask("9999 9999 9??999");
				$('[data-tipe=rt]').mask("999");
				$('[data-tipe=rw]').mask("999");
				$('[name=ktp_plg]').mask("9999999999999999");
				var npwp = $('[name=npwp_plg]').val();
				if ( !empty(v_id) && empty(resubmit) ) {
					if ( !empty(npwp.trim()) ) {
						$('[name=npwp_plg]').mask("999.999.999.9-999.999");
					} else {
						$('[name=npwp_plg]').val("-");
					}
				} else {
					$('[name=npwp_plg]').mask("999.999.999.9-999.999");
				}

				$('#tglHbsBerlaku').datetimepicker({
					locale: 'id',
					format: 'DD MMM Y'
				});
		
				var tgl = $('#tglHbsBerlaku').find('input').attr('data-tgl');
				if ( !empty(tgl) ) {
					$('#tglHbsBerlaku').data('DateTimePicker').date(new Date(tgl));
				}

                plg.setBindSHA1();

                if ( !empty(resubmit) ) {
                	plg.load_kab_plg();
                };

                hideLoading();
            },
        });
    }, // end - load_form

    load_kab_plg : function() {
    	var select_prov_plg = $('select[name=propinsi_plg]');
    	var tipe_lok_plg = $('select[name=tipe_lokasi]');
    	var tipe_lok_usaha = $('select[name=tipe_lokasi_usaha]');

    	plg.getListLokasi_Update(tipe_lok_plg, '#alamat_pelanggan', 'kab', '');
    	plg.getListLokasi_UpdateUsaha(tipe_lok_usaha, '#alamat_usaha_pelanggan', 'kab', '_usaha');
    }, // end - load_tipe_lokasi

	list_load : function(elm) {
		$.ajax({
			url : 'parameter/Pelanggan/list_pelanggan',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				$('#table-list-PLG tbody').html(data);
			}
		});
	}, // end - list_load_do

	getListLokasi : function(elm, close = '', req = '', tipe = ''){
		var _form = $(elm).closest(close);
		var induk = '';
		var jenis = '';

		// reset pilihan kabupaten
		var eSelect = null;
		var optDefault = '';
		if (req == 'kab') {
			eSelect = _form.find('select[name=kabupaten'+tipe+'_plg]'); // element kabupaten
			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_plg]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_plg] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_plg]');
			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_plg]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Pelanggan/getLokasiJson',
				data : {'jenis' : jenis, 'induk' : induk},
				dataType : 'JSON',
				type : 'GET',
				success : function(data){
					for (loc of data.content) {
						eSelect.append('<option value="'+loc.id+'">'+ loc.nama +'</option>')
					}
				}
			});
		}

	}, // end - onChangeProvinsi

	getListLokasi_Update : function(elm, close = '', req = '', tipe = ''){
		var _form = $(elm).closest(close);
		var induk = '';
		var jenis = '';

		// reset pilihan kabupaten
		var eSelect = null;
		var optDefault = '';

		var id = null;
		if (req == 'kab') {
			eSelect = _form.find('select[name=kabupaten'+tipe+'_plg]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_plg]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_plg] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_plg]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_plg]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Pelanggan/getLokasiJson',
				data : {'jenis' : jenis, 'induk' : induk},
				dataType : 'JSON',
				type : 'GET',
				success : function(data){
					for (loc of data.content) {
						var selected = null;
						if ( id == loc.id ) {
							selected = 'selected';
						};

						eSelect.append('<option value="'+loc.id+'" '+selected+' >'+ loc.nama +'</option>')
					}

					if ( jenis != 'KC' ) {
							var select_kab_plg = $('select[name=kabupaten_plg]');
	    					plg.getListLokasi_Update(select_kab_plg, '#alamat_pelanggan', 'kec', tipe);
					};
				}
			});
		}
	}, // end - onChangeProvinsi

	getListLokasi_UpdateUsaha : function(elm, close = '', req = '', tipe = ''){
		var _form = $(elm).closest(close);
		var induk = '';
		var jenis = '';

		// reset pilihan kabupaten
		var eSelect = null;
		var optDefault = '';

		var id = null;
		if (req == 'kab') {
			eSelect = _form.find('select[name=kabupaten'+tipe+'_plg]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_plg]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_plg] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_plg]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_plg]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Pelanggan/getLokasiJson',
				data : {'jenis' : jenis, 'induk' : induk},
				dataType : 'JSON',
				type : 'GET',
				success : function(data){
					for (loc of data.content) {
						var selected = null;
						if ( id == loc.id.trim() ) {
							selected = 'selected';
						};

						eSelect.append('<option value="'+loc.id+'" '+selected+' >'+ loc.nama +'</option>')
					}

					if ( jenis != 'KC' ) {
							var select_kab_usaha_plg = $('select[name=kabupaten_usaha_plg]');
							plg.getListLokasi_UpdateUsaha(select_kab_usaha_plg, '#alamat_usaha_pelanggan', 'kec', '_usaha')
					};
				}
			});
		}
	}, // end - onChangeProvinsi

	save : function () {
		var error = 0;
		var lbl_errors = [];
		$('[required]').parent().removeClass('has-error');
		$.map($('[required]'), function(elm){
			if( empty( $(elm).val() ) ){
				error++;
				lbl_errors.push( '* ' + $(elm).attr('placeholder') );
				$('[required]').parent().addClass('has-error');
			}else{
				$(elm).parent().removeClass('has-error');
			}
		});

		if (error > 0) {
			bootbox.alert('Data belum lengkap : <br> ' + lbl_errors.join('<br>') );
		} else {
			bootbox.confirm('Apakah anda yakin data mitra akan disimpan?', function(result){
    			if (result) {
    				var div_pelanggan = $('div[name=data-pelanggan]');
    				var rek_pelanggan = $('div#rekening_pelanggan');

					var banks = $.map( $(rek_pelanggan).find('tr.detail_rekening'), function(tr) {
						var data = {
							'nomer_rekening' : $(tr).find('input[name=rekening_plg]').val(),
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : $(tr).find('input[name=bank_rekening]').val(),
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data pelanggan
    				var jenis_pelanggan = $(div_pelanggan).find('select[name=jenis_plg]').val();
    				var nama_pelanggan = $(div_pelanggan).find('input[name=nama_plg]').val();
    				var contact_person = $(div_pelanggan).find('input[name=contact_plg]').val();
    				var platform = numeral.unformat( $(div_pelanggan).find('input[name=platform]').val() );

    				var telepons = $.map( $(div_pelanggan).find('input[name=telp_plg]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_pelanggan).find('input[name=ktp_plg]').mask();
					var alamat_pelanggan = {
						'kecamatan' : $(div_pelanggan).find('select[name=kecamatan_plg]').val(),
						'kelurahan' : $(div_pelanggan).find('input[name=kelurahan_plg]').val(),
						'alamat' : $(div_pelanggan).find('textarea[name=alamat_plg]').val().trim(),
						'rt' :  $(div_pelanggan).find('input[name=rt_plg]').val().trim(),
						'rw' :  $(div_pelanggan).find('input[name=rw_plg]').val().trim(),
					};
					var npwp = $(div_pelanggan).find('input[name=npwp_plg]').mask();
					var skb = $(div_pelanggan).find('input[name=skb_plg]').val().trim();
					var tgl_habis_skb = !empty($(div_pelanggan).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_pelanggan).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_pelanggan).find('select[name=kecamatan_usaha_plg]').val(),
						'kelurahan' : $(div_pelanggan).find('input[name=kelurahan_usaha_plg]').val(),
						'alamat' : $(div_pelanggan).find('textarea[name=alamat_usaha_plg]').val().trim(),
						'rt' :  $(div_pelanggan).find('input[name=rt_usaha_plg]').val().trim(),
						'rw' :  $(div_pelanggan).find('input[name=rw_usaha_plg]').val().trim(),
					};

					var data_pelanggan = {
						'jenis_pelanggan' : jenis_pelanggan,
						'ktp' : ktp,
						'nama' : nama_pelanggan,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_pelanggan' : alamat_pelanggan,
						'alamat_usaha' : alamat_usaha,
						'banks' : banks,
						'platform' : platform
					};

					var params = data_pelanggan;

					$.ajax({
						url :'parameter/Pelanggan/save',
						type : 'POST',
						dataType : 'JSON',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								plg.uploadFile( data.content.id );
							}else{
								hideLoading();
								bootbox.alert(data.message);
							}
						}
					});
	    		}
    		});
		}
	}, // end - save

	edit : function () {
		var error = 0;
		var lbl_errors = [];
		$('[required]').parent().addClass('has-error');
		$.map($('[required]'), function(elm){
			if( empty( $(elm).val() ) ){
				error++;
				lbl_errors.push( '* ' + $(elm).attr('placeholder') );
			}else{
				$(elm).parent().removeClass('has-error');
			}
		});

		if (error > 0) {
			bootbox.alert('Data belum lengkap : <br> ' + lbl_errors.join('<br>') );
		} else {
			bootbox.confirm('Apakah anda yakin data mitra akan disimpan?', function(result){
    			if (result) {
    				var div_pelanggan = $('div[name=data-pelanggan]');
    				var rek_pelanggan = $('div#rekening_pelanggan');

    				var id = $('input[type=hidden]').data('id');
    				var nomor = $('input[type=hidden]').data('nomor');
    				var status = $('input[type=hidden]').data('status');
    				var mstatus = $('input[type=hidden]').data('mstatus');
    				var version = $('input[type=hidden]').data('version');

					var banks = $.map( $(rek_pelanggan).find('tr.detail_rekening'), function(tr) {
						var data = {
							'id_old' : $(tr).find('input[name=rekening_plg]').data('id'),
							'nomer_rekening' : $(tr).find('input[name=rekening_plg]').val(),
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : $(tr).find('input[name=bank_rekening]').val(),
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data pelanggan
    				var jenis_pelanggan = $(div_pelanggan).find('select[name=jenis_plg]').val();
    				var nama_pelanggan = $(div_pelanggan).find('input[name=nama_plg]').val();
    				var contact_person = $(div_pelanggan).find('input[name=contact_plg]').val();
    				var platform = numeral.unformat( $(div_pelanggan).find('input[name=platform]').val() );

    				var telepons = $.map( $(div_pelanggan).find('input[name=telp_plg]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_pelanggan).find('input[name=ktp_plg]').mask();
					var alamat_pelanggan = {
						'kecamatan' : $(div_pelanggan).find('select[name=kecamatan_plg]').val(),
						'kelurahan' : $(div_pelanggan).find('input[name=kelurahan_plg]').val(),
						'alamat' : $(div_pelanggan).find('textarea[name=alamat_plg]').val().trim(),
						'rt' :  $(div_pelanggan).find('input[name=rt_plg]').val().trim(),
						'rw' :  $(div_pelanggan).find('input[name=rw_plg]').val().trim(),
					};
					var npwp = $(div_pelanggan).find('input[name=npwp_plg]').mask();
					var skb = $(div_pelanggan).find('input[name=skb_plg]').val().trim();
					var tgl_habis_skb = !empty($(div_pelanggan).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_pelanggan).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_pelanggan).find('select[name=kecamatan_usaha_plg]').val(),
						'kelurahan' : $(div_pelanggan).find('input[name=kelurahan_usaha_plg]').val(),
						'alamat' : $(div_pelanggan).find('textarea[name=alamat_usaha_plg]').val().trim(),
						'rt' :  $(div_pelanggan).find('input[name=rt_usaha_plg]').val().trim(),
						'rw' :  $(div_pelanggan).find('input[name=rw_usaha_plg]').val().trim(),
					};

					var data_pelanggan = {
						'id' : id,
						'nomor' : nomor,
						'status' : status,
						'mstatus' : mstatus,
						'version' : version,
						'jenis_pelanggan' : jenis_pelanggan,
						'ktp' : ktp,
						'nama' : nama_pelanggan,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_pelanggan' : alamat_pelanggan,
						'alamat_usaha' : alamat_usaha,
						'banks' : banks,
						'platform' : platform
					};

					var params = data_pelanggan;

					$.ajax({
						url :'parameter/Pelanggan/edit',
						type : 'POST',
						dataType : 'JSON',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								plg.uploadFile( data.content.id );
							}else{
								hideLoading();
								bootbox.alert(data.message);
							}
						}
					});
	    		}
    		});
		}
	}, // end - edit

	uploadFile: function(id) {
		var div_action = $('div#action');

		var idx_lampirans = 0;
		var lampirans = [];

		var formData = new FormData();
		$.map( $(div_action).find('input[name=lampiran_ktp], input[name=lampiran_npwp], input[name=lampiran_ddp]'), function(ipt){
			var key = $(ipt).attr('name');
			
			var name = null;

			if (!empty( $(ipt).val() )) {
				var __file = $(ipt).get(0).files[0];
				name = __file.name;

				formData.append('files[]', __file);
			}

			lampirans[idx_lampirans] = {
				'id' : $(ipt).closest('label').attr('data-idnama'),
				'name' : name,
				'sha1' : $(ipt).attr('data-sha1'),
				'key' : key,
				'old' : $(ipt).data('old')
			};

			idx_lampirans++;
		});

		$.map( $(div_action).find('tr.detail_rekening'), function(tr) {
			var nama_bank = $(tr).find('input[name=bank_rekening]').val();
			var nomer_rekening = $(tr).find('input[name=rekening_plg]').val();

			var key = 'BANK_'+nama_bank+'_'+nomer_rekening;

			var ipt = $(tr).find('input:file');
			var name = null;

			if (!empty( $(ipt).val() )) {
				var __file = $(ipt).get(0).files[0];
				name = __file.name;

				formData.append('files[]', __file);
			}

			lampirans[idx_lampirans] = {
				'id' : $(ipt).closest('label').attr('data-idnama'),
				'name' : name,
				'sha1' : $(ipt).attr('data-sha1'),
				'key' : key,
				'old' : $(ipt).data('old')
			};

			idx_lampirans++;
		});

		var data = {
			'id': id,
			'lampirans': lampirans,
			'idx_upload': idxUploadFile
		};
		formData.append('data', JSON.stringify(data));

		$.ajax({
			url :'parameter/Pelanggan/uploadFile',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				if(data.status == 1){
					if ( idxUploadFile < lampirans.length ) {
						idxUploadFile++;

						plg.uploadFile(id);
					} else {
						hideLoading();
						bootbox.alert(data.message,function() {
							plg.getLists();
							plg.load_form(data.content.id);

							idxUploadFile = 0;
						});
					}
				}else{
					hideLoading();
					bootbox.alert(data.message);
				}
			},
			contentType : false,
			processData : false,
		});
	}, // end - uploadFile

	non_aktif : function (tipe = null) {
		var div_tab_pane = $('div.tab-pane');
    	var formData = new FormData();

    	var nomor = $('div.bootbox-body').find('input[name=nomor_pelanggan]').attr('data-nomor');
    	var keterangan = $('div.bootbox-body').find('input[name=nonaktif_keterangan]').val();

    	var lampiran = $.map( $('div#form_status').find('input:file'), function(ipt){
    		if (!empty( $(ipt).val() )) {
    			var __file = $(ipt).get(0).files[0];
    			formData.append('files[]', __file);
    			return {
    				'id' : $(ipt).closest('label').attr('data-idnama'),
    				'name' : __file.name,
    				'sha1' : $(ipt).attr('data-sha1'),
    			};
    		}
    	});

    	var data = {
    		'nomor' : nomor,
    		'ket' : keterangan,
    		'lampiran' : lampiran,
    		'tipe' : tipe
    	};

    	formData.append('data', JSON.stringify(data));

    	$.ajax({
			url :'parameter/Pelanggan/nonAktif',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if(data.status){
					bootbox.alert(data.message,function() {
						plg.getLists();
					});
				} else {
					bootbox.alert(data.message);
				}
			},
			contentType : false,
			processData : false,
		});
	}, // end - non_aktif

	ack : function () {
		var ids = $('input[type=hidden]').data('id');
		bootbox.confirm('Data mitra akan di-ack', function(result){
			if (result) {
				$.ajax({
					url : 'parameter/Pelanggan/ack',
					data : {'params' : ids},
					dataType : 'JSON',
					type : 'POST',
					beforeSend : function () {
						showLoading();
					},
					success : function(data){
						hideLoading();
						if(data.status){
							bootbox.alert(data.message,function(){
								plg.getLists();
								plg.load_form(data.content.id);
							});
						}else{
							bootbox.alert(data.message);
						}
					},
				});
			}
		});
	}, // end - ack

	addRowTable : function (elm, action) {
		var row = $(elm).closest("tr");
		var row_clone = row.clone();
		row_clone.find('select, input').val('');
		var tbody = $(elm).closest("tbody");
		tbody.append(row_clone);
		$('[data-tipe=phone]').mask("9999 9999 9??999");
		plg.setBindSHA1()
	}, // end - addRowTelepon

	removeRowTable : function (elm) {
		var row = $(elm).closest("tr");
		row.find('select, input').val('');
		if ( row.prev('tr').length > 0 || row.next('tr').length > 0 ) {
			row.remove();
		}
	}, // end - removeRowTable

    load_form_status : function(elm) {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_pelanggan]').data('nomor');
    	var tipe = $(elm).data('tipe');

    	var title = null;
    	if ( tipe == 'aktif' ) {
    		title = 'Aktif Pelanggan'
    	} else {
    		title = 'Non Aktif Pelanggan';
    	};

    	$.ajax({
            url: 'parameter/Pelanggan/loadFormStatus',
            data: {
            	params: nomor
            },
            type: 'GET',
            dataType: 'HTML',
            success: function(html) {
            	var modal = bootbox.dialog({
            		message: html,
            		title: title,
            		buttons: [
            		{
            			label: "Simpan",
            			className: "btn btn-primary pull-right",
            			callback: function() {
            				plg.non_aktif(tipe);
            			},
            		},
            		{
            			label: "Close",
            			className: "btn btn-default pull-right",
            			callback: function() {
            				modal.modal("hide");	
            			}
            		}],
            		show: false,
            		onEscape: function() {
            			modal.modal("hide");
            		}
			    });
	    
			    modal.modal("show");
			    plg.setBindSHA1();
        	}        	
        });
    }, // end - load_form_status

    load_form_saldo : function () {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_pelanggan]').data('nomor');

    	$.ajax({
            url: 'parameter/Pelanggan/loadFormSldAwal',
            data: {
            	params: nomor
            },
            type: 'GET',
            dataType: 'HTML',
            success: function(html) {
            	var modal = bootbox.dialog({
            		message: html,
            		title: "Saldo Awal Pelanggan",
            		buttons: [
            		{
            			label: "Simpan",
            			className: "btn btn-primary pull-right",
            			callback: function() {
            				plg.sld_awal();
            			},
            		},
            		{
            			label: "Close",
            			className: "btn btn-default pull-right",
            			callback: function() {
            				modal.modal("hide");	
            			}
            		}],
            		show: false,
            		onEscape: function() {
            			modal.modal("hide");
            		}
			    });
	    
			    modal.modal("show");
			    plg.setBindSHA1();
        	}        	
        });
    }, // end - load_form_saldo

    cari_pelanggan : function () {
    	var index = $("#filter_search").val();

    	var input, filter, table, tr, td, i, txtValue;
    	input = document.getElementById("input_cari_pelanggan");
    	filter = input.value.toUpperCase();
    	table = document.getElementById("table_pelanggan");
    	tr = table.getElementsByTagName("tr");
    	for (i = 0; i < tr.length; i++) {
    		td = tr[i].getElementsByTagName("td")[index];
    		if (td) {
    			txtValue = td.textContent || td.innerText;
    			if (txtValue.toUpperCase().indexOf(filter) > -1) {
    				tr[i].style.display = "";
    			} else {
    				tr[i].style.display = "none";
    			}
    		}
    	}
    }, // end - cari_pelanggan

    form_export_excel : function () {
		$.get('parameter/Pelanggan/form_export_excel',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});
            });
        },'html');
	}, // end - form_export_excel

	verifikasi_export_excel : function (elm) {
		var modal_body = $(elm).closest('div.modal-body');

		var err = 0;
		$.map( $(modal_body).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap isi Username dan Password terlebih dahulu.');
		} else {
			var params = {
				'username': $(modal_body).find('input[name=username]').val(),
				'password': $(modal_body).find('input[name=password]').val()
			};

			$.ajax({
				url : 'parameter/Pelanggan/verifikasi_export_excel',
				data : {'params' : params},
				dataType : 'JSON',
				type : 'POST',
				beforeSend : function () {
					showLoading();
				},
				success : function(data){
					if (data.status) {
						bootbox.hideAll();
						hideLoading();

						window.open('parameter/Pelanggan/export_excel', '_blank');
					} else {
						hideLoading();
						bootbox.alert( data.message );
					}
				}
			});
		}
	}, // end - verifikasi_export_excel
};

plg.start_up();