var skp = {
	start_up : function () {
		skp.get_list();
		skp.setBindSHA1();
	}, // end - start_up

	get_list : function () {
		var dContent = $('table.tbl_skp tbody');

		$.ajax({
            url : 'parameter/SkpMitra/get_list',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dContent); },
            success : function(html){
                App.hideLoaderInContent(dContent, html);
            }
        });
	}, // end - get_list

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

        let div = $(_label).closest('div');

        div.find('span[name=dokumen]').addClass('hide');

        let _a = div.find('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.text(_namafile);
                    _a.attr('href', _temp_url);
                }
            }else if (isLable == 0) {
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

    set_data_mitra: function (elm) {
    	let div_form_group = $(elm).closest('div.form-group');

    	let nama_mitra = $(elm).find('option:selected').data('nama');

    	$(div_form_group).find('input.nama_mitra').val( nama_mitra );
    }, // end - set_data_mitra

	add_form : function () {
		$.get('parameter/SkpMitra/add_form',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	var modal_dialog = $(this).find('.modal-dialog');
   				$(modal_dialog).css({'max-width' : '80%'});
   				$(modal_dialog).css({'width' : '80%'});

   				var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $("[name=start_date]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y'
				});
				$("[name=end_date]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y',
					useCurrent: false //Important! See issue #1075
				});
				$("[name=start_date]").on("dp.change", function (e) {
					$("[name=end_date]").data("DateTimePicker").minDate(e.date);
					$("[name=end_date]").data("DateTimePicker").date(e.date);
				});
				$("[name=end_date]").on("dp.change", function (e) {
					$('[name=start_date]').data("DateTimePicker").maxDate(e.date);
				});

				skp.setBindSHA1();
            });
        },'html');
	}, // end - add_form

	edit_form : function (elm) {
		var id = $(elm).data('id');
		$.get('parameter/Vaksin/edit_form',{
			'id' : id
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
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

	save : function (elm) {
		let modal_body = $(elm).closest('.modal-body');

		let err = 0;

		$.map( $(modal_body).find('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');

				if ( $(ipt).hasClass('file_lampiran') ) {
					$(modal_body).find('span[name=dokumen]').css('color','#a94442');
	                $(modal_body).find('i.glyphicon-paperclip ').css('color','#a94442');
				}

				err++;
			} else {
				$(ipt).parent().removeClass('has-error');

				if ( $(ipt).hasClass('file_lampiran') ) {
					$(modal_body).find('span[name=dokumen]').css('color','#000000');
	                $(modal_body).find('i.glyphicon-paperclip ').css('color','#000000');
	            }
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			let nama_mitra = $(modal_body).find('input.nama_mitra').val();

			bootbox.confirm('Apakah anda yakin ingin menyimpan data SKP untuk peternak <b>'+ nama_mitra +'</b> ?', function (result) {
				if ( result ) {
					let nomor = $(modal_body).find('select.nomor_mitra').val();
					let nama = $(modal_body).find('input.nama_mitra').val();
					let mulai = dateSQL( $(modal_body).find('#start_date').data('DateTimePicker').date() );
					let berakhir = dateSQL( $(modal_body).find('#end_date').data('DateTimePicker').date() );

					let ipt = $(modal_body).find('input:file');
					let _filetmp = $(ipt).get(0).files[0];
                    let lampiran = {
                    	'id' : $(ipt).data('idnama'),
                        'name' : _filetmp.name,
                        'sha1' : $(ipt).attr('data-sha1'),
                    };

					let data = {
						'nomor' : nomor,
						'nama' : nama,
						'mulai' : mulai,
						'berakhir' : berakhir,
						'lampiran' : lampiran
					};

					skp.execute_save(data, _filetmp);
				};
			});
		};
	}, // end - save

	execute_save : function (data, file_tmp) {
		let formData = new FormData();
        formData.append("data", JSON.stringify(data));
		formData.append('file', file_tmp);

		$.ajax({
            url: 'parameter/SkpMitra/save',
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
                    bootbox.alert(data.message, function(){
                        skp.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_save

	// edit : function (elm) {
	// 	var err = 0;

	// 	$.map( $('[data-required=1]'), function (ipt) {
	// 		if ( empty($(ipt).val()) ) {
	// 			$(ipt).parent().addClass('has-error');
	// 			err++;
	// 		} else {
	// 			$(ipt).parent().removeClass('has-error');
	// 		};
	// 	});

	// 	if ( err > 0 ) {
	// 		bootbox.alert('Harap lengkapi data yang anda input.');
	// 	} else {
	// 		bootbox.confirm('Apakah anda yakin ingin meng-update data Vaksin ?', function (result) {
	// 			if ( result ) {
	// 				var id = $(elm).data('id');
	// 				var nama = $('input.nama_vaksin').val();
	// 				var harga = numeral.unformat( $('input.hrg_vaksin').val() );

	// 				var params = {
	// 					'id' : id,
	// 					'nama' : nama,
	// 					'harga' : harga
	// 				};

	// 				vaksin.execute_edit(params);
	// 			};
	// 		});
	// 	};
	// }, // end - edit

	// execute_edit : function (params = null) {
	// 	$.ajax({
 //            url : 'parameter/Vaksin/edit',
 //            data : {'params' : params},
 //            type : 'POST',
 //            dataType : 'JSON',
 //            beforeSend : function(){ showLoading(); },
 //            success : function(data){
 //                hideLoading();
 //                if (data.status) {
 //                    bootbox.alert(data.message, function(){
 //                        vaksin.get_list();
 //                        bootbox.hideAll();
 //                    });
 //                } else {
 //                    alertDialog(data.message);
 //                }
 //            }
 //        });
	// }, // end - execute_edit
};

skp.start_up();