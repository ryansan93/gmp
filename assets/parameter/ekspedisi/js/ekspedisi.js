var idxUploadFile = 0;

var ekspedisi = {
	start_up : function	() {
		ekspedisi.setBindSHA1();
		ekspedisi.getLists();

		$('[data-tipe=phone]').mask("9999 9999 9??999");
		$('[data-tipe=rt]').mask("999");
		$('[data-tipe=rw]').mask("999");
		$('[name=ktp_ekspedisi]').mask("9999999999999999");
		$('[name=npwp_ekspedisi]').mask("999.999.999.9-999.999");

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

	getLists : function(keyword = null){
        $.ajax({
            url : 'parameter/Ekspedisi/list_ekspedisi',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){
            	App.showLoaderInContent($('table.tbl_ekspedisi tbody'));
            },
            success : function(data){
            	App.hideLoaderInContent($('table.tbl_ekspedisi tbody'), data);
                // $('table.tbl_ekspedisi tbody').html(data);
            }
        });
    }, // end - getLists

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

            ekspedisi.loadForm(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    loadForm: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/Ekspedisi/loadForm',
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
				$('[name=ktp_ekspedisi]').mask("9999999999999999");
				$('[name=npwp_ekspedisi]').mask("999.999.999.9-999.999");
				$('[name=npwp_ekspedisi]').val( $('[name=npwp_ekspedisi]').attr('data-val') ).trigger('input');

				$('#tglHbsBerlaku').datetimepicker({
					locale: 'id',
					format: 'DD MMM Y'
				});
		
				var tgl = $('#tglHbsBerlaku').find('input').attr('data-tgl');
				if ( !empty(tgl) ) {
					$('#tglHbsBerlaku').data('DateTimePicker').date(new Date(tgl));
				}

                ekspedisi.setBindSHA1();

                if ( !empty(resubmit) ) {
                	ekspedisi.load_kab_ekspedisi();
                };

                hideLoading();
            },
        });
    }, // end - loadForm

    load_kab_ekspedisi : function() {
    	var select_prov_ekspedisi = $('select[name=propinsi_ekspedisi]');
    	var tipe_lok_ekspedisi = $('select[name=tipe_lokasi]');
    	var tipe_lok_usaha = $('select[name=tipe_lokasi_usaha]');

    	ekspedisi.getListLokasi_Update(tipe_lok_ekspedisi, '#alamat_ekspedisi', 'kab', '');
    	ekspedisi.getListLokasi_UpdateUsaha(tipe_lok_usaha, '#alamat_usaha_ekspedisi', 'kab', '_usaha');
    }, // end - load_tipe_lokasi

	list_load : function(elm) {
		$.ajax({
			url : 'parameter/Ekspedisi/list_ekspedisi',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				$('#table-list-ekspedisi tbody').html(data);
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]'); // element kabupaten
			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_ekspedisi]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_ekspedisi] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_ekspedisi]');
			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Ekspedisi/getLokasiJson',
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_ekspedisi]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_ekspedisi] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_ekspedisi]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Ekspedisi/getLokasiJson',
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
							var select_kab_ekspedisi = $('select[name=kabupaten_ekspedisi]');
	    					ekspedisi.getListLokasi_Update(select_kab_ekspedisi, '#alamat_ekspedisi', 'kec', tipe);
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_ekspedisi]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_ekspedisi] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_ekspedisi]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_ekspedisi]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Ekspedisi/getLokasiJson',
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
							var select_kab_usaha_ekspedisi = $('select[name=kabupaten_usaha_ekspedisi]');
							ekspedisi.getListLokasi_UpdateUsaha(select_kab_usaha_ekspedisi, '#alamat_usaha_ekspedisi', 'kec', '_usaha')
					};
				}
			});
		}
	}, // end - onChangeProvinsi

	save : function () {
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
			bootbox.confirm('Apakah anda yakin igin menyimpan data ekspedisi ?', function(result){
    			if (result) {
    				var div_ekspedisi = $('div[name=data-ekspedisi]');
    				var rek_ekspedisi = $('div#rekening_ekspedisi');

					var banks = $.map( $(rek_ekspedisi).find('tr.detail_rekening'), function(tr) {
						var nama_bank = $(tr).find('input[name=bank_rekening]').val();
						var nomer_rekening = $(tr).find('input[name=rekening_ekspedisi]').val();

						var data = {
							'nomer_rekening' : nomer_rekening,
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : nama_bank,
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data ekspedisi
    				var jenis_ekspedisi = $(div_ekspedisi).find('select[name=jenis_ekspedisi]').val();
    				var nama_ekspedisi = $(div_ekspedisi).find('input[name=nama_ekspedisi]').val();
    				var contact_person = $(div_ekspedisi).find('input[name=contact_ekspedisi]').val();
    				var platform = 0;

    				var telepons = $.map( $(div_ekspedisi).find('input[name=telp_ekspedisi]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_ekspedisi).find('input[name=ktp_ekspedisi]').mask();
					var alamat_ekspedisi = {
						'kecamatan' : $(div_ekspedisi).find('select[name=kecamatan_ekspedisi]').val(),
						'kelurahan' : $(div_ekspedisi).find('input[name=kelurahan_ekspedisi]').val(),
						'alamat' : $(div_ekspedisi).find('textarea[name=alamat_ekspedisi]').val().trim(),
						'rt' :  $(div_ekspedisi).find('input[name=rt_ekspedisi]').val().trim(),
						'rw' :  $(div_ekspedisi).find('input[name=rw_ekspedisi]').val().trim(),
					};
					var npwp = $(div_ekspedisi).find('input[name=npwp_ekspedisi]').mask();
					var skb = $(div_ekspedisi).find('input[name=skb_ekspedisi]').val().trim();
					var tgl_habis_skb = !empty($(div_ekspedisi).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_ekspedisi).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_ekspedisi).find('select[name=kecamatan_usaha_ekspedisi]').val(),
						'kelurahan' : $(div_ekspedisi).find('input[name=kelurahan_usaha_ekspedisi]').val(),
						'alamat' : $(div_ekspedisi).find('textarea[name=alamat_usaha_ekspedisi]').val().trim(),
						'rt' :  $(div_ekspedisi).find('input[name=rt_usaha_ekspedisi]').val().trim(),
						'rw' :  $(div_ekspedisi).find('input[name=rw_usaha_ekspedisi]').val().trim(),
					};
					var potongan_pph = $(div_ekspedisi).find('select.potongan_pph').val();

					var data_ekspedisi = {
						'jenis_ekspedisi' : jenis_ekspedisi,
						'ktp' : ktp,
						'nama' : nama_ekspedisi,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_ekspedisi' : alamat_ekspedisi,
						'alamat_usaha' : alamat_usaha,
						'potongan_pph' : potongan_pph,
						'banks' : banks,
						'platform' : platform
					};

					var params = data_ekspedisi;

					$.ajax({
						url :'parameter/Ekspedisi/save',
						type : 'post',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								ekspedisi.uploadFile( data.content.id );
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data ekspedisi ?', function(result){
    			if (result) {
    				var div_ekspedisi = $('div[name=data-ekspedisi]');
    				var rek_ekspedisi = $('div#rekening_ekspedisi');

    				var id = $('input[type=hidden]').data('id');
    				var nomor = $('input[type=hidden]').data('nomor');
    				var status = $('input[type=hidden]').data('status');
    				var mstatus = $('input[type=hidden]').data('mstatus');
    				var version = $('input[type=hidden]').data('version');

					var banks = $.map( $(rek_ekspedisi).find('tr.detail_rekening'), function(tr) {
						var data = {
							'id_old' : $(tr).find('input[name=rekening_ekspedisi]').data('id'),
							'nomer_rekening' : $(tr).find('input[name=rekening_ekspedisi]').val(),
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : $(tr).find('input[name=bank_rekening]').val(),
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data ekspedisi
    				var jenis_ekspedisi = $(div_ekspedisi).find('select[name=jenis_ekspedisi]').val();
    				var nama_ekspedisi = $(div_ekspedisi).find('input[name=nama_ekspedisi]').val();
    				var contact_person = $(div_ekspedisi).find('input[name=contact_ekspedisi]').val();
    				var platform = 0;

    				var telepons = $.map( $(div_ekspedisi).find('input[name=telp_ekspedisi]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_ekspedisi).find('input[name=ktp_ekspedisi]').mask();
					var alamat_ekspedisi = {
						'kecamatan' : $(div_ekspedisi).find('select[name=kecamatan_ekspedisi]').val(),
						'kelurahan' : $(div_ekspedisi).find('input[name=kelurahan_ekspedisi]').val(),
						'alamat' : $(div_ekspedisi).find('textarea[name=alamat_ekspedisi]').val().trim(),
						'rt' :  $(div_ekspedisi).find('input[name=rt_ekspedisi]').val().trim(),
						'rw' :  $(div_ekspedisi).find('input[name=rw_ekspedisi]').val().trim(),
					};
					var npwp = $(div_ekspedisi).find('input[name=npwp_ekspedisi]').mask();
					var skb = $(div_ekspedisi).find('input[name=skb_ekspedisi]').val().trim();
					var tgl_habis_skb = !empty($(div_ekspedisi).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_ekspedisi).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_ekspedisi).find('select[name=kecamatan_usaha_ekspedisi]').val(),
						'kelurahan' : $(div_ekspedisi).find('input[name=kelurahan_usaha_ekspedisi]').val(),
						'alamat' : $(div_ekspedisi).find('textarea[name=alamat_usaha_ekspedisi]').val().trim(),
						'rt' :  $(div_ekspedisi).find('input[name=rt_usaha_ekspedisi]').val().trim(),
						'rw' :  $(div_ekspedisi).find('input[name=rw_usaha_ekspedisi]').val().trim(),
					};
					var potongan_pph = $(div_ekspedisi).find('select.potongan_pph').val();

					var data_ekspedisi = {
						'id' : id,
						'nomor' : nomor,
						'status' : status,
						'mstatus' : mstatus,
						'version' : version,
						'jenis_ekspedisi' : jenis_ekspedisi,
						'ktp' : ktp,
						'nama' : nama_ekspedisi,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_ekspedisi' : alamat_ekspedisi,
						'alamat_usaha' : alamat_usaha,
						'potongan_pph' : potongan_pph,
						'banks' : banks,
						'platform' : platform
					};

					var params = data_ekspedisi;

					$.ajax({
						url :'parameter/Ekspedisi/edit',
						type : 'post',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								ekspedisi.uploadFile( data.content.id );
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
		$.map( $(div_action).find('input[name=lampiran_ktp], input[name=lampiran_npwp], input[name=lampiran_dds]'), function(ipt){
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
			var nomer_rekening = $(tr).find('input[name=rekening_ekspedisi]').val();

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
			url :'parameter/Ekspedisi/uploadFile',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				if(data.status == 1){
					if ( idxUploadFile < lampirans.length ) {
						idxUploadFile++;

						ekspedisi.uploadFile(id);
					} else {
						hideLoading();
						bootbox.alert(data.message,function() {
							ekspedisi.getLists();
							ekspedisi.loadForm(id);

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

    	var nomor = $('input[type=hidden]').data('nomor');
    	var keterangan = $('input[name=nonaktif_keterangan]').val();

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
			url :'parameter/Ekspedisi/nonAktif',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if(data.status){
					bootbox.alert(data.message,function() {
						ekspedisi.getLists();
					});
				} else {
					bootbox.alert(data.message);
				}
			},
			contentType : false,
			processData : false,
		});
	},

	ack : function () {
		var ids = $('input[type=hidden]').data('id');
		bootbox.confirm('Data mitra akan di-ack', function(result){
			if (result) {
				$.ajax({
					url : 'parameter/Ekspedisi/ack',
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
								ekspedisi.getLists();
								ekspedisi.loadForm(data.content.id);
							});
						}else{
							bootbox.alert(data.message);
						}
					},
				});
			}
		});
	},

	// approve : function () {
	// 	alert("Test Approve");
	// },

	// reject : function () {
	// 	alert("Test Reject");
	// },

	addRowTable : function (elm, action) {
		var row = $(elm).closest("tr");
		var row_clone = row.clone();
		row_clone.find('select, input').val('');
		var tbody = $(elm).closest("tbody");
		tbody.append(row_clone);
		$('[data-tipe=phone]').mask("9999 9999 9??999");
		ekspedisi.setBindSHA1()
	}, // end - addRowTelepon

	removeRowTable : function (elm) {
		var row = $(elm).closest("tr");
		row.find('select, input').val('');
		if ( row.prev('tr').length > 0 || row.next('tr').length > 0 ) {
			row.remove();
		}
	},

    loadForm_status : function(elm) {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_ekspedisi]').data('nomor');
    	var tipe = $(elm).data('tipe');

    	var title = null;
    	if ( tipe == 'aktif' ) {
    		title = 'Aktif Ekspedisi'
    	} else {
    		title = 'Non Aktif Ekspedisi';
    	};

    	$.ajax({
            url: 'parameter/Ekspedisi/loadFormStatus',
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
            				ekspedisi.non_aktif(tipe);
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
			    ekspedisi.setBindSHA1();
        	}        	
        });
    },

    loadFormSaldo : function () {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_ekspedisi]').data('nomor');

    	$.ajax({
            url: 'parameter/Ekspedisi/loadFormSldAwal',
            data: {
            	params: nomor
            },
            type: 'GET',
            dataType: 'HTML',
            success: function(html) {
            	var modal = bootbox.dialog({
            		message: html,
            		title: "Saldo Awal Ekspedisi",
            		buttons: [
            		{
            			label: "Simpan",
            			className: "btn btn-primary pull-right",
            			callback: function() {
            				ekspedisi.sld_awal();
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
			    ekspedisi.setBindSHA1();
        	}        	
        });
    },

    cari_ekspedisi : function () {
    	var index = $("#filter_search").val();

    	var input, filter, table, tr, td, i, txtValue;
    	input = document.getElementById("input_cari_ekspedisi");
    	filter = input.value.toUpperCase();
    	table = document.getElementById("table_ekspedisi");
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
    }
};

ekspedisi.start_up();