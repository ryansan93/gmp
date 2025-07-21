var idxUploadFile = 0;

var supl = {
	start_up : function	() {
		supl.setBindSHA1();
		supl.getLists();

		$('[data-tipe=phone]').mask("9999 9999 9??999");
		$('[data-tipe=rt]').mask("999");
		$('[data-tipe=rw]').mask("999");
		$('[name=ktp_supl]').mask("9999999999999999");
		$('[name=npwp_supl]').mask("999.999.999.9-999.999");

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
            url : 'parameter/Supplier/list_supl',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){
            	App.showLoaderInContent($('table.tbl_supl tbody'));
            },
            success : function(data){
            	App.hideLoaderInContent($('table.tbl_supl tbody'), data);
                // $('table.tbl_supl tbody').html(data);
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

            supl.load_form(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/Supplier/load_form',
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
				$('[name=ktp_supl]').mask("9999999999999999");
				$('[name=npwp_supl]').mask("999.999.999.9-999.999");

				$('#tglHbsBerlaku').datetimepicker({
					locale: 'id',
					format: 'DD MMM Y'
				});
		
				var tgl = $('#tglHbsBerlaku').find('input').attr('data-tgl');
				if ( !empty(tgl) ) {
					$('#tglHbsBerlaku').data('DateTimePicker').date(new Date(tgl));
				}

                supl.setBindSHA1();

                if ( !empty(resubmit) ) {
                	supl.load_kab_supl();
                };

                hideLoading();
            },
        });
    }, // end - load_form

    load_kab_supl : function() {
    	var select_prov_supl = $('select[name=propinsi_supl]');
    	var tipe_lok_supl = $('select[name=tipe_lokasi]');
    	var tipe_lok_usaha = $('select[name=tipe_lokasi_usaha]');

    	supl.getListLokasi_Update(tipe_lok_supl, '#alamat_supplier', 'kab', '');
    	supl.getListLokasi_UpdateUsaha(tipe_lok_usaha, '#alamat_usaha_supplier', 'kab', '_usaha');
    }, // end - load_tipe_lokasi

	list_load : function(elm) {
		$.ajax({
			url : 'parameter/Supplier/list_supplier',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				$('#table-list-supl tbody').html(data);
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_supl]'); // element kabupaten
			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_supl]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_supl] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_supl]');
			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_supl]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Supplier/getLokasiJson',
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_supl]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_supl]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_supl] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_supl]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_supl]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Supplier/getLokasiJson',
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
							var select_kab_supl = $('select[name=kabupaten_supl]');
	    					supl.getListLokasi_Update(select_kab_supl, '#alamat_supplier', 'kec', tipe);
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
			eSelect = _form.find('select[name=kabupaten'+tipe+'_supl]'); // element kabupaten

			id = $(eSelect).data('id');

			optDefault = _form.find('select[name=tipe_lokasi'+tipe+'] option:selected').text().toLowerCase();

			induk = _form.find('select[name=propinsi'+tipe+'_supl]').val();
			jenis = _form.find('select[name=tipe_lokasi'+tipe+']').val();

			_form.find('select[name=kecamatan'+tipe+'_supl] option').remove();

		} else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan'+tipe+'_supl]');

			id = $(eSelect).data('id');

			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten'+tipe+'_supl]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="" hidden>Pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Supplier/getLokasiJson',
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
							var select_kab_usaha_supl = $('select[name=kabupaten_usaha_supl]');
							supl.getListLokasi_UpdateUsaha(select_kab_usaha_supl, '#alamat_usaha_supplier', 'kec', '_usaha')
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data supplier ?', function(result){
    			if (result) {
    				var div_supplier = $('div[name=data-supplier]');
    				var rek_supplier = $('div#rekening_supplier');

					var banks = $.map( $(rek_supplier).find('tr.detail_rekening'), function(tr) {
						var data = {
							'nomer_rekening' : $(tr).find('input[name=rekening_supl]').val(),
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : $(tr).find('input[name=bank_rekening]').val(),
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data supplier
    				var jenis_supplier = $(div_supplier).find('select[name=jenis_supl]').val();
    				var nama_supplier = $(div_supplier).find('input[name=nama_supl]').val();
    				var contact_person = $(div_supplier).find('input[name=contact_supl]').val();
    				var plafon = numeral.unformat( $(div_supplier).find('input[name=plafon]').val() );
    				var jatuh_tempo = numeral.unformat( $(div_supplier).find('input[name=jatuh_tempo]').val() );

    				var telepons = $.map( $(div_supplier).find('input[name=telp_supl]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_supplier).find('input[name=ktp_supl]').mask();
					var alamat_supplier = {
						'kecamatan' : $(div_supplier).find('select[name=kecamatan_supl]').val(),
						'kelurahan' : $(div_supplier).find('input[name=kelurahan_supl]').val(),
						'alamat' : $(div_supplier).find('textarea[name=alamat_supl]').val().trim(),
						'rt' :  $(div_supplier).find('input[name=rt_supl]').val().trim(),
						'rw' :  $(div_supplier).find('input[name=rw_supl]').val().trim(),
					};
					var npwp = $(div_supplier).find('input[name=npwp_supl]').mask();
					var skb = $(div_supplier).find('input[name=skb_supl]').val().trim();
					var tgl_habis_skb = !empty($(div_supplier).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_supplier).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_supplier).find('select[name=kecamatan_usaha_supl]').val(),
						'kelurahan' : $(div_supplier).find('input[name=kelurahan_usaha_supl]').val(),
						'alamat' : $(div_supplier).find('textarea[name=alamat_usaha_supl]').val().trim(),
						'rt' :  $(div_supplier).find('input[name=rt_usaha_supl]').val().trim(),
						'rw' :  $(div_supplier).find('input[name=rw_usaha_supl]').val().trim(),
					};

					var data_supplier = {
						'jenis_supplier' : jenis_supplier,
						'ktp' : ktp,
						'nama' : nama_supplier,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_supplier' : alamat_supplier,
						'alamat_usaha' : alamat_usaha,
						'banks' : banks,
						'plafon' : plafon,
						'jatuh_tempo' : jatuh_tempo
					};

					var params = data_supplier;

					$.ajax({
						url :'parameter/Supplier/save',
						type : 'post',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								supl.uploadFile( data.content.id );
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data supplier ?', function(result){
    			if (result) {
    				var div_supplier = $('div[name=data-supplier]');
    				var rek_supplier = $('div#rekening_supplier');

    				var id = $('input[type=hidden]').data('id');
    				var nomor = $('input[type=hidden]').data('nomor');
    				var status = $('input[type=hidden]').data('status');
    				var mstatus = $('input[type=hidden]').data('mstatus');
    				var version = $('input[type=hidden]').data('version');

					var banks = $.map( $(rek_supplier).find('tr.detail_rekening'), function(tr) {
						var data = {
							'id_old' : $(tr).find('input[name=rekening_supl]').data('id'),
							'nomer_rekening' : $(tr).find('input[name=rekening_supl]').val(),
							'nama_pemilik' : $(tr).find('input[name=pemilik_rekening]').val(),
							'nama_bank' : $(tr).find('input[name=bank_rekening]').val(),
							'cabang_bank' : $(tr).find('input[name=cabang_rekening]').val()
						}

						return data;
					});

    				// data supplier
    				var jenis_supplier = $(div_supplier).find('select[name=jenis_supl]').val();
    				var nama_supplier = $(div_supplier).find('input[name=nama_supl]').val();
    				var contact_person = $(div_supplier).find('input[name=contact_supl]').val();
    				var plafon = numeral.unformat( $(div_supplier).find('input[name=plafon]').val() );
    				var jatuh_tempo = numeral.unformat( $(div_supplier).find('input[name=jatuh_tempo]').val() );

    				var telepons = $.map( $(div_supplier).find('input[name=telp_supl]'), function(ipt) {
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var ktp = $(div_supplier).find('input[name=ktp_supl]').mask();
					var alamat_supplier = {
						'kecamatan' : $(div_supplier).find('select[name=kecamatan_supl]').val(),
						'kelurahan' : $(div_supplier).find('input[name=kelurahan_supl]').val(),
						'alamat' : $(div_supplier).find('textarea[name=alamat_supl]').val().trim(),
						'rt' :  $(div_supplier).find('input[name=rt_supl]').val().trim(),
						'rw' :  $(div_supplier).find('input[name=rw_supl]').val().trim(),
					};
					var npwp = $(div_supplier).find('input[name=npwp_supl]').mask();
					var skb = $(div_supplier).find('input[name=skb_supl]').val().trim();
					var tgl_habis_skb = !empty($(div_supplier).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_supplier).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var alamat_usaha = {
						'kecamatan' : $(div_supplier).find('select[name=kecamatan_usaha_supl]').val(),
						'kelurahan' : $(div_supplier).find('input[name=kelurahan_usaha_supl]').val(),
						'alamat' : $(div_supplier).find('textarea[name=alamat_usaha_supl]').val().trim(),
						'rt' :  $(div_supplier).find('input[name=rt_usaha_supl]').val().trim(),
						'rw' :  $(div_supplier).find('input[name=rw_usaha_supl]').val().trim(),
					};

					var data_supplier = {
						'id' : id,
						'nomor' : nomor,
						'status' : status,
						'mstatus' : mstatus,
						'version' : version,
						'jenis_supplier' : jenis_supplier,
						'ktp' : ktp,
						'nama' : nama_supplier,
						'cp' : contact_person,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat_supplier' : alamat_supplier,
						'alamat_usaha' : alamat_usaha,
						'banks' : banks,
						'plafon' : plafon,
						'jatuh_tempo' : jatuh_tempo
					};

					var params = data_supplier;

					$.ajax({
						url :'parameter/Supplier/edit',
						type : 'post',
						data : {
							'params': params
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							if(data.status == 1){
								supl.uploadFile( data.content.id );
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
			var nomer_rekening = $(tr).find('input[name=rekening_supl]').val();

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
			url :'parameter/Supplier/uploadFile',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				if(data.status == 1){
					if ( idxUploadFile < lampirans.length ) {
						idxUploadFile++;

						supl.uploadFile(id);
					} else {
						hideLoading();
						bootbox.alert(data.message,function() {
							supl.getLists();
							supl.load_form(data.content.id);

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
			url :'parameter/Supplier/nonAktif',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if(data.status){
					bootbox.alert(data.message,function() {
						supl.getLists();
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
					url : 'parameter/Supplier/ack',
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
								supl.getLists();
								supl.load_form(data.content.id);
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
		supl.setBindSHA1()
	}, // end - addRowTelepon

	removeRowTable : function (elm) {
		var row = $(elm).closest("tr");
		row.find('select, input').val('');
		if ( row.prev('tr').length > 0 || row.next('tr').length > 0 ) {
			row.remove();
		}
	},

	// active_tab: function(elm, tipe) {
	// 	var tr = $(elm).closest('tr');
	// 	var div_tab_pane = $(elm).closest('div.tab-pane');
	// 	var div_tab_content = $(div_tab_pane).closest('div.tab-content');
	// 	var div_tab_header = $(div_tab_content).prev();

	// 	var div_tab_pane_next = $(div_tab_pane).next();

	// 	var id = $(tr).find('td[name=id_supplier]').data('id');

	// 	$(div_tab_pane).removeClass('active');
	// 	$(div_tab_pane_next).addClass('active');

	// 	$(div_tab_header).find('li.daftar').removeClass('active');
	// 	$(div_tab_header).find('li.master').addClass('active');

	// 	supl.load_detail(id, div_tab_pane_next, tipe);
	// },

	// load_detail : function(param, pane, tipe) {
	// 	var dcontent = $(pane).find('div#detail_master');
	// 	var tujuan = '';
	// 	if (tipe == "add") {
	// 		tujuan = 'parameter/Supplier/loadFormInput';
	// 	} else {
	// 		tujuan = "parameter/Supplier/viewDataSupplier";
	// 	}

	// 	$.ajax({
	// 	    url: tujuan,
	// 	    data: {
	// 	        params: param
	// 	    },
	// 	    type: 'GET',
	// 	    dataType: 'HTML',
	// 	    beforeSend: function() {
	// 	        App.showLoaderInContent(dcontent);
	// 	    },
	// 	    success: function(html) {
	// 	        App.hideLoaderInContent(dcontent, html);
	// 	        supl.load_list_supplier();
	// 	        supl.start_up();
	// 	    }
	// 	});
 //    },

    // load_list_supplier : function () {
    // 	var dcontent = $('#daftar_supplier');

    //     var tujuan = 'parameter/Supplier/refreshListSupplier';

    //     $.ajax({
    //         url: tujuan,
    //         data: {},
    //         type: 'GET',
    //         dataType: 'HTML',
    //         beforeSend: function() {
    //             App.showLoaderInContent(dcontent);
    //         },
    //         success: function(html) {
    //             App.hideLoaderInContent(dcontent, html);
    //         },
    //     });
    // },

    load_form_status : function(elm) {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_supplier]').data('nomor');
    	var tipe = $(elm).data('tipe');

    	console.log(nomor);

    	var title = null;
    	if ( tipe == 'aktif' ) {
    		title = 'Aktif Supplier'
    	} else {
    		title = 'Non Aktif Supplier';
    	};

    	$.ajax({
            url: 'parameter/Supplier/loadFormStatus',
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
            				supl.non_aktif(tipe);
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
			    supl.setBindSHA1();
        	}        	
        });
    },

    load_form_saldo : function () {
    	var tr = $(elm).closest('tr');
    	var nomor = $(tr).find('td[name=id_supplier]').data('nomor');

    	$.ajax({
            url: 'parameter/Supplier/loadFormSldAwal',
            data: {
            	params: nomor
            },
            type: 'GET',
            dataType: 'HTML',
            success: function(html) {
            	var modal = bootbox.dialog({
            		message: html,
            		title: "Saldo Awal Supplier",
            		buttons: [
            		{
            			label: "Simpan",
            			className: "btn btn-primary pull-right",
            			callback: function() {
            				supl.sld_awal();
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
			    supl.setBindSHA1();
        	}        	
        });
    },

    cari_supplier : function () {
    	var index = $("#filter_search").val();

    	var input, filter, table, tr, td, i, txtValue;
    	input = document.getElementById("input_cari_supplier");
    	filter = input.value.toUpperCase();
    	table = document.getElementById("table_supplier");
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

supl.start_up();