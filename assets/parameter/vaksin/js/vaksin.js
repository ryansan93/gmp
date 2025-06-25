var vaksin = {
	start_up : function () {
		vaksin.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/Vaksin/get_list',
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
		$.get('parameter/Vaksin/add_form',{
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Vaksin ?', function (result) {
				if ( result ) {
					var nama = $('input.nama_vaksin').val();
					var harga = numeral.unformat( $('input.hrg_vaksin').val() );

					var params = {
						'nama' : nama,
						'harga' : harga
					};

					vaksin.execute_save(params);
				};
			});
		};
	}, // end - save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/Vaksin/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        vaksin.get_list();
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data Vaksin ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var nama = $('input.nama_vaksin').val();
					var harga = numeral.unformat( $('input.hrg_vaksin').val() );

					var params = {
						'id' : id,
						'nama' : nama,
						'harga' : harga
					};

					vaksin.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params = null) {
		$.ajax({
            url : 'parameter/Vaksin/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        vaksin.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit
};

vaksin.start_up();