var odvp = {
	start_up: function(){
		odvp.setting_up();
		odvp.get_lists_doc();

		$('select.filter').find('option[value=submit]').attr('selected', 'selected');
	}, // end - start_up

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });

        $('#datetimepicker2').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });

        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - setting_up

	get_lists_doc: function() {
		var div_doc = $('div#doc');
		var table = $(div_doc).find('table');
		var tbody = $(table).find('tbody');

		var start_date = $('[name=startDate]').data('DateTimePicker').date();
		var end_date = $('[name=endDate]').data('DateTimePicker').date();

		if ( !empty(start_date) && !empty(end_date) ) {
			var start_date = dateSQL( $('[name=startDate]').data('DateTimePicker').date() );
			var end_date = dateSQL( $('[name=endDate]').data('DateTimePicker').date() );
		} else {
			start_date = null;
			end_date = null;
		};

		var date = {
			'start_date': start_date,
			'end_date': end_date,
		};

		$.ajax({
			url: 'transaksi/ODVP/get_lists_doc',
			data: {
				'params': date
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				if ( data.status == 1 ) {
					// $(tbody).find('tr').remove();
					$(tbody).html(data.content);
				};

				hideLoading();
			},
	    });
	}, // end - get_lists_doc

	get_lists_voadip: function() {
		var div_doc = $('div#voadip');
		var table = $(div_doc).find('table');
		var tbody = $(table).find('tbody');

		var start_date = $(div_doc).find('[name=startDate]').data('DateTimePicker').date();
		var end_date = $(div_doc).find('[name=endDate]').data('DateTimePicker').date();

		if ( !empty(start_date) && !empty(end_date) ) {
			var start_date = dateSQL( $(div_doc).find('[name=startDate]').data('DateTimePicker').date() );
			var end_date = dateSQL( $(div_doc).find('[name=endDate]').data('DateTimePicker').date() );
		} else {
			start_date = null;
			end_date = null;
		};

		var date = {
			'start_date': start_date,
			'end_date': end_date,
		};

		$.ajax({
			url: 'transaksi/ODVP/get_lists_voadip',
			data: {
				'params': date
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				if ( data.status == 1 ) {
					// $(tbody).find('tr').remove();
					$(tbody).html(data.content);
				};

				hideLoading();
			},
	    });
	}, // end - get_lists_voadip

	order_doc_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/order_doc_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('select.supplier').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				$(this).find('select.jns_doc').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				$(this).find('div[name=tgl_tiba_kdg]').data('DateTimePicker').date( moment(tgl_docin) );

				$(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				$(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_doc_form

	order_doc_edit_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/order_doc_edit_form',{
				no_order : no_order,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('select.supplier').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				$(this).find('select.jns_doc').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				var tgl_tiba_kdg = $('div[name=tgl_tiba_kdg]').data('tgl');

				$(this).find('div[name=tgl_tiba_kdg]').data('DateTimePicker').date( moment(tgl_tiba_kdg) );

				$(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				$(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_doc_edit_form

	order_doc_view_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/order_doc_view_form',{
				no_order : no_order,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('select.supplier').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				$(this).find('select.jns_doc').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				// var tgl_tiba_kdg = $('div[name=tgl_tiba_kdg]').data('tgl');

				// $(this).find('div[name=tgl_tiba_kdg]').data('DateTimePicker').date( moment(tgl_tiba_kdg) );

				$(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				$(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_doc_view_form

	terima_doc_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/terima_doc_form',{
				no_order : no_order,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

		        $(this).find('select.supplier').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				$(this).find('select.jns_doc').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

		        $(this).find('div#datetimepicker1').data('DateTimePicker').date( moment(tgl_docin) );
			});
		},'html');
	}, // end - terima_doc_form

	terima_doc_edit_form: function(elm) {
		var tr = $(elm).closest('tr');

		var no_terima = $(elm).data('terima');
		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/terima_doc_edit_form',{
				no_terima : no_terima,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

		        $(this).find('select.supplier').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				$(this).find('select.jns_doc').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				var tgl_tiba_kdg = $('div[name=tgl_tiba_kdg]').data('tgl');
				var tgl_kirim_doc = $('div[name=tgl_kirim_doc]').data('tgl');
		        $(this).find('div#datetimepicker1').data('DateTimePicker').date( moment(tgl_tiba_kdg) );
		        $(this).find('div#datetimepicker2').data('DateTimePicker').date( moment(tgl_kirim_doc) );
			});
		},'html');
	}, // end - terima_doc_edit_form

	terima_doc_view_form: function(elm) {
		var tr = $(elm).closest('tr');

		var no_terima = $(elm).data('terima');
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();

		$.get('transaksi/ODVP/terima_doc_view_form',{
				no_terima : no_terima,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				$(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
			});
		},'html');
	}, // end - terima_doc_edit_form

	order_voadip_form: function(elm) {
		var tr = $(elm).closest('tr');

		$.get('transaksi/ODVP/order_voadip_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').addClass('voadip');

				$(this).find('div[name=tgl_order_voadip]').data('DateTimePicker').date( moment(new Date()) );
			});
		},'html');
	}, // end - order_voadip_form

	order_voadip_edit_form: function(elm) {
		var tr = $(elm).closest('tr');
		var no_order = $(elm).data('id');

		$.get('transaksi/ODVP/order_voadip_edit_form',{
			'no_order': no_order
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				var table = $(this).find('table');
				var tbody = $(this).find('tbody');

				$.map( $(tbody).find('tr'), function(tr) {
					var sel_kategori = $(tr).find('select.kategori');
					var sel_kantor = $(tr).find('select.kantor');

					odvp.set_item_voadip(sel_kategori, 'EDIT');
					odvp.set_item_pwk(sel_kantor, 'EDIT');
				});


				$(this).find('.modal-dialog').addClass('voadip');
			});
		},'html');
	}, // end - order_voadip_edit_form

	order_voadip_view_form: function(elm) {
		var tr = $(elm).closest('tr');
		var no_order = $(elm).data('id');

		$.get('transaksi/ODVP/order_voadip_view_form',{
			'no_order': no_order
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').addClass('voadip');
			});
		},'html');
	}, // end - order_voadip_view_form

	hit_box: function(elm) {
		var div = $(elm).closest('div.form-group');

		var ekor = numeral.unformat( $(elm).val() );
		var box = ekor / 100;

		$(div).find('input.box').val( numeral.formatInt(box) );
	}, // end - hit_box

	set_item_voadip: function(elm, edit=null) {
		var tr = $(elm).closest('tr');
		var sel_brg = $(tr).find('select.barang');

		var val = $(elm).val();

		$.ajax({
			url: 'transaksi/ODVP/get_data_voadip',
			data: {
				'kategori': val
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				// showLoading();
			},
			success: function(data) {
				// hideLoading();
				if ( data.status == 1 ) {
					var opt = '<option value="">-- Pilih Barang --</option>';
					if ( data.content.length > 0 ) {
						for (var i = 0; i < data.content.length; i++) {
							var selected = null;

							if ( !empty(edit) ) {
								var val_barang = $(sel_brg).data('barang');
								if ( data.content[i].kode == val_barang ) {
									selected = 'selected';
								};
							};

							opt += '<option value="'+data.content[i].kode+'" '+selected+' >'+data.content[i].nama+'</option>';
						};
					};

					sel_brg.html(opt);

				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_item_voadip

	set_item_pwk: function(elm, edit=null) {
		var tr = $(elm).closest('tr');
		var sel_peternak = $(tr).find('select.peternak');

		var val = $(elm).val();

		$(tr).find('input.alamat_peternak').val("");

		$.ajax({
			url: 'transaksi/ODVP/get_list_peternak',
			data: {
				'id_pwk': val
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				// showLoading();
			},
			success: function(data) {
				// hideLoading();
				if ( data.status == 1 ) {
					var opt = '<option value="">-- Pilih Peternak --</option>';
					if ( data.content.length > 0 ) {
						for (var i = 0; i < data.content.length; i++) {
							var selected = null;

							if ( !empty(edit) ) {
								var val_peternak = $(sel_peternak).data('peternak');
								if ( data.content[i].nomor == val_peternak ) {
									selected = 'selected';
								};
							};

							opt += '<option value="'+data.content[i].nomor+'" data-alamat="'+data.content[i].alamat+'" '+selected+' >'+data.content[i].nama+'</option>';
						};
					};

					sel_peternak.html(opt);

					odvp.set_alamat(sel_peternak);
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_item_pwk

	set_alamat: function(elm) {
		var tr = $(elm).closest('tr');
		var ipt_alamat = $(tr).find('input.alamat_peternak');

		var opt = $(elm).find('option:selected');
		var alamat = $(opt).data('alamat');

		$(ipt_alamat).val(alamat);
	}, // end - set_alamat

	hit_total_order_voadip: function(elm) {
		var tr = $(elm).closest('tr');
		var ipt_harga = numeral.unformat($(tr).find('input.harga').val());
		var ipt_jumlah = numeral.unformat($(tr).find('input.jumlah').val());

		var total = ipt_harga * ipt_jumlah;

		$(tr).find('input.total').val( numeral.formatDec(total) );
	}, // end - hit_total_order_voadip

	save_order_doc: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]:not(.supplier, .jns_doc)'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		var sel_supl = $('select.supplier');
		if ( empty(sel_supl.val()) ) {
			err++;
			$(sel_supl).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_supl).next().find('.select2-selection').removeClass('has-error');
		};

		var sel_doc = $('select.jns_doc');
		if ( empty(sel_doc.val()) ) {
			err++;
			$(sel_doc).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_doc).next().find('.select2-selection').removeClass('has-error');
		};

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Order DOC ?', function(result) {
				if ( result ) {
					var data = {
						'noreg': $(div).find('input[type=hidden]').data('noreg'),
						'supplier': $(div).find('select.supplier').select2('val'),
						'item': $(div).find('select.jns_doc').select2('val'),
						'jml_ekor': numeral.unformat( $(div).find('input.ekor').val() ),
						'jml_box': numeral.unformat( $(div).find('input.box').val() ),
						'rencana_tiba': dateSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'keterangan': $(div).find('textarea.ket').val()
					};

					odvp.exec_save_order_doc(data);
				};
			});
		};
	}, // end - save_order_doc

	exec_save_order_doc: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/save_order_doc',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_doc();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_save_order_doc

	edit_order_doc: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]:not(.supplier, .jns_doc)'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		var sel_supl = $('select.supplier');
		if ( empty(sel_supl.val()) ) {
			err++;
			$(sel_supl).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_supl).next().find('.select2-selection').removeClass('has-error');
		};

		var sel_doc = $('select.jns_doc');
		if ( empty(sel_doc.val()) ) {
			err++;
			$(sel_doc).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_doc).next().find('.select2-selection').removeClass('has-error');
		};

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Order DOC ?', function(result) {
				if ( result ) {
					var data = {
						'no_order': $(div).find('input.no_order').val(),
						'noreg': $(div).find('input[type=hidden]').data('noreg'),
						'supplier': $(div).find('select.supplier').select2('val'),
						'item': $(div).find('select.jns_doc').select2('val'),
						'jml_ekor': numeral.unformat( $(div).find('input.ekor').val() ),
						'jml_box': numeral.unformat( $(div).find('input.box').val() ),
						'rencana_tiba': dateSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'keterangan': $(div).find('textarea.ket').val(),
						'version': $(div).find('input.no_order').data('version')
					};

					odvp.exec_edit_order_doc(data);
				};
			});
		};
	}, // end - edit_order_doc

	exec_edit_order_doc: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/edit_order_doc',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_doc();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_order_doc

	save_terima_doc: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]:not(.supplier, .jns_doc)'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		var sel_supl = $('select.supplier');
		if ( empty(sel_supl.val()) ) {
			err++;
			$(sel_supl).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_supl).next().find('.select2-selection').removeClass('has-error');
		};

		var sel_doc = $('select.jns_doc');
		if ( empty(sel_doc.val()) ) {
			err++;
			$(sel_doc).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_doc).next().find('.select2-selection').removeClass('has-error');
		};

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Terima DOC ?', function(result) {
				if ( result ) {
					var data = {
						'no_order' : $(div).find('input.no_order').val(),
						'no_sj' : $(div).find('input.no_sj').val(),
						'nopol' : $(div).find('input.nopol').val(),
						'datang' : dateTimeSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'kirim': dateTimeSQL( $('[name=tgl_kirim_doc]').data('DateTimePicker').date() ),
						'supplier' : $(div).find('select.supplier').select2('val'),
						'jml_ekor' : numeral.unformat( $(div).find('input.ekor').val() ),
						'jml_box' : numeral.unformat( $(div).find('input.box').val() ),
						'kondisi' : $(div).find('input.kondisi').val(),
						'keterangan' : $(div).find('textarea.ket').val()
					};

					odvp.exec_save_terima_doc(data);
				};
			});
		};
	}, // end - save_terima_doc

	exec_save_terima_doc: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/save_terima_doc',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_doc();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_save_terima_doc

	edit_terima_doc: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]:not(.supplier, .jns_doc)'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		var sel_supl = $('select.supplier');
		if ( empty(sel_supl.val()) ) {
			err++;
			$(sel_supl).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_supl).next().find('.select2-selection').removeClass('has-error');
		};

		var sel_doc = $('select.jns_doc');
		if ( empty(sel_doc.val()) ) {
			err++;
			$(sel_doc).next().find('.select2-selection').addClass('has-error');
		} else {
			$(sel_doc).next().find('.select2-selection').removeClass('has-error');
		};

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Terima DOC ?', function(result) {
				if ( result ) {
					var data = {
						'no_terima' : $(div).find('input[type=hidden]').data('terima'),
						'no_order' : $(div).find('input.no_order').val(),
						'no_sj' : $(div).find('input.no_sj').val(),
						'nopol' : $(div).find('input.nopol').val(),
						'datang' : dateTimeSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'kirim': dateTimeSQL( $('[name=tgl_kirim_doc]').data('DateTimePicker').date() ),
						'supplier' : $(div).find('select.supplier').select2('val'),
						'jml_ekor' : numeral.unformat( $(div).find('input.ekor').val() ),
						'jml_box' : numeral.unformat( $(div).find('input.box').val() ),
						'kondisi' : $(div).find('input.kondisi').val(),
						'keterangan' : $(div).find('textarea.ket').val(),
						'version' : $(div).find('input[type=hidden]').data('version')
					};

					odvp.exec_edit_terima_doc(data);
				};
			});
		};
	}, // end - edit_terima_doc

	exec_edit_terima_doc: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/edit_terima_doc',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_doc();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_terima_doc

	save_order_voadip: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			var data_detail = $.map( $(div).find('table tbody tr'), function(tr) {
				var kantor = $(tr).find('select.kantor').val();
				var peternak = $(tr).find('select.peternak').val();

				var kirim_ke = 'kantor';
				var alamat = $(tr).find('select.kantor option:selected').text();
				if ( !empty(kantor) && !empty(peternak) ) {
					kirim_ke = 'peternak';
					alamat = $(tr).find('input.alamat_peternak').val();
				};

				var data = {
					'kategori': $(tr).find('select.kategori').val(),
					'barang': $(tr).find('select.barang').val(),
					'kemasan': $(tr).find('input.kemasan').val(),
					'harga': numeral.unformat( $(tr).find('input.harga').val() ),
					'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
					'total': numeral.unformat( $(tr).find('input.total').val() ),
					'kantor': $(tr).find('select.kantor').val(),
					'peternak': $(tr).find('select.peternak').val(),
					'alamat': alamat,
					'kirim_ke': kirim_ke
				};

				return data;
			});

			if ( data_detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data Order Voadip ?', function(result) {
					if ( result ) {
						var data = {
							'no_order': $(div).find('input.no_order').val(),
							'tanggal': dateSQL( $('[name=tgl_order_voadip]').data('DateTimePicker').date() ),
							'supplier': $(div).find('select.supplier').val(),
							'detail': data_detail
						};

						console.log(data);
						odvp.exec_save_order_voadip(data);
					};
				});
			} else {
				bootbox.alert('Data detail masih kosong.');
			};
		};
	}, // end - save_order_voadip

	exec_save_order_voadip: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/save_order_voadip',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_voadip();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_save_order_voadip

	edit_order_voadip: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			var data_detail = $.map( $(div).find('table tbody tr'), function(tr) {
				var kantor = $(tr).find('select.kantor').val();
				var peternak = $(tr).find('select.peternak').val();

				var kirim_ke = 'kantor';
				var alamat = $(tr).find('select.kantor option:selected').text();
				if ( !empty(kantor) && !empty(peternak) ) {
					kirim_ke = 'peternak';
					alamat = $(tr).find('input.alamat_peternak').val();
				};

				var data = {
					'kategori': $(tr).find('select.kategori').val(),
					'barang': $(tr).find('select.barang').val(),
					'kemasan': $(tr).find('input.kemasan').val(),
					'harga': numeral.unformat( $(tr).find('input.harga').val() ),
					'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
					'total': numeral.unformat( $(tr).find('input.total').val() ),
					'kantor': $(tr).find('select.kantor').val(),
					'peternak': $(tr).find('select.peternak').val(),
					'alamat': alamat,
					'kirim_ke': kirim_ke
				};

				return data;
			});

			if ( data_detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data Order Voadip ?', function(result) {
					if ( result ) {
						var data = {
							'no_order': $(div).find('input.no_order').val(),
							'tanggal': dateSQL( $('[name=tgl_order_voadip]').data('DateTimePicker').date() ),
							'supplier': $(div).find('select.supplier').val(),
							'version': $(div).find('input.no_order').data('version'),
							'detail': data_detail
						};

						console.log(data);
						odvp.exec_edit_order_voadip(data);
					};
				});
			} else {
				bootbox.alert('Data detail masih kosong.');
			};
		};
	}, // end - edit_order_voadip

	exec_edit_order_voadip: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/edit_order_voadip',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						odvp.setting_up();
						odvp.get_lists_voadip();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_order_voadip

	filter_by_status: function(elm) {
		var div = $(elm).closest('div#doc');
		var table = $(div).find('table.tbl_odvp');

		var val = $(elm).val();

		$(table).find('tbody tr').removeClass('hide');
		if ( val == 'submit' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter == 'ordered' || filter == 'terima' ) {
					$(tr).addClass('hide');
				};
			});
		} else if ( val == 'ordered' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter != 'ordered' || empty(filter) ) {
					$(tr).addClass('hide');
				};
			});
		} else if ( val == 'terima' ) {
			$.map( $(table).find('tbody tr'), function(tr) {
				var filter = $(tr).data('filter');
				if ( filter != 'terima' || empty(filter) ) {
					$(tr).addClass('hide');
				};
			});
		} else {
			$(table).find('tbody tr').removeClass('hide');
		};
	}, // end - filter_by_status

	addRowChild: function(elm) {
        let row = $(elm).closest('tr');
        let newRow = row.clone();
        // newRow.find('[name=tanggal]').val('');

        newRow.find('input, select').val('');
        row.find('.btn-ctrl').hide();
        row.after(newRow);
        
        let tbody = $(row).closest('tbody');
        if ( $(tbody).find('tr').length > 0 ) {
        	newRow.find('.btn_del_row_2x').removeClass('hide');
        };

        var sel_brg = $(newRow).find('select.barang');
        var sel_peternak = $(newRow).find('select.peternak');
        var opt_barang = '<option value="">-- Pilih Barang --</option>';
        var opt_peternak = '<option value="">-- Pilih Peternak --</option>';
		sel_brg.html(opt_barang);
		sel_peternak.html(opt_peternak);

        App.formatNumber();
    }, // end - addRowChild

    removeRowChild: function(elm) {
        let row = $(elm).closest('tr');
        if ($(row).prev('tr.child').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).addClass('inactive');
        }
    }, // end - removeRowChild
};

odvp.start_up();