var gudang = {
	start_up: function () {
		gudang.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/Gudang/get_list',
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
		$.get('parameter/Gudang/add_form',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.unit').select2();
                $('.perusahaan').select2();

                $('.modal').removeAttr('tabindex');
            });
        },'html');
	}, // end - add_form

	edit_form : function (elm) {
		var id = $(elm).data('id');
		$.get('parameter/Gudang/edit_form',{
			'id' : id
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.unit').select2();
                $('.perusahaan').select2();

                $('.modal').removeAttr('tabindex');
            });
        },'html');
	}, // end - edit_form

	save : function () {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Gudang ?', function (result) {
				if ( result ) {
					var nama = $('input.nama').val().toUpperCase();
					var alamat = $('textarea.alamat').val().toUpperCase();
					var jenis = $('select.jenis').val().toUpperCase();
					var penanggung_jawab = $('input.penanggung_jawab').val().toUpperCase();
					var unit = $('select.unit').select2('val').toUpperCase();
					var perusahaan = $('select.perusahaan').select2('val');

					var params = {
						'nama' : nama,
						'alamat' : alamat,
						'jenis' : jenis,
						'penanggung_jawab' : penanggung_jawab,
						'unit' : unit,
						'perusahaan': perusahaan
					};

					gudang.execute_save(params);
				};
			});
		};
	}, // end - save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/Gudang/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        gudang.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_save

	edit : function (elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin meng-update data Gudang ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var nama = $('input.nama').val().toUpperCase();
					var alamat = $('textarea.alamat').val().toUpperCase();
					var jenis = $('select.jenis').val().toUpperCase();
					var penanggung_jawab = $('input.penanggung_jawab').val().toUpperCase();
					var unit = $('select.unit').select2('val').toUpperCase();
					var perusahaan = $('select.perusahaan').select2('val');

					var params = {
						'id' : id,
						'nama' : nama,
						'alamat' : alamat,
						'jenis' : jenis,
						'penanggung_jawab' : penanggung_jawab,
						'unit' : unit,
						'perusahaan' : perusahaan
					};

					gudang.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params = null) {
		$.ajax({
            url : 'parameter/Gudang/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        gudang.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit
};

gudang.start_up();