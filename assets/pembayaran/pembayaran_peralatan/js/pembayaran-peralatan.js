var formData = null;

var pp = {
	startUp: function () {
		pp.settingUp();

		formData = new FormData();
	}, // end - startUp

	showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        // var _allowtypes = ['xlsx'];
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

            pp.compressImg($(elm), null);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

    compressImg: function(elm, key) {
        showLoading();

        var file_tmp = $(elm).get(0).files[0];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();

        var _allowtypes_compress = ['jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG'];

        if ( in_array(_type, _allowtypes_compress) ) {
	        ci.compress_img(file_tmp, file_tmp.name, 480, function(data) {
	            formData.append('file', data);

	            hideLoading();
	        });
        } else {
        	formData.append('file', file_tmp);

        	hideLoading();
        }
    }, // end - compressImg

	settingUp: function () {
		var div_riwayat = $('#riwayat');
		var div_action = $('#action');

		$('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $.map( $('.date'), function(div) {
        	var tgl = $(div).find('input').attr('data-tgl');
        	if ( !empty(tgl) ) {
        		$(div).data('DateTimePicker').date( new Date(tgl) );
        	}
        });

		$(div_riwayat).find('.supplier').select2({placeholder: 'Pilih Supplier'}).on("select2:select", function (e) {
            var supplier = $(div_riwayat).find('.supplier').select2().val();

            for (var i = 0; i < supplier.length; i++) {
                if ( supplier[i] == 'all' ) {
                    $(div_riwayat).find('.supplier').select2().val('all').trigger('change');

                    i = supplier.length;
                }
            }
        });

        $(div_riwayat).find('.mitra').select2({placeholder: 'Pilih Plasma'}).on("select2:select", function (e) {
            var mitra = $(div_riwayat).find('.mitra').select2().val();

            for (var i = 0; i < mitra.length; i++) {
                if ( mitra[i] == 'all' ) {
                    $(div_riwayat).find('.mitra').select2().val('all').trigger('change');

                    i = mitra.length;
                }
            }
        });

        $(div_action).find('.supplier').select2().on("select2:select", function (e) {
        	var supplier = $(div_action).find('.supplier').select2().val();

        	pp.getNoOrder( supplier );
        });
        $(div_action).find('select.no_order').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
	}, // end - settingUp

	changeTabActive: function(elm) {
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
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'pembayaran/PembayaranPeralatan/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                formData = new FormData();

                pp.settingUp();

                if ( !empty(edit) ) {
                	$('div#'+href).find('.supplier').trigger("select2:select");
                }

                hideLoading();
            }
        });
	}, // end - loadForm

	getLists: function () {
		var div = $('#riwayat');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
			var params = {
				'startDate': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
				'endDate': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
				'supplier': $(div).find('.supplier').select2('val'),
				'mitra': $(div).find('.mitra').select2('val')
			};

			$.ajax({
	            url: 'pembayaran/PembayaranPeralatan/getLists',
	            data: { 'params': params },
	            type: 'GET',
	            dataType: 'HTML',
	            beforeSend: function(){ showLoading() },
	            success: function(html){
	            	$(div).find('table tbody').html( html );

	            	hideLoading();
	            }
	        });
		}
	}, // end - getLists

	getNoOrder: function (supplier) {
		var div = $('#action');

		var params = {
			'supplier': supplier
		};

		$.ajax({
            url: 'pembayaran/PembayaranPeralatan/getNoOrder',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
            	var option = '<option value="">-- Pilih No. Order --</option>';
            	if ( data.status == 1 ) {
            		if ( !empty( data.content ) ) {
            			var val = $(div).find('select.no_order').attr('data-val');
            			$(div).find('select.no_order').removeAttr('disabled');
            			for (var i = 0; i < data.content.length; i++) {
            				var selected = null;
            				if ( val == data.content[i].no_order ) {
            					selected = 'selected';
            				}

            				option += '<option value="'+data.content[i].no_order+'" data-namamitra="'+data.content[i].nama_mitra+'" data-jmltagihan="'+data.content[i].total+'"	'+selected+' >'+data.content[i].tgl_order+' | '+data.content[i].no_order+'</option>';
            			}
            			$(div).find('select.no_order').html(option);
            			$(div).find('select.no_order').select2().on('select2:select', function (e) {
            				var no_order = $(div).find('select.no_order').select2().val();

            				var nama_mitra = $(div).find('select.no_order option:selected').attr('data-namamitra');
            				var jml_tagihan = $(div).find('select.no_order option:selected').attr('data-jmltagihan');

            				$(div).find('.mitra').val( nama_mitra );
            				$(div).find('.jumlah_tagihan').val( numeral.formatDec(jml_tagihan) );
            				$(div).find('.saldo').val(0);

            				pp.getDetailOrder( no_order );
            			});
            		} else {
            			$(div).find('select.no_order').html(option);
            			$(div).find('select.no_order').attr('disabled', 'disabled');
            		}
            		hideLoading();
            	} else {
            		$(div).find('select.no_order').html(option);
            		$(div).find('select.no_order').attr('disabled', 'disabled');

            		hideLoading();
            		bootbox.alert(data.message);
            	}
            }
        });
	}, // end - getNoOrder

	getDetailOrder: function (no_order) {
		var div = $('#action');

		var params = {
			'no_order': no_order
		};

		$.ajax({
            url: 'pembayaran/PembayaranPeralatan/getDetailOrder',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
            	$(div).find('table tbody').html( html );

            	hideLoading();
            }
        });
	}, // end - getDetailOrder

	hitTotalBayar: function() {
		var div = $('#action');

		var saldo = numeral.unformat( $(div).find('.saldo').val() );
		var jml_bayar = numeral.unformat( $(div).find('.jumlah_bayar').val() );

		var tot_bayar = saldo + jml_bayar;

		$(div).find('.total_bayar').val( numeral.formatDec( tot_bayar ) );
	}, // end - hitTotalBayar

	save: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
			var jml_tagihan = numeral.unformat($(div).find('.jumlah_tagihan').val());
			var tot_bayar = numeral.unformat($(div).find('.total_bayar').val());

			var ket = '';
			if ( jml_tagihan <= tot_bayar ) {
				ket = 'Apakah anda yakin ingin menyimpan data pembayaran ?';
			} else {
				ket = 'Total bayar kurang dari jumlah tagihan, apakah anda yakin ingin tetep menyimpan data pembayaran?';
			}

			bootbox.confirm(ket, function(result) {
				if ( result ) {
					var params = {
						'no_order': $(div).find('.no_order').select2('val'),
						'tgl_bayar': dateSQL($(div).find('#TglBayar').data('DateTimePicker').date()),
						'jml_tagihan': numeral.unformat($(div).find('.jumlah_tagihan').val()),
						'saldo': numeral.unformat($(div).find('.saldo').val()),
						'jml_bayar':  numeral.unformat($(div).find('.jumlah_bayar').val()),
						'tot_bayar': tot_bayar,
						'no_faktur': $(div).find('.no_faktur').val()
					};

					formData.append('data', JSON.stringify( params ));

					$.ajax({
			            url: 'pembayaran/PembayaranPeralatan/save',
			            data: formData,
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			            	hideLoading();

			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
			            			pp.loadForm(data.content.id, null, 'action');
			            			// location.reload();
			            		});
			            	} else {
			            		bootbox.alert(data.message);
			            	}
			            },
			            contentType : false,
			            processData : false,
			        });
				}
			});
		}
	}, // end - save

	edit: function(elm) {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
			var jml_tagihan = numeral.unformat($(div).find('.jumlah_tagihan').val());
			var tot_bayar = numeral.unformat($(div).find('.total_bayar').val());

			var ket = '';
			if ( jml_tagihan <= tot_bayar ) {
				ket = 'Apakah anda yakin ingin menyimpan data pembayaran ?';
			} else {
				ket = 'Total bayar kurang dari jumlah tagihan, apakah anda yakin ingin tetep menyimpan data pembayaran?';
			}

			bootbox.confirm(ket, function(result) {
				if ( result ) {
					var params = {
						'id': $(elm).attr('data-id'),
						'no_order': $(div).find('.no_order').select2('val'),
						'tgl_bayar': dateSQL($(div).find('#TglBayar').data('DateTimePicker').date()),
						'jml_tagihan': numeral.unformat($(div).find('.jumlah_tagihan').val()),
						'saldo': numeral.unformat($(div).find('.saldo').val()),
						'jml_bayar':  numeral.unformat($(div).find('.jumlah_bayar').val()),
						'tot_bayar': tot_bayar,
						'no_faktur': $(div).find('.no_faktur').val()
					};

					formData.append('data', JSON.stringify( params ));

					$.ajax({
			            url: 'pembayaran/PembayaranPeralatan/edit',
			            data: formData,
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading() },
			            success: function(data){
			            	hideLoading();

			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
			            			pp.loadForm(data.content.id, null, 'action');
			            			// location.reload();
			            		});
			            	} else {
			            		bootbox.alert(data.message);
			            	}
			            },
			            contentType : false,
			            processData : false,
			        });
				}
			});
		}
	}, // end - edit

	delete: function(elm) {
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
			if ( result ) {
				var params = {
					'id': $(elm).attr('data-id')
				};

				$.ajax({
		            url: 'pembayaran/PembayaranPeralatan/delete',
		            data: {'params': params},
		            type: 'POST',
		            dataType: 'JSON',
		            beforeSend: function(){ showLoading() },
		            success: function(data){
		            	hideLoading();

		            	if ( data.status == 1 ) {
		            		bootbox.alert(data.message, function() {
		            			pp.loadForm(null, null, 'action');
		            			// location.reload();
		            		});
		            	} else {
		            		bootbox.alert(data.message);
		            	}
		            }
		        });
			}
		});
	}, // end - delete
};

pp.startUp();