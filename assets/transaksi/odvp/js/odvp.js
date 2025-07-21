var odvp = {
	start_up: function(){
		odvp.setting_up();
		odvp.get_lists_doc();
		odvp.set_table_page('.tbl_odvp');

		$('select.filter').find('option[value=submit]').attr('selected', 'selected');
	}, // end - start_up

	set_table_page : function(tbl_id){
        let _t_rdim = TUPageTable;
        _t_rdim.destroy();
        _t_rdim.setTableTarget(tbl_id);
        _t_rdim.setPages(['page1', 'page2']);
        _t_rdim.setHideButton(true);
        _t_rdim.onClickNext(function(){
            // console.log('Log onClickNext');
        });
        _t_rdim.start();
    }, // end - set_table_page

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

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

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

        $('[name=tgl_kirim]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$('[name=tglOrder]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$('div#pakan').find('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
			var perusahaan = $('select.perusahaan').select2().val();

			for (var i = 0; i < perusahaan.length; i++) {
				if ( perusahaan[i] == 'all' ) {
					$('select.perusahaan').select2().val('all').trigger('change');

					i = perusahaan.length;
				}
			}
		});
		
		// $('[name=tgl_kirim]').on('dp.change', function (e) {
		// 	odvp.set_item_pwk(this);
		// });
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

				$(this).find('select.perusahaan').select2({
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
		var nama_mitra = $(tr).find('td.nama_mitra').html().split('<br>');
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

				$(this).find('select.perusahaan').select2({
					theme: "classic",
					dropdownParent: $(this)
				});

				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				var tgl_order = $('div[name=tglOrder]').data('tgl');
				$(this).find('div[name=tglOrder]').data('DateTimePicker').date( moment(tgl_order) );

				var tgl_tiba_kdg = $('div[name=tgl_tiba_kdg]').data('tgl');
				$(this).find('div[name=tgl_tiba_kdg]').data('DateTimePicker').date( moment(tgl_tiba_kdg) );

				$(div_detail).find('div.nama_mitra b').html(nama_mitra[0] + ' ' + nama_mitra[1]+ ', KDG : ' + kandang + ', POPULASI : ' + populasi);
				$(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_doc_edit_form

	order_doc_view_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').html().split('<br>');
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

				$(div_detail).find('div.nama_mitra b').html(nama_mitra[0] + ' ' + nama_mitra[1]+ ', KDG : ' + kandang + ', POPULASI : ' + populasi);
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

		        odvp.setBindSHA1();
			});
		},'html');
	}, // end - terima_doc_form

	terima_doc_edit_form: function(elm) {
		var tr = $(elm).closest('tr');

		var id = $(elm).data('id');
		var noreg = $(elm).data('noreg');
		var no_order = $(tr).find('td.no_order').text();
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		$.get('transaksi/ODVP/terima_doc_edit_form',{
				id : id,
				no_order : no_order
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

		var id = $(elm).data('id');
		var nama_mitra = $(tr).find('td.nama_mitra').html().split('<br>');
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();

		$.get('transaksi/ODVP/terima_doc_view_form',{
				id : id,
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

				$(div_detail).find('div.nama_mitra b').html(nama_mitra[0] + ' ' + nama_mitra[1] + ', KDG : ' + kandang + ', POPULASI : ' + populasi);
			});
		},'html');
	}, // end - terima_doc_edit_form

	addRowChildVoadip: function(elm) {
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

        $(newRow).find('[name=tgl_kirim]').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$(newRow).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - addRowChild

	order_voadip_form: function(elm) {
		var tr = $(elm).closest('tr');

		showLoading();
		$.get('transaksi/ODVP/order_voadip_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			hideLoading();
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

		showLoading();
		$.get('transaksi/ODVP/order_voadip_edit_form',{
			'no_order': no_order
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			hideLoading();
			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				var table = $(this).find('table');
				var tbody = $(this).find('tbody');

				$.map( $(tbody).find('tr'), function(tr) {
					var sel_kategori = $(tr).find('select.kategori');
					odvp.set_item_voadip(sel_kategori, 'EDIT');

					var tgl_kirim = $(tr).find('[name=tgl_kirim]').data('tanggal');
					$(tr).find('[name=tgl_kirim]').data('DateTimePicker').date( moment(tgl_kirim) );
				});

				$(this).find('.modal-dialog').addClass('voadip');
				var tgl_order_voadip = $(this).find('div[name=tgl_order_voadip]').closest('div').data('tanggal');
				$(this).find('div[name=tgl_order_voadip]').data('DateTimePicker').date( moment(tgl_order_voadip) );
			});
		},'html');
	}, // end - order_voadip_edit_form

	order_voadip_view_form: function(elm) {
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

		odvp.hit_total_order_doc(elm);
	}, // end - hit_box

	hit_total_order_doc: function(elm) {
		var form = $(elm).closest('form');

		var harga = numeral.unformat( $(form).find('input.harga').val() );
		var ekor = numeral.unformat( $(form).find('input.ekor').val() );
		var total = harga * ekor;

		$(form).find('input.total').val( numeral.formatInt(total) );
	}, // end - hit_total_order_doc

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

							opt += '<option value="'+data.content[i].kode+'" data-hithrgjual="'+data.content[i].hit_hrg_jual+'" data-decimal="'+data.content[i].desimal_harga+'" '+selected+' >'+data.content[i].nama+'</option>';
						};
					};

					sel_brg.html(opt);

				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_item_voadip

	cek_decimal_harga: function(elm) {
		var tr = $(elm).closest('tr');
		var barang = $(elm).val();

		var data_tipe = 'decimal';
		var maxlength = 10;
		if ( !empty(barang) ) {
			var decimal = $(elm).find('option:selected').attr('data-decimal');

			if ( decimal > 2 ) {
				data_tipe += decimal;
				maxlength = 13;
			}
		}

		$(tr).find('.harga, .harga_jual, .total').attr('data-tipe', data_tipe);
		$(tr).find('.harga, .harga_jual, .total').attr('maxlength', maxlength);

		$(tr).find('.harga, .harga_jual, .total').each(function() {
			console.log( $(this).attr('data-tipe') );

			$(this).priceFormat(Config[$(this).attr('data-tipe')]);
		});
	}, // end - cek_decimal_barang

	set_item_pwk: function(elm, edit=null) {
		var tr = $(elm).closest('tr');
		var sel_peternak = $(tr).find('select.peternak');

		var tgl_docin = dateSQL( $(tr).find('[name=tgl_docin]').data('DateTimePicker').date() );
		var pwk = $(tr).find('select.kantor').val();

		$(tr).find('input.alamat_peternak').val("");

		if ( !empty( $(tr).find('[name=tgl_docin]').val() ) && !empty( pwk ) ) {
			let params = {
				'tgl_docin': tgl_docin,
				'id_pwk': pwk
			};

			$.ajax({
				url: 'transaksi/ODVP/get_list_peternak',
				data: {
					'params': params
				},
				type: 'POST',
				dataType: 'JSON',
				beforeSend: function() {},
				success: function(data) {
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

								opt += '<option value="'+data.content[i].nomor+'" data-noreg="'+data.content[i].noreg+'" data-alamat="'+data.content[i].alamat+'" '+selected+' >'+data.content[i].noreg + ' | ' + data.content[i].nama+'</option>';
							};
						};

						sel_peternak.html(opt);

						odvp.set_alamat(sel_peternak);
					} else {
						bootbox.alert(data.message);
					};
				},
		    });
		} else {
			var opt = '<option value="">-- Pilih Peternak --</option>';
			sel_peternak.html(opt);
		}
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
		var tbody = $(tr).closest('tbody');
		var table = $(tbody).closest('table');

		var ipt_harga = numeral.unformat($(tr).find('input.harga').val());
		var ipt_jumlah = numeral.unformat($(tr).find('input.jumlah').val());

		var total = ipt_harga * ipt_jumlah;

		var decimal = $(tr).find('input.total').attr('data-tipe').replace('decimal', '');
		var _total = numeral.formatDec(total);
		if ( !empty(decimal) ) {
			_total = numeral.formatDec(total, decimal);
		}
		$(tr).find('input.total').val( _total );

		var total_beli = 0;
		$.map( $(tbody).find('tr'), function (tr) {
			var total = numeral.unformat( $(tr).find('input.total').val() );

			total_beli += total;
		});

		$(table).find('td.total_beli b').text( numeral.formatDec( total_beli ) );
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
						'harga': numeral.unformat( $(div).find('input.harga').val() ),
						'total': numeral.unformat( $(div).find('input.total').val() ),
						'tgl_order': dateSQL( $('[name=tglOrder]').data('DateTimePicker').date() ),
						'rencana_tiba': dateSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'keterangan': $(div).find('textarea.ket').val(),
						'perusahaan': $(div).find('select.perusahaan').select2('val'),
						'jns_box': $(div).find('input.jns_box').val()
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
						'no_po': $(div).find('input.no_po').val(),
						'noreg': $(div).find('input[type=hidden]').data('noreg'),
						'supplier': $(div).find('select.supplier').select2('val'),
						'item': $(div).find('select.jns_doc').select2('val'),
						'jml_ekor': numeral.unformat( $(div).find('input.ekor').val() ),
						'jml_box': numeral.unformat( $(div).find('input.box').val() ),
						'harga': numeral.unformat( $(div).find('input.harga').val() ),
						'total': numeral.unformat( $(div).find('input.total').val() ),
						'tgl_order': dateSQL( $('[name=tglOrder]').data('DateTimePicker').date() ),
						'rencana_tiba': dateSQL( $('[name=tgl_tiba_kdg]').data('DateTimePicker').date() ),
						'keterangan': $(div).find('textarea.ket').val(),
						'version': $(div).find('input.no_order').data('version'),
						'perusahaan': $(div).find('select.perusahaan').select2('val'),
						'jns_box': $(div).find('input.jns_box').val()
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

	delete_order_doc: function(elm) {
		var id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data Order DOC ?', function(result) {
			if ( result ) {
				$.ajax({
					url: 'transaksi/ODVP/delete_order_doc',
					data: {
						'params': id
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
								odvp.get_lists_doc();
								bootbox.hideAll();
							});
						} else {
							bootbox.alert(data.message);
						};
					},
			    });
			}
		});
	}, // end - delete_order_doc

	save_terima_doc: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$.map( $(div).find('[data-required=1]:not(.supplier, .jns_doc)'), function(ipt) {
			if ( empty($(ipt).val()) && $(ipt).val() == 0 ) {
				err++;
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
			} else {
				if ( $(ipt).hasClass('file_lampiran_sj') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#000000'});
				} else {
					$(ipt).parent().removeClass('has-error');
				}
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
						'harga' : numeral.unformat( $(div).find('input.harga').val() ),
						'total' : numeral.unformat( $(div).find('input.total').val() ),
						'bb' : numeral.unformat( $(div).find('input.bb').val() ),
						'kondisi' : $(div).find('input.kondisi').val(),
						'keterangan' : $(div).find('textarea.ket').val(),
						'uniformity' : numeral.unformat( $(div).find('input.uniformity').val() )
					};

					var file_tmp = $(div).find('.file_lampiran_sj').get(0).files[0];

					odvp.exec_save_terima_doc(data, file_tmp);
				};
			});
		};
	}, // end - save_terima_doc

	exec_save_terima_doc: function(data, file_tmp) {
		var formData = new FormData();

		formData.append("data", JSON.stringify(data));
        formData.append('file', file_tmp);

		$.ajax({
			url: 'transaksi/ODVP/save_terima_doc',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
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
						'harga' : numeral.unformat( $(div).find('input.harga').val() ),
						'total' : numeral.unformat( $(div).find('input.total').val() ),
						'bb' : numeral.unformat( $(div).find('input.bb').val() ),
						'kondisi' : $(div).find('input.kondisi').val(),
						'keterangan' : $(div).find('textarea.ket').val(),
						'uniformity' : numeral.unformat( $(div).find('input.uniformity').val() ),
						'version' : $(div).find('input[type=hidden]').data('version')
					};

					var file_tmp = $(div).find('.file_lampiran_sj').get(0).files[0];

					odvp.exec_edit_terima_doc(data, file_tmp);
				};
			});
		};
	}, // end - edit_terima_doc

	exec_edit_terima_doc: function(data, file_tmp) {
	    var formData = new FormData();

		formData.append("data", JSON.stringify(data));
        formData.append('file', file_tmp);

		$.ajax({
			url: 'transaksi/ODVP/edit_terima_doc',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
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

	delete_terima_doc: function(elm) {
		var id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data Terima DOC ?', function(result) {
			if ( result ) {
				$.ajax({
					url: 'transaksi/ODVP/delete_terima_doc',
					data: {
						'params': id
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
								odvp.get_lists_doc();
								bootbox.hideAll();
							});
						} else {
							bootbox.alert(data.message);
						};
					},
			    });
			}
		});
	}, // end - delete_terima_doc

	save_order_voadip: function(elm) {
		var div = $(elm).closest('div.detailed');
		var err = 0;

		$('input, select').parent().removeClass('has-error');
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
				var tgl_kirim = null;
				if ( !empty( $(tr).find('[name=tgl_kirim]').val() ) ) {
					tgl_kirim = dateSQL( $(tr).find('[name=tgl_kirim]').data('DateTimePicker').date() );
				}

				var id_tujuan_kirim = null;
				var tujuan_kirim = null;

				if ( $(tr).find('select.gudang').hasClass('aktif') ) {
					id_tujuan_kirim = $(tr).find('select.gudang').val();
					tujuan_kirim = 'gudang';
				} else {
					id_tujuan_kirim = $(tr).find('select.peternak').val();
					tujuan_kirim = 'peternak';
				}

				var data = {
					'kategori': $(tr).find('select.kategori').val(),
					'barang': $(tr).find('select.barang').val(),
					'kemasan': $(tr).find('input.kemasan').val(),
					'harga': numeral.unformat( $(tr).find('input.harga').val() ),
					'harga_jual': numeral.unformat( $(tr).find('input.harga_jual').val() ),
					'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
					'total': numeral.unformat( $(tr).find('input.total').val() ),
					'kirim_ke': tujuan_kirim,
					'kirim': id_tujuan_kirim,
					'alamat': $(tr).find('div.alamat').text(),
					'perusahaan': $(tr).find('select.perusahaan').val(),
					'tgl_kirim': tgl_kirim,
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

						// console.log(data);
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

		$('input, select').parent().removeClass('has-error');
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
				var tgl_kirim = null;
				if ( !empty( $(tr).find('[name=tgl_kirim]').val() ) ) {
					tgl_kirim = dateSQL( $(tr).find('[name=tgl_kirim]').data('DateTimePicker').date() );
				}

				var id_tujuan_kirim = null;
				var tujuan_kirim = null;

				if ( $(tr).find('select.gudang').hasClass('aktif') ) {
					id_tujuan_kirim = $(tr).find('select.gudang').val();
					tujuan_kirim = 'gudang';
				} else {
					id_tujuan_kirim = $(tr).find('select.peternak').val();
					tujuan_kirim = 'peternak';
				}

				var data = {
					'kategori': $(tr).find('select.kategori').val(),
					'barang': $(tr).find('select.barang').val(),
					'kemasan': $(tr).find('input.kemasan').val(),
					'harga': numeral.unformat( $(tr).find('input.harga').val() ),
					'harga_jual': numeral.unformat( $(tr).find('input.harga_jual').val() ),
					'jumlah': numeral.unformat( $(tr).find('input.jumlah').val() ),
					'total': numeral.unformat( $(tr).find('input.total').val() ),
					'kirim_ke': tujuan_kirim,
					'kirim': id_tujuan_kirim,
					'alamat': $(tr).find('div.alamat').text(),
					'perusahaan': $(tr).find('select.perusahaan').val(),
					'tgl_kirim': tgl_kirim,
				};

				return data;
			});

			if ( data_detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-ubah data Order Voadip ?', function(result) {
					if ( result ) {
						var data = {
							'no_order': $(div).find('input.no_order').val(),
							'no_po': $(div).find('input.no_po').val(),
							'tanggal': dateSQL( $('[name=tgl_order_voadip]').data('DateTimePicker').date() ),
							'supplier': $(div).find('select.supplier').val(),
							'version': $(div).find('input.no_order').data('version'),
							'detail': data_detail
						};

						// console.log(data);
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
					odvp.hitungStokVoadipByTransaksi( data.content, data.message );
					// bootbox.alert( data.message, function () {
					// 	odvp.setting_up();
					// 	odvp.get_lists_voadip();
					// 	bootbox.hideAll();
					// });
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_order_voadip

	order_voadip_delete: function(elm) {
		let id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data order voadip ?', function(result) {
			if ( result ) {
				$.ajax({
					url: 'transaksi/ODVP/order_voadip_delete',
					data: {
						'params': id
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
			}
		});
	}, // end - order_voadip_delete

	hitungStokVoadipByTransaksi: function(content, message) {
		var params = content;

		$.ajax({
			url: 'transaksi/ODVP/hitungStokVoadipByTransaksi',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading('Hitung Stok Ulang . . .');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(message, function() {
						odvp.setting_up();
						odvp.get_lists_voadip();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokByTransaksi

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
  //       var opt_barang = '<option value="">-- Pilih Barang --</option>';
		// sel_brg.html(opt_barang);

        App.formatNumber();
        odvp.setting_up();
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

    get_lists_pakan: function() {
    	var div_doc = $('div#pakan');
		var table = $(div_doc).find('table');
		var tbody = $(table).find('tbody');

		var err = 0;
		$.map( $(div_doc).find('[data-required=1]'), function(ipt) {
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
			var start_date = dateSQL($(div_doc).find('[name=startDate]').data('DateTimePicker').date());
			var end_date = dateSQL($(div_doc).find('[name=endDate]').data('DateTimePicker').date());
			var perusahaan = $(div_doc).find('select.perusahaan').select2().val();

			var date = {
				'start_date': start_date,
				'end_date': end_date,
				'perusahaan': perusahaan
			};

			$.ajax({
				url: 'transaksi/ODVP/get_lists_pakan',
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

						odvp.set_table_page('.tbl_odvp');
					};

					hideLoading();
				},
			});
		}
    }, // end - get_lists_pakan

    order_pakan_form: function(elm) {
		var tr = $(elm).closest('tr');

		var noreg = $(elm).data('noreg');
		var nama_mitra = $(tr).find('td.nama_mitra').text();
		var kdg = $(tr).find('td.kandang').text();
		var populasi = $(tr).find('td.populasi').text();
		var tgl_docin = $(tr).find('td.tgl_docin').data('tgl');

		showLoading();
		$.get('transaksi/ODVP/order_pakan_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			hideLoading();
			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').css({'width':'95%'});

				var div_detail = $(this).find('div.detailed');

				var kandang = kdg;
				if ( kdg.length == 1 ) {
					kandang = '0'+kdg;
				};

				$(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				$(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_doc_form

	order_pakan_edit_form: function(elm) {
		var tr = $(elm).closest('tr');
		var no_order = $(elm).data('id');

		showLoading();
		$.get('transaksi/ODVP/order_pakan_edit_form',{
				'params': no_order
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			hideLoading();
			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').css({'width':'95%'});

				var div_detail = $(this).find('div.detailed');

				// var kandang = kdg;
				// if ( kdg.length == 1 ) {
				// 	kandang = '0'+kdg;
				// };

				// $(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				// $(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_pakan_edit_form

	order_pakan_view_form: function(elm) {
		var tr = $(elm).closest('tr');
		var no_order = $(elm).data('id');

		showLoading();
		$.get('transaksi/ODVP/order_pakan_view_form',{
				'params': no_order
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			hideLoading();
			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').css({'width':'95%'});

				var div_detail = $(this).find('div.detailed');

				// var kandang = kdg;
				// if ( kdg.length == 1 ) {
				// 	kandang = '0'+kdg;
				// };

				// $(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				// $(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - order_pakan_view_form

	addRowChildOrderPakan: function(elm) {
        let row = $(elm).closest('tr');
        
		var err = 0;
		$.map( $(row).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu sebelum menambah baris baru.');
		} else {
			let newRow = row.clone();
	        newRow.find('input').val('');
	        row.find('.btn-ctrl').hide();
	        row.after(newRow);
	        
	        let tbody = $(row).closest('tbody');
	        if ( $(tbody).find('tr').length > 0 ) {
	        	newRow.find('.btn_del_row_2x').removeClass('hide');
	        };

	        App.formatNumber();
		}
    }, // end - addRowChildOrderPakan

    hit_total_order_pakan: function(elm) {
    	let tr = $(elm).closest('tr');
		var tbody = $(tr).closest('tbody');
		var table = $(tbody).closest('table');

    	var harga = numeral.unformat($(tr).find('input.harga').val());
    	var jumlah = numeral.unformat($(tr).find('input.jumlah').val());
    	var total = harga * jumlah;

    	$(tr).find('td.total').text( numeral.formatInt(total) );

		var total_beli = 0;
		$.map( $(tbody).find('tr'), function (tr) {
			var total = numeral.unformat( $(tr).find('td.total').text() );

			total_beli += total;
		});

		$(table).find('td.total_beli b').text( numeral.formatInt( total_beli ) );
    }, // end - hit_total_order_pakan

    set_alamat_order_pakan: function(elm) {
    	let row = $(elm).closest('tr');
    	let td_alamat = $(row).find('td.alamat');
    	let div_alamat = $(td_alamat).find('div.alamat');

    	var jenis = $(elm).data('jenis');

    	var _val = $(elm).val();
    	if ( !empty(_val) ) {
    		var alamat = $(elm).find('option:selected').data('alamat').toUpperCase();
    		$(div_alamat).text(alamat);

    		if ( jenis == 'gudang' ) {
	    		$(row).find('select.gudang').removeAttr('disabled');
	    		$(row).find('select.gudang').attr('data-required', '1');
	    		$(row).find('select.gudang').addClass('aktif');

	    		$(row).find('select.peternak').removeAttr('data-required');
	    		$(row).find('select.peternak').val('');
	    		$(row).find('select.peternak').attr('disabled', 'disabled');
	    		$(row).find('select.peternak').removeClass('aktif');
	    	} else {
	    		$(row).find('select.peternak').removeAttr('disabled');
	    		$(row).find('select.peternak').attr('data-required', '1');
	    		$(row).find('select.peternak').addClass('aktif');

	    		$(row).find('select.gudang').removeAttr('data-required');
	    		$(row).find('select.gudang').val('');
	    		$(row).find('select.gudang').attr('disabled', 'disabled');
	    		$(row).find('select.gudang').removeClass('aktif');
	    	}
    	} else {
    		$(div_alamat).text('-');

    		$(row).find('select.gudang').removeAttr('disabled');
			$(row).find('select.gudang').attr('data-required', '1');
			$(row).find('select.peternak').removeAttr('disabled');
			$(row).find('select.peternak').attr('data-required', '1');
    	}
    }, // end - set_alamat_order_pakan

    set_bentuk_order_pakan: function(elm) {
    	let row = $(elm).closest('tr');
    	let td_bentuk = $(row).find('td.bentuk');

    	var _val = $(elm).val();
    	if ( !empty(_val) ) {
    		var bentuk = $(elm).find('option:selected').data('bentuk').toUpperCase();
    		$(td_bentuk).text(bentuk);
    	} else {
    		$(td_bentuk).text('-');
    	}
    }, // end - set_bentuk_order_pakan

    save_order_pakan: function(elm) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Order Pakan ?', function(result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var id_tujuan_kirim = null;
						var tujuan_kirim = null;

						if ( $(tr).find('select.gudang').hasClass('aktif') ) {
							id_tujuan_kirim = $(tr).find('select.gudang').val();
							tujuan_kirim = 'gudang';
						} else {
							id_tujuan_kirim = $(tr).find('select.peternak').val();
							tujuan_kirim = 'peternak';
						}

						var data = {
							'perusahaan': $(tr).find('select.perusahaan').val(),
							'barang': $(tr).find('select.barang').val(),
							'harga': numeral.unformat($(tr).find('input.harga').val()),
							'harga_jual': numeral.unformat($(tr).find('input.harga_jual').val()),
							'jumlah': numeral.unformat($(tr).find('input.jumlah').val()),
							'total': numeral.unformat($(tr).find('td.total').text()),
							'tujuan_kirim': tujuan_kirim,
							'id_tujuan_kirim': id_tujuan_kirim,
							'alamat': $(tr).find('td.alamat').text().trim(),
						};

						return data;
					});

					var data = {
						'supplier': $(div).find('select.supplier').val(),
						'tgl_trans': dateSQL( $(div).find('[name=tanggal]').data('DateTimePicker').date() ),
						'rcn_kirim': dateSQL( $(div).find('[name=rcn_kirim]').data('DateTimePicker').date() ),
						'detail': detail
					};

					// console.log( data );
					odvp.exec_save_order_pakan(data);
				}
			});
		}
    }, // end - save_order_pakan

    exec_save_order_pakan: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/save_order_pakan',
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
						// odvp.setting_up();

						var start_date = $('div#pakan').find('[name=startDate]').data('DateTimePicker').date();
						var end_date = $('div#pakan').find('[name=endDate]').data('DateTimePicker').date();
						var perusahaan = $('div#pakan').find('select.perusahaan').select2().val();

						if ( !empty(start_date) && !empty(end_date) && !empty(!empty(perusahaan)) ) {
							odvp.get_lists_pakan();
						};

						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_save_order_pakan

	edit_order_pakan: function(elm) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data Order Pakan ?', function(result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var id_tujuan_kirim = null;
						var tujuan_kirim = null;

						if ( $(tr).find('select.gudang').hasClass('aktif') ) {
							id_tujuan_kirim = $(tr).find('select.gudang').val();
							tujuan_kirim = 'gudang';
						} else {
							id_tujuan_kirim = $(tr).find('select.peternak').val();
							tujuan_kirim = 'peternak';
						}

						var data = {
							'perusahaan': $(tr).find('select.perusahaan').val(),
							'barang': $(tr).find('select.barang').val(),
							'harga': numeral.unformat($(tr).find('input.harga').val()),
							'harga_jual': numeral.unformat($(tr).find('input.harga_jual').val()),
							'jumlah': numeral.unformat($(tr).find('input.jumlah').val()),
							'total': numeral.unformat($(tr).find('td.total').text()),
							'tujuan_kirim': tujuan_kirim,
							'id_tujuan_kirim': id_tujuan_kirim,
							'alamat': $(tr).find('td.alamat').text().trim(),
						};

						return data;
					});

					var data = {
						'id': $(elm).data('id'),
						'supplier': $(div).find('select.supplier').val(),
						'tgl_trans': dateSQL( $(div).find('[name=tanggal]').data('DateTimePicker').date() ),
						'rcn_kirim': dateSQL( $(div).find('[name=rcn_kirim]').data('DateTimePicker').date() ),
						'detail': detail
					};

					// console.log( data );
					odvp.exec_edit_order_pakan(data);
				}
			});
		}
    }, // end - edit_order_pakan

    exec_edit_order_pakan: function(params) {
		$.ajax({
			url: 'transaksi/ODVP/edit_order_pakan',
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
					odvp.hitungStokPakanByTransaksi( data.content, data.message );
					// bootbox.alert( data.message, function () {
						// odvp.setting_up();
						// odvp.get_lists_pakan();
						// bootbox.hideAll();
					// });
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - exec_edit_order_pakan

	order_pakan_delete: function(elm) {
		let id = $(elm).data('id');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data order pakan ?', function(result) {
			if ( result ) {
				$.ajax({
					url: 'transaksi/ODVP/order_pakan_delete',
					data: {
						'params': id
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
								odvp.get_lists_pakan();
								bootbox.hideAll();
							});
						} else {
							bootbox.alert(data.message);
						};
					},
			    });
			}
		});
	}, // end - order_pakan_delete

	hitungStokPakanByTransaksi: function(content, message) {
		var params = content;

		$.ajax({
			url: 'transaksi/ODVP/hitungStokPakanByTransaksi',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading('Hitung Stok Ulang . . .');
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(message, function() {
						// odvp.setting_up();
						var start_date = $('div#pakan').find('[name=startDate]').data('DateTimePicker').date();
						var end_date = $('div#pakan').find('[name=endDate]').data('DateTimePicker').date();
						var perusahaan = $('div#pakan').find('select.perusahaan').select2().val();

						if ( !empty(start_date) && !empty(end_date) && !empty(!empty(perusahaan)) ) {
							odvp.get_lists_pakan();
						};

						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - hitungStokByTransaksi

	kirim_pakan_form: function(elm) {
		var tr = $(elm).closest('tr');

		$.get('transaksi/ODVP/kirim_pakan_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').css({'width':'70%'});

				// var div_detail = $(this).find('div.detailed');

				// var kandang = kdg;
				// if ( kdg.length == 1 ) {
				// 	kandang = '0'+kdg;
				// };

				// $(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				// $(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - kirim_pakan_form

	terima_pakan_form: function(elm) {
		var tr = $(elm).closest('tr');

		$.get('transaksi/ODVP/terima_pakan_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};

			bootbox.dialog(_options).bind("shown.bs.modal", function () {
				odvp.setting_up();

				$(this).find('.modal-dialog').css({'width':'70%'});

				// var div_detail = $(this).find('div.detailed');

				// var kandang = kdg;
				// if ( kdg.length == 1 ) {
				// 	kandang = '0'+kdg;
				// };

				// $(div_detail).find('div.nama_mitra b').html(nama_mitra + ' ' + kandang + ' POPULASI ' + populasi);
				// $(div_detail).find('input[type=hidden]').attr('data-noreg', noreg);
			});
		},'html');
	}, // end - terima_pakan_form

	save_kirim_pakan: function(elm) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Order Pakan ?', function(result) {
				if ( result ) {
					var detail = $.map( $(div).find('table tbody tr'), function(tr) {
						var id_tujuan_kirim = null;
						var tujuan_kirim = null;

						console.log('Gudang : '+$(tr).find('select.gudang').data('required'));
						console.log('Petrnak : '+$(tr).find('select.peternak').data('required'));

						if ( $(tr).find('select.gudang').hasClass('aktif') ) {
							id_tujuan_kirim = $(tr).find('select.gudang').val();
							tujuan_kirim = 'gudang';
						} else {
							id_tujuan_kirim = $(tr).find('select.peternak').val();
							tujuan_kirim = 'peternak';
						}

						var data = {
							'perusahaan': $(tr).find('select.perusahaan').val(),
							'barang': $(tr).find('select.barang').val(),
							'harga': numeral.unformat($(tr).find('input.harga').val()),
							'jumlah': numeral.unformat($(tr).find('input.jumlah').val()),
							'total': numeral.unformat($(tr).find('td.total').text()),
							'tujuan_kirim': tujuan_kirim,
							'id_tujuan_kirim': id_tujuan_kirim,
							'alamat': $(tr).find('td.alamat').text().trim(),
						};

						return data;
					});

					var data = {
						'supplier': $(div).find('select.supplier').val(),
						'tgl_trans': dateSQL( $(div).find('[name=tanggal]').data('DateTimePicker').date() ),
						'rcn_kirim': dateSQL( $(div).find('[name=rcn_kirim]').data('DateTimePicker').date() ),
						'detail': detail
					};

					// console.log( data );
					odvp.exec_save_order_pakan(data);
				}
			});
		}
	}, // end - save_kirim_pakan

	hit_hrg_jual_voadip: function(elm) {
		var tr = $(elm).closest('tr');

		var select_brg = $(tr).find('select.barang');
		var hit_hrg_jual = $(select_brg).find('option:selected').attr('data-hithrgjual');

		var harga_beli = numeral.unformat($(elm).val());
		var harga_jual = 0;
		if ( hit_hrg_jual == 1 ) {
			var harga = harga_beli + (harga_beli  * (5/100));

			var sisa_hasil_bagi = harga % 50;

			harga_jual = harga - sisa_hasil_bagi;
		} else {
			harga_jual = harga_beli;
		}

		var decimal = $(tr).find('input.harga_jual').attr('data-tipe').replace('decimal', '');
		var _hrg_jual = numeral.formatDec(harga_jual);
		if ( !empty(decimal) ) {
			_hrg_jual = numeral.formatDec(harga_jual, decimal);
		}
		$(tr).find('input.harga_jual').val( _hrg_jual );

		odvp.hit_total_order_voadip( elm );
	}, // end - hit_hrg_jual_voadip

	listActivity: function(elm) {
		let tr = $(elm).closest('tr');
		let jenis = $(elm).data('jenis');

		let params = null;
		if ( jenis == 'voadip' ) {
	        params = {
	            'id' : $(elm).data('id'),
	            'jenis' : jenis,
	            'tanggal' : $(tr).find('td.tanggal').text(),
	            'no_order' : $(tr).find('td.no_order').text(),
	            'supplier' : $(tr).find('td.supplier div.supplier').text()
	        }
		} else {
			params = {
	            'id' : $(elm).data('id'),
	            'jenis' : jenis,
	            'tanggal' : $(tr).find('td.tanggal').text(),
	            'supplier' : $(tr).find('td.supplier').text(),
	            'rcn_kirim' : $(tr).find('td.rcn_kirim').text(),
	            'no_order' : $(tr).find('td.no_order').text()
	        }
		}

        $.get('transaksi/ODVP/listActivity',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                $(modal_dialog).css({'max-width' : '80%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});
            });
        },'html');
	}, // end - listActivity
};

odvp.start_up();