var pegawai = {
	start_up : function () {
		pegawai.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/Pegawai/get_list',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dContent); },
            success : function(html){
                App.hideLoaderInContent(dContent, html);
            }
        });
	}, // end - get_list

	add_form : function () {
		$.get('parameter/Pegawai/add_form',{
        },function(data){
            var _options = {
                className : 'veryWidth',
				message : data,
				size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.wilayah').select2();
                $('.wilayah').next('span.select2').css('width', '100%');

                $('.unit').select2();
                $('.unit').next('span.select2').css('width', '100%');
            });
        },'html');
	}, // end - add_form

	edit_form : function (elm) {
		var id = $(elm).data('id');
		$.get('parameter/Pegawai/edit_form',{
			'id' : id
        },function(data){
            var _options = {
                className : 'veryWidth',
				message : data,
				size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.wilayah').select2();
                $('.wilayah').next('span.select2').css('width', '100%');

                $('.unit').select2();
                $('.unit').next('span.select2').css('width', '100%');

                var select_jabatan = $(this).find('select.jabatan');
                pegawai.set_disable_by_jabatan(select_jabatan, 'edit');
            });
        },'html');
	}, // end - edit_form

	set_disable_by_jabatan : function (elm, tipe = null) {
		var div = $('div.body');
		var jabatan = $(elm).val();

		if ( empty(tipe) ) {
			$(div).find('select.wilayah option').prop('selected', false);
		} else {
			var Values = new Array();

			var select_wilayah = $(div).find('select.wilayah');
			$.map( $(select_wilayah).find('option'), function(opt) {
				var select = $(opt).data('selected');

				if ( select == true ) {
					Values.push( $(opt).val() );
				};
			});

			$(div).find('select.wilayah').val(Values);
			$(div).find('select.wilayah').select2().trigger('change');

			var select_unit = $(div).find('select.unit');
			$.map( $(select_unit).find('option'), function(opt) {
				var select = $(opt).data('selected');

				if ( select == true ) {
					Values.push( $(opt).val() );
				};
			});

			$(div).find('select.unit').val(Values);
			$(div).find('select.unit').select2().trigger('change');
		}

		if ( !empty(jabatan) ) {
			if ( jabatan == 'coo' ) {
				$(div).find('select.atasan, input:not(.nama_pegawai)').attr('disabled', 'disabled');
				$(div).find('select.atasan, input:not(.nama_pegawai)').removeAttr('data-required');

				if ( empty(tipe) ) {
					$(div).find('select.koordinator option[value=all]').prop('selected', true);
					$(div).find('select.marketing option[value=all]').prop('selected', true);
					// Set selected 
					$(div).find('select.wilayah').val('all');
				    $(div).find('select.wilayah').select2().trigger('change');

				    $(div).find('select.unit').val('all');
				    $(div).find('select.unit').select2().trigger('change');
				}
			    $(div).find('select.wilayah').next('span.select2').css('width', '100%');
			    $(div).find('select.unit').next('span.select2').css('width', '100%');

			} else if ( jabatan == 'kepala admin' ) {
				$(div).find('select.atasan').removeAttr('disabled');
				$(div).find('select.atasan').attr('data-required', 1);

				$(div).find('input:not(.nama_pegawai)').attr('disabled', 'disabled');
				$(div).find('input:not(.nama_pegawai)').removeAttr('data-required');

				if ( empty(tipe) ) {
					$(div).find('select.koordinator option[value=all]').prop('selected', true);
					$(div).find('select.marketing option[value=all]').prop('selected', true);
					// Set selected 
					$(div).find('select.wilayah').val('all');
				    $(div).find('select.wilayah').select2().trigger('change');

				    $(div).find('select.unit').val('all');
				    $(div).find('select.unit').select2().trigger('change');
				}
				$(div).find('select.wilayah').next('span.select2').css('width', '100%');
				$(div).find('select.unit').next('span.select2').css('width', '100%');

			} else if ( jabatan == 'admin pusat' ) {
				$(div).find('select.atasan').removeAttr('disabled');
				$(div).find('select.atasan').attr('data-required', 1);

				$(div).find('input:not(.nama_pegawai)').attr('disabled', 'disabled');
				$(div).find('input:not(.nama_pegawai)').removeAttr('data-required');

				if ( empty(tipe) ) {
					$(div).find('select.koordinator option[value=all]').prop('selected', true);
					$(div).find('select.marketing option[value=all]').prop('selected', true);
					// Set selected 
					$(div).find('select.wilayah').val('all');
				    $(div).find('select.wilayah').select2().trigger('change');

				    $(div).find('select.unit').val('all');
				    $(div).find('select.unit').select2().trigger('change');
				}
				$(div).find('select.wilayah').next('span.select2').css('width', '100%');
				$(div).find('select.unit').next('span.select2').css('width', '100%');

			} else if ( jabatan == 'penanggung jawab' ) {
				$(div).find('select.atasan').removeAttr('disabled');
				$(div).find('select.atasan').attr('data-required', 1);

				$(div).find('input:not(.nama_pegawai)').attr('disabled', 'disabled');
				$(div).find('input:not(.nama_pegawai)').removeAttr('data-required');

				if ( empty(tipe) ) {
					$(div).find('select.koordinator option[value=4]').prop('selected', true);
					$(div).find('select.marketing option[value=4]').prop('selected', true);
					// Set selected 
					$(div).find('select.wilayah').val(null);
				    $(div).find('select.wilayah').select2().trigger('change');

				    $(div).find('select.unit').val(null);
				    $(div).find('select.unit').select2().trigger('change');
				}
				$(div).find('select.wilayah').next('span.select2').css('width', '100%');
				$(div).find('select.unit').next('span.select2').css('width', '100%');

			} else {
				$(div).find('select, input').removeAttr('disabled');
				$(div).find('select.atasan').attr('data-required', 1);

				$(div).find('input[disabled]').removeAttr('data-required');
				
				if ( empty(tipe) ) {
					$(div).find('select.koordinator option:first').prop('selected', true);
					$(div).find('select.marketing option:first').prop('selected', true);
					// Set selected 
				    $(div).find('select.wilayah').val(null).trigger('change');
				    $(div).find('select.unit').val(null).trigger('change');
				};

				$(div).find('select.wilayah').next('span.select2').css('width', '100%');
				$(div).find('select.unit').next('span.select2').css('width', '100%');
			}
		} else {
			$(div).find('input:not(.nama_pegawai)').attr('disabled', 'disabled');
			$(div).find('input:not(.nama_pegawai)').removeAttr('data-required');
			// Set selected 
			$(div).find('select.wilayah').val(null).trigger('change');
		    $(div).find('select.wilayah').next('span.select2').css('width', '100%');

		    $(div).find('select.unit').val(null).trigger('change');
		    $(div).find('select.unit').next('span.select2').css('width', '100%');

		};

		if ( !empty(jabatan) && jabatan != "" ) {
			pegawai.set_atasan(jabatan, tipe);
		} else {
			var select = $(div).find('select.atasan');
			var option = "<option value=''>-- Pilih Atasan --</option>";

			$(select).html(option);
		};
	}, // end - set_disable_by_jabatan

	set_atasan : function (jabatan, tipe=null) {
		var div = $('div.body');

		$.ajax({
            url : 'parameter/Pegawai/get_atasan',
            data : {'jabatan' : jabatan},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){},
            success : function(data){
                if (data.status) {
                	if ( !empty(data.content) ) {
                		var select = $(div).find('select.atasan');
                		var option = "<option value=''>-- Pilih Atasan --</option>";
                		for (var i = 0; i < data.content.length; i++) {
                			var urut = null;
                			if ( data.content[i].jabatan == 'koordinator' ) {
                				urut = data.content[i].kordinator;
                				option += "<option value='"+data.content[i].id+"'>"+data.content[i].jabatan.toUpperCase() + ' (' + urut + ') <b>|</b> ' + data.content[i].nama+"</option>";
                			} else if ( data.content[i].jabatan == 'marketing' ) {
                				urut = data.content[i].marketing;
                				option += "<option value='"+data.content[i].id+"'>"+data.content[i].jabatan.toUpperCase() + ' (' + urut + ') <b>|</b> ' + data.content[i].nama+"</option>";
                			} else {
                				option += "<option value='"+data.content[i].id+"'>"+data.content[i].jabatan.toUpperCase() + ' <b>|</b> ' + data.content[i].nama+"</option>";
                			};
                		};

                		$(select).html(option);

                		if ( tipe == 'edit' ) {
                			var id_atasan = $(select).data('atasan');
                			$(select).find('option[value="'+id_atasan+'"]').prop('selected', true);
                		};
                	};
                }
            }
        });
	}, // end - set_atasan

	save : function () {
		var div = $('div.body');
		var err = 0;

		$(div).find('select, input').parent().removeClass('has-error');
		$.map( $(div).find('[data-required=1]:not(.marketing, .wilayah, .koordinator)'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Karyawan ?', function (result) {
				if ( result ) {
					var level = $(div).find('select.level').val();
					var atasan = $(div).find('select.atasan').val();
					var nama = $(div).find('input.nama_pegawai').val();
					var wilayah = $(div).find('select.wilayah').select2().val();
					var koordinator = $(div).find('select.koordinator').val();
					var marketing = $(div).find('select.marketing').val();
					var unit = $(div).find('select.unit').select2().val();
					var jabatan = $(div).find('select.jabatan').val();

					var params = {
						'level' : level,
						'atasan' : atasan,
						'nama' : nama,
						'wilayah' : wilayah,
						'koordinator' : koordinator,
						'marketing' : marketing,
						'unit' : unit,
						'jabatan' : jabatan
					};

					// console.log( params );
					pegawai.execute_save(params);
				};
			});
		};
	}, // end - save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/Pegawai/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pegawai.get_list();
                        bootbox.hideAll();
                        // location.reload();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_save

	edit : function (elm) {
		var div = $('div.body');
		var err = 0;

		$(div).find('select, input').parent().removeClass('has-error');
		$.map( $(div).find('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin mengupdate data Karyawan ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var nik = $(elm).data('nik');
					var level = $(div).find('select.level').val();
					var atasan = $(div).find('select.atasan').val();
					var nama = $(div).find('input.nama_pegawai').val();
					var wilayah = $(div).find('select.wilayah').select2().val();
					var koordinator = $(div).find('select.koordinator').val();
					var marketing = $(div).find('select.marketing').val();
					var unit = $(div).find('select.unit').select2().val();
					var jabatan = $(div).find('select.jabatan').val();

					var params = {
						'id' : id,
						'nik' : nik,
						'level' : level,
						'atasan' : atasan,
						'nama' : nama,
						'wilayah' : wilayah,
						'koordinator' : koordinator,
						'marketing' : marketing,
						'unit' : unit,
						'jabatan' : jabatan
					};

					// console.log( params );
					pegawai.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params = null) {
		$.ajax({
            url : 'parameter/Pegawai/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pegawai.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit

	modalGaji : function (elm) {
		var nik = $(elm).data('nik');
		$.get('parameter/Pegawai/modalGaji',{
			'nik' : nik
        },function(data){
            var _options = {
                className : 'veryWidth',
				message : data,
				size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - edit_form
};

pegawai.start_up();