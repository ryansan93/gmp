function renameFilename(file, prefiks) {
	return file.renameFilename = prefiks + file.split('.').pop();
}

var html_batal_reset_jaminan = null;

var idxUploadFile = 0;

var ptk = {

	start_up : function(){
		$('button#search-pagination').on('click', function() {
			ptk.set_pagination();
		});

		$('[data-tipe=ktp]').mask("999999-999999-9999");
		$('[data-tipe=phone]').mask("9999 9999 9??999");

		$('#tglHbsBerlaku').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		var tgl = $('#tglHbsBerlaku').find('input').attr('data-tgl');
		if ( !empty(tgl) ) {
			$('#tglHbsBerlaku').data('DateTimePicker').date(new Date(tgl));
		}

		if ( $('#history').attr('data-ismobile') == 1 ) {
			// $('select.unit').select2();
			$('select.unit').select2().on('select2:select', function(e) {
				$('select.kecamatan').removeAttr('disabled');
				$('select.kecamatan').find('option').attr('disabled', 'disabled');
				$('select.kecamatan').find('option[data-kabkota="'+e.params.data.text+'"]').removeAttr('disabled');

				$('select.kecamatan').select2();
			});
			$('select.mitra').select2();
		} else {
			ptk.set_pagination();
		}
		ptk.setBindSHA1();
	}, // end - start_up

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
			var resubmit = $(elm).attr('data-resubmit');

			ptk.load_form(v_id, resubmit);
		};
	}, // end - changeTabActive

	load_form: function(v_id = null, resubmit = null) {
		var div_action = $('div#action');

		$.ajax({
			url : 'parameter/Peternak/load_form',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){ App.showLoaderInContent( $(div_action) ); },
			success : function(html){
				App.hideLoaderInContent( $(div_action), html );

				// $(div_action).html(html);

				$('[data-tipe=ktp]').mask("999999-999999-9999");
				$('[data-tipe=phone]').mask("9999 9999 9??999");

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

				ptk.setBindSHA1();

				if ( !empty(resubmit) ) {
					ptk.setLokasiUpdate(div_action);
				}
			},
		});
	}, // end - load_form

	set_pagination : function(){
		var search_by = $('#search-by-pagination').val();
		var search_val = $('#search-val-pagination').val();

		$.ajax({
			url : 'parameter/Peternak/amount_of_data',
			data : {'search_by': search_by, 'search_val': search_val},
			dataType : 'JSON',
			type : 'POST',
			beforeSend : function(){},
			success : function(data){
				pagination.set_pagination( data.content.jml_row, data.content.jml_page, data.content.list, ptk.getLists );
			}
		});
	}, // end - set_pagination

	getLists : function(elm){
		var list_nomor = $(elm).data('list_id_page');

		$.ajax({
			url : 'parameter/Peternak/list_sk',
			data : {'params' : list_nomor},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				$('table.tbl_peternak tbody').html(data);
				
				hideLoading();
			}
		});
	}, // end - getLists

	getListMobile : function(elm){
		var params = {
			'mitra': $('select.mitra').select2().val(),
			'unit': $('select.unit').select2().val(),
			'kecamatan': $('select.kecamatan').select2().val()
		};

		$.ajax({
			url : 'parameter/Peternak/getListMobile',
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
			url : 'parameter/Peternak/detailMobile',
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

	cariIndexMitra : function(){
		var column = $('select[name=column-tbl]').val();
		var regex =  $('input[name=cari]').val().trim();

		if (!empty(column) && !empty(regex)) {
			ptk.getListMitra();
		}
		else if (empty(column)) {
			$('input[name=cari]').val('');
			ptk.getListMitra();
		}
	}, // end - cariIndexMitra

	set_mark: function(elm) {
		$all_mark = $('[name=mark]');
		$row_marking = $.map($all_mark, function(ipt) {
			if ($(ipt).is(':checked')) {
				return $(ipt);
			}
		});

		if ($all_mark.length == $row_marking.length) {
			$('#markAll').prop('checked', true);
		} else {
			$('#markAll').prop('checked', false);
		}
	}, // end - set_mark

	set_mark_all: function(elm) {
		if ($(elm).is(':checked')) {
			$('[name=mark]').prop('checked', true);
		} else {
			$('[name=mark]').prop('checked', false);
		}
	}, // end - set_mark_all

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

	addRowTable : function (elm, action) {
		var row = $(elm).closest("tr");
		var row_clone = row.clone();
		row_clone.find('select, input').val('');
		var tbody = $(elm).closest("tbody");
		tbody.append(row_clone);
		$('[data-tipe=phone]').mask("9999 9999 9??999");
		App.format();
	}, // end - addRowTelepon

	removeRowTable : function (elm) {
		var row = $(elm).closest("tr");
		row.find('select, input').val('');
		if ( row.prev('tr').length > 0 || row.next('tr').length > 0 ) {
			row.remove();
		}
	}, // end - removeRowTelepon

	collapseLampiran : function(elm){
		var $this = $(elm);
		if(!$this.hasClass('tpanel-collapsed')) {
			$this.closest('.tpanel').find('.tpanel-body').show();
			$this.addClass('tpanel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
		} else {
			$this.closest('.tpanel').find('.tpanel-body').hide();
			$this.removeClass('tpanel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
		}
	}, // end - collapseLampiran

	set_autocomplete_lokasi : function (elements, tipe_lokasi = '') {

		// $( "[name=kabupaten]" ).autocomplete({
		$( elements ).autocomplete({
			source : function(request, response){

				var elm = $(this)[0].element[0];
				var elm_name = $(elm).attr('name');
				var induk = '';

				if (elm_name == 'kabupaten') {
					tipe_lokasi = $(elm).closest('.form-group').find('select[name=tipe_lokasi] option:selected').val();
					induk = $(elm).closest('.form-lokasi').find('input[name=provinsi]').attr('data-id');
				}else if (elm_name == 'kecamatan') {
					induk = $(elm).closest('.form-lokasi').find('input[name=kabupaten]').attr('data-id');
				}else if (elm_name == 'kelurahan') {
					induk = $(elm).closest('.form-lokasi').find('input[name=kecamatan]').attr('data-id');
				}

				$.ajax({
					url: 'master/mitra/autocomplete_lokasi?tipe_lokasi=' + tipe_lokasi + '&induk=' + induk,
					beforeSend: function(){
						$(elm).attr('data-id', "" );
					},
					async:    true,
					data : request,
					dataType: "json",
					success: response
				});
			},
			minLength: 2,
			select: function( event, ui ) {
				$(this).attr('data-id', ui.item.id );
			}
		});

	}, // end - set_autocomplete_pelanggan

	getListUnitPerwakilan : function(elm){
		var id_perwakilan = $(elm).val();
		var div_perwakilan = $(elm).closest('div[name=data-perwakilan]');
		if (empty(id_perwakilan)) {
			div_perwakilan.find('select[name=unit] option').remove();
		}else{
			$.ajax({
			url : 'parameter/Peternak/getListUnit?induk='+id_perwakilan,
				type : 'GET',
				dataType : 'JSON',
				success : function(data){
					div_perwakilan.find('select[name=unit] option').remove();
					div_perwakilan.find('select[name=unit]').append('<option value="">pilih unit</option>');
					for (var i = 0; i < data.length; i++) {
						var unit = data[i];
						div_perwakilan.find('select[name=unit]').append('<option value="'+unit.id+'">'+unit.nama+'</option>');
					}
				}
			});
		}
	}, // end - getListUnitPerwakilan

	getListUnitPerwakilanAfterApprove : function(){
		var div_perwakilan = $('div[name=data-perwakilan]');

		$.map( $(div_perwakilan) ,function(div_pwk) {
			var id_pwk = $(div_pwk).find('select[name=perwakilan]').val();
			$.map( $(div_pwk).find('div[name=data-kandang]') ,function(div_kdg) {
				$.ajax({
					url : 'parameter/Peternak/getListUnit?induk='+id_pwk,
					type : 'GET',
					dataType : 'JSON',
					success : function(data){
						var id_unit = $(div_kdg).find('select[name=unit] option').val();


						$(div_kdg).find('select[name=unit] option').remove();
						$(div_kdg).find('select[name=unit]').append('<option value="">pilih unit</option>');
						for (var i = 0; i < data.length; i++) {
							var selected = null;
							var unit = data[i];
							if ( unit.id == id_unit ) {
								selected = 'selected';
							};

							$(div_kdg).find('select[name=unit]').append('<option value="'+unit.id+'" '+selected+' >'+unit.nama+'</option>');
						}
					}
				});
			});
		});
	}, // end - getListUnitPerwakilanAfterApprove

	tambahKandang : function (elm) {
		var div_perwakilan = $(elm).closest('div[name=data-perwakilan]');
		var div_kandang = div_perwakilan.find('div[name=data-kandang]:last');
		var div_kandang_clone = div_kandang.clone();
		div_kandang_clone.attr('data-id', '');
		div_kandang_clone.find('select, input').val('');
		div_kandang_clone.find('td.lampiran span').remove();
		div_kandang_clone.find('table.bangunan-kandang tbody tr:not(:first)').remove();

		var hapus_kdg = div_kandang_clone.find('button.hapus_kandang').length;
		if ( hapus_kdg > 0 ) {
			div_kandang_clone.find('button.hapus_kandang').removeClass('hide');
		};

		div_kandang_clone.insertAfter(div_kandang);
		ptk.setBindSHA1();
		App.format();
	}, // end - tambahKandang

	hapusKandang : function(elm){
		var _div = $(elm).closest("div[name=data-kandang]");
		_div.find('select, input').val('');
		_div.find('td.lampiran span').remove();
		_div.find(':file').attr('data-sha1', '');
		_div.find('table.bangunan-kandang tbody tr:not(:first)').remove();
		if ( _div.closest('div[name=data-perwakilan]').find('div[name=data-kandang]').length > 1) {
			_div.remove();
		}
	}, // end - hapusKandang

	tambahPerwakilan : function (elm) {
		var div_parent_perwakilan = $(elm).closest('div#kandang');
		var div_perwakilan = div_parent_perwakilan.find('div[name=data-perwakilan]:last');
		var div_perwakilan_clone = div_perwakilan.clone();
		div_perwakilan_clone.find('div[name=data-kandang]:not(:first)').remove();
		div_perwakilan_clone.find('select, input').val('');
		div_perwakilan_clone.find('td.lampiran span').remove();
		div_perwakilan_clone.find(':file').attr('data-sha1', '');
		div_perwakilan_clone.insertAfter(div_perwakilan);
		ptk.setBindSHA1();
		App.format();
	}, // end - tambahPerwakilan

	hapusPerwakilan : function(elm){
		var _div = $(elm).closest("div[name=data-perwakilan]");
		if ( _div.closest('div#kandang').find('div[name=data-perwakilan]').length > 1) {
			_div.remove();
		}
	}, // end - hapusKandang

	tambahPerwakilanAfterApprove : function (elm) {
		var div_parent_perwakilan = $(elm).closest('div#kandang');
		var div_perwakilan = div_parent_perwakilan.find('div[name=data-perwakilan]:last');
		var div_perwakilan_clone = div_perwakilan.clone();
		var div_head_pwk = div_perwakilan_clone.find('div.head_pwk');

		div_head_pwk.find('select, input').prop('disabled', false);
		div_head_pwk.find('button').removeClass('hide');
		div_perwakilan_clone.attr('data-id', '');
		div_perwakilan_clone.find('div[name=data-kandang]').attr('data-id', '');
		div_perwakilan_clone.find('div[name=data-kandang]:not(:first)').remove();
		div_perwakilan_clone.find('select, input').val('');
		div_perwakilan_clone.find('td.lampiran span').remove();
		div_perwakilan_clone.find(':file').attr('data-filename', '');
		div_perwakilan_clone.find(':file').attr('data-old', '');
		div_perwakilan_clone.insertAfter(div_perwakilan);

		ptk.setBindSHA1();
		App.format();
	}, // end - tambahPerwakilan

	getListLokasi : function(elm, req = ''){
		var _form = $(elm).closest('.form-lokasi');
		var induk = '';
		var jenis = '';

		// reset pilihan kabupaten
		var eSelect = null;
		var optDefault = '';
		if (req == 'kab') {
			eSelect = _form.find('select[name=kabupaten]'); // element kabupaten
			optDefault = _form.find('select[name=tipe_lokasi] option:selected').text().toLowerCase();

			induk = _form.find('select[name=provinsi]').val();
			jenis = _form.find('select[name=tipe_lokasi]').val();

			_form.find('select[name=kecamatan] option').remove();

		}else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan]');
			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten]').val();
			jenis = 'KC';
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="">pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Peternak/getLokasiJson',
				data : {'jenis' : jenis, 'induk' : induk},
				dataType : 'JSON',
				type : 'GET',
				beforeSend : function () {
					// showLoading();
				},
				success : function(data){
					for (loc of data.content) {
						eSelect.append('<option value="'+loc.id+'">'+ loc.nama +'</option>')
					}
					// hideLoading();
				}
			});
		}
	}, // end - onChangeProvinsi

	setLokasiUpdate : function(elm){
		$(elm).find('[data-tipe=ktp]').mask("999999-999999-9999");
		$(elm).find('[data-tipe=phone]').mask("9999 9999 9??999");
		ptk.setBindSHA1();

		$.map( $(elm).find('div.form-lokasi'), function(div){
			// var div_form_lokasi = $(div).find('div.form-lokasi');
			var select_prov = $(div).find('select[name=provinsi]');
			var select_lokasi = $(div).find('select[name=tipe_lokasi]');
			var select_kab = $(div).find('select[name=kabupaten]');

			ptk.getListLokasiUpdate(select_prov, 'kab');
		});

		ptk.getListUnitPerwakilanAfterApprove();
	}, // end - setLokasiUpdate

	getListLokasiUpdate : function(elm, req = ''){
		var _form = $(elm).closest('.form-lokasi');
		var induk = '';
		var jenis = '';

		// reset pilihan kabupaten
		var eSelect = null;
		var id = null;
		var optDefault = '';
		if (req == 'kab') {
			eSelect = _form.find('select[name=kabupaten]'); // element kabupaten

			id = eSelect.val(); // id kabupaten
			optDefault = _form.find('select[name=tipe_lokasi] option:selected').text().toLowerCase();

			induk = _form.find('select[name=provinsi]').val();
			jenis = _form.find('select[name=tipe_lokasi]').val();

		}else if (req = 'kec') {
			eSelect = _form.find('select[name=kecamatan]'); // element kecamatan

			id = eSelect.val(); // id kecamatan
			optDefault = 'kecamatan';

			induk = _form.find('select[name=kabupaten]').val();
			jenis = 'KC';

			_form.find('select[name=kecamatan] option').remove();
		}

		eSelect.find('option').remove();
		eSelect.append('<option value="">pilih '+  optDefault +'</option>')
		if (! empty(jenis) && ! empty(induk)) {
			$.ajax({
				url : 'parameter/Peternak/getLokasiJson',
				data : {'jenis' : jenis, 'induk' : induk, 'id' : id},
				dataType : 'JSON',
				type : 'GET',
				beforeSend : function () {
					// showLoading();
				},
				success : function(data){
					var index = 0;
					for (loc of data.content) {
						if ( loc.id == id ) {
							eSelect.append('<option value="'+loc.id+'" selected>'+ loc.nama +'</option>')  
						};
						eSelect.append('<option value="'+loc.id+'">'+ loc.nama +'</option>')
						index++;
					}

					if (req == 'kab') {
						if (index == data.content.length) {
							ptk.getListLokasiUpdate(eSelect, 'kec');
						};
					}
					// hideLoading();
				}
			});
		}
	}, // end - getListLokasiUpdate

	save : function(){
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

		$.map($(':file[data-required=1]'), function(elm){
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
					// ubah value input, textarea menjadi uppercase
					$.map($('input:text, textarea'), function(_e){ $(_e).val( $(_e).val().toUpperCase() ); });

					var formData = new FormData();
					var div_mitra = $('div[name=data-mitra]');
					var div_lampiran_mitra = $('div#lampiran_mitra');

					// data mitra
					var jenis_mitra = $(div_mitra).find('select[name=jenis_mitra]').val();
					var perusahaan = $(div_mitra).find('select[name=perusahaan]').val();
					var ktp = $(div_mitra).find('input[name=ktp]').mask();
					var nama = $(div_mitra).find('input[name=nama_mitra]').val().trim();
					var npwp = $(div_mitra).find('input[name=npwp]').val().trim();
					var skb = $(div_mitra).find('input[name=skb]').val().trim();
					var tgl_habis_skb = !empty($(div_mitra).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_mitra).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var telepons = $.map( $(div_mitra).find('input[name=telepon]'), function(ipt){
						var telp = $(ipt).mask();
						if (!empty(telp)) {
							return telp;
						}
					});

					var alamat_mitra = {
						'kecamatan' : $(div_mitra).find('select[name=kecamatan]').val(),
						'kelurahan' : $(div_mitra).find('input[name=kelurahan]').val(),
						'alamat' : $(div_mitra).find('textarea[name=alamat]').val().trim(),
						'rt' :  $(div_mitra).find('input[name=rt]').val().trim(),
						'rw' :  $(div_mitra).find('input[name=rw]').val().trim(),
					};

					var data_bank = {
						'bank' : $(div_mitra).find('input[name=bank]').val().trim(),
						'cabang' : $(div_mitra).find('input[name=cabang-bank]').val().trim(),
						'rekening' : $(div_mitra).find('input[name=no-rekening]').val().trim(),
						'pemilik' : $(div_mitra).find('input[name=pemilik-rekening]').val().trim(),
					};

					var keterangan_jaminan = $(div_mitra).find('textarea[name=jaminan]').val().trim();

					// var lampiran_mitra = $.map( $('div#lampiran_mitra').find('td input:file'), function(ipt){
					// 	if (!empty( $(ipt).val() )) {
					// 		var __file = $(ipt).get(0).files[0];
					// 		formData.append('files[]', __file);
					// 		return {
					// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
					// 			'name' : __file.name,
					// 			'sha1' : $(ipt).attr('data-sha1'),
					// 		};
					// 	}
					// });

					// var lampirans_jaminan = $.map( $('div#lampiran_jaminan_mitra').find('td input:file'), function(ipt){
					// 	if (!empty( $(ipt).val() )) {
					// 		var __file = $(ipt).get(0).files[0];
					// 		formData.append('files[]', __file);
					// 		return {
					// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
					// 			'name' : __file.name,
					// 			'sha1' : $(ipt).attr('data-sha1'),
					// 		};
					// 	}
					// });

					var perwakilans = $.map( $('div[name=data-perwakilan]'), function(div_perwakilan){

						var perwakilan_id = $(div_perwakilan).find('select[name=perwakilan]').val();
						var nim = $(div_perwakilan).find('input[name=nim]').val();

						var kandangs =  $.map( $(div_perwakilan).find('div[name=data-kandang]'), function(div_kandang){
							var grup = $(div_kandang).find('input[name=grup]').val();
							var no_kandang = $(div_kandang).find('input[name=no-kandang]').val();
							var kapasitas = numeral.unformat( $(div_kandang).find('input[name=kapasitas]').val() );
							var tipe_kandang = $(div_kandang).find('select[name=tipe_kandang]').val();
							var status = $(div_kandang).find('select[name=status]').val();
							var unit = $(div_kandang).find('select[name=unit]').val();
							var ongkos_angkut = numeral.unformat( $(div_kandang).find('input[name=ongkos-angkut]').val() );

							var alamat_kandang = {
								'kecamatan' : $(div_kandang).find('select[name=kecamatan]').val(),
								'kelurahan' : $(div_kandang).find('input[name=kelurahan]').val(),
								'alamat' : $(div_kandang).find('textarea[name=alamat]').val().trim(),
								'rt' :  $(div_kandang).find('input[name=rt]').val().trim(),
								'rw' :  $(div_kandang).find('input[name=rw]').val().trim(),
							};

							var table_bangunan_kandangs = $(div_kandang).find('table.bangunan-kandang');
							var bangunan_kandangs = $.map( table_bangunan_kandangs.find('tbody tr'), function( row ){
								return {
									'no' : $(row).find('input[name=no]').val(),
									'panjang' : numeral.unformat( $(row).find('input[name=panjang]').val() ),
									'lebar' : numeral.unformat( $(row).find('input[name=lebar]').val() ),
									'jml_unit' : numeral.unformat( $(row).find('input[name=jml]').val() ),
								};
							});

							// var table_lampiran_kandang = $(div_kandang).find('table.lampiran-kandang');
							// var lampiran_kandangs = $.map( table_lampiran_kandang.find('td input:file'), function( ipt ){
							// 	if (!empty( $(ipt).val() )) {
							// 		var __file = $(ipt).get(0).files[0];
							// 		formData.append('files[]', __file);
							// 		return {
							// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
							// 			'name' : __file.name,
							// 			'sha1' : $(ipt).attr('data-sha1'),
							// 		};
							// 	}
							// });

							var __kandang = {
								'grup' : grup,
								'no' : no_kandang,
								'kapasitas' : kapasitas,
								'tipe' : tipe_kandang,
								'unit' : unit,
								'status' : status,
								'ongkos_angkut' : ongkos_angkut,
								'alamat' : alamat_kandang,
								'bangunans' : bangunan_kandangs,
								// 'lampirans' : lampiran_kandangs
							};
							return __kandang;

						});

						return {
							'perwakilan_id' : perwakilan_id,
							// 'nim' : nim,
							'd_kandangs' : kandangs
						};
					});

					var data_mitra = {
						'jenis_mitra' : jenis_mitra,
						'perusahaan' : perusahaan,
						'ktp' : ktp,
						'nama' : nama,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat' : alamat_mitra,
						// 'lampirans' : lampiran_mitra,
						// 'lampirans_jaminan' : lampirans_jaminan,
						'd_bank' : data_bank,
						'keterangan_jaminan' : keterangan_jaminan,
						'd_perwakilans' : perwakilans
					};

					// console.log(data_mitra);
					// formData.append('data_mitra', JSON.stringify(data_mitra));
					// ptk.execSave(formData);

					ptk.execSave(data_mitra);
				}
			});
		}
	}, // end - save

	execSave: function(data) {
		$.ajax({
			url :'parameter/Peternak/save',
			type : 'POST',
			dataType : 'JSON',
			data : {
				'params': data
			},
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if(data.status){
					// bootbox.alert(data.message,function(){
						// ptk.set_pagination();
                        // ptk.load_form(data.content.id);

						ptk.uploadFile(data.content.id);
					// });
				}else{
					bootbox.alert(data.message);
				}
			},
		});
	}, // end - execSave

	// execSave : function(formData){
	// 	$.ajax({
	// 		url :'parameter/Peternak/save',
	// 		type : 'post',
	// 		data : formData,
	// 		beforeSend : function(){
	// 			showLoading();
	// 		},
	// 		success : function(data){
	// 			hideLoading();
    //             if ( data.status == 1 ) {
    //                 bootbox.alert(data.message, function(){
    //                     ptk.set_pagination();
    //                     ptk.load_form(data.content.id);
    //                 });
    //             } else {
    //                 bootbox.alert(data.message);
    //             }
	// 		},
	// 		contentType : false,
	// 		processData : false,
	// 	});
	// }, // end - executeSave

	simpanPerwakilanAfterApprove : function () {
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

		$.map($(':file[data-required=1]'), function(elm){
			if( empty( $(elm).attr('data-filename') ) ){
				error++;
				lbl_errors.push( '* ' + $(elm).attr('placeholder') );
			}else{
				$(elm).parent().removeClass('has-error');
			}
		});

		if (error > 0) {
			bootbox.alert('Data belum lengkap : <br> ' + lbl_errors.join('<br>') );
		}else{

			bootbox.confirm('Apakah yakin data mitra akan diupdate?', function(result){
				if (result) {

					// ubah value input, textarea menjadi uppercase
					$.map($('input:text, textarea'), function(_e){ $(_e).val( $(_e).val().toUpperCase() ); });

					var formData = new FormData();
					var div_mitra = $('div[name=data-mitra]');
					var div_lampiran_mitra = $('div#lampiran_mitra');

					// data mitra
					var id_mitra = $('label.head').data('id');
					var jenis_mitra = $(div_mitra).find('select[name=jenis_mitra]').val();
					var perusahaan = $(div_mitra).find('select[name=perusahaan]').val();
					var ktp = $(div_mitra).find('input[name=ktp]').mask();
					var nama = $(div_mitra).find('input[name=nama_mitra]').val().trim();
					var npwp = $(div_mitra).find('input[name=npwp]').val().trim();
					var skb = $(div_mitra).find('input[name=skb]').val().trim();
					var tgl_habis_skb = !empty($(div_mitra).find('#tglHbsBerlaku input').val()) ? dateSQL($(div_mitra).find('#tglHbsBerlaku').data('DateTimePicker').date()) : null;
					var telepons = $.map( $(div_mitra).find('input[name=telepon]'), function(ipt){
						var telp = $(ipt).mask();

						if (!empty(telp)) {
							return telp;
						}
					});

					var alamat_mitra = {
						'kecamatan' : $(div_mitra).find('select[name=kecamatan]').val(),
						'kelurahan' : $(div_mitra).find('input[name=kelurahan]').val(),
						'alamat' : $(div_mitra).find('textarea[name=alamat]').val().trim(),
						'rt' :  $(div_mitra).find('input[name=rt]').val().trim(),
						'rw' :  $(div_mitra).find('input[name=rw]').val().trim(),
					};

					var data_bank = {
						'bank' : $(div_mitra).find('input[name=bank]').val().trim(),
						'cabang' : $(div_mitra).find('input[name=cabang-bank]').val().trim(),
						'rekening' : $(div_mitra).find('input[name=no-rekening]').val().trim(),
						'pemilik' : $(div_mitra).find('input[name=pemilik-rekening]').val().trim(),
					};

					var keterangan_jaminan = $(div_mitra).find('textarea[name=jaminan]').val().trim();

					// var lampiran_mitra = $.map( $('div#lampiran_mitra').find('td input:file'), function(ipt){
						
					// 	if (!empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
					// 		var filename = $(ipt).data('old');
					// 		if ( !empty( $(ipt).val() ) ) {
					// 			var __file = $(ipt).get(0).files[0];
					// 			formData.append('files[]', __file);

					// 			filename = __file.name;
					// 		};

					// 		return {
					// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
					// 			'name' : filename,
					// 			'sha1' : $(ipt).attr('data-sha1'),
					// 			'old' : $(ipt).data('old')
					// 		};
					// 	};
					// });

					// var lampirans_jaminan = $.map( $('div#lampiran_jaminan_mitra').find('td input:file'), function(ipt){
					// 	if (!empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
					// 		var filename = $(ipt).data('old');
					// 		if ( !empty( $(ipt).val() ) ) {
					// 			var __file = $(ipt).get(0).files[0];
					// 			formData.append('files[]', __file);

					// 			filename = __file.name;
					// 		};

					// 		return {
					// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
					// 			'name' : filename,
					// 			'sha1' : $(ipt).attr('data-sha1'),
					// 			'old' : $(ipt).data('old')
					// 		};
					// 	}
					// });

					var perwakilans = $.map( $('div[name=data-perwakilan]'), function(div_perwakilan){

						var mitra_mapping = $(div_perwakilan).data('id');
						var perwakilan_id = $(div_perwakilan).find('select[name=perwakilan]').val();
						var nim = $(div_perwakilan).find('input[name=nim]').val();

						var kandangs =  $.map( $(div_perwakilan).find('div[name=data-kandang]'), function(div_kandang){
							var id_kdg = $(div_kandang).data('id');
							var grup = $(div_kandang).find('input[name=grup]').val();
							var no_kandang = $(div_kandang).find('input[name=no-kandang]').val();
							var kapasitas = numeral.unformat( $(div_kandang).find('input[name=kapasitas]').val() );
							var tipe_kandang = $(div_kandang).find('select[name=tipe_kandang]').val();
							var status = $(div_kandang).find('select[name=status]').val();
							var unit = $(div_kandang).find('select[name=unit]').val();
							var ongkos_angkut = numeral.unformat( $(div_kandang).find('input[name=ongkos-angkut]').val() );

							var alamat_kandang = {
								'kecamatan' : $(div_kandang).find('select[name=kecamatan]').val(),
								'kelurahan' : $(div_kandang).find('input[name=kelurahan]').val(),
								'alamat' : $(div_kandang).find('textarea[name=alamat]').val().trim(),
								'rt' :  $(div_kandang).find('input[name=rt]').val().trim(),
								'rw' :  $(div_kandang).find('input[name=rw]').val().trim(),
							};

							var table_bangunan_kandangs = $(div_kandang).find('table.bangunan-kandang');
							var bangunan_kandangs = $.map( table_bangunan_kandangs.find('tbody tr'), function( row ){
								return {
									'no' : $(row).find('input[name=no]').val(),
									'panjang' : numeral.unformat( $(row).find('input[name=panjang]').val() ),
									'lebar' : numeral.unformat( $(row).find('input[name=lebar]').val() ),
									'jml_unit' : numeral.unformat( $(row).find('input[name=jml]').val() ),
								};
							});

							// var table_lampiran_kandang = $(div_kandang).find('table.lampiran-kandang');
							// var lampiran_kandangs = $.map( table_lampiran_kandang.find('td input:file'), function( ipt ){
							// 	if (!empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
							// 		var filename = $(ipt).data('old');
							// 		if ( !empty( $(ipt).val() ) ) {
							// 			var __file = $(ipt).get(0).files[0];
							// 			formData.append('files[]', __file);

							// 			filename = __file.name;
							// 		};

							// 		return {
							// 			'id' : $(ipt).closest('tr').attr('data-idnama'),
							// 			'name' : filename,
							// 			'sha1' : $(ipt).attr('data-sha1'),
							// 			'old' : $(ipt).data('old')
							// 		};
							// 	}
							// });

							var __kandang = {
								'id_kdg' : id_kdg,
								'grup' : grup,
								'no' : no_kandang,
								'kapasitas' : kapasitas,
								'tipe' : tipe_kandang,
								'unit' : unit,
								'status' : status,
								'ongkos_angkut' : ongkos_angkut,
								'alamat' : alamat_kandang,
								'bangunans' : bangunan_kandangs,
								// 'lampirans' : lampiran_kandangs
							};
							return __kandang;

						});

						return {
							'mitra_mapping' : mitra_mapping,
							'perwakilan_id' : perwakilan_id,
							'nim' : nim,
							'd_kandangs' : kandangs
						};
					});


					var data_mitra = {
						'id_mitra' : id_mitra,
						'jenis_mitra' : jenis_mitra,
						'perusahaan' : perusahaan,
						'ktp' : ktp,
						'nama' : nama,
						'npwp' : npwp,
						'skb' : skb,
						'tgl_habis_skb' : tgl_habis_skb,
						'telepons' : telepons,
						'alamat' : alamat_mitra,
						// 'lampirans' : lampiran_mitra,
						// 'lampirans_jaminan' : lampirans_jaminan,
						'd_bank' : data_bank,
						'keterangan_jaminan' : keterangan_jaminan,
						'd_perwakilans' : perwakilans
					};

					// console.log(data_mitra);
					// formData.append('data_mitra', JSON.stringify(data_mitra));
					// ptk.execSimpanPerwakilanAfterApprove(formData);

					ptk.execSimpanPerwakilanAfterApprove(data_mitra);
				}
			});
		}
	}, // end - simpanPerwakilanAfterApprove

	execSimpanPerwakilanAfterApprove: function(data) {
		$.ajax({
			url :'parameter/Peternak/save_after_approve',
			type : 'POST',
			dataType : 'JSON',
			data : {
				'params': data
			},
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if(data.status){
					// bootbox.alert(data.message,function(){
						// ptk.set_pagination();
                        // ptk.load_form(data.content.id);

						ptk.uploadFile(data.content.id);
					// });
				}else{
					bootbox.alert(data.message);
				}
			},
		});
	}, // end - execSimpanPerwakilanAfterApprove

	// execSimpanPerwakilanAfterApprove: function(formData) {
	// 	$.ajax({
	// 		url :'parameter/Peternak/save_after_approve',
	// 		type : 'post',
	// 		data : formData,
	// 		beforeSend : function(){
	// 			showLoading();
	// 		},
	// 		success : function(data){
	// 			hideLoading();
	// 			if(data.status){
	// 				bootbox.alert(data.message,function(){
	// 					ptk.set_pagination();
    //                     ptk.load_form(data.content.id);
	// 				});
	// 			}else{
	// 				bootbox.alert(data.message);
	// 			}
	// 		},
	// 		contentType : false,
	// 		processData : false,
	// 	});

	// 	type : 'POST',
	// 	dataType : 'JSON',
	// }, // end - execSimpanPerwakilanAfterApprove

	uploadFile: function(id) {
		var div_action = $('div#action');

		var idx_lampirans = 0;
		var lampirans = [];

		var formData = new FormData();
		$.map( $(div_action).find('div#lampiran_mitra td input:file'), function(ipt){
			var key = $(ipt).closest('tr').attr('data-idnama');
			
			var name = null;

			if (!empty( $(ipt).val() )) {
				var __file = $(ipt).get(0).files[0];
				name = __file.name;

				formData.append('files[]', __file);
			}

			lampirans[idx_lampirans] = {
				'id' : $(ipt).closest('tr').attr('data-idnama'),
				'name' : name,
				'sha1' : $(ipt).attr('data-sha1'),
				'key' : key,
				'old' : $(ipt).data('old')
			};

			idx_lampirans++;
		});

		$.map( $(div_action).find('div#lampiran_jaminan_mitra td input:file'), function(ipt){
			var key = $(ipt).closest('tr').attr('data-idnama');
			
			var name = null;

			if (!empty( $(ipt).val() )) {
				var __file = $(ipt).get(0).files[0];
				name = __file.name;

				formData.append('files[]', __file);
			}

			lampirans[idx_lampirans] = {
				'id' : $(ipt).closest('tr').attr('data-idnama'),
				'name' : name,
				'sha1' : $(ipt).attr('data-sha1'),
				'key' : key,
				'old' : $(ipt).data('old')
			};

			idx_lampirans++;
		});

		$.map( $(div_action).find('div[name=data-perwakilan]'), function(div_perwakilan){
			var perwakilan_id = $(div_perwakilan).find('select[name=perwakilan]').val();

			$.map( $(div_perwakilan).find('div[name=data-kandang]'), function(div_kandang){
				var no_kandang = $(div_kandang).find('input[name=no-kandang]').val();

				var table_lampiran_kandang = $(div_kandang).find('table.lampiran-kandang');
				$.map( $(table_lampiran_kandang).find('td input:file'), function( ipt ){
					var key = perwakilan_id+'_'+no_kandang+'_'+$(ipt).closest('tr').attr('data-idnama');
			
					var name = null;

					if (!empty( $(ipt).val() )) {
						var __file = $(ipt).get(0).files[0];
						name = __file.name;

						formData.append('files[]', __file);
					}

					lampirans[idx_lampirans] = {
						'id' : $(ipt).closest('tr').attr('data-idnama'),
						'name' : name,
						'sha1' : $(ipt).attr('data-sha1'),
						'key' : key,
						'old' : $(ipt).data('old')
					};

					idx_lampirans++;
				});
			});
		});

		var data = {
			'id': id,
			'lampirans': lampirans,
			'idx_upload': idxUploadFile
		};
		formData.append('data', JSON.stringify(data));

		$.ajax({
			url :'parameter/Peternak/uploadFile',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading('Proses upload file lampiran . . .');
			},
			success : function(data){
				if(data.status == 1){
					if ( idxUploadFile < lampirans.length ) {
						idxUploadFile++;

						ptk.uploadFile(id);
					} else {
						hideLoading();
						bootbox.alert(data.message,function() {
							ptk.set_pagination();
                        	ptk.load_form(data.content.id);

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

	deleteMitra : function(elm) {
		var id = $(elm).data('id');

		bootbox.confirm('Apakah anda yakin ingin menghapus data Peternak ?', function (result) {
			if ( result ) {
				$.ajax({
					url :'parameter/Peternak/delete',
					type : 'post',
					dataType : 'json',
					data : {'params': id},
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if(data.status){
							bootbox.alert(data.message,function(){
								ptk.set_pagination();
							});
						}else{
							bootbox.alert(data.message);
						}
					}
				});
			}
		});
	}, // end - deleteMitra

	ack_reject : function (elm) {
		var action = $(elm).attr('data-action');
		// collect id mitra
		var ids = [];

		// if ( $('div#index').length > 0 ) {
		// 	if ( $('[name=mark]:checked').length > 0 ){
		// 		ids = $.map($('[name=mark]:checked'), function(icheck) {
		// 			return $(icheck).val();
		// 		});
		// 	}
		// }else 
		if( $('div#view_data_mitra').length > 0 ) {
			var id = $(elm).attr('data-id');
			ids = [id];
		}

		if (ids.length > 0) {
			bootbox.confirm('Data mitra akan di-' + action, function(result){
				if (result) {
					$.ajax({
						url : 'parameter/Peternak/ackReject',
						data : {'ids' : ids, 'action' : action},
						dataType : 'JSON',
						type : 'POST',
						beforeSend : function () {
							showLoading();
						},
						success : function(data){
							hideLoading();
							if(data.status){
								bootbox.alert(data.message,function(){
									ptk.getLists();

									if ( action != 'approve' ) {
			                        	ptk.load_form(data.content.id);
									};
								});
							}else{
								bootbox.alert(data.message);
							}
						},
					});
				}
			});
		}else{
			bootbox.alert('Tidak ada data mitra yang akan di-' + action + '!');
		}
	}, //end - ack_reject

	onChangeAkunRK : function (elm) {
		var row = $(elm).closest('tr');
		var dKeterangan = $(elm).find('option:selected').attr('data-keterangan');
		var dPosisi = $(elm).find('option:selected').attr('data-posisi');

		row.find('input[name=keterangan]').val(dKeterangan + ' - ');
		if (dPosisi == 'Debet') {
			row.find('input[name=kredit]').val('0').prop('disabled', true);
			row.find('input[name=debet]').val('0').prop('disabled', false);
		}else if (dPosisi == 'Kredit') {
			row.find('input[name=debet]').val('0').prop('disabled', true);
			row.find('input[name=kredit]').val('0').prop('disabled', false);
		}else{
			row.find('input[name=debet]').val('0').prop('disabled', true);
			row.find('input[name=kredit]').val('0').prop('disabled', true);
		}
	}, // end - onChangeAkunRK

	getDataRK : function(isScrollDown = false){
		var mitra_id = $('input[name=nama-mitra]').attr('data-id');
		var nim_id = $('select[name=nim]').val();
		var kode = $('input[name=filter-trx]:checked').val();
		var row = $('input[name=request-row]').val();

		$.ajax({
			url : 'master/mitra/getDataRK',
			data : {'row':row, 'nim_id' : nim_id, 'mitra_id' : mitra_id, 'kode' : kode},
			type : 'GET',
			dataType : 'JSON',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				var jut = numeral.formatInt(data.content.jut);
				var populasi = numeral.formatInt(data.content.populasi);

				if (data.content.jut > 0) {
					$('input[name=kewajiban-jut]').prop('readonly', true);
					$('#btnJut').hide();
				}else{
					$('input[name=kewajiban-jut]').prop('readonly', false);
					$('#btnJut').show();
				}

				if (nim_id == 'ALL') {
					$('input[name=kewajiban-jut]').prop('readonly', true);
					$('#btnJut').hide();
				}


				$('input[name=kewajiban-jut]').val(jut);
				$('input[name=populasi]').val(populasi);

				var eKandang = $('tr.form-input-data').find('select[name=kandang]');
				eKandang.html('<option value="">-</option>');
				$.each(data.content.kandangs, function(){
					eKandang.append('<option value="'+this.id+'">'+this.kandang+'</option>');
				});

				$('tr.form-input-data').nextAll().remove();
				$('tr.form-input-data').after( data.content.histories );
				if (isScrollDown) {
					$('html, body').animate({
						scrollTop: $("tr.total").offset().top
					}, 1500);
				}
			}
		});
	}, // getDataRK

	saveRK : function (elm) {

		// ubah value input, textarea menjadi uppercase
		$.map($('input:text, textarea'), function(_e){ $(_e).val( $(_e).val().toUpperCase() ); });

		var nim_id = $('select[name=nim]').val();
		if (nim_id == 'ALL') {
			alertDialog('NIM mitra tidak boleh ALL');
		}else{

			var row = $(elm).closest('tr');
			var tgl_buku = dateSQL(row.find('input[name=tanggal]').datepicker('getDate'));
			var kode_akun = row.find('select[name=akun]').val();
			var bukti = row.find('input[name=no-bukti]').val();
			var phb = row.find('input[name=no-phb]').val();
			var nkk = row.find('input[name=no-nkk]').val();
			var siklus = row.find('input[name=siklus]').val();
			var kandang_id = row.find('select[name=kandang]').val();
			var keterangan = row.find('input[name=keterangan]').val();
			var debet = numeral.unformat( row.find('input[name=debet]').val() );
			var kredit = numeral.unformat( row.find('input[name=kredit]').val() );
			var jenis_trx = row.find('select[name=akun]').find('option:selected').attr('data-posisi');

			var data_params = {
				'jenis_trx' : jenis_trx,
				'nim_id' : nim_id,
				'tgl_buku' : tgl_buku,
				'kode_akun' : kode_akun,
				'bukti' : bukti,
				'phb' : phb,
				'nkk' : nkk,
				'siklus' : siklus,
				'kandang_id' : kandang_id,
				'keterangan' : keterangan,
				'debet' : debet,
				'kredit' : kredit,
			};

			var formData = new FormData();
			var __file = row.find('input[name=lampiran]').get(0).files[0];
			formData.append('attach', __file);
			formData.append('params', JSON.stringify(data_params));

			bootbox.confirm('Simpan transaksi rekening koran.', function(result){
				if (result) {
					ptk.executeSaveRK(formData);
				}
			});
		}
	}, // end - saveRK

	executeSaveRK : function (formData) {
		$.ajax({
			url : 'master/mitra/saveRK',
			data : formData,
			dataType : 'JSON',
			type : 'POST',
			beforeSend : function () {
				showLoading();
			},
			success : function(data){
				if (data.status) {
					bootbox.alert(data.message, function(){
						$('tr.form-input-data').find('input, select').val('');
						ptk.getDataRK(true);
					});
				}
				hideLoading();
			},
			contentType : false,
			processData : false,
		});
	}, // end - executeSaveRK

	collapseRow: function() {
		// NOTE: setup untuk expand collapse row table detail-keranjang
		$('tr.header td span.btn-collapse').click(function() {
			var row = $(this).closest('tr.header');
			$(row).toggleClass('expand').nextUntil('tr.header').slideToggle(100);
			var _el = $(row).closest('tr').find('span.btn-collapse');
			if (_el.hasClass('fa-folder-open')) {
				_el.removeClass('fa-folder-open');
				_el.addClass('fa-folder');
			} else {
				_el.removeClass('fa-folder');
				_el.addClass('fa-folder-open');
			}
		});
	}, // end - collapseRow

	updateAckRK : function (elm, act) {
		var row = $(elm).closest('tr');
		var row_p = $(row).closest('tr.trx').prev('tr.header');

		var nama_mitra = row_p.find('td.mitra').text();
		var nim = row.attr('data-nim');

		var ack_ids = [];
		var reject_ids = [];

		// NOTE: collect data id yang akan di-ack + reject
		if (act == 'ack') {
			ack_ids = $.map( row.prevAll().andSelf(), function (row_rk) {
				return $(row_rk).attr('data-id');
			} );

			reject_ids = $.map( row.nextAll(), function (row_rk) {
				return $(row_rk).attr('data-id');
			} );
		}

		// NOTE: collect data id yang akan direject saja
		else if (act == 'reject') {
			reject_ids = $.map( row.nextAll().andSelf(), function (row_rk) {
				return $(row_rk).attr('data-id');
			} );
		}

		var message_trx = '';
		if (ack_ids.length > 0) {
			message_trx += '<span class="text-primary"><b>Data ACK</b></span> : (TRX) ' + ack_ids.join(', ') + '<hr>';
		}
		if (reject_ids.length > 0) {
			message_trx += '<span class="text-danger"><b>Data REJECT</b></span> : (TRX) ' + reject_ids.join(', ');
		}

		var message = '<b>'+ act.toUpperCase() +' Rekening Koran</b> <br> NIM. <b>' + nim + '</b> (' + nama_mitra + ')<br>' + message_trx;
		bootbox.confirm(message, function (result) {
			if (result) {

				var data_params = {
					'action' : act,
					'nim_id' : nim,
					'ack_ids' : ack_ids,
					'reject_ids' : reject_ids
				};

				$.ajax({
					url : 'master/mitra/updateAckRK',
					data : {'params' : data_params},
					dataType : 'JSON',
					type : 'POST',
					beforeSend : function () {
						showLoading();
					},
					success : function(data){
						if (data.status) {
							bootbox.alert(data.message, function(){
								row_p.next('tr.trx').remove();
								row_p.remove();
							});
						}
						hideLoading();
					}
				});
			} // end if confirm result
		});
	}, // end - updateAckRK

	saveJut : function (elm) {
		var nim = $('select[name=nim]').text();
		var nim_id = $('select[name=nim]').val();
		var v_jut = $('input[name=kewajiban-jut]').val();
		var jut = numeral.unformat( v_jut );

		var data_params = {
			'nim_id' : nim_id,
			'jut' : jut
		};

		if (jut > 0) {
			bootbox.confirm('Simpan JUT (' + nim + ') : ' + v_jut, function(result){
				if (result) {
					$.ajax({
						url : 'master/mitra/saveJut',
						data : {'params' : data_params},
						dataType : 'JSON',
						type : 'POST',
						beforeSend : function () {
							showLoading();
						},
						success : function(data){
							if (data.status) {
								bootbox.alert(data.message, function(){
									$('input[name=kewajiban-jut]').prop('readonly', true);
									$(elm).hide();
								});
							}
							hideLoading();
						}
					});
				} // end if confirm result
			});
		}else{
			alertDialog('JUT tidak boleh kurang dari <b>0</b>')
		}
	}, // end  - saveJut

	form_export_excel : function () {
		$.get('parameter/Peternak/form_export_excel',{
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
				url : 'parameter/Peternak/verifikasi_export_excel',
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

						window.open('parameter/Peternak/export_excel', '_blank');
					} else {
						hideLoading();
						bootbox.alert( data.message );
					}
				}
			});
		}
	}, // end - verifikasi_export_excel

	formPindahPerusahaan: function(elm) {
		var id = $(elm).attr('data-id');

		var params = {
			'id': id
		};

		$.get('parameter/Peternak/formPindahPerusahaan',{
			'params': params
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '40%', 'max-width': '100%'});

				$(this).find('select.perusahaan').select2();
				$(this).removeAttr('tabindex');
            });
        },'html');
	}, // end - formPindahPerusahaan

	batalPindah: function () {
		$('.modal').modal('hide');
	}, // end - batalPindah

	pindahPerusahaan: function (elm) {
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
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var nama_peternak = $(elm).attr('data-np');
			var nama_perusahaan = $(modal_body).find('select.perusahaan option:selected').text();

			bootbox.confirm('Apakah anda yakin ingin memindah peternak <b>'+nama_peternak+'</b> ke perusahaan <b>'+nama_perusahaan+'</b> ?', function (result) {
				if ( result ) {
					var params = {
						'id': $(elm).attr('data-id'),
						'perusahan_baru': $(modal_body).find('select.perusahaan').select2().val()
					};

					$.ajax({
						url : 'parameter/Peternak/pindahPerusahaan',
						data : {
							'params' : params
						},
						dataType : 'JSON',
						type : 'POST',
						beforeSend : function () {
							showLoading('Proses pindah perusahaan . . .');
						},
						success : function(data){
							hideLoading();
							if (data.status == 1) {
								bootbox.alert(data.message, function() {
									$('.modal').modal('hide');

									ptk.set_pagination();
                        			ptk.load_form(data.content.id);
								});
							} else {
								bootbox.alert( data.message );
							}
						}
					});
				}
			});
		}
	}, // end - pindahPerusahaan
};

ptk.start_up();
